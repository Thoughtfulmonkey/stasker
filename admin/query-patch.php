<?php include 'login-check.php'; ?>


<?php
include '../dbconnect.php';
?>
	
<?php

	mysql_query("ALTER TABLE `task_auto` CHANGE `calc` `calc` VARCHAR( 50 )");

?>
