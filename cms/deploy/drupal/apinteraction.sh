#!/bin/bash

########################  CAMBIAR API Y DEFAULT WEBSERVER, hay que instalar jq  ##########################################
########################  DESACTIVAR URL FORM HARDENING EN EL PERFIL AVANZADO DEL WAF  ######################
case $1 in

1)
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
		"$2"
	],
	"exceptions":[],
	"htmlrewrite":true,
	"htmlrewrite_cookies":true,
	"implicitredirect":true,
	"lbmethod":"bybusyness",
	"locations":[
		"REF_RevLoc"
	],
	"name":"$2",
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
echo $confchecksum
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
		"$2"
	],
	"exceptions":[],
	"htmlrewrite":true,
	"htmlrewrite_cookies":true,
	"implicitredirect":true,
	"lbmethod":"bybusyness",
	"locations":[
		"REF_RevLoc$confchecksum"
	],
	"name":"$2",
	"port":80,
	"preservehost":false,
	"profile":"REF_WAFDefaultProfileAdvanced",
	"status":true,
	"type":"http",
	"xheaders":false
}
END
;;


2)
temp=$RANDOM.temp
curl -X GET \
--header 'Accept: application/json' \
--header 'Authorization: Basic dG9rZW46bXdMWEViaFJpY1lKbnZjbW50QmluQUpEaUhGa2lEalM=' \
'https://192.168.1.59:4444/api/objects/reverse_proxy/frontend/' -k -s > "$temp"
var=$(wc -l "$temp" | cut -d " " -f 1)


counter=0
IFS=', ' read -r -a array <<< $(jq '.[].domain' "$temp" | grep  \"*\" | sed 's/\"//g')
IFS=', ' read -r -a array2 <<< $(jq '.[]._ref' "$temp" | grep  \"*\" | sed 's/\"//g')
for i in "${array[@]}"; do 
	if [ $i = $2 ]; then
		ref="${array2[$counter]}"
	fi
((counter++))
done

rm "$temp"

curl -k -X DELETE --header 'Accept: application/json' \
--header 'X-Restd-Err-Ack: all' \
--header 'Authorization: Basic dG9rZW46bXdMWEViaFJpY1lKbnZjbW50QmluQUpEaUhGa2lEalM=' \
"https://192.168.1.59:4444/api/objects/reverse_proxy/frontend/$ref"
;;
esac