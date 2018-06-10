<?php
//DEFINING VARIABLES THAT WILL BE USED LATER ON
$uniqueidbool=false;

//IMPORTED FORM VALUES
$rootpass = $_POST["rootpass"];
$dbname = $_POST["dbname"];
$dbuser = $_POST["dbuser"];
$dbpass = $_POST["dbpass"];
$cmsname = $_POST["dbname"]."cms";
$domname = $_POST["domname"];

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
while ($uniqueidbool==false){
	if (exec("sudo /bin/bash /var/www/html/deploy/drupal/tests.sh 1 $uniquedbname"))
	uniqueidgen();
	else
	$cmsport=exec("sudo /bin/bash /var/www/html/deploy/drupal/tests.sh 2");
	exec("sudo /bin/bash /var/www/html/deploy/drupal/script.sh $rootpass $uniquedbname $dbuser $dbpass $uniquecmsname $cmsport $domname");
	echo "
	The information of the site you generated is as follows:</br>

	Domain: localhost:$cmsport</br>
	Database Unique String (Name): $uniquedbname</br>
	Database User: $dbuser</br>
	Domain Name: $domname</br>
	";
	$uniqueidbool=true;
}


//exec("sudo /bin/bash /var/www/html/script.sh $rootpass $dbname $dbuser $dbpass $cmsname $cmsport");
//if ($name=="''") {
//    header('Location: '. 'index.html');
//}
//ADD EMPTY CHECKS FOR ALL REQUIRED FIELDS
//echo md5(uniqid(rand(), true));
//echo $name;
?>
