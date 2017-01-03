<?php include 'login-check.php'; ?>

<?php 
function displayNewTaskForm($stylePath, $groupType){
	
	// Move table to last row of table
	if (!isset($_GET['edit'])){
		echo "<tr>";
		echo "<form id='addAuto' name='addAuto' method='POST' action='task-auto.php'>";
		echo "<td><input type='text' name='title' size='18' /><input type='hidden' name='gtype' value='$groupType' /></td>";
		echo "<td><textarea name='desc' cols='20' rows='2'></textarea></td>";
			
		echo "<td><select name='type' id='type'><option value='bill'>bill</option><option value='payment'>payment</option></select>";
			
		echo "<td><select name='dom' id='dom'>";
		for ($dl=1; $dl<29; $dl++){
			echo "<option value='$dl'>$dl</option>";
		}
		echo "</select></td>";
		
		echo "<td><input type='text' name='calc' size='25' /></td>";
		echo "<td><a href='javascript:void(0)' onclick='displayParams();'><img src='$stylePath/view.png' /></a><br /><input type='image' src='$stylePath/accept.png' value='Save' name='submit' title='Save' /></td>";
		
		echo "</form>";
		echo "</tr>";
				
	}
}
?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
<script src="common.js"></script>


</head>

<body>
	
	<?php
		include '../dbconnect.php';
		include 'standard-header.php';
	?>
	
	<div class="content">
	
		<h2 class="title">Automatic Payments</h2>
	
		<?php
			
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Dealing with changes to the data - forms submissions etc.
		
			// See if edit has been saved
			if (isset($_GET['save'])){
				
				// Still need post params to save an edit
				if (isset($_POST['dom'])){
					
					// Extract post variables, and update database
					$title = mysql_real_escape_string($_POST['title']);
					$description = mysql_real_escape_string($_POST['desc']);
					$type = mysql_real_escape_string($_POST['type']);
					$dom = mysql_real_escape_string($_POST['dom']);
					$calc = mysql_real_escape_string($_POST['calc']);
					$saveID = mysql_real_escape_string($_GET['save']);
					
					// Only save if some values were supplied
					if ($title != ""){
						if ($description != ""){
							if ($calc != ""){
								
								mysql_query("UPDATE `task_auto` SET `title`='$title' WHERE `id`='$saveID'", $db);
								mysql_query("UPDATE `task_auto` SET `type`='$type' WHERE `id`='$saveID'", $db);
								mysql_query("UPDATE `task_auto` SET `description`='$description' WHERE `id`='$saveID'", $db);
								mysql_query("UPDATE `task_auto` SET `dom`='$dom' WHERE `id`='$saveID'", $db);
								mysql_query("UPDATE `task_auto` SET `calc`='$calc' WHERE `id`='$saveID'", $db);
								
							}else echo "<script type='text/javascript'>alert('You need to enter a value for calculating cost.');</script>";
							
						}else echo "<script type='text/javascript'>alert('You need to enter a description.');</script>";
						
					}else echo "<script type='text/javascript'>alert('You need to enter a title.');</script>";
					
				}
			
			} else {  
			

				// If there are some post details, then form must have been submitted to add a new parameter
				if (isset($_POST['title'])){
				
					// Extract post variables, and write to database
					$groupType = mysql_real_escape_string($_POST['gtype']);
					$title = mysql_real_escape_string($_POST['title']);
					$description = mysql_real_escape_string($_POST['desc']);
					$type = mysql_real_escape_string($_POST['type']);
					$dom = mysql_real_escape_string($_POST['dom']);
					$calc = mysql_real_escape_string($_POST['calc']);
					
					// Only save if some values were supplied
					if ($title != ""){
						if ($description != ""){
							if ($calc != ""){
								
								$query = "INSERT INTO `task_auto` (`group_type`, `title`, `type`, `description`, `dom`, `calc`) VALUES ('$groupType', '$title', '$type', '$description', '$dom', '$calc')";
								
								//echo "<p>$query</p>";
								
								mysql_query($query, $db);
								
							}else echo "<script type='text/javascript'>alert('You need to enter a value for calculating cost.');</script>";
									
						}else echo "<script type='text/javascript'>alert('You need to enter a description.');</script>";
								
					}else echo "<script type='text/javascript'>alert('You need to enter a title.');</script>";
				}
			}
			
			// See if we were deleting an option
			if (isset($_GET['delete'])){
			
				$id = mysql_real_escape_string($_GET['delete']);
				mysql_query("DELETE FROM `task_auto` WHERE `id`='$id'", $db);
			}
			

			// -------------------------------------------------------------------------------------------------------------------------------------
			// Display of current data
			
			// See if an auto task is being added for any of the group types
			if ( isset($_GET['addto']) ) $addID = mysql_real_escape_string($_GET['addto']);
			else $addID = -1;
			
			$gtypes = mysql_query("SELECT * FROM `group_type`");
			if ($gtypes) $numtypes = mysql_num_rows($gtypes); else $numtypes = 0;
			
			// Start of table
			echo "<table>";
			echo "<tr><th>Title</th><th>Description</th><th>Type</th><th>Day</th><th>Amount</th><th>Action</th></tr>";
			
			// Loop through all group types
			for ($glp=0; $glp<$numtypes; $glp++){
				
				// Section header for group ID
				echo "<tr><td colspan='6'>".mysql_result($gtypes, $glp, "name");
				echo " <a href='task-auto.php?addto=".mysql_result($gtypes, $glp, "id")."'><img class='bullet' src='$stylePath/add.png' title='Add Payment' alt='Add Payment' /></a>";
				echo "</td></tr>";
				
				// Search for parameters
				$result = mysql_query("SELECT * FROM `task_auto` WHERE `group_type`='".mysql_result($gtypes, $glp, "id")."'", $db);
				
				if ($result){
					$numOptions = mysql_num_rows($result);
				
					// If there are some options, then display then in a table
					if ($numOptions > 0){

						// If not in edit mode
						if (!isset($_GET['edit'])){
				
							// Loop to display
							for ($lp=0; $lp<$numOptions; $lp++){
								echo "<tr><td>".mysql_result($result, $lp, "title")."</td><td>".mysql_result($result, $lp, "description")."</td><td>".mysql_result($result, $lp, "type")."</td><td>".mysql_result($result, $lp, "dom")."</td><td>".mysql_result($result, $lp, "calc")."</td>";
								echo "<td><a href='task-auto.php?edit=".mysql_result($result, $lp, "id")."'><img src='$stylePath/edit.png' title='edit' alt='edit' /></a>";
								echo "<a href='javascript:void(0)' onclick='confirmDelete(\"task-auto.php\", ".mysql_result($result, $lp, "id").")'><img src='$stylePath/del.png' title='delete' alt='delete' /></a>";
								echo "</td>";
								echo "</tr>";
							}
						}
						else{
								
								
							// Loop to display
							for ($lp=0; $lp<$numOptions; $lp++){
									
								// Posts to same page
								echo "<form method='POST' action='task-auto.php?save=".$_GET['edit']."'>";
									
								// Display form if editing a parameter
								if ( $_GET['edit'] == mysql_result($result, $lp, "id") ){
									echo "<tr>";
									echo "<form id='addAuto' name='addAuto' method='POST' action='task-auto.php'>";
									echo "<td><input type='text' name='title' size='18' value='".mysql_result($result, $lp, "title")."'/></td>";
									echo "<td><textarea name='desc' cols='20' rows='2'>".mysql_result($result, $lp, "description")."</textarea></td>";
				
									if ( mysql_result($result, $lp, "type") == "bill") echo "<td><select name='type' id='type'><option selected=''selected' value='bill'>bill</option><option value='payment'>payment</option></select>";
									else echo "<td><select name='type' id='type'><option value='bill'>bill</option><option selected=''selected' value='payment'>payment</option></select>";
				
									echo "<td><select name='dom' id='dom'>";
									for ($dl=1; $dl<29; $dl++){
										if ( mysql_result($result, $lp, "dom") == $dl) echo "<option selected=''selected' value='$dl'>$dl</option>";
										else echo "<option value='$dl'>$dl</option>";
									}
									echo "</select></td>";
				
									echo "<td><input type='text' name='calc' size='25' value='".mysql_result($result, $lp, "calc")."' /></td>";
									echo "<td><a href='javascript:void(0)' onclick='displayParams();'><img src='$stylePath/view.png' /></a><br/><input type='image' src='$stylePath/accept.png' value='Save' name='submit' title='Save' /></td>";
									echo "</form>";
									echo "</tr>";
				
								} else {
								// Parameters not being edited
									echo "<tr><td>".mysql_result($result, $lp, "title")."</td><td>".mysql_result($result, $lp, "description")."</td><td>".mysql_result($result, $lp, "type")."</td><td>".mysql_result($result, $lp, "dom")."</td><td>".mysql_result($result, $lp, "calc")."</td>";
									echo "<td>-</td>";
									echo "</tr>";
								}
								echo "</form>";
							}
				
									
						}
			
					}
					else{
						//echo "<tr><td colspan='6'>No automatic tasks found</td></tr>";
					}
				}
				else{
					//echo "<tr><td colspan='6'>No automatic tasks found</td></tr>";
				}
				
				// Display adding for if required
				if (mysql_result($gtypes, $glp, "id")==$addID) displayNewTaskForm($stylePath, mysql_result($gtypes, $glp, "id"));
				
				// Add a horizontal divider in the table
				echo "<tr><td class='tableDivide' colspan='6'></td></tr>";
			
			}
		
			echo "</table>";
			
		?>

	</div>
	
</body>

</html>