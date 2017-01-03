<?php
session_start();

if (isset($_SESSION['username'])) {
     // login is fine
}
else {
	header( 'Location: login.php' ) ;
}

?>
