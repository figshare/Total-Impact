#! /bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

source ${DIR}/lib.sh
if [ $# -lt 1 ]; then
    echo "You must supply a password for the ti user account."
    exit 2
fi
PW=$1

apt-get update
apt-get upgrade

# create new user ti
useradd -d /home/ti -m -p $(openssl passwd -crypt $PW) ti
chsh -s /bin/bash ti # use bash shell

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
apache_tune 40

# install curl and pecl/http
php_install_libs

#install python libs
apt-get install python-setuptools --assume-yes
easy_install simplejson BeautifulSoup nose