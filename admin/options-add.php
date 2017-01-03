<?php include 'login-check.php'; ?>


<?php 

// The form to add new effects onto an option
function echoEffectAddForm($taskInfo, $option, $db, $stylePath){
	
	echo "<form method='POST' action='options-add.php?id=$taskInfo'>";

	echo "<input type='hidden' name='parentOption' value='$option' />";
	
	echo "<td><select name='affect' id='affect' onchange='affectWhat();'>";
	echo "<option value='parameter' selected='selected'>Change Parameter</option><option value='task'>Trigger Task</option><option value='script'>Trigger Script</option>";
	echo "</select></td>";
	
	// Choice box - either param, task, or script name
	echo "<td>";
	
	// The parameter selection
	echo "<div id='affectParam'>";
	echo "<p class='tableDescription'>Which parameter:</p>";
	echo "<select name='affparam' id='affparam'>";
	// Search for all parameters
	$stmt = $db->prepare("SELECT `id`, `parameter` FROM `type_params`");
	$stmt->bindParam(':id', $tid, PDO::PARAM_INT);
	$stmt->execute();
	// Loop to output parameters
	while ($row = $stmt->fetch()){
		echo "<option value='".$row['id']."'>".$row['parameter']."</option>";
	}
	echo "</select>";
	echo "</div>";
	
	// The task selection
	echo "<div id='affectTask'>";
	echo "<p class='tableDescription'>Which task:</p>";
	echo "<select name='afftask' id='afftask'>";
	// Search for all tasks
	$stmt = $db->prepare("SELECT id, title FROM task_info");
	$stmt->bindParam(':id', $tid, PDO::PARAM_INT);
	$stmt->execute();
	// Loop to output tasks
	while ($row = $stmt->fetch()){
		echo "<option value='".$row['id']."'>".$row['title']."</option>";
	}
	echo "</select>";
	echo "</div>";
		
	// Entry of script name
	echo "<div id='affectScript'>";
	echo "<p class='tableDescription'>Which script:</p>";
	echo "<input type='text' name='affscript' size='25' />";
	echo "</div>";
		
	echo "</td>";
	
	// Display different input boxes depending on choices
	echo "<td>";
	echo "<div id='valueParam'>";
	echo "<p class='tableDescription'>(Parameter) What effect:</p>";
	echo "<input type='text' name='affmod' size='20' />";
	echo "</div>";
	echo "<div id='valueTask'>";
	echo "<p class='tableDescription'>(Task) In how many days:</p>";
	echo "<input type='text' name='affdaysToTask' size='20' />";
	echo "</div>";
	echo "<div id='valueScript'>";
	echo "<p class='tableDescription'>(Script) Extra parameters:</p>";
	echo "<input type='text' name='scriptParams' size='20' />";
	echo "</div>";
	echo "</td>";
	
	echo "<td><input type='image' src='$stylePath/accept.png' value='Save' name='submit' title='Save'></td>";
	
	echo "</form>";
}


// Similar to the function above, but also shows previously selected options when editing
function echoEffectEditForm ($taskInfo, $option, $db, $stylePath, $type, $choice, $value, $effect){
	
	echo "<form method='POST' action='options-add.php?id=$taskInfo&save=$effect&target=effect'>";

	echo "<td><select name='affect' id='affect' onchange='affectWhat();'>";
	if ( $type == "parameter" ) {
		echo "<option value='parameter' selected='selected'>Change Parameter</option><option value='task'>Trigger Task</option><option value='script'>Trigger Script</option>";
	}
	if ( $type == "task" ) {
		echo "<option value='parameter'>Change Parameter</option><option value='task' selected='selected'>Trigger Task</option><option value='script'>Trigger Script</option>";
	}
	if ( $type == "script" ) {
		echo "<option value='parameter'>Change Parameter</option><option value='task'>Trigger Task</option><option value='script' selected='selected'>Trigger Script</option>";
	}
	echo "</select></td>";
	
	// Choice box - either param, task, or script name
	echo "<td>";
	
	// The parameter selection
	echo "<div id='affectParam'>";
	echo "<p class='tableDescription'>Which parameter:</p>";
	echo "<select name='affparam' id='affparam'>";
	// Search for all parameters
	$stmt = $db->prepare("SELECT `id`, `parameter` FROM `type_params`");
	$stmt->bindParam(':id', $tid, PDO::PARAM_INT);
	$stmt->execute();
	// Loop to output parameters
	while ($row = $stmt->fetch()){
		echo "<option value='".$row["id"]."' ";
		if ($row["id"] == $choice) echo "selected='selected'"; // Not always correct
		echo ">".$row["parameter"]."</option>";
	}
	echo "</select>";
	echo "</div>";
	
	// The task selection
	echo "<div id='affectTask'>";
	echo "<p class='tableDescription'>Which task:</p>";
	echo "<select name='afftask' id='afftask'>";
	// Search for all tasks
	$stmt = $db->prepare("SELECT id, title FROM task_info");
	$stmt->bindParam(':id', $tid, PDO::PARAM_INT);
	$stmt->execute();
	// Loop to output tasks
	while ($row = $stmt->fetch()){
		echo "<option value='".$row['id']."'>".$row['title']."</option>";
		
		echo "<option value='".$row["id"]."' ";
		if ($row["id"] == $choice) echo "selected='selected'"; // Not always correct
		echo ">".$row["title"]."</option>";
	}
	echo "</select>";
	echo "</div>";
		
	// Entry of script name
	echo "<div id='affectScript'>";
	echo "<p class='tableDescription'>Which script:</p>";
	// Only display script name in box if option was originally of the script type
	if ( $type == "script" ) echo "<input type='text' name='affscript' size='25' value='$choice' />";
	else echo "<input type='text' name='affscript' size='25' />";
	echo "</div>";
		
	echo "</td>";
	
	echo "<td><input type='text' name='value' size='25' value='$value' /></td>";
	
	echo "<td><input type='image' src='$stylePath/accept.png' value='Save' name='submit' title='Save'></td>";
}

?>

<html>

<head>
<?php include 'admin-style.php'; ?>
<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
<script src="jquery-1.7.2.min.js"></script>
<script src="common.js"></script>

<script type="text/javascript">

// Actual hiding and showing of divs
// - other functions are wrappers for this
function switchView (targetType){

	if (targetType == "parameter"){			// For everyone - no choices to make
		$('#affectParam').show();
		$('#affectTask').hide();
		$('#affectScript').hide();
		$('#valueParam').show();
		$('#valueTask').hide();
		$('#valueScript').hide();
	}
	else if (targetType == "task"){	// For specific group types - show options
		$('#affectParam').hide();
		$('#affectTask').show();
		$('#affectScript').hide();
		$('#valueParam').hide();
		$('#valueTask').show();
		$('#valueScript').hide();
	}
	else if (targetType == "script"){		// For specific groups - show options
		$('#affectParam').hide();
		$('#affectTask').hide();
		$('#affectScript').show();
		$('#valueParam').hide();
		$('#valueTask').hide();
		$('#valueScript').show();
	}
}

// Toggle view based on who the task is to be assigned to
function affectWhat(){
	var affectWhat = $("#affect").val();
	switchView(affectWhat);
}

// Hide selection areas for types/groups on page load
$(document).ready(function(){
	// See what option is currently selected in the drop-down
	var chosen = $('#affect').val();
	switchView(chosen);
});
	 
</script>
</head>

<body>
	
	<?php
		include '../dbconnect.php';
		
		$currentLocation = "<a href='task-list.php'>Task List</a> &gt; Add Options";
		include 'standard-header.php';
	?>
	
	<div class="content">
	
		<h2 class="title">Add Options to Task</h2>
	
		<?php
		
			if ( ISSET($_GET['id']) ) $tid = $_GET['id'];
			else $tid = false;
			
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Dealing with changes to the data - forms submissions etc.
		
			// If an id was provided (in the URL)
			if ($tid){
			
				// See if saving updates
				if ( isset($_GET["save"]) ){
					
					// Check that required form information has been passed
					if ( isset($_GET['target']) ){
						
						if ( $_GET['target']== "option"){
							
							$description = $_POST['description'];
							$saveID = $_GET['save'];
							
							if ($description != ""){
								
								$stmt = $db->prepare("UPDATE `option` SET `description`=:description WHERE `id`=:saveID");
								$stmt->bindValue(':description', $description, PDO::PARAM_STR);
								$stmt->bindValue(':saveID', $saveID, PDO::PARAM_INT);
								$stmt->execute();
							
							} else echo "<script type='text/javascript'>alert('You need to include a description for the option.');</script>";
							
						} else {

							// Must be editing an effect
							//  could check for target variable, but probably no need
							$type = $_POST['affect'];
							$value = $_POST['value'];
							$saveID = $_GET['save'];
								
							if ($type=="parameter"){
								$choice = $_POST['affparam'];
							} else if ($type=="task"){
								$choice = $_POST['afftask'];
							} else {
								// Script as default??
								$choice = $_POST['affscript'];
							}
							
							// SQL to update
							$stmt = $db->prepare("UPDATE `effect` SET `type`=:type WHERE `id`=:saveID");
							$stmt->bindValue(':type', $type, PDO::PARAM_STR);
							$stmt->bindValue(':saveID', $saveID, PDO::PARAM_INT);
							$stmt->execute();
							
							$stmt = $db->prepare("UPDATE `effect` SET `choice`=:choice WHERE `id`=:saveID");
							$stmt->bindValue(':choice', $choice, PDO::PARAM_STR);
							$stmt->bindValue(':saveID', $saveID, PDO::PARAM_INT);
							$stmt->execute();
							
							$stmt = $db->prepare("UPDATE `effect` SET `value`=:value WHERE `id`=:saveID");
							$stmt->bindValue(':value', $value, PDO::PARAM_STR);
							$stmt->bindValue(':saveID', $saveID, PDO::PARAM_INT);
							$stmt->execute();

						}
					}
					
				} else if ( isset($_POST['description']) ){

					// Adding a new option
					$description =$_POST['description'];
					
					if ($description != ""){

						$stmt = $db->prepare("INSERT INTO `option` (`taskinfo`, `description`) VALUES (:tid, :description)");
						$stmt->bindValue(':tid', $tid, PDO::PARAM_INT);
						$stmt->bindValue(':description', $description, PDO::PARAM_STR);
						$stmt->execute();
					
					} else echo "<script type='text/javascript'>alert('You need to include a description for the option.');</script>";
					
				} else if ( isset($_POST['parentOption']) ){
					
					// Adding an effect to an option
					$parent = $_POST['parentOption'];
					$type = $_POST['affect'];
	
					if ($type=="parameter"){
						$choice = $_POST['affparam'];
						$value = $_POST['affmod'];
					} else if ($type=="task"){
						$choice = $_POST['afftask'];
						$value = $_POST['affdaysToTask'];
					} else {
						// Script as default??
						$choice = $_POST['affscript'];
						$value = $_POST['scriptParams'];
					}

					$delete = $db->prepare("INSERT INTO `effect` (`option`, `type`, `choice`, `value`) VALUES (:parent, :type, :choice, :value)");
					$delete->bindParam(':parent', $parent, PDO::PARAM_INT);
					$delete->bindParam(':type', $type, PDO::PARAM_STR);
					$delete->bindParam(':choice', $choice, PDO::PARAM_STR);
					$delete->bindParam(':value', $value, PDO::PARAM_STR);
					$delete->execute();
					
				}

				// See if deleting a parameter
				if ( isset($_GET['delete']) ){

					// See if deleting an option or an effect
					if ( isset($_GET['target']) ){

						$delID = $_GET['delete'];
						
						// If targetting an option
						// - delete option and attached effects
						if ( $_GET['target']=='option' ){

							// Delete both option, and any attached effects
							$delete = $db->prepare("DELETE FROM `option` WHERE `id`=:id");
							$delete->bindParam(':id', $delID, PDO::PARAM_INT);
							$delete->execute();	

							$delete = $db->prepare("DELETE FROM `effect` WHERE `option`=:id");
							$delete->bindParam(':id', $delID, PDO::PARAM_INT);
							$delete->execute();

						} 
						else if ( $_GET['target']=='effect' ){
							// Just deleting an effect
							$delete = $db->prepare("DELETE FROM `effect` WHERE `id`=:id");
							$delete->bindParam(':id', $delID, PDO::PARAM_INT);
							$delete->execute();
						}
						
					}// End of check if target set
					
				}// End of delete check
				
				// -------------------------------------------------------------------------------------------------------------------------------------
				// Display of current data

				// See if editing something
				if ( isset($_GET['edit']) ) $editID = $_GET['edit'];
				else $editID = false;
				
				// Find details of the supplied task ID
				$tasks = $db->prepare("SELECT `title`, `description` FROM `task_info` WHERE `id`=:id");
				$tasks->bindParam(':id', $tid, PDO::PARAM_INT);
				$tasks->execute();
				$foundTasks = $tasks->rowCount();
				
				
				if ($foundTasks > 0){
				
					// Extract single task information
					$task = $tasks->fetch();

					// Extract details
					$recoveredTitle = $task['title'];
					$recoveredDesc = $task['description'];
					
					// Output the details found (title and description)
					echo "<h3 id='previewHeader'>$recoveredTitle</h3>";
					echo "<div class='description'>$recoveredDesc</div>";
				
					// Start outputting options
					echo "<hr /><h3 class='title'>Options</h3>";
					
					echo "<table>";
					echo "<tr><th>Description</th><th>Type</th><th>Choice</th><th>Value</th><th>Actions</th></tr>";
					
					// Search for options connected to this task
					$options = $db->prepare("SELECT * FROM `option` WHERE `taskinfo`=:id");
					$options->bindParam(':id', $tid, PDO::PARAM_INT);
					$options->execute();
					$numOptions = $options->rowCount();
					
					
					// If there are some options, then display then in a table
					if ($numOptions > 0){
						
						// Loop to display options
						while ($option = $options->fetch()){
							 
							$optID = $option['id'];

							// Find any associated effects and list them				
							$effects = $db->prepare("SELECT * FROM `effect` WHERE `option`=:id");
							$effects->bindParam(':id', $optID, PDO::PARAM_INT);
							$effects->execute();
							
							// Are there any effects?
							$numEffects = $effects->rowCount();
							
							if ( isset($_GET['option']) ) $chnOpt = $_GET['option']; else $chnOpt = -1;
							
							// Display the description
							if ( ($editID==$option['id']) && ($_GET['target']=='option') ) {
								// If being edited
								echo "<tr><form method='POST' action='options-add.php?id=$tid&target=option&save=$editID'><td rowspan='".($numEffects+1)."'><input type='text' name='description' size='25' value='".$option['description']."' /></td>";
								echo "<td colspan='4'><input type='image' src='$stylePath/accept.png' value='Save' name='submit' title='Save'></td></form>";
							} else {
								// If not being edited
								echo "<tr><td rowspan='".($numEffects+1)."'>".$option['description']."</td>";
								
								// Either display choice of adding an effect, or the form to fill for a new effect
								if ($chnOpt == $optID) {
									echoEffectAddForm($tid, $optID, $db, $stylePath);
								} else {
									echo "<td colspan='4'>";
									echo "<a href='options-add.php?id=$tid&edit=".$option['id']."&target=option'><img src='$stylePath/edit.png' class='bullet' title='edit' alt='edit' /></a> <a href='javascript:void(0)' onclick='confirmDelete(\"options-add.php\", \"".$option['id']."&target=option&id=$tid\")'><img src='$stylePath/del.png' class='bullet' title='delete' alt='delete' /></a> ";
									//echo "<img src='$stylePath/divide.png' class='bullet' />";
									echo " : ";
									echo "<a href='options-add.php?id=$tid&option=$optID'><img src='$stylePath/add.png' class='bullet' />Add effect</a></td></tr>";
								}
							}
							
							// Loop through all effects
							while($effect = $effects->fetch()){
								
								if ( ($editID==$effect['id']) && ($_GET['target']=='effect') ) {
									
									// Editing this effect
									echo "<tr>";
									echoEffectEditForm($tid, $optID, $db, $stylePath, $effect['type'], $effect['choice'], $effect['value'], $editID);
									echo "</tr>";
									
								} else {

									//Just display data
									echo "<tr>";
									if ( $effect['type'] == "parameter" ) {
										echo "<td>Change Parameter</td>";
										$choice = $db->prepare("SELECT `parameter` FROM `type_params` WHERE `id`=:id");
										$choice->bindParam(':id', $effect['choice'], PDO::PARAM_INT);
										$choice->execute();
										$choiceInfo = $choice->fetch();
										echo "<td>".$choiceInfo['parameter']."</td>";
									}
									if ( $effect['type'] == "task" ) {
										echo "<td>Trigger Task</td>";
										$choice = $db->prepare("SELECT `title` FROM `task_info` WHERE `id`=:id");
										$choice->bindParam(':id', $effect['choice'], PDO::PARAM_INT);
										$choice->execute();
										$choiceInfo = $choice->fetch();
										echo "<td>".$choiceInfo['title']."</td>";
									}
									if ( $effect['type'] == "script" ) {
										echo "<td>Trigger Script</td>";
										echo "<td>".$effect['choice']."</td>";
									}
									echo "<td>".$effect['value']."</td>";
									
									// Slightly strange fudge needed on this one, because two params need to be passed for the delete
									echo "<td><a href='options-add.php?id=$tid&target=effect&edit=".$effect['id']."'><img src='$stylePath/edit.png' title='edit' alt='edit' /></a> <a href='javascript:void(0)' onclick='confirmDelete(\"options-add.php\", \"".$effect['id']."&target=effect&id=$tid\")'><img src='$stylePath/del.png' title='delete' alt='delete' /></a></td>";
									
									echo "</tr>";
								}
							}
							
							// Add a horizontal divider in the table
							echo "<tr><td class='tableDivide' colspan='5'></td></tr>";
						}
						
					}
					else{
						echo "<p>No options found for this task</p>";
					}
					
					
					// Display the choice to add a new option <form method='POST' action='options-add.php?id=$tid> </form>
					if (!isset($_GET['edit'])){
						echo "<form method='POST' action='options-add.php?id=$tid'><tr>";
						echo "<td><input type='text' name='description' size='25' /></td>";
						echo "<td colspan='4'><input type='image' src='$stylePath/add.png' value='New Option' name='submit' title='New Option'></td>";
						echo "</tr></form>";
					}
					
					echo "</table>";
			?>
			
		
		<?php	
				}else{
					// Or if no ID is supplied, or it doesn't match an entry, then display error
					echo "<p>Requested task could not be found.</p>";
				}
				
			}else{
				// No ID supplied
				echo "<p>No task was selected</p>";
			}
		?>
	
	</div>
	
</body>

</html>