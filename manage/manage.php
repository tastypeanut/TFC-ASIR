<?php
session_start(); //Lo primero que hay que hacer si se quieren usar variables de una sesión es abrir la sesión al principio del script.
$userid = $_SESSION['userid'];  //Con esto, se guarda la variable del id de usuario almacenada en la session en una variable nueva.
if (!empty($userid) && $userid != 0){ //Esta condición comprueba que ninguna persona que no esté autenticada pueda gestionar o borrar un contenedor.
?>

<!DOCTYPE HTML>
<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="container" style="overflow: hidden">
<div id="contact" style="overflow: hidden">

<?php
$servername = "localhost"; //Estas variables guardan datos sobre la base de datos de la que se va a borrar información.
$username = "php"; //Estas variables guardan datos sobre la base de datos de la que se va a borrar información.
$password = "password"; //Estas variables guardan datos sobre la base de datos de la que se va a borrar información.
$dbname = "php"; //Estas variables guardan datos sobre la base de datos de la que se va a borrar información.

$conn = new mysqli($servername, $username, $password, $dbname); //Esta línea de código se utiliza para iniciar una nueva conexión con la base de datos, coge los valores definidos anteriormente.

if ($conn->connect_error) { //Realiza una comprobación de la conexión con la base de datos, y si falla, sale del script y salta un mensaje de error.
    die("An error occured. Please contact your administrator. Error code: dbx0875");
}

$sql = "SELECT cms, database_container_name, domain_name FROM php.containers where user_id = $userid"; ///Con esto se forma la sentencia sql a ejecutar, que será una que saque todos los datos de los contenedores asociados a un usuario.
$result = $conn->query($sql); //Se realiza la consulta y el resultado se introduce en una variable.

if ($result->num_rows > 0) { //La función num_rows comprueba que se devuelvan más de cero filas, si esto es así, continúa con la condición.
	echo "<h3>Your launched containers</h3>";
	echo "<br/>";
    while($row = $result->fetch_assoc()) { //Coge todos los resultados de la variable por filas, y lo mete en el array "$row", y para cada valor del array, hace lo siguiente.
    	echo "<div style='width: 25%; display: inline-block; text-align: center';>";
    	switch ($row["cms"]){ //Este switch se usa para que, dependiendo del CMS, aparezca un icono u otro.
    		case "Drupal":
    			echo "<img src='logos/drupal.jpeg'/>";
    		break;
    		case "WordPress":
    			echo "<img src='logos/wordpress.jpeg'/>";
    		break;
    		case "Joomla":
    			echo "<img src='logos/joomla.jpeg'/>";
    		break;
    	}
        echo "<br/>";
        echo $row["cms"]; //Imprime el nombre del CMS, sacándolo del array.
        echo "<br/>";
        echo $row["domain_name"]; //Imprime el nombre de dominio sacándolo del array.
        echo "<br/>";
        echo "<form action='delete.php' method='post'>";
        echo "<a href='delete.php' style='text-decoration:none' onclick='return confirm(\"Are you sure you want to delete this container?\")'><button name='submit' type='submit' style='display: block; margin: auto; width: 60%; margin-bottom: 10%;' data-submit='...Sending' value='" . $row["database_container_name"] . "'>Delete Container</button></a>"; //concatena HTML y valores de un array para crear un botón que de la opción de eliminar un contenedor, y que al pulsar dicho botón, aparezca un pop-up. (El pop-up se logra con la propiedad de "onclick" de HTML). Este botón pasará como valor en un POST el nombre de la base de datos al script php "delete.php"
        echo "</form>";
        echo "</div>";
    }
    echo "<br/><br/><a href='../index.php'><button id='back' name='submit' type='submit' style='display: block; margin: auto;' data-submit='...Sending'>Go back</button></a>"; //Imprime un botón para retroceder e ir al menú anterior
} else { //Si la función num_rows devuelve cero filas, se imprime el siguiente texto y un botón para retroceder.
    echo "<h3>It seems you haven't launched anything yet.</h3>";
    echo "<br/><br/><a href='../index.php'><button id='back' name='submit' type='submit' style='display: block; margin: auto;' data-submit='...Sending'>Go back</button></a>";
}
$conn->close(); //Cierra la conexión con la base de datos
?>

</div>
</div>
</div>
</body>
</html>

<?php
} 
else {
	exit; //Si el usuario no tiene ID, o el ID es 0, el script se cierra automáticamente como medida de seguridad.
}
?>