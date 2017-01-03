<?php
session_start();

if ( isset($_SESSION['adminname']) ) {
//if (true){
	// login is fine
}
else {
	header( 'Location: login.php' ) ;
}


?>
