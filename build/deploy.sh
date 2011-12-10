#! /bin/bash
source ./lib.sh

# system_update
postfix_install_loopback_only
php_install_with_apache && php_tune
apache_install && apache_tune 40 && apache_virtualhost_from_rdns
goodstuff
restartServices