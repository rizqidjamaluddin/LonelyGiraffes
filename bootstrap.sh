#!/usr/bin/env bash

#####################
#Install base things#
#####################

yum -y install gcc make perl wget nano 

##################################
#Install PHP 5.5 and dependencies#
##################################
#rpm -Uvh http://mirror.webtatic.com/yum/el6/latest.rpm
#yum -y install php55w php55w-opcache php55w-xml php55w-mcrypt php55w-sqlite php55w-pdo php55w-pear php55w-mbstring php55w-gd

###########################
#Install nginx and php-fpm#
###########################
rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-6.rpm

#touch /etc/yum.repos.d/nginx.repo

#echo "[nginx]
#name=nginx repo
#baseurl=http://nginx.org/packages/centos/6/x86_64/
#gpgcheck=0
#enabled=1" > /etc/yum.repos.d/nginx.repo

yum -y --enablerepo=remi,remi-php55 install nginx php-fpm

yum -y --enablerepo=remi,remi-php55 install php php-opcache php-xml php-mcrypt php-pdo php-pear php-mbstring php-gd

###################################
#Start nginx and php-fpm processes#
###################################
/etc/init.d/nginx start
/etc/init.d/php-fpm start
chkconfig nginx on
chkconfig php-fpm on

