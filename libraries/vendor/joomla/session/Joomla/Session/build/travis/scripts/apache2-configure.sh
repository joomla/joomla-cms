#!/bin/sh

VHOSTNAME="virtualhost.local"
if [ "$1" ]
then
    VHOSTNAME="$1"
fi

echo "---> Applying $(tput bold ; tput setaf 2)apache2 configuration$(tput sgr0)"
echo "--> Enabling virtual host $(tput setaf 2)$VHOSTNAME$(tput sgr0)"
sudo a2enmod rewrite
sudo a2ensite $VHOSTNAME

echo "---> Restarting $(tput bold ; tput setaf 2)apache2$(tput sgr0)"

sudo /etc/init.d/apache2 restart
