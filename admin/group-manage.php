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
		
		$currentLocation = "Manage Groups";
		include 'standard-header.php';
	?>
	
	<div class="content">
	
		<h2 class="title">Manage Groups</h2>
	
		<?php
		
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Dealing with changes to the data - forms submissions etc.
		
			// See if saving an edit
			if (isset($_GET['save'])){
				
				// Still need post details to update
				if (isset($_POST['groupname'])){
					
					$groupname = $_POST['groupname'];
					$grouptype = $_POST['type'];
					$saveID = $_GET['save'];
					
					if ($groupname != ""){
						
						$stmt = $db->prepare("UPDATE `group` SET `name`=:groupname WHERE `id`=:saveid");
						$stmt->bindValue(':groupname', $groupname, PDO::PARAM_STR);
						$stmt->bindValue(':saveid', $saveID, PDO::PARAM_INT);
						$stmt->execute();
							
						$stmt = $db->prepare("UPDATE `group` SET `type`=:grouptype WHERE `id`=:saveid");
						$stmt->bindValue(':grouptype', $grouptype, PDO::PARAM_INT);
						$stmt->bindValue(':saveid', $saveID, PDO::PARAM_INT);
						$stmt->execute();
					
					} else echo "<script type='text/javascript'>alert('You need to supply a group name.');</script>";
				}
				
			} else {
				
				// If a group name has been supplied, then form was probably submitted
				if (isset($_POST['groupname'])){
	
					// Create an entry for the group in the database
					$groupname = $_POST['groupname'];
					$grouptype = $_POST['type'];
					
					if ($groupname != ""){
						
						$stmt = $db->prepare("INSERT INTO `group` (`name`, `type`) VALUES (:groupname, :grouptype)");
						$stmt->bindValue(':groupname', $groupname, PDO::PARAM_STR);
						$stmt->bindValue(':grouptype', $grouptype, PDO::PARAM_INT);
						$stmt->execute();
					
					} else echo "<script type='text/javascript'>alert('You need to supply a group name.');</script>";
				}
			}
			
			// Delete if required
			if (isset($_GET['delete'])){
					
				$stmt = $db->prepare("DELETE FROM `group` WHERE `id`=:id");
				$stmt->bindValue(':id', $_GET['delete'], PDO::PARAM_INT);
				$stmt->execute();
				
				// What about deleting the group's parameters?
				
				// What about removing users from this group?
			}
			
			
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Display of current data
			
			// Search for all current groups - to output as a table
			$stmt = $db->prepare("SELECT `group`.`id`, `group`.`name`, `group_type`.`name` AS type FROM `group` INNER JOIN `group_type` ON `group`.`type` = `group_type`.`id`");
			$stmt->execute();
			$numGroups = $stmt->rowCount();

			
			echo "<table>";
			echo "<tr><th>Group name</th><th>Group Type</th><th>Action</th></tr>";
			
			// If not in edit mode
			if (!isset($_GET['edit'])){
			
				// Output the table of groups
				while ($row = $stmt->fetch()){
					echo "<tr>";
					echo "<td>".$row['name']."</td>";
					echo "<td>".$row['type']."</td>";
					echo "<td><a href='group-users.php?group=".$row['id']."'><img src='$stylePath/group.png' title='add users' alt='add users' /></a>";
					echo " <a href='group-manage.php?edit=".$row['id']."' ><img src='$stylePath/edit.png' title='edit' alt='edit' /></a>";
					echo "<a href='javascript:void(0)' onclick='confirmDelete(\"group-manage.php\", ".$row['id'].")'><img src='$stylePath/del.png' title='delete' alt='delete' /></a></td>";
					echo "</tr>";
				}
				
			} else { // in edit mode
				
				// Posts to same page
				echo "<form method='POST' action='group-manage.php?save=".$_GET['edit']."'>";
				
				// Output the table of groups
				while ($row = $stmt->fetch()){
					
					// See if it is the item being edited
					if ( $_GET['edit'] == $row['id'] ){
						echo "<tr>";
						echo "<td><input type='text' name='groupname' size='20' value='".$row['name']."'></td>";
						echo "<td><select name='type' id='type'>";
							
							// Find available business types
							$grpmt = $db->prepare("SELECT * FROM `group_type`");
							$grpmt->execute();
							$numTypes = $grpmt->rowCount();
						
							while ($typerow = $grpmt->fetch()){
								echo "<option value='".$typerow['id']."'";
								
								// Set the correct option value to be selected
								if ( $row['type'] == $typerow['name'] ) echo " selected = 'selected' ";
								echo ">".$typerow['name']."</option>";
							}
							
						echo "</select></td>";
						echo "<td><input type='image' src='$stylePath/accept.png' value='Save' name='submit'></td>";
						echo "</tr>";
						
					} else {
						echo "<tr>";
						echo "<td>".$row['name']."</td>";
						echo "<td>".$row['type']."</td>";
						echo "<td>-</td>";
						echo "</tr>";
					}
					
				}
				
				// close form
				echo "</form>";
			}
			
			// Move form to last line of table
			//if (!isset($_GET['edit'])){
			if (true){	
				echo "<tr>";
				echo "<form method='POST' action='group-manage.php'>";
				echo "<td><input type='text' name='groupname' size='25'></td>";
				echo "<td><select name='type' id='type'>";

				// Find available business types
				$grpmt = $db->prepare("SELECT * FROM `group_type`");
				$grpmt->execute();
				$numTypes = $grpmt->rowCount();
				
				while ($typerow = $grpmt->fetch()){
					echo "<option value='".$typerow['id']."'>".$typerow['name']."</option>";
				}
				

				echo "</select></td>";
				echo "<td><input type='image' src='$stylePath/add.png' value='Add New' name='submit' title='Add New'></td>";
				echo "</form>";
				echo "</tr>";
			}
			
			echo "</table>";

		
		?>
		
		<br />
		
	
	</div>

</body>

</html>