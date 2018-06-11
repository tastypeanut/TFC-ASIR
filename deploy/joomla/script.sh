#!/bin/bash
rootpass=$1
dbname=$2
dbuser=$3
dbpass=$4
cmsname=$5
cmsport=$6
domname=$7

echo $*

sudo docker pull mariadb
sudo docker pull drupal
sudo docker run --restart=always -e MYSQL_ROOT_PASSWORD="$rootpass" -e MYSQL_DATABASE="$dbname" -e MYSQL_USER="$dbuser" -e MYSQL_PASSWORD="$dbpass" -v "$dbname":/var/lib/mysql -d --name "$dbname" mariadb
sudo docker run --restart=always --name "$cmsname" --link "$dbname":mysql -p "$cmsport":80 -d joomla


########################  CAMBIAR API Y DEFAULT WEBSERVER (REF_RevBacDefauWebServe), hay que instalar jq  ##########################################
########################  DESACTIVAR URL FORM HARDENING EN EL PERFIL AVANZADO DEL WAF  ######################


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
	"htmlrewrite":false,
	"htmlrewrite_cookies":false,
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

curl -k -X POST 'https://192.168.1.59:4444/api/objects/reverse_proxy/location/' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Basic dG9rZW46bXdMWEViaFJpY1lKbnZjbW50QmluQUpEaUhGa2lEalM=' \
--data @- <<END;
{
	"access_control":"0",
	"allowed_networks":["REF_NetworkAny"],
	"auth_profile":"",
	"backend":["REF_RevBacDefauWebServe"],
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

curl -k -X POST 'https://192.168.1.59:4444/api/objects/reverse_proxy/frontend/' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Basic dG9rZW46bXdMWEViaFJpY1lKbnZjbW50QmluQUpEaUhGa2lEalM=' \
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
	"htmlrewrite":false,
	"htmlrewrite_cookies":false,
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