<?php

session_start(); 
session_destroy();

$validLogin = true;

if (isset($_POST['username']) && isset($_POST['password'])){

	include 'dbconnect.php';
	include 'config.php';

	//$user = $_POST['username'];
	//$pass = $_POST['password'];
	//$result=mysql_query("SELECT * FROM `user` WHERE `login`='$user' AND `password`='$pass'", $db);
	
	$hashedpass = crypt( $_POST['password'], $secretsalt );
	
	$check = $db->prepare("SELECT * FROM `user` WHERE `login`=:user AND `password`=:password");
	$check->bindValue(":user", $_POST['username'], PDO::PARAM_STR);
	$check->bindValue(":password", $hashedpass, PDO::PARAM_STR);
	$check->execute();
	
	//check that at least one row was returned
	if($check->rowCount() > 0){
			
			$userDetails = $check->fetch();
		
			session_start();
			$_SESSION['username'] = $userDetails['login'];
			//$_SESSION['password'] = $check['password'];	// Shouldn't save the password
			$_SESSION['fullname'] = $userDetails['display_name'];
			$_SESSION['groupnum'] = $userDetails['group'];

			header( "Location: index.php" );
	}
	else {

	  $validLogin = false;
	}
}
?>

<html>

<head>
<?php include 'user-style.php'; ?>
</head>

<body>
	<?php
		include 'dbconnect.php';
	?>

	<div class="content">
		<h2 class="title">Login</h2>
		
		<form method="POST" action="login.php">
		<div class="formElement">Username: <input type="text" name="username" size="20"></div>
		<div class="formElement">Password: <input type="password" name="password" size="20"></div>
		<br><input type="submit" value="Login" name="login">
		</form>

		<?php

		if (!$validLogin) echo "<p>Incorrect login details</p>";

		echo "</div>";

		?>
	</div>
	
</body>

</html>