<?php
session_start();
$userid = $_SESSION['userid'];
if (!empty($userid) && $userid != 0){

$servername = "localhost";
$username = "php";
$password = "password";
$dbname = "php";
$database_container_name = $_POST["submit"];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("An error occured. Please contact your administrator. Error code: dbx0875");
}

$sql = "SELECT database_container_name, cms_container_name, domain_name FROM php.containers where database_container_name = '$database_container_name'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
	exec("sudo /bin/bash /var/www/html/manage/delete.sh " .  $row["database_container_name"] . " " . $row["cms_container_name"] . " " . $row["domain_name"]);
	echo "<html><head><meta http-equiv='refresh' content='0; url= manage.php'/></head></html>";
    }
} else {
    exit;
}
$conn->close();

} 
else {
	exit;
}
?> 