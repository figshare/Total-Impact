#! /bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
pull=off

source ${DIR}/lib.sh
if [ $# -lt 1 ]; then
    echo "You must supply a password for the ti user account."
    exit 2
fi
PW=$1