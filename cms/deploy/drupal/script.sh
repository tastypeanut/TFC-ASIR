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


virtualwebserverconfig=$(echo "
{
	"add_content_type_header":true,
	"address":"REF_DefaultInternalAddress",
	"allowed_networks":[
		"REF_NetworkAny"
	],
	"certificate":"",
	"comment":"",
	"disable_compression":false,
	"domain":[
		"$domname"
	],
	"exceptions":[],
	"htmlrewrite":true,
	"htmlrewrite_cookies":true,
	"implicitredirect":true,
	"lbmethod":"bybusyness",
	"locations":[
		"REF_RevLoc"
	],
	"name":"$domname",
	"port":80,
	"preservehost":false,
	"profile":"REF_WAFDefaultProfileAdvanced",
	"status":true,
	"type":"http",
	"xheaders":false
}")


confchecksum=$(sha256sum <<< $virtualwebserverconfig | cut -d " " -f 1)
confchecksum=${confchecksum:0:10}
confchecksum=${confchecksum^}

curl -k -X POST 'https://10.0.0.200:4444/api/objects/reverse_proxy/location/' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Basic YXBpX3VzZXI6YXBpdXNlcg==' \
--data @- <<END;
{
	"access_control":"0",
	"allowed_networks":["REF_NetworkAny"],
	"auth_profile":"",
	"backend":["REF_RevBacNginxReverProxy"],
	"be_path":"",
	"comment":"",
	"denied_networks":[],
	"hot_standby":false,
	"name":"/ ($confchecksum)",
	"path":"/",
	"status":true,
	"stickysession_id":"ROUTEID",
	"stickysession_status":false,
	"websocket_passthrough":false
}
END

curl -k -X POST 'https://10.0.0.200:4444/api/objects/reverse_proxy/frontend/' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Basic YXBpX3VzZXI6YXBpdXNlcg==' \
--data @- <<END;
{
	"add_content_type_header":true,
	"address":"REF_DefaultInternalAddress",
	"allowed_networks":[
		"REF_NetworkAny"
	],
	"certificate":"",
	"comment":"",
	"disable_compression":false,
	"domain":[
		"$domname"
	],
	"exceptions":[],
	"htmlrewrite":true,
	"htmlrewrite_cookies":true,
	"implicitredirect":true,
	"lbmethod":"bybusyness",
	"locations":[
		"REF_RevLoc$confchecksum"
	],
	"name":"$domname",
	"port":80,
	"preservehost":false,
	"profile":"REF_WAFDefaultProfileAdvanced",
	"status":true,
	"type":"http",
	"xheaders":false
}
END

sudo echo "
server {
    listen 80;
    server_name $domname;

    location / {
        proxy_pass http://10.0.1.201:$cmsport/;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    }
}
" > "$dbname"

scp -i "/opt/cms/keys/NGINXProxySSHKey.pem" $dbname ubuntu@10.0.1.200:/home/ubuntu/
ssh -i "/opt/cms/keys/NGINXProxySSHKey.pem" ubuntu@10.0.1.200 "sudo mv /home/ubuntu/$dbname /etc/nginx/sites-available && sudo ln -s /etc/nginx/sites-available/$dbname /etc/nginx/sites-enabled/$dbname"
sudo rm $dbname