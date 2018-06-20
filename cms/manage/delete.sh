#!/bin/bash
database_container_name=$1
cms_container_name=$2
domain_name=$3

echo "delete from php.containers where database_container_name = \"$database_container_name\" and cms_container_name = \"$cms_container_name\"" | mysql -u php -ppassword || exit

sudo docker kill $1
sudo docker kill $2
sudo docker rm -v $1
sudo docker rm -v $2

ssh -i "/opt/cms/keys/NGINXProxySSHKey.pem" ubuntu@10.0.1.200 "sudo rm /etc/nginx/sites-enabled/$database_container_name && sudo rm /etc/nginx/sites-available/$database_container_name && sudo service nginx reload"