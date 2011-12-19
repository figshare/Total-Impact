#! /bin/bash

# Sets up a running total-impact from a bare Ubuntu 10.04 Server install.
# You'll need to supply, when prompted, 
#   1. the password of the "ti" user that'll be created to host the files.
#   2. the passphrase to unlock db and api credentials.

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source ${DIR}/lib.sh


apt-get update
apt-get upgrade --assume-yes

# create new user ti
useradd -d /home/ti -m ti
chsh -s /bin/bash ti # use bash shell for ti
passwd ti

#download the total-impact application code
apt-get install git-core --assume-yes
cd /home/ti
git clone git://github.com/mhahnel/Total-Impact.git
chown -R ti /home/ti/Total-Impact

#install php
php_install_with_apache
php_tune

#install apache
# I'm using the "default" vhost, so that this can work from an arbitrary IP not served by DNS;
# this is useful in creating disposable, virtualised test instances.
# Restarts will throw "could not determine sever's FQDN, using 127.0.0.1...";
# this can be safely ignored (http://wiki.apache.org/httpd/CouldNotDetermineServerName)
cp ${DIR}/default /etc/apache2/sites-available/
apache_install
apache_tune 70

# install curl and pecl/http
php_install_libs

# install and start memcached
apt-get install memcached --assume-yes
memcached -u ti -d -m 24 -l 127.0.0.1 -p 11211 # 24M for now, increase if needed

#install python libs
apt-get install python-setuptools --assume-yes
easy_install simplejson BeautifulSoup nose httplib2
apt-get install python-memcache

# unpack passwords
cd /home/ti/Total-Impact/config
./build-config.sh
chmod a+w creds.ini