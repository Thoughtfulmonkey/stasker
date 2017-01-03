<?php

$dbHost = "localhost";
//$port = "4330";
$dbUser = "root";
$dbPass = "";
$dbDatabase = "business";


//connet to the database
try {
	// Use MySQL
	//$db = new PDO("mysql:host=$dbHost;port=$port;dbname=$dbDatabase", $dbUser, $dbPass);
	$db = new PDO("mysql:host=$dbHost;dbname=$dbDatabase", $dbUser, $dbPass);
	
} catch(PDOException $e) {
	
	echo $e->getMessage();
}
	
?>