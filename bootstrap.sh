#!/usr/bin/env bash

echo '#######################'
echo '# Install base things #'
echo '#######################'

sudo echo 'nameserver 4.2.2.4' > /etc/resolv.conf

sudo yum -y install gcc make perl wget nano git

echo '#############################'
echo '# Install nginx and php-fpm #'
echo '#############################'
sudo rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
sudo rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-6.rpm

echo '###################'
echo '# Install PHP 5.5 #'
echo '###################'
sudo yum -y --enablerepo=remi,remi-php55 install nginx php-fpm
sudo yum -y --enablerepo=remi,remi-php55 install php php-opcache php-xml php-mcrypt php-pdo php-pear php-mbstring php-gd php-mysql

echo '####################'
echo '# Install Composer #'
echo '####################'
cd /tmp
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/bin/composer
cd /tmp
wget -q https://phar.phpunit.de/phpunit.phar
mv phpunit.phar /usr/bin/phpunit
chmod +x /usr/bin/composer
chmod +x /usr/bin/phpunit

echo '#########################'
echo '# Custom Configurations #'
echo '#########################'
sudo sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php.ini
sudo sed -i "s/display_errors = .*/display_errors = On/" /etc/php.ini

mv /etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf.bak
touch /etc/nginx/conf.d/default.conf
sudo chmod 655 /etc/nginx/conf.d/default.conf

echo "server {
    server_name lg.local;
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;
    root /vagrant/public;

    index           index.php;

    location ~ \.php$ {
        root           /vagrant/public;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include        fastcgi_params;
    }
}" > /etc/nginx/conf.d/default.conf

echo '#####################################'
echo '# Start nginx and php-fpm processes #'
echo '#####################################'
sed -i '5s/.*/user vagrant/' /etc/nginx/nginx.conf
sed -i '39s/.*/user = vagrant/' /etc/php-fpm.d/www.conf
sed -i '41s/.*/group = vagrant/' /etc/php-fpm.d/www.conf

39 41

sudo /etc/init.d/nginx start
sudo /etc/init.d/php-fpm start
sudo chkconfig nginx on
sudo chkconfig php-fpm on

echo '################################'
echo '# Install Percona MySQL Server #'
echo '################################'

sudo yum -y install http://www.percona.com/downloads/percona-release/percona-release-0.0-1.x86_64.rpm
sudo yum -y install Percona-Server-client-55 Percona-Server-server-55
sudo service mysql start

echo '####################'
echo '# Installing Redis #'
echo '####################'

cd /tmp
wget -q http://redis.googlecode.com/files/redis-2.2.12.tar.gz
tar -xf redis-2.2.12.tar.gz
cd redis-2.2.12
make
make install

echo '------------------------------------------------------------'
mkdir /etc/redis /var/lib/redis
sed -e "s/^daemonize no$/daemonize yes/" -e "s/^dir \.\//dir \/var\/lib\/redis\//" -e "s/^loglevel debug$/loglevel notice/" -e "s/^logfile stdout$/logfile \/var\/log\/redis.log/" redis.conf > /etc/redis/redis.conf

echo '------------------------------------------------------------'

cd /tmp
wget -q https://gist.github.com/paulrosania/257849/raw/9f1e627e0b7dbe68882fa2b7bdb1b2b263522004/redis-server
sed -i "s/usr\/local\/sbin\/redis/usr\/local\/bin\/redis/" redis-server
chmod u+x redis-server
mv redis-server /etc/init.d
sudo chkconfig -add redis-server
sudo chkconfig redis-server on
/etc/init.d/redis-server start

echo '##################################'
echo '# Configure Percona MySQL Server #'
echo '##################################'
mysql -u root -e 'create database lg'

echo '##############################'
echo '# Installing node.js and npm #'
echo '##############################'
sudo yum -y install nodejs npm --enablerepo=epel

cd /vagrant

echo '#####################'
echo '# Install Grunt CLI #'
echo '#####################'
sudo npm install -g grunt-cli

echo '################################'
echo '# Install dependencies via npm #'
echo '################################'
cd /vagrant
sudo npm install

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

echo '##############'
echo '# Run Intern #'
echo '##############'
grunt intern
