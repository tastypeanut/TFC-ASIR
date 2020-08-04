# TRABAJO FIN DE CICLO
### BIENVENID@ A MI TRABAJO FINAL PARA EL CICLO FORMATIVO DE GRADO SUPERIOR EN ADMINISTRACIÓN DE SISTEMAS INFORMÁTICOS EN RED
</br>
Este proyecto está montado sobre un VPC en AWS. El servicio que se ofrece es hosting de <i>Content Management Systems</i>. Todo el código en este repositorio pertenece a la herramienta de gestión y despliegue de dichos CMS montada sobre WordPress. El resto del proyecto está cubierto en la documentación entregada.  
</br>  

</br>

</br>  

## Estructura
El código aquí mostrado se distribuye en dos directorios principales: **/opt/cms** y **/var/www/html/website**. Para poder comenzar con él, hay que asegurarse de que todo esté en su sitio. Lo primero que se tiene que hacer es clonar el repositorio y mover cada cosa a su sitio con los comandos que se muestran a continuación:
```
git clone https://github.com/tastypeanut/Website.git && cd Website
mkdir /opt/cms && cp -r cms /opt/cms
mkdir /var/www/html/website && cp -r !(README.md) /var/www/html/website
```
