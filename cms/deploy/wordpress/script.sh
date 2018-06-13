#!/bin/bash
rootpass=$1
dbname=$2
dbuser=$3
dbpass=$4
cmsname=$5
cmsport=$6
domname=$7


sudo docker pull mariadb
sudo docker pull drupal
sudo docker run --restart=always -e MYSQL_ROOT_PASSWORD="$rootpass" -e MYSQL_DATABASE="$dbname" -e MYSQL_USER="$dbuser" -e MYSQL_PASSWORD="$dbpass" -v "$dbname":/var/lib/mysql -d --name "$dbname" mariadb
sudo docker run --restart=always --name "$cmsname" --link "$dbname":mysql -p "$cmsport":80 -d drupal

sudo echo "
server {
    listen 80;
    server_name $domname;

    location / {
        proxy_pass http://10.0.1.201:$cmsport/;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;

    }
}
" > "$dbname"

scp -i "/opt/cms/keys/NGINXProxySSHKey.pem" $dbname ubuntu@10.0.1.200:/home/ubuntu/
ssh -i "/opt/cms/keys/NGINXProxySSHKey.pem" ubuntu@10.0.1.200 "sudo mv /home/ubuntu/$dbname /etc/nginx/sites-available && sudo ln -s /etc/nginx/sites-available/$dbname /etc/nginx/sites-enabled/$dbname && sudo service nginx reload"
sudo rm $dbname