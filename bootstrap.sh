#!/usr/bin/env bash

echo '#######################'
echo '# Install base things #'
echo '#######################'

sudo echo 'nameserver 4.2.2.4' > /etc/resolv.conf

echo '#####################################'
echo '# Start nginx and php-fpm processes #'
echo '#####################################'
sed -i '5s/.*/user vagrant;/' /etc/nginx/nginx.conf
sed -i '39s/.*/user = vagrant/' /etc/php-fpm.d/www.conf
sed -i '41s/.*/group = vagrant/' /etc/php-fpm.d/www.conf

sudo /etc/init.d/nginx restart
sudo /etc/init.d/php-fpm restart
sudo chkconfig nginx on
sudo chkconfig php-fpm on

cd /vagrant

echo '################################'
echo '# Install dependencies via npm #'
echo '################################'
cd /vagrant
sudo npm install --no-bin-links

echo '#####################'
echo '# Install Grunt CLI #'
echo '#####################'
sudo npm install -g grunt-cli 

echo '#####################################'
echo '# Install dependencies via Composer #'
echo '#####################################'
grunt composer

echo '###########################'
echo '# Run database migrations #'
echo '###########################'
grunt migrate

echo '###############'
echo '# Run PHPunit #'
echo '###############'
grunt phpunit
sudo yum -y install hiredis
sudo yum --enablerepo=remi,remi-php56 install php-devel
sudo yum -y install hiredis-devel

cd /home/vagrant
sudo git clone https://github.com/nrk/phpiredis.git
cd /home/vagrant/phpiredis
sudo phpize && ./configure --enable-phpiredis
sudo make && make install

sudo echo "extension=/home/vagrant/phpiredis/modules/phpiredis.so"

sudo service php-fpm restart
