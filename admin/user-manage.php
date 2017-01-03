<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
<script src="common.js"></script>
</head>

<body>
	
	<?php
		include '../dbconnect.php';
		include '../config.php';
		
		$currentLocation = "Manage Users";
		include 'standard-header.php';
	?>
	
	<div class="content">
	
		<h2 class="title">Manage Users</h2>
	
		<?php
		
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Dealing with changes to the data - forms submissions etc.
		
			// See if edit has been saved
			if (isset($_GET['save'])){
		
				// Still need post params to save an edit
				if (isset($_POST['username'])){
		
					// Extract details and write to database.
					$login = $_POST['username'];
					$display = $_POST['displayname'];
					$password = $_POST['password'];
					$saveID = $_GET['save'];
					
					// Write to DB if a reasonable amount of details have been supplied
					if ($login != ""){
						if ($display != ""){
							if ($password != ""){

								// Write to db
								$stmt = $db->prepare("UPDATE `user` SET `login`=:login WHERE `id`=:saveid");
								$stmt->bindValue(':login', $login, PDO::PARAM_STR);
								$stmt->bindValue(':saveid', $saveID, PDO::PARAM_INT);
								$stmt->execute();
									
								$stmt = $db->prepare("UPDATE `user` SET `display_name`=:displayname WHERE `id`=:saveid");
								$stmt->bindValue(':displayname', $display, PDO::PARAM_STR);
								$stmt->bindValue(':saveid', $saveID, PDO::PARAM_INT);
								$stmt->execute();
									
								$hashedpass = crypt( $password, $secretsalt );	
								
								$stmt = $db->prepare("UPDATE `user` SET `password`=:password WHERE `id`=:saveid");
								$stmt->bindValue(':password', $hashedpass, PDO::PARAM_STR);
								$stmt->bindValue(':saveid', $saveID, PDO::PARAM_INT);
								$stmt->execute();
							
							} else echo "<script type='text/javascript'>alert('A password must be set.');</script>";
							
						} else echo "<script type='text/javascript'>alert('Please also supply a Display Name.');</script>";
					
					} else echo "<script type='text/javascript'>alert('All users need a login name.');</script>";
				}
			
			} else {
				
				// If there are post details, then the form must have been submitted
				if (isset($_POST['username'])){
				
					// Extract details and write to database.
					$login = $_POST['username'];
					$display = $_POST['displayname'];
					$password = $_POST['password'];
				
					// This would probably be a function to generate a random password
					//$password = "password";
				
					// Write to DB if a reasonable amount of details have been supplied
					if ($login != ""){
						if ($display != ""){
							if ($password != "") {
							
								$hashedpass = crypt( $password, $secretsalt );
							
								// Write to db
								$stmt = $db->prepare("INSERT INTO `user` (`login`, `display_name`, `password`, `email`) VALUES (:login, :display, :password, '')");
								$stmt->bindValue(':login', $login, PDO::PARAM_STR);
								$stmt->bindValue(':display', $display, PDO::PARAM_STR);
								$stmt->bindValue(':password', $hashedpass, PDO::PARAM_STR);
								$stmt->execute();
								
							} else echo "<script type='text/javascript'>alert('A password must be set.');</script>";
								
						} else echo "<script type='text/javascript'>alert('Please also supply a Display Name.');</script>";
				
					} else echo "<script type='text/javascript'>alert('All users need a login name.');</script>";
				
				}
			}
			
			// Delete if required
			if (isset($_GET['delete'])){

				$stmt = $db->prepare("DELETE FROM `user` WHERE `id`=:id");
				$stmt->bindValue(':id', $_GET['delete'], PDO::PARAM_INT);
				$stmt->execute();
			}
			
			
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Display of current data
			
			// Search for all users in db
			// - including the one just added above if the form was posted
			$stmt = $db->prepare("SELECT `user`.*, `group`.`name` AS gname FROM `user` LEFT JOIN `group` ON `user`.`group`=`group`.`id`");
			$stmt->execute();
			$numUsers = $stmt->rowCount();
			

			// Display as table
			echo "<table>";
			echo "<tr><th>Login</th><th>Display name</th><th>Password</th><th>Group</th><th>Action</th></tr>";
			
			// If users were found
			if ($numUsers > 0){
				
				// Loop through users
				while ($row = $stmt->fetch()){
					
					// See if editing
					if (isset($_GET['edit'])){
						
						if ( $_GET['edit'] != $row['id'] ){
							
							echo "<tr>";
							
							echo "<td>".$row['login']."</td>";
							echo "<td>".$row['display_name']."</td>";
							echo "<td class='tblPwd'></td>";
							
							if ( $row['group'] != NULL ) {
								echo "<td>".$row['gname']."</td>";
							} else {
								echo "<td>-</td>";
							}
								
							echo "<td>-</td>";
							echo "</tr>";
						
						} else {
							echo "<tr>";
							echo "<form method='POST' action='user-manage.php?save=".$_GET['edit']."'>";
								
							echo "<td><input type='text' name='username' size='25' value='".$row['login']."' /></td>";
							echo "<td><input type='text' name='displayname' size='25' value='".$row['display_name']."' /></td>";
							echo "<td><input type='text' name='password' size='25' value='' /></td>";
								
							echo "<td>Change elsewhere</td>";
							
							echo "<td><input type='image' src='$stylePath/accept.png' value='Save' name='submit'></td>";
								
							echo "</form>";
							echo "</tr>";
						}
						
					} else {
						
						echo "<tr>";
						echo "<td>".$row['login']."</td>";
						echo "<td>".$row['display_name']."</td>";
						echo "<td class='tblPwd'></td>";
						
							
						if ( $row['group'] != NULL ) {
							echo "<td>".$row['gname']."</td>";
						} else {
							echo "<td>-</td>";
						}
						
						// Hide edit options for System user
						if ($row['id']!=0){
							echo "<td><a href='user-manage.php?edit=".$row['id']."'><img src='$stylePath/edit.png' title='edit' alt='edit' /></a>";
							echo "<a href='javascript:void(0)' onclick='confirmDelete(\"user-manage.php\", ".$row['id'].")'><img src='$stylePath/del.png' title='delete' alt='delete' /></a></td>";
						}else{
							echo "<td>-</td>";
						}
							
						echo "</tr>";
					}
		
				}
				
			}
			else{
				// Or possibly no users found
				echo "<p>No users are stored.</p>";
			}
			
			// Display form as last line in table
			if (!isset($_GET['edit'])){
				echo "<tr>";
				echo "<form method='POST' action='user-manage.php'>";
				echo "<td><input type='text' name='username' size='25'></td>";
				echo "<td><input type='text' name='displayname' size='25'></td>";
				echo "<td><input type='text' name='password' size='25'></td>";
				echo "<td>-</td>";
				echo "<td><input type='image' src='$stylePath/add.png' value='New' name='submit' title='New'></td>";
				echo "</form>";
				echo "<tr>";
			}
			
			echo "</table>";
		
		?>
		<br />
		
		
	</div>

</body>

</html>