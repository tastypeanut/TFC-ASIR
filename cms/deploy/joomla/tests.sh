#!/bin/bash

option=$1 #Guarda los valores que se le pasan como parámetros en variables
dbname=$2 #Guarda los valores que se le pasan como parámetros en variables

case "$option" in

1)
	sudo docker ps -a | cut -d " " -f 30-100 | grep -w "$dbname"  #Comprueba si el ID de la máquina que se le pasa como parámetro existe o no
	;;

2)	#Lo siguiente coge, utilizando netstat, un puerto que no se esté utilizando en el rango 49152-65535, y le devuelve el número de puerto al script PHP.
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
