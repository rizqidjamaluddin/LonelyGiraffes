#!/usr/bin/env bash

echo ">>> Starting Install Script"
 
# Update
sudo apt-get update
 
# Install MySQL without prompt
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
 
echo ">>> Installing Base Items"
 
# Install base items
sudo apt-get install -y vim tmux curl wget build-essential python-software-properties redis-server
 
echo ">>> Adding PPA's and Installing Server Items"
 
# Add repo for PHP5 and nodejs
sudo add-apt-repository -y ppa:ondrej/php5
sudo add-apt-repository -y ppa:chris-lea/node.js

# Update again, probably for good measure
sudo apt-get update
 
# Install the Rest
sudo apt-get install -y git-core php5 apache2 libapache2-mod-php5 php5-mysql php5-curl php5-gd php5-mcrypt php5-xdebug mysql-server nodejs
 
echo ">>> Configuring Server"
 
# xdebug Config
cat << EOF | sudo tee -a /etc/php5/mods-available/xdebug.ini
xdebug.scream=0
xdebug.cli_color=1
xdebug.show_local_vars=1
EOF
 
# Apache Config
sudo a2enmod rewrite
curl -L https://gist.github.com/fideloper/2710970/raw/vhost.sh > vhost
sudo chmod guo+x vhost
sudo mv vhost /usr/local/bin
sudo sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
sudo vhost -d /vagrant/public -s 192.168.33.10.xip.io

 
# PHP Config
sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php5/apache2/php.ini
sed -i "s/display_errors = .*/display_errors = On/" /etc/php5/apache2/php.ini
# Remove some disabled functions for Boris / artisan tinker
grep -E '^disable_functions' /etc/php5/cli/php.ini | sed -r 's/pcntl_(signal|fork|waitpid|signal_dispatch),//g' > /etc/php5/cli/conf.d/99-boris.ini

sudo service apache2 restart
 
echo ">>> Installing Composer"
 
# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
# Add global phpunit - do the next 2 lines if it says phpunit isn't installed
sudo composer global require 'phpunit/phpunit=3.7.*'
printf 'PATH="~/.composer/vendor/bin:$PATH"' > ~/.bash_profile


# Start redis

echo ">>> Starting Redis"
sudo redis-server /etc/redis/redis.conf

# Create main db
mysql -u root -proot -e 'create database lg'

# update dependencies
cd /vagrant
composer update -vvv

# node already installed; do other node installs here
sudo npm install -g grunt-cli

# if laravel refuses to start properly, adjust the permissions afterwards:
# chmod -R o+w /vagrant/app/storage