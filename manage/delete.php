<?php
session_start(); //Lo primero que hay que hacer si se quieren usar variables de una sesión es abrir la sesión al principio del script.
$userid = $_SESSION['userid']; //Con esto, se guarda la variable del id de usuario almacenada en la session en una variable nueva.
if (!empty($userid) && $userid != 0){ //Esta condición comprueba que ninguna persona que no esté autenticada pueda borrar un contenedor.
$servername = "localhost"; //Estas variables guardan datos sobre la base de datos de la que se va a borrar información.
$username = "php"; //Estas variables guardan datos sobre la base de datos de la que se va a borrar información.
$password = "password"; //Estas variables guardan datos sobre la base de datos de la que se va a borrar información.
$dbname = "php"; //Estas variables guardan datos sobre la base de datos de la que se va a borrar información.
$database_container_name = $_POST["submit"]; //Guarda el nombre de la instancia con la base de datos que se le ha pasado en el script manage.php en forma de POST.

$conn = new mysqli($servername, $username, $password, $dbname); //Se utiliza para establecer una conexión con la base de datos.

if ($conn->connect_error) { //Realiza una comprobación de la conexión con la base de datos, y si falla, sale del script y salta un mensaje de error.
    die("An error occured. Please contact your administrator. Error code: dbx0875");
}

$sql = "SELECT database_container_name, cms_container_name, domain_name FROM php.containers where database_container_name = '$database_container_name'"; //Introduce en una variable una consulta que saca toda la información de un contenedor y de la instancia CMS asociada a ella, de la base de datos.

$result = $conn->query($sql); //Se realiza la consulta y el resultado se introduce en una variable.

if ($result->num_rows > 0) {//La función num_rows comprueba que se devuelvan más de cero filas, si esto es así, continúa con la condición.
    while($row = $result->fetch_assoc()) { //Coge todos los resultados de la variable por filas, y lo mete en el array "$row", y para cada valor del array, hace lo siguiente.
	exec("sudo /bin/bash /opt/cms/manage/delete.sh " .  $row["database_container_name"] . " " . $row["cms_container_name"] . " " . $row["domain_name"]); //Esta línea ejecuta el script delete.sh pasándole los parámetros que necesita.
	echo "<html><head><meta http-equiv='refresh' content='0; url= manage.php'/></head></html>"; //Una vez que se termina de ejecutar lo anterior, con esto se imprime una línea en html que redirige al menú de gestión principal "manage.php"
    }
} else {
    exit; //Si la función num_rows devuelve cero filas, mata el script.
}
$conn->close(); //Cierra la conexión con la base de datos

} 
else {
	exit; //Si el usuario no tiene ID, o el ID es 0, el script se cierra automáticamente como medida de seguridad.
}
?> 