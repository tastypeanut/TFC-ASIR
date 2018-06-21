#!/bin/bash
rootpass=$1 #Guarda los valores que se le pasan como parámetros en variables
dbname=$2 #Guarda los valores que se le pasan como parámetros en variables
dbuser=$3 #Guarda los valores que se le pasan como parámetros en variables
dbpass=$4 #Guarda los valores que se le pasan como parámetros en variables
cmsname=$5 #Guarda los valores que se le pasan como parámetros en variables
cmsport=$6 #Guarda los valores que se le pasan como parámetros en variables
domname=$7 #Guarda los valores que se le pasan como parámetros en variables

sudo docker pull mariadb #Hace un pull de los contenedores que se van a usar, manteniendo así la última versión de los mismos.
sudo docker pull wordpress #Hace un pull de los contenedores que se van a usar, manteniendo así la última versión de los mismos.
sudo docker run --restart=always -e MYSQL_ROOT_PASSWORD="$rootpass" -e MYSQL_DATABASE="$dbname" -e MYSQL_USER="$dbuser" -e MYSQL_PASSWORD="$dbpass" -v "$dbname":/var/lib/mysql -d --name "$dbname" mariadb #Con este comando se despliega el contenedor que se usará como base de datos.
sudo docker run --restart=always -e WORDPRESS_DB_USER="$dbuser" -e WORDPRESS_DB_PASSWORD="$dbpass" -e WORDPRESS_DB_NAME="$dbname" -p "$cmsport":80 -v /opt/wordpress/html:/var/www/html --link "$dbname":mysql --name "$cmsname" -d wordpress #Con éste comando se despliega el contenedor que contiene el CMS y se asocia a la base de datos.

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
" > "$dbname" #Este comando introduce en un archivo la regla del proxy necesaria para que la página sea accesible desde fuera con el nombre de dominio solicitado por el usuario. En los siguientes comandos se copiará al servidor NGINX y se aplicará.

scp -i "/opt/cms/keys/NGINXProxySSHKey.pem" $dbname ubuntu@10.0.1.200:/home/ubuntu/ #Este comando pasa el archivo creado anteriormente al proxy inverso NGINX.
ssh -i "/opt/cms/keys/NGINXProxySSHKey.pem" ubuntu@10.0.1.200 "sudo mv /home/ubuntu/$dbname /etc/nginx/sites-available && sudo ln -s /etc/nginx/sites-available/$dbname /etc/nginx/sites-enabled/$dbname && sudo service nginx reload" #Este comando mueve el archivo con la regla al directorio correspondiente (/etc/nginx/sites-available/), realiza un enlace simbólico entre sites-available y sites-enabled, y recarga las configuraciones de NGINX, aplicando así la regla de redirección y activándola.
sudo rm $dbname #Este comando borra el archivo generado anteriormente.