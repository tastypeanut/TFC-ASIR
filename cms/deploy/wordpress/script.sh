#!/bin/bash
rootpass=$1
dbname=$2
dbuser=$3
dbpass=$4
cmsname=$5
cmsport=$6
domname=$7


sudo docker pull mariadb
sudo docker pull wordpress
sudo docker run --restart=always -e MYSQL_ROOT_PASSWORD="$rootpass" -e MYSQL_DATABASE="$dbname" -e MYSQL_USER="$dbuser" -e MYSQL_PASSWORD="$dbpass" -v "$dbname":/var/lib/mysql -d --name "$dbname" mariadb
sudo docker run --restart=always -e WORDPRESS_DB_USER="$dbuser" -e WORDPRESS_DB_PASSWORD="$dbpass" -e WORDPRESS_DB_NAME="$dbname" -p "$cmsport":80 -v /opt/wordpress/html:/var/www/html --link "$dbname":mysql --name "$cmsname" -d wordpress




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
--header 'Authorization: Basic dG9rZW46b29WAWPvaK51WUrYq1VzcVNJZ2jjDNhYrLnRuUfGq0Q=' \
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
--header 'Authorization: Basic dG9rZW46b29WAWPvaK51WUrYq1VzcVNJZ2jjDNhYrLnRuUfGq0Q=' \
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