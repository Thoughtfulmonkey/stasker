<?php

session_start(); 
session_destroy();

$validLogin = true;

if (isset($_POST['username']) && isset($_POST['password'])){

	include '../dbconnect.php';
	include '../config.php';

	$hashedpass = crypt( $_POST['password'], $secretsalt );
	
	$stmt = $db->prepare("SELECT * FROM `admin` WHERE `login`=:user AND `password`=:pwd");
	$stmt->bindValue(':user', $_POST['username'], PDO::PARAM_STR);
	$stmt->bindValue(':pwd', $hashedpass, PDO::PARAM_STR);
	$stmt->execute();
	
	//check that at least one row was returned

	$rowCheck = $stmt->rowCount();
	if($rowCheck > 0){
				
			session_start();
			$_SESSION['adminname'] =  $_POST['username'];  // Should be safe, because in DB

			header( "Location: index.php" );
	}
	else {

	  $validLogin = false;
	}
}
?>

<html>

<head>
<?php include 'admin-style.php'; ?>
</head>

<body>

	<div class="content">
		<h2 class="title">Login</h2>
		
		<form method="POST" action="login.php">
		<div style='float:left; clear:both; width:100px;'>Username:</div> <div><input type="text" name="username" size="20"></div>
		<div style='float:left; clear:both; width:100px;'>Password:</div> <div><input type="password" name="password" size="20"></div>
		<br />
		<input type="submit" value="Login" name="login">
		</form>

		<?php

		if (!$validLogin) echo "<p>Incorrect login details</p>";

		echo "</div>";

		?>
	</div>
	
</body>

</html>