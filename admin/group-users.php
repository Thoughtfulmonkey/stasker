<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
</head>

<body>
	
	<?php
		include '../dbconnect.php';
		
		$currentLocation = "<a href='group-manage.php'>Manage Groups</a> &gt; Add users to Group";
		include 'standard-header.php';
	?>
	
	<div class="content">
	
		<h2 class="title">Add Users to Group</h2>
		
		<?php
		
			if (isset($_GET['group'])){
		
				// If there are post details, then the form must have been submitted
				if (isset($_POST['submitCheck'])){
				
					// Search for all users in db
					$result = $db->prepare("SELECT * FROM user");
					$result->execute();
					$numUsers = $result->rowCount();

					// Extract list of ones that were checked
					$userList = $_POST['user'];
					
					// Loop through all users in the database
					// - seems lengthy
					while($user = $result->fetch()){
					
						//echo "<p>User $lp value ";
						
						$wasSelected = false;
						
						// If some boxes were ticked
						if(!empty($userList))
						{
							// Loop through every user's tick box
							foreach($userList as $chkval)
							{
								// See if ID for current user match one of the boxes ticked
								if($chkval == $user["id"])
								{
									// Tag to say that they were selected
									$wasSelected = true;
								}
							}
						}
						
						// If user's box was ticked
						if ($wasSelected){
							// Add user to this group
							$update = $db->prepare("UPDATE user SET `group`=:group WHERE `id`=:id");
							$update->bindParam(':group', $_GET['group'], PDO::PARAM_INT);
							$update->bindParam(':id', $user["id"], PDO::PARAM_INT);
							$update->execute();
						}
						else{
							// Ensure user is not added to this group
							$update = $db->prepare("UPDATE user SET `group`=NULL WHERE `group`=:group AND `id`=:id");
							$update->bindParam(':group', $_GET['group'], PDO::PARAM_INT);
							$update->bindParam(':id', $user["id"], PDO::PARAM_INT);
							$update->execute();
						}
						
						echo "</p>";
					}
				}
				
		?>
		
		<form method='POST' action='group-users.php?group=<?php echo $_GET['group'] ?>'>
		
		<?php
				
				// Search for all users in db
				$result = $db->prepare("SELECT user.*, group.name FROM `user` LEFT JOIN `group` ON user.group = group.id");
				$result->execute();
				$numUsers = $result->rowCount();
				
				// Display as table
				echo "<table>";
				echo "<tr><th>Login</th><th>Display name</th><th>Group Membership</th></tr>";
				
				// Loop through users
				while ($row = $result->fetch()){
					echo "<tr>";
					echo "<td>".$row["login"]."</td>";
					echo "<td>".$row["display_name"]."</td>";
					
					// If they're not the system user
					if ( $row["login"] != "System") {
					
						// If they are in this group
						if ( $row["group"] ==  $_GET['group']) {
							// Say that they are, and tick the box
							echo "<td><input type='checkbox' name='user[]' value='". $row["id"]."' checked='yes'/> This</td></tr>";
						} else {
							// If not, see if they are not in any group (NULL group)
							if ($row["name"] == ""){
								// Display that not in any group
								echo "<td><input type='checkbox' name='user[]' value='". $row["id"]."' /> <i>None</i></td></tr>";
							}else{
								// Display group that they're in.
								echo "<td><input type='checkbox' name='user[]' value='". $row["id"]."' /> <i>". $row["name"]."</i></td></tr>";
							}
						}
					
				} else echo "<td>-</td>";
					
					echo "</tr>";
				}
				
				echo "</table>";
				
				if ($numUsers==0){
					// Or possibly no users found
					echo "<p>There are no users to add.</p>";
				}
			
				// If the form was posted
				if (isset($_POST['username'])){

		?>

			<div align='center'>
				<p>User added.  They will be emailed a password.</p>
				<p><a href="admin.php">Click here</a> to return to the admin area, or create another user.</p>
			</div>

		<?php
				}
		?>
			<br />
			
				<input type="hidden" name="submitCheck" value="submitCheck">
				<div><input type="submit" value="Update" name="submit"></div>
			</form>
			
		<?php
			}
			else{
				// No group id supplied
				echo "<p>No group ID was supplied.</p>";
			}
		?>
		
	</div>

</body>

</html>