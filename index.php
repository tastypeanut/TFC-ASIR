<!--USE PHP SESSION VARIABLES TO OPEN A SESSION AND STORE THE USER'S ID, SO THAT LATER ON I CAN INPUT IT INTO A DATABASE WITH THE DOCKER CONTAINER'S ID

//On page 1
$_SESSION['varname'] = $var_value;

//On page 2
$var_value = $_SESSION['varname'];

Remember to run the session_start(); statement on both these pages before you try to access the $_SESSION array, and also before any output is sent to the browser.-->

<!DOCTYPE HTML>
<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<style>
</style>
</head>
<body>
<div class="container" style="overflow: hidden">
	<div id="contact" style="overflow: hidden">
		<h3>What would you like to do?</h3>	
		<br/>
		<div>
		<a href=deploy/start.html><button name="submit" type="submit" style="margin-right: 0 px; float: left; display: inline-block;" data-submit="...Sending">Deploy CMS</button></a>
		<a href=deploy/start.html><button name="submit" type="submit" style="margin-left: 0 px; float: right; display: inline-block;" data-submit="...Sending">Manage CMS</button></a>
		</div>
	</div>
</div>
</body>
</html>


<?php
session_start();
$_SESSION["userid"] = get_current_user_id();
?>