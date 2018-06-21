<?php
session_start(); //Lo primero que hay que hacer si se quieren usar variables de una sesión es abrir la sesión al principio del script.
$uniqueidbool=false; //Define una variable que posteriormente se usará en el bucle que comprueba que el id del contenedor sea único.
$rootpass = $_POST["rootpass"]; //Con $_POST["nombre del objeto en el HTML que hace post"], se puede sacar el contenido de un campo con dicho nombre contenido en un HTML que haga POST del formulario en el que se encuentre.
$dbname = $_POST["dbname"];
$dbuser = $_POST["dbuser"];
$dbpass = $_POST["dbpass"];
$cmsname = $_POST["dbname"]."cms"; //Esto añade "cms" al final del nombre de la base de datos, para hacer una distinción entre el contenedor con el CMS y el contenedor con la base de datos.
$domname = $_POST["domname"];
$root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/'; //Esto almacena en una variable la raíz de la URL. (Por ejemplo, la raíz de myhostingservice.ddns.net/website/index.php es myhostingservice.ddns.net).
$userid = $_SESSION["userid"]; //Con esto, se guarda la variable del id de usuario almacenada en la session en una variable nueva.
$servername = "localhost"; //Estas variables guardan información sobre la base de datos en la que se van a insertar diferentes registros cuando un usuario cree un contenedor nuevo.
$username = "php"; //Estas variables guardan información sobre la base de datos en la que se van a insertar diferentes registros cuando un usuario cree un contenedor nuevo.
$password = "password"; //Estas variables guardan información sobre la base de datos en la que se van a insertar diferentes registros cuando un usuario cree un contenedor nuevo.
$databasename = "php"; //Estas variables guardan información sobre la base de datos en la que se van a insertar diferentes registros cuando un usuario cree un contenedor nuevo.
$cms = "WordPress"; //Estas variables guardan información sobre la base de datos en la que se van a insertar diferentes registros cuando un usuario cree un contenedor nuevo.
$conn = new mysqli($servername, $username, $password, $databasename); //Esta línea de código se utiliza para iniciar una nueva conexión con la base de datos, coge los valores definidos anteriormente.

if (!empty($userid) && $userid != 0){ //Esta condición comprueba que ninguna persona que no esté autenticada pueda desplegar un contenedor.
	function uniqueidgen($dbname, $cmsname){ //Esta función genera un nuevo ID de contenedor, tanto para la base de datos como para el CMS.
	global $uniquedbname;
	global $uniquecmsname;
	$idhash=md5(uniqid(rand(), true)); //Con esto se genera una serie de caracteres aleatorios que se concatenarán con los nombres de los contenedores para generar los IDs.
	$uniquedbname=$dbname.$idhash;
	$uniquecmsname=$cmsname.$idhash;
	}

	uniqueidgen($dbname, $cmsname); //Llamada a la función para el generado de IDs.

	if (!empty($rootpass) && !empty($dbname) && !empty($dbuser) && !empty($dbpass) && !empty($domname)){ //Con ésta condición se comprueba que no haya ningún campo vacío.
		while ($uniqueidbool==false){ //Condición que indica que mientras $uniqueidbool sea falso, se ejecute la condición siguiente.
			if (exec("sudo /bin/bash /opt/cms/deploy/wordpress/tests.sh 1 $uniquedbname")){ //Condición que ejecuta el script de checkeo pasándole dos parámetros que requiere dicho script, si se ejecuta bien, significa que el ID ya existe, por lo tanto, vuelve a generar un nuevo ID.
			   uniqueidgen();
			} else { //Si falla el script, significa que el ID no existe, por lo que se ejecuta todo lo que venga a continuación.
			   if ($conn->connect_error) { //Realiza una comprobación de la conexión con la base de datos, y si falla, sale del script y salta un mensaje de error.
			   die("An error occured. Please contact your administrator. Error code: dbx0875");
			   }
			   $sql = "INSERT INTO php.containers (user_id, cms, cms_container_name, database_container_name, domain_name) VALUES ( $userid, '$cms', '$uniquecmsname', '$uniquedbname', '$domname')"; //Con esto se forma la sentencia sql a ejecutar, que será la que introducirá todos los valores que se muestran, en la base de datos, para su posterior uso.
				if ($conn->query($sql) === TRUE) { //Condición que ejecuta la sentencia SQL, y si se ejecuta correctamente, pasa a realizar las acciones siguientes.

                	$cmsport=exec("sudo /bin/bash /opt/cms/deploy/wordpress/tests.sh 2"); //Esta línea ejecuta la opción 2 del script de tests, que devuelve un número de puerto que no se está utilizando en el rango 49152-65535, y mete dicho valor en una variable para su posterior uso.
                	exec("sudo /bin/bash /opt/cms/deploy/wordpress/script.sh $rootpass $uniquedbname $dbuser $dbpass $uniquecmsname $cmsport $domname"); //Ejecuta el script principal que crea todos los contenedores, e introduce las reglas necesarias en el proxy. La ejecución la realiza pasándole todos los valores necesarios al script.
                    //El siguiente echo imprime la página de ayuda una vez creados los contenedores y las reglas necesarias. Lo hace de forma dinámica, sustituyendose las variables por los valores correspondientes.
                	echo "
                    <!DOCTYPE HTML>
                    <html>
                    <head>
                    <link rel='stylesheet' type='text/css' href='style.css'>
                    <style>
                    </style>
                    </head>
                    <body>
                    <div class='container' style='max-width: 70%; overflow: auto;'>
                    <div id='contact' style='overflow: auto;'>
                	<h3 style='font-weight: bold;'>Yay! Your WordPress website was deployed successfully.</h3>
                	<h4>Follow the instructions below to get started with it.</h4>
                	<hr style='width: 20%; display: block; margin: auto;'>
                	<br/>
                	<p style='text-align: justify'>Once your WordPress website is generated, you will need to register or have already registered the domain name you entered when creating the site. You'll have to create an A registry record binding your domain name (<span style='font-weight: bold;'>$domname</span>) to the following IP: <span style='font-weight: bold;'>54.88.238.75</span>. As a temporary measure, you can add it to your 'hosts' file as shown below in the example:</p>
                	<br/>
                	<img src='screenshots/hosts.png' style='width: 80%; height: auto; display: block; margin: auto; box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);'/>
                	<br/>
                	<p style='text-align: justify'>Once the domain name is registered, or the entry is added to your 'hosts' configuration file, you'll be able to access your website simply by entering the domain name in your browser's search bar. The first thing you'll see is the WordPress installation interface, continue with the installation until finished, and from there on you're set to go!</p>
                	<br/>
                		<div style='display: block; margin: auto; width: 50%; overflow: visible;'>
                		<p style='text-align: left; font-size: 20px; font-weight: bold;'>In case you don't remember, here is your info:</p>
                		<p style='text-align: left; font-size: 14px;'><span style='font-weight: bold;'>Database Type:</span> MySQLi <p>
                		<p style='text-align: left; font-size: 14px;'><span style='font-weight: bold;'>Hostname:</span> mysql<p>
                		<p style='text-align: left; font-size: 14px;'><span style='font-weight: bold;'>Username:</span> $dbuser<p>
                		<p style='text-align: left; font-size: 14px;'><span style='font-weight: bold;'>Password:</span> The database user password that you entered before<p>
                		<p style='text-align: left; font-size: 14px;'><span style='font-weight: bold;'>Database Name:</span> $uniquedbname<p>
                		<p style='text-align: left; font-size: 14px;'><span style='font-weight: bold;'>Table Prefix:</span> Leave the default one (it may vary)<p>
                		<p style='text-align: left; font-size: 14px;'><span style='font-weight: bold;'>Domain Name:</span> $domname<p>
                	</div>
                	<br/>
                		<a href=$root><button name='submit' type='submit' style='margin-left: 2%; float: left; width: 45%; display: inline-block;' data-submit='...Sending'>Go back to home</button></a>
                		<a href='http://$domname' target='_blank'><button name='submit' type='submit' style='margin-right: 2%; float: right; width: 45%; display: inline-block;' data-submit='...Sending'>Go to launched website</button></a>
                    </div>
                    </div>
                    </body>
                    </html>
					";
					$uniqueidbool=true; //Cuando se ejecuta todo lo anterior correctamente, $uniqueidbool se fija como verdadero para que no se repita el bucle.
			   } 
			   else {
					echo "An error occured. Please contact your administrator. Error code: dbx0876"; //Muestra un mensaje de error si falla la ejecución de la sentencia SQL.
				}
			}
		}
	} 
	else {
		echo "An error occured. Please go back."; //Muestra un mensaje de error si alguno de los campos obligatorios está vacío. Aunque esta comprobación también se hace mediante HTML en la página, siempre se debe comprobar lo introducido desde el lado del servidor.
	}
} 
else {
	exit; //Si no tiene ID de usuario, el script se sale automáticamente.
}
?>
