<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
<script src="jquery-1.7.2.min.js"></script>
<script src="common.js"></script>

</head>


<body>

<?php
include '../dbconnect.php';

$currentLocation = "Group Types";
include 'standard-header.php';
?>
	
	<div class="content">
	
		<h2 class="title">Group Types</h2>
	
		<?php
		
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Dealing with changes to the data - forms submissions etc.
		
			// See if edit has been saved
			if (isset($_GET['save'])){
				
				// Still need post params to save an edit
				if (isset($_POST['grouptype'])){
					
					// Save changes
					// Check that the supplied type isn't blank
					if ($_POST['grouptype'] != ""){
					
						// Create an entry for the group in the database
						$stmt = $db->prepare("UPDATE `group_type` SET `name`=:grouptype WHERE `id`=:saveid");
						$stmt->bindValue(':grouptype', $_POST['grouptype'], PDO::PARAM_STR);
						$stmt->bindValue(':saveid', $_GET['save'], PDO::PARAM_INT);
						$stmt->execute();
							
					} else { // Error message
						echo "<script type='text/javascript'>alert('The supplied name was blank.');</script>";
					}
				}
				
			} else {
				// Not being saved, but see if post details were submitted
		
				// If a group type has been supplied, then form was probably submitted
				if (isset($_POST['grouptype'])){
	
					// Check that the supplied type isn't blank
					if ($_POST['grouptype'] != ""){
						
						// Create an entry for the group in the database
						$stmt = $db->prepare("INSERT INTO `group_type` (name) VALUES (:grouptype)");
						$stmt->bindValue(':grouptype', $_POST['grouptype'], PDO::PARAM_STR);
						$stmt->execute();
					
					} else { // Error message
						echo "<script type='text/javascript'>alert('You need to enter a name for the new business type.');</script>";
					}
				}
				
			}
			
			// See if we were deleting an option
			if (isset($_GET['delete'])){
				
				$stmt = $db->prepare("DELETE FROM `group_type` WHERE `id`=:id");
				$stmt->bindValue(':id', $_GET['delete'], PDO::PARAM_INT);
				$stmt->execute();
			}

			
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Display of current data
			
			// Search for all current groups	
			$stmt = $db->prepare("SELECT * FROM `group_type`");
			$stmt->execute();
			$numTypes = $stmt->rowCount();
			
			// If some groups were found
			if ($numTypes > 0){
				echo "<table>";
				echo "<tr><th>Business Type</th><th>Action</th></tr>";
				
				// Output the table of groups
				while ($row = $stmt->fetch()){
					
					// Show form if editing
					if (isset($_GET['edit'])){
					
						// Only show edit/delete options for types other than none
						if ( $row['id'] == $_GET['edit']){
							echo "<tr>";
							echo "<form  method='POST' action='group-types.php?save=".$_GET['edit']."'>";
							echo "<td><input type='text' name='grouptype' size='25' value='".$row['name']."' /></td>";
							echo "<td><input type='image' src='$stylePath/accept.png' value='Save' name='submit'></td>";
							echo "</form></tr>";
						} else {
							echo "<tr>";
							echo "<td>".$row['name']."</td>";
							echo "<td>-</td>";
							echo "</tr>";
						}
						
					} else { // Not editing
						
						echo "<tr>";
						echo "<td>".$row['name']."</td>";
						
						// Only show edit/delete options for types other than none
						if ( $row['id'] != "0"){
							echo "<td><a href='group-types.php?edit=".$row['id']."'><img src='$stylePath/edit.png' title='edit' alt='edit' /></a>";
							echo "<a href='javascript:void(0)' onclick='confirmDelete(\"group-types.php\", ".$row['id'].")'><img src='$stylePath/del.png' title='delete' alt='delete' /></a></td>";
						} else echo "<td></td>";	
						echo "</tr>";
					}
				}
				
				// Move the new item form here
				if (!isset($_GET['edit'])){
					echo "<tr>";
					echo "<form method='POST' action='group-types.php'>";
					echo "<td><input type='text' name='grouptype' size='25'></td>";
					echo "<td><input type='image' src='$stylePath/add.png' value='Add New' name='submit' title='Add New'></td>";
					echo "</form>";
					echo "</tr>";
				}
				
				echo "</table>";
			}
			else{
				// Or possibly no groups were found
				echo "<p>No types found - which is bad</p>";
				/*
				$db->query("INSERT INTO `group_type` (`id`, `name`) VALUES (0, 'None')");
				echo "<p>Attempting to add the NONE group type.</p>";
				
				$db->query("UPDATE `group_type` SET `id`=0 WHERE `name`='None'");
				echo "<p>Attempting to reset NONE type.</p>";
				
				echo "<p><a href='group-types.php'>Reload the page</a></p>";
				*/
			}
		
			
		?>
		<br />
		
	
	</div>

</body>

</html>
