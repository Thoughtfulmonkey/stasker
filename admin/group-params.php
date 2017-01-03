<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
<script src="jquery-1.7.2.min.js"></script>
<script src="common.js"></script>

<script type="text/javascript">

/*
// Use of JQuery to make the user interaction a little nicer
// - If Javascript is not turned on, then form behaves normally
// - If Javascript is turned on, then
$(document).ready(function(){
	$('#updt').hide();

	// When the add form is submitted, check to see if all values should be updated
	$("form#addParam").submit( function (e) {
		var response = confirm('Do you want to add this parameter to all existing groups (recommended)?');
		if (response) $('input[name=update]').attr('checked', true);
		else $('input[name=update]').attr('checked', false);
	});
	
});
*/

</script>

</head>

<body>
	
	<?php
		include '../dbconnect.php';
		
		$currentLocation = "Define Parameters";
		include 'standard-header.php';
	?>
	
	<div class="content">
	
		<h2 class="title">Define Parameters</h2>
	
		<?php
			
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Dealing with changes to the data - forms submissions etc.
		
			// See if edit has been saved
			if (isset($_GET['save'])){
				
				// Still need post params to save an edit
				if (isset($_POST['param'])){
					
					// Extract post variables, and update database
					$param = $_POST['param'];
					$type = $_POST['type'];
					$default = $_POST['default'];
					$saveID = $_GET['save'];
					
					// Min and max values can be NULL
					if ( ($_POST['min']=="") || ($_POST['min']=="-") ) $min = "NULL";
					else $min = $_POST['min'];
					if ( ($_POST['max']=="") || ($_POST['max']=="-") ) $max = "NULL";
					else $max = $_POST['max'];
					
					// Only save if some values were supplied
					if ($param != ""){
						if ($default != ""){
							
							$stmt = $db->prepare("UPDATE `type_params` SET `parameter`=:param WHERE `id`=:saveid");
							$stmt->bindValue(':param', $param, PDO::PARAM_STR);
							$stmt->bindValue(':saveid', $saveID, PDO::PARAM_INT);
							$stmt->execute();
							
							$stmt = $db->prepare("UPDATE `type_params` SET `type`=:type WHERE `id`=:saveid");
							$stmt->bindValue(':type', $type, PDO::PARAM_STR);
							$stmt->bindValue(':saveid', $saveID, PDO::PARAM_INT);
							$stmt->execute();
			
							$stmt = $db->prepare("UPDATE `type_params` SET `default`=:default WHERE `id`=:saveid");
							$stmt->bindValue(':default', $default, PDO::PARAM_STR);
							$stmt->bindValue(':saveid', $saveID, PDO::PARAM_INT);
							$stmt->execute();
							
							if ($min!="NULL"){
								$stmt = $db->prepare("UPDATE `type_params` SET `min`=:min WHERE `id`=:saveid");
								$stmt->bindValue(':min', $min);
								$stmt->bindValue(':saveid', $saveID, PDO::PARAM_INT);
							} else {
								$stmt = $db->prepare("UPDATE `type_params` SET `min`=NULL WHERE `id`=:saveid");
								$stmt->bindValue(':saveid', $saveID, PDO::PARAM_INT);
							}
							$stmt->execute();
							
							if ($max!="NULL"){
								$stmt = $db->prepare("UPDATE `type_params` SET `max`=:max WHERE `id`=:saveid");
								$stmt->bindValue(':max', $max);
								$stmt->bindValue(':saveid', $saveID, PDO::PARAM_INT);
							} else {
								$stmt = $db->prepare("UPDATE `type_params` SET `max`=NULL WHERE `id`=:saveid");
								$stmt->bindValue(':saveid', $saveID, PDO::PARAM_INT);
							}
							$stmt->execute();
						
						}else echo "<script type='text/javascript'>alert('You need to enter a default value for the parameter.');</script>";
						
					}else echo "<script type='text/javascript'>alert('You need to enter a name for the parameter.');</script>";
					
				}
			
			} else {

				// If there are some post details, then form must have been submitted to add a new parameter
				if (isset($_POST['param'])){
				
					// Extract post variables, and write to database
					$param = $_POST['param'];
					$type = $_POST['type'];
					$default = $_POST['default'];
					
					// Min and max values can be NULL
					if ( ($_POST['min']=="") || ($_POST['min']=="-") ) $min = "NULL";
					else $min = $_POST['min'];
					if ( ($_POST['max']=="") || ($_POST['max']=="-") ) $max = "NULL";
					else $max = $_POST['max'];
					
					// Only save if some values were supplied
					if ($param != ""){
						if ($default != ""){
							
							$stmt = $db->prepare("INSERT INTO `type_params` (`parameter`, `type`, `default`, `min`, `max`) VALUES (:param, :type, :default, :min, :max)");
							$stmt->bindValue(':param', $param, PDO::PARAM_STR);
							$stmt->bindValue(':type', $type, PDO::PARAM_STR);
							$stmt->bindValue(':default', $default, PDO::PARAM_STR);
							$stmt->bindValue(':min', $min);
							$stmt->bindValue(':max', $max);
							$stmt->execute();
						
						}else echo "<script type='text/javascript'>alert('You need to enter a default value for the parameter.');</script>";
						
					}else echo "<script type='text/javascript'>alert('You need to enter a name for the parameter.');</script>";
					

				}
			}
			
			// See if we were deleting an option
			if (isset($_GET['delete'])){
			
				$stmt = $db->prepare("DELETE FROM `type_params` WHERE `id`=:id");
				$stmt->bindValue(':id', $_GET['delete'], PDO::PARAM_INT);
				$stmt->execute();
			}
			

			// -------------------------------------------------------------------------------------------------------------------------------------
			// Display of current data
			
			echo "<table>";
			echo "<tr><th>Parameter</th><th>Type</th><th>Default value</th><th>Min</th><th>Max</th><th>Action</th></tr>";
			
			// Search for parameters
			$stmt = $db->prepare("SELECT * FROM `type_params`");
			$stmt->execute();
			$numOptions = $stmt->rowCount();
			
				
			// If there are some options, then display them in a table
			if ($numOptions > 0){
				
				// If not in edit mode
				if (!isset($_GET['edit'])){
					
					// Loop to display
					while ($row = $stmt->fetch()){
						echo "<tr><td>".$row['parameter']."</td><td>".$row['type']."</td><td>".$row['default']."</td>";
						if ( !is_null($row['min']) ) echo "<td>".$row['min']."</td>";
						else echo "<td>-</td>";
						if (!is_null($row['max']) ) echo "<td>".$row['max']."</td>";
						else echo "<td>-</td>";
						echo "<td><a href='group-params.php?edit=".$row['id']."'><img src='$stylePath/edit.png' title='edit' alt='edit' /></a>";
						echo "<a href='javascript:void(0)' onclick='confirmDelete(\"group-params.php\", ".$row['id'].")'><img src='$stylePath/del.png' title='delete' alt='delete' /></a></td>";
						echo "</tr>";
					}
				}
				else{
					// Loop to display
					while ($row = $stmt->fetch()){
						
						// Posts to same page
						echo "<form method='POST' action='group-params.php?save=".$_GET['edit']."'>";
						
						// Display form if editing a parameter
						if ( $_GET['edit'] == $row['id'] ){
							echo "<tr><td><input type='text' name='param' size='20' value='".$row['parameter']."'></td>";
							echo "<td><select name='type' id='type'>";
								if ( $row['type']=="Currency" ) echo "<option value='Currency' selected='selected'>Currency</option>";
									else echo "<option value='Currency'>Currency</option>";
								if (  $row['type']=="Number" ) echo "<option value='Number' selected='selected'>Number</option>";
									else echo "<option value='Number'>Number</option>";
								if (  $row['type']=="Text" ) echo "<option value='Text' selected='selected'>Text</option>";
									else echo "<option value='Text'>Text</option>";
							echo "<td><input type='text' name='default' size='15' value='".$row['default']."'></td>";
							echo "<td><input type='text' name='min' size='15' value='".$row['min']."'></td>";
							echo "<td><input type='text' name='max' size='15' value='".$row['max']."'></td>";
							echo "<td><input type='image' src='$stylePath/accept.png' value='Save' name='submit' title='Save' /></td>";
							echo "</tr>";
						} else {
							// Parameters not being edited
							echo "<tr><td>".$row['parameter']."</td><td>".$row['type']."</td><td>".$row['default']."</td><td>".$row['min']."</td><td>".$row['max']."</td>";
							echo "<td>-</td>";
							echo "</tr>";
						}
						echo "</form>";
					}
				}
			
			}
			else{
				echo "<p>No parameters found</p>";
			}
			

			// Move table to last row of table
			if (!isset($_GET['edit'])){
				echo "<tr>";
				echo "<form id='addParam' name='addParam' method='POST' action='group-params.php'>";
				echo "<td><input type='text' name='param' size='25' ></td>";
				echo "<td><select name='type' id='type'>";
				echo "<option value='Currency'>Currency</option>";
				echo "<option value='Number'>Number</option>";
				echo "<option value='Text'>Text</option>";
				echo "</select></td>";
				echo "<td><input type='text' name='default' size='15' ></td>";
				echo "<td><input type='text' name='min' size='15' ></td>";
				echo "<td><input type='text' name='max' size='15' ></td>";
				echo "<td><input type='image' src='$stylePath/add.png' value='Add New' name='submit' title='Add New'></td>";
				echo "</form>";
				echo "</tr>";
			}
				
			echo "</table>";
			
			
			// Removed update option:
			// <div id="updt"><input type="checkbox" name="update" value="yes" />Update existing groups</div>
			?>

		
			<h3 class="title">Synchronise Parameters</h3>
			<div>Update groups with new parameters and remove old ones: <a href="param-sync.php">Sync</a><br />
			(Existing parameters are not affected) </div>
	</div>
	
</body>

</html>