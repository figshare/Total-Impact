#!/bin/bash
#
# StackScript Bash Library
#
# Copyright (c) 2010 Linode LLC / Christopher S. Aker <caker@linode.com>
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without modification,
# are permitted provided that the following conditions are met:
#
# * Redistributions of source code must retain the above copyright notice, this
# list of conditions and the following disclaimer.
#
# * Redistributions in binary form must reproduce the above copyright notice, this
# list of conditions and the following disclaimer in the documentation and/or
# other materials provided with the distribution.
#
# * Neither the name of Linode LLC nor the names of its contributors may be
# used to endorse or promote products derived from this software without specific prior
# written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
# EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
# OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT
# SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
# INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
# TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
# BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
# CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
# ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
# DAMAGE.


# From Jason: I've modified several functions here.


###########################################################
# System
###########################################################

function system_update {
	apt-get update
	apt-get -y install aptitude
	aptitude -y full-upgrade
}

function system_primary_ip {
	# returns the primary IP assigned to eth0
	echo $(ifconfig eth0 | awk -F: '/inet addr:/ {print $2}' | awk '{ print $1 }')
}

function get_rdns {
	# calls host on an IP address and returns its reverse dns

	if [ ! -e /usr/bin/host ]; then
		aptitude -y install dnsutils > /dev/null
	fi
	echo $(host $1 | awk '/pointer/ {print $5}' | sed 's/\.$//')
}

function get_rdns_primary_ip {
	# returns the reverse dns of the primary IP assigned to this system
	echo $(get_rdns $(system_primary_ip))
}

###########################################################
# Postfix
###########################################################

function postfix_install_loopback_only {
	# Installs postfix and configure to listen only on the local interface. Also
	# allows for local mail delivery

	echo "postfix postfix/main_mailer_type select Internet Site" | debconf-set-selections
	echo "postfix postfix/mailname string localhost" | debconf-set-selections
	echo "postfix postfix/destinations string localhost.localdomain, localhost" | debconf-set-selections
	aptitude -y install postfix
	/usr/sbin/postconf -e "inet_interfaces = loopback-only"
	#/usr/sbin/postconf -e "local_transport = error:local delivery is disabled"

	touch /tmp/restart-postfix
}


###########################################################
# Apache
###########################################################

function apache_install {
	# installs the system default apache2 MPM
	aptitude -y install apache2

	# a2dissite default # disable the interfering default virtualhost

	# clean up, or add the NameVirtualHost line to ports.conf
	sed -i -e 's/^NameVirtualHost \*$/NameVirtualHost *:80/' /etc/apache2/ports.conf
	if ! grep -q NameVirtualHost /etc/apache2/ports.conf; then
		echo 'NameVirtualHost *:80' > /etc/apache2/ports.conf.tmp
		cat /etc/apache2/ports.conf >> /etc/apache2/ports.conf.tmp
		mv -f /etc/apache2/ports.conf.tmp /etc/apache2/ports.conf
	fi
        a2enmod rewrite
}

function apache_tune {
	# Tunes Apache's memory to use the percentage of RAM you specify, defaulting to 40%

	# $1 - the percent of system memory to allocate towards Apache

	if [ ! -n "$1" ];
		then PERCENT=40
		else PERCENT="$1"
	fi

	aptitude -y install apache2-mpm-prefork
	PERPROCMEM=10 # the amount of memory in MB each apache process is likely to utilize
	MEM=$(grep MemTotal /proc/meminfo | awk '{ print int($2/1024) }') # how much memory in MB this system has
	MAXCLIENTS=$((MEM*PERCENT/100/PERPROCMEM)) # calculate MaxClients
	MAXCLIENTS=${MAXCLIENTS/.*} # cast to an integer
	sed -i -e "s/\(^[ \t]*MaxClients[ \t]*\)[0-9]*/\1$MAXCLIENTS/" /etc/apache2/apache2.conf

	apache2ctl graceful
}

function apache_virtualhost {
	# Configures a VirtualHost

	# $1 - required - the hostname of the virtualhost to create

	if [ ! -n "$1" ]; then
		echo "apache_virtualhost() requires the hostname as the first argument"
		return 1;
	fi

	if [ -e "/etc/apache2/sites-available/$1" ]; then
		echo /etc/apache2/sites-available/$1 already exists
		return;
	fi

	mkdir -p /srv/www/$1/public_html /srv/www/$1/logs

	echo "<VirtualHost *:80>" > /etc/apache2/sites-available/$1
	echo "    ServerName $1" >> /etc/apache2/sites-available/$1
	echo "    DocumentRoot /srv/www/$1/public_html/" >> /etc/apache2/sites-available/$1
	echo "    ErrorLog /srv/www/$1/logs/error.log" >> /etc/apache2/sites-available/$1
    echo "    CustomLog /srv/www/$1/logs/access.log combined" >> /etc/apache2/sites-available/$1
	echo "</VirtualHost>" >> /etc/apache2/sites-available/$1

	a2ensite $1

	apache2ctl graceful
}

function apache_virtualhost_from_rdns {
	# Configures a VirtualHost using the rdns of the first IP as the ServerName

	apache_virtualhost $(get_rdns_primary_ip)
}


function apache_virtualhost_get_docroot {
	if [ ! -n "$1" ]; then
		echo "apache_virtualhost_get_docroot() requires the hostname as the first argument"
		return 1;
	fi

	if [ -e /etc/apache2/sites-available/$1 ];
		then echo $(awk '/DocumentRoot/ {print $2}' /etc/apache2/sites-available/$1 )
	fi
}


###########################################################
# PHP functions
###########################################################

function php_install_with_apache {
	aptitude -y install php5 php5-mysql libapache2-mod-php5
	apache2ctl graceful
}

function php_tune {
	# Tunes PHP to utilize up to 32M per process

	sed -i'-orig' 's/memory_limit = [0-9]\+M/memory_limit = 32M/' /etc/php5/apache2/php.ini
	apache2ctl graceful
}

function php_install_pecl_http {
    apt-get install php5-dev --assume-yes
    apt-get install libcurl4-gnutls-dev --assume-yes
    apt-get install php-pear  --assume-yes
    printf "\n" | pecl install pecl_http # accept default at all prompts
    cp /usr/lib/php5/20090626+lfs/http.so /usr/lib/apache2/modules/
    touch /etc/php5/conf.d/http.ini
    echo "extension=http.so" > /etc/php5/conf.d/http.ini
    apache2ctl graceful
}

###########################################################
# Other niceties!
###########################################################

function goodstuff {
	# Installs the REAL vim, wget, less, and enables color root prompt and the "ll" list long alias

	aptitude -y install wget vim less
	sed -i -e 's/^#PS1=/PS1=/' /root/.bashrc # enable the colorful root bash prompt
	sed -i -e "s/^#alias ll='ls -l'/alias ll='ls -al'/" /root/.bashrc # enable ll list long alias <3
}


###########################################################
# utility functions
###########################################################

function restartServices {
	# restarts services that have a file in /tmp/needs-restart/

	for service in $(ls /tmp/restart-* | cut -d- -f2-10); do
		/etc/init.d/$service restart
		rm -f /tmp/restart-$service
	done
}

function randomString {
	if [ ! -n "$1" ];
		then LEN=20
		else LEN="$1"
	fi

	echo $(</dev/urandom tr -dc A-Za-z0-9 | head -c $LEN) # generate a random string
}