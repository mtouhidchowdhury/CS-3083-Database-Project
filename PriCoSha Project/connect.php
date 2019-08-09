<?php
$user= 'root';
$pass = '';
$db = 'pricoshare';
$mysqli= new mysqli("localhost",$user, $pass,$db) or die("unable to connect");
echo 'Great Work!, You are connected to Pricoshare now!<br>';

//var_dump($mysqli);
?>