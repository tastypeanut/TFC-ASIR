#!/bin/bash
database_container_name=$1
cms_container_name=$2
domain_name=$3

echo "delete from php.containers where database_container_name = \"$database_container_name\" and cms_container_name = \"$cms_container_name\"" | mysql -u php -ppassword || exit

sudo docker kill $1
sudo docker kill $2
sudo docker rm -v $1
sudo docker rm -v $2

temp=$RANDOM.temp
curl -X GET \
--header 'Accept: application/json' \
--header 'Authorization: Basic dG9rZW46b29WAWPvaK51WUrYq1VzcVNJZ2jjDNhYrLnRuUfGq0Q=' \
'https://10.0.0.200:4444/api/objects/reverse_proxy/frontend/' -k -s > "$temp"
var=$(wc -l "$temp" | cut -d " " -f 1)


counter=0
IFS=', ' read -r -a array <<< $(jq '.[].domain' "$temp" | grep  \"*\" | sed 's/\"//g')
IFS=', ' read -r -a array2 <<< $(jq '.[]._ref' "$temp" | grep  \"*\" | sed 's/\"//g')
for i in "${array[@]}"; do 
	if [ $i = $3 ]; then
		ref="${array2[$counter]}"
	fi
((counter++))
done

rm "$temp"

curl -k -X DELETE --header 'Accept: application/json' \
--header 'X-Restd-Err-Ack: all' \
--header 'Authorization: Basic dG9rZW46b29WAWPvaK51WUrYq1VzcVNJZ2jjDNhYrLnRuUfGq0Q=' \
"https://10.0.0.200:4444/api/objects/reverse_proxy/frontend/$ref"