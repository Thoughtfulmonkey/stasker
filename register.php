<html>

<head>
<?php include 'user-style.php'; ?>
</head>


<body>

<?php
include 'dbconnect.php';

// See if information has been posted
if ( isset($_POST['login']) ) $login = $_POST['login']; else $login="";
if ( isset($_POST['name']) ) $name = $_POST['name']; else $name = "";
if ( isset($_POST['password']) ) $pwd = $_POST['password']; else $pwd = "";
if ( isset($_POST['confirm']) ) $pwdCheck = $_POST['confirm']; else $pwdCheck = "";

$success = false;

if ( isset($_POST['login']) ){

	if ($login != "") {
	
		if ($name != ""){
	
			if ($pwd != ""){
				
				if ($pwd == $pwdCheck){
					
					// Actually add the user
					$success = true;
					
					$insert = $db->prepare("INSERT INTO `user` (`login`, `display_name`, `password`) VALUES (:login, :name, :password)");
					$insert->bindValue(':login', $login, PDO::PARAM_STR);
					$insert->bindValue(':name', $name, PDO::PARAM_STR);
					$insert->bindValue(':password', $pwd, PDO::PARAM_STR);
					$insert->execute();
					
					
				} else echo "<script type='text/javascript'>alert('The passwords that you entered did not match.');</script>";
				
			} else echo "<script type='text/javascript'>alert('Please enter a password.');</script>";
			
		} else echo "<script type='text/javascript'>alert('Please enter your name as well.');</script>";
	
	} else echo "<script type='text/javascript'>alert('You need to enter a login name.');</script>";
	
}

?>
	
	<div class="content">
	
		<h2 class="title">Registering an Account</h2>
		
		<?php 
		
			if (!$success){  
		
				// See if allowed to register
				$allowReg = $db->prepare("SELECT `registration_open` FROM `sim` WHERE `registration_open`='yes'");
				$allowReg->execute();
				$regFound = $allowReg->rowCount();

	
				if ($regFound){
			
					?>
					
						<div id="regForm">
						
							<form method='POST' action='register.php'>
								<table width="400px">
									<tr class="oddRow">
										<td>Choose a login name:<p class="extraInfo">This the name that you will use to login to the simulation.</p></td>
										<td><input name="login" type="text" value="<?php  echo $login; ?>" /></td>
									</tr>
									<tr>
										<td>Enter your full name:<p class="extraInfo">Firstname and Surname separated by a space.</p></td>
										<td><input name="name" type="text" value="<?php  echo $name; ?>" /></td>
									</tr>
									<tr class="oddRow">
										<td>Enter a password:<p class="extraInfo">DO NOT use a password that you use for anything else.</p></td>
										<td><input name="password" type="password" /></td>
									</tr>
									<tr>
										<td>Re-enter a password:</td>
										<td><input name="confirm" type="password" /></td>
									</tr>
									<tr>
										<td colspan="2"><div id="regForm"><input type="submit" value="Register" /></div></td>
									</tr>
								</table>
							</form>
							
						</div>
						
					<?php 
					
				} else {
					echo "<p>Registration is not allowed at this time</p>";
				}

			} else { ?>
		
			<div id="regForm">
			
				<p>Registration Complete</p>
				
				<p>You will need to be added to a group before you can login.</p>
				
				<p>Your tutor will be in touch</p>
			
			</div>
		
		<?php } ?>
		
	</div>
	
</body>

</html>