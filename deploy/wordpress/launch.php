<?php
session_start();
//DEFINING VARIABLES THAT WILL BE USED LATER ON
$uniqueidbool=false;

//IMPORTED FORM VALUES
$rootpass = $_POST["rootpass"];
$dbname = $_POST["dbname"];
$dbuser = $_POST["dbuser"];
$dbpass = $_POST["dbpass"];
$cmsname = $_POST["dbname"]."cms";
$domname = $_POST["domname"];
$root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
$userid = $_SESSION["userid"];
$servername = "localhost";
$username = "php";
$password = "password";
$databasename = "php";
$cms = "WordPress";
// Create connection
$conn = new mysqli($servername, $username, $password, $databasename);

if (!empty($userid) && $userid != 0){
//FUNCTION TO GENERATE UNIQUE NAMES FOR THE CONTAINERS
	function uniqueidgen($dbname, $cmsname){
	global $uniquedbname;
	global $uniquecmsname;
	$idhash=md5(uniqid(rand(), true));
	$uniquedbname=$dbname.$idhash;
	$uniquecmsname=$cmsname.$idhash;
	}

//CALLING THE FUNCTION TO GENERATE UNIQUE IDS AT SCRIPT START
	uniqueidgen($dbname, $cmsname);

//CHECK TO REALLY MAKE SURE NAMES DO NOT EXIST BEFORE CREATING THE CONTAINERS
	if (!empty($rootpass) && !empty($dbname) && !empty($dbuser) && !empty($dbpass) && !empty($domname)){
		while ($uniqueidbool==false){
			if (exec("sudo /bin/bash /opt/cms/deploy/wordpress/tests.sh 1 $uniquedbname")){
			   uniqueidgen();
			} else { 

			   // Check connection
			   if ($conn->connect_error) {
			   die("An error occured. Please contact your administrator. Error code: dbx0875");
			   }
			   $sql = "INSERT INTO php.containers (user_id, cms, cms_container_name, database_container_name, domain_name) VALUES ( $userid, '$cms', '$uniquecmsname', '$uniquedbname', '$domname')";
				if ($conn->query($sql) === TRUE) {

                	$cmsport=exec("sudo /bin/bash /opt/cms/deploy/wordpress/tests.sh 2");
                	exec("sudo /bin/bash /opt/cms/deploy/wordpress/script.sh $rootpass $uniquedbname $dbuser $dbpass $uniquecmsname $cmsport $domname");
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
					$uniqueidbool=true;
			   } 
			   else {
					echo "An error occured. Please contact your administrator. Error code: dbx0876";
				}
			}
		}
	} 
	else {
		echo "An error occured. Please go back.";
	}
} 
else {
	exit;
}
?>
