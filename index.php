<?php
$name = escapeshellarg($_POST["name"]);
echo $name;
exec("sudo bash script.sh $name");
if ($name=="''") {
    header('Location: '. 'index.html');
}
?>
