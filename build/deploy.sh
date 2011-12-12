#! /bin/bash
source ./lib.sh
if [ $# -lt 1 ]; then
    echo "You must supply a password for the ti user account."
    exit 2
fi
PW=$1

# system_update
#postfix_install_loopback_only
#php_install_with_apache && php_tune
#apache_install && apache_tune 40 && apache_virtualhost_from_rdns
#goodstuff
#restartServices

useradd -d /home/ti -m -p $(openssl passwd -crypt $PW) ti
# TODO add basic console helps to this account, like tab completion, highlighting etc.
apt-get install git-core --assume-yes
cd /home/ti
git clone git://github.com/mhahnel/Total-Impact.git
chown -R ti /home/ti/Total-Impact
apache_install
apache_new_default_vhost