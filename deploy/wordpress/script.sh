#!/bin/bash
rootpass=$1
dbname=$2
dbuser=$3
dbpass=$4
cmsname=$5
cmsport=$6

sudo docker pull mariadb
sudo docker pull wordpress
sudo docker run --restart=always -e MYSQL_ROOT_PASSWORD="$rootpass" -e MYSQL_DATABASE="$dbname" -e MYSQL_USER="$dbuser" -e MYSQL_PASSWORD="$dbpass" -v "$dbname":/var/lib/mysql -d --name "$dbname" mariadb
sudo docker run --restart=always -e WORDPRESS_DB_USER="$dbuser" -e WORDPRESS_DB_PASSWORD="$dbpass" -e WORDPRESS_DB_NAME="$dbname" -p "$cmsport":80 -v /opt/wordpress/html:/var/www/html --link "$dbname":mysql --name "$cmsname" -d wordpress