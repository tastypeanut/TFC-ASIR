#!/bin/bash

option=$1
dbname=$2

case "$option" in

1)
	sudo docker ps -a | cut -d " " -f 30-100 | grep -w "$dbname"
	;;

2)
	BASE='49152'
	INCREMENT='1'
	port=$BASE
	isfree=$(netstat -tapln | grep $port)
	while [ -n "$isfree" ]; do
		port=$((port+INCREMENT))
		isfree=$(netstat -tapln | grep $port)
	done
	echo "$port"
	;;
esac
