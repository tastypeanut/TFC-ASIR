<?php
session_start();
$userid = $_SESSION['userid'];
if (!empty($userid) && $userid != 0){
?>

<!DOCTYPE HTML>
<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<head>
</head>
<body>
<div class="container" style="overflow: hidden">
<div id="contact" style="overflow: hidden">

<?php
$servername = "localhost";
$username = "php";
$password = "password";
$dbname = "php";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT cms, database_container_name, domain_name FROM php.containers where user_id = $userid";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	echo "<h3>Your launched containers</h3>";
	echo "</br>";
    // output data of each row
    while($row = $result->fetch_assoc()) {
    	echo "<div style='width: 25%; display: inline-block; text-align: center';>";
    	switch ($row["cms"]){
    		case "Drupal":
    			echo "<img src='logos/drupal.jpeg'/></br>";
    		break;
    		case "WordPress":
    			echo "<img src='logos/wordpress.jpeg'/></br>";
    		break;
    		case "Joomla":
    			echo "<img src='logos/joomla.jpeg'/></br>";
    		break;
    	}
        echo "</br>"
        echo $row["cms"];
        echo "</br>";
        echo $row["domain_name"];
        echo "</br>";
        echo "<form action='delete.php' method='post'>";
        echo "<a href='delete.php' style='text-decoration:none' onclick='return confirm(\"Are you sure you want to delete this container?\")'><button name='submit' type='submit' style='display: block; margin: auto; width: 60%; margin-bottom: 10%;' data-submit='...Sending' value='" . $row["database_container_name"] . "'>Delete Container</button></a>";
        echo "</form>";
        echo "</div>";
    }
    echo "<br/><br/><a href='../index.php'><button id='back' name='submit' type='submit' style='display: block; margin: auto;' data-submit='...Sending'>Go back</button></a>";
} else {
    echo "<h3>It seems you haven't launched anything yet.</h3>";
    echo "<br/><br/><a href='../index.php'><button id='back' name='submit' type='submit' style='display: block; margin: auto;' data-submit='...Sending'>Go back</button></a>";
}
$conn->close();
?>

</div>
</div>
</div>
</body>
</html>

<?php
} 
else {
	exit;
}
?>