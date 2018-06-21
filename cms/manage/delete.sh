#!/bin/bash
database_container_name=$1 #Guarda los valores que se le pasan como parámetros en variables
cms_container_name=$2 #Guarda los valores que se le pasan como parámetros en variables
domain_name=$3 #Guarda los valores que se le pasan como parámetros en variables

echo "delete from php.containers where database_container_name = \"$database_container_name\" and cms_container_name = \"$cms_container_name\"" | mysql -u php -ppassword || exit #Este comando le pasa a mysql la sentencia SQL que borra el registro de las instancias que se van a borrar a continuación.

sudo docker kill $1 #Mata el contenedor con la base de datos
sudo docker kill $2 #Mata el contenedor con el CMS
sudo docker rm -v $1 #Borra el contenedor con la base de datos
sudo docker rm -v $2 #Borra el contenedor con el CMS

ssh -i "/opt/cms/keys/NGINXProxySSHKey.pem" ubuntu@10.0.1.200 "sudo rm /etc/nginx/sites-enabled/$database_container_name && sudo rm /etc/nginx/sites-available/$database_container_name && sudo service nginx reload" #Se conecta al servidor proxy, borra el enlace simbólico y el archivo con la regla correspondientes, y recarga las configuraciones de NGINX.