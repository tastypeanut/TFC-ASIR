#!/bin/bash
rootpass=$1
dbname=$2
dbuser=$3
dbpass=$4
cmsname=$5
cmsport=$6


sudo docker pull mariadb
sudo docker pull drupal
sudo docker run --restart=always -e MYSQL_ROOT_PASSWORD="$rootpass" -e MYSQL_DATABASE="$dbname" -e MYSQL_USER="$dbuser" -e MYSQL_PASSWORD="$dbpass" -v "$dbname":/var/lib/mysql -d --name "$dbname" mariadb
sudo docker run --restart=always --name "$cmsname" --link "$dbname":mysql -p "$cmsport":80 -d joomla