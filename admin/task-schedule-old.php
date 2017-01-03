<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
<link rel="stylesheet" href="../kalendae/kalendae.css" type="text/css">
<script src="../kalendae/kalendae.js" type="text/javascript"></script>
<script src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
<script src="common.js"></script>

<script type="text/javascript">

// Toggle view based on who the task is to be assigned to
function selectWho(){
	var forWho = $("#forWho").val();

	if (forWho == "everyone"){			// For everyone - no choices to make
		$('#grpTyp').hide();		
		$('#selGrp').hide();
	}
	else if (forWho == "groupType"){	// For specific group types - show options
		$('#grpTyp').show();
		$('#selGrp').hide();
	}
	else if (forWho == "selected"){		// For specific groups - show options
		$('#grpTyp').hide();
		$('#selGrp').show();
	}
}

// Hide selection areas for types/groups on page load
$(document).ready(function(){
	$('#grpTyp').hide();
	$('#selGrp').hide();
});
	 
</script>

</head>

<body>
	<?php
		include '../dbconnect.php';
		
		$currentLocation = "Schedule Tasks";
		include 'standard-header.php';
	?>

	<div class="content">
		<h2 class="title">Scheduled Tasks</h2>
		<?php
		
			// Toggle of showing system tasks
			if ( isset($_GET['showall']) ){
				if ( $_GET['showall'] == 'true') $_SESSION['showall'] = true;
				else $_SESSION['showall'] = false;
			}
			if ( !isset($_SESSION['showall']) ) $_SESSION['showall'] = false;

			
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Dealing with changes to the data - forms submissions etc.
		
			// If there are post details from the 'Add schedule' form
			if (ISSET($_POST['task'])){

				
				// Create task group
				$chosenTask = mysql_real_escape_string($_POST['task']);
				$startDate = mysql_real_escape_string($_POST['start']);
				$endDate = mysql_real_escape_string($_POST['end']);
				$forWho = mysql_real_escape_string($_POST['forWho']);
				
				// Set delivery value based on drop-down option chosen
				$delivery = 0;
				if ($forWho == "groupType") $delivery = 1;
				if ($forWho == "selected") $delivery = 2;
				
				// Check that dates have been set
				if ( ($startDate != "") && ($endDate != "") ){
				
					// Create task group entry, and find it's id
					$stmt = $db->prepare("INSERT INTO `task_group` (`delivery`, `content`, `author`, `task`, `startdate`, `enddate`) VALUES (:delivery, '0', '1', :tskid, :start, :end)");
					$stmt->bindValue(':delivery', $delivery, PDO::PARAM_INT);
					$stmt->bindValue(':tskid', $chosenTask, PDO::PARAM_INT);
					$stmt->bindValue(':start', $startDate);
					$stmt->bindValue(':end', $endDate);
					$stmt->execute();
					
					// Find the entry's id
					$last = $db->lastInsertId();

					$groupsFound = false;
					
					// Add individual tasks, based on selection
					if ($delivery == 0){
						
						// Search for every group
						$groups = $db->prepare("SELECT `id` FROM `group`");
						$groups->execute();

						// Create individual tasks
						while ($group = $groups->fetch()){
							//mysql_query("INSERT INTO `task` (`taskgroup`, `group`, `taskinfo`) VALUES ('".mysql_result($idSearch, 0, "last")."', '".mysql_result($result, $lp, "id")."', '$chosenTask')", $db);

							$insert = $db->prepare("INSERT INTO `task` (`taskgroup`, `group`, `taskinfo`) VALUES (:last, :group, :task)");
							$insert->bindValue(":last", $last, PDO::PARAM_INT);
							$insert->bindValue(":group", $group['id'], PDO::PARAM_INT);
							$insert->bindValue(":task", $chosenTask, PDO::PARAM_INT);
							$insert->execute();
							
							if (!$groupsFound) $groupsFound = true;
						}

					}
					else if ($delivery == 1){
						// Extract chosen group types
						$chnTypes = $_POST['CsnTyp'];
						$numChosen = count($chnTypes);
						
						// Loop through chosen types
						for ($lp = 0; $lp < $numChosen; $lp++){
						
							// Find groups of that type
							//$result = mysql_query("SELECT `id` FROM `group` WHERE `type`=".$chnTypes[$lp], $db);
							$groups = $db->prepare("SELECT `id` FROM `group` WHERE `type`=:type");
							$groups->bindValue(":type", $chnTypes[$lp], PDO::PARAM_INT);
							$groups->execute();
							
							// Create entries
							while($group = $groups->fetch()){
								//mysql_query("INSERT INTO `task` (`taskgroup`, `group`, `taskinfo`) VALUES ('".mysql_result($idSearch, 0, "last")."', '".mysql_result($result, $clp, "id")."', '$chosenTask')");
								
								$insert = $db->prepare("INSERT INTO `task` (`taskgroup`, `group`, `taskinfo`) VALUES (:last, :group, :task)");
								$insert->bindValue(":last", $last, PDO::PARAM_INT);
								$insert->bindValue(":group", $group['id'], PDO::PARAM_INT);
								$insert->bindValue(":task", $chosenTask, PDO::PARAM_INT);
								$insert->execute();
								
								if (!$groupsFound) $groupsFound = true;
							}
						}
						
					}
					else {
						// Extract list of chosen groups from post variable
						if ( isset($_POST['CsnGrp']) ) {
							$csnGrps = $_POST['CsnGrp'];
							$numChosen = count($csnGrps);
						}
						else $numChosen = 0;	// Catch if none of the groups were selected (or none to select)
						
						
						// Loop through chosen groups
						for ($lp = 0; $lp < $numChosen; $lp++){
	
							// Create entry for each group
							//mysql_query("INSERT INTO `task` (`taskgroup`, `group`, `taskinfo`) VALUES ('".mysql_result($idSearch, 0, "last")."', '".mysql_real_escape_string($csnGrps[$lp])."', '$chosenTask')", $db);
							
							$insert = $db->prepare("INSERT INTO `task` (`taskgroup`, `group`, `taskinfo`) VALUES (:last, :group, :task)");
							$insert->bindValue(":last", $last, PDO::PARAM_INT);
							$insert->bindValue(":group", $csnGrps[$lp], PDO::PARAM_INT);
							$insert->bindValue(":task", $chosenTask, PDO::PARAM_INT);
							$insert->execute();
							
							if (!$groupsFound) $groupsFound = true;
						}
					}
					
					// If no groups could be found to add the task to, then delete the task group
					if (!$groupsFound) {
						//mysql_query("DELETE FROM `task_group` WHERE `id`='".mysql_result($idSearch, 0, "last")."'");
						
						$delete = $db->prepare("DELETE FROM `task_group` WHERE `id`=:last");
						$delete->bindValue(":last", $last, PDO::PARAM_INT);
						$delete->execute();
						
						echo "<script type='text/javascript'>alert('Task not scheduled. No groups matched criteria.');</script>";
					}
				
				} else echo "<script type='text/javascript'>alert('You need to supply both start and end dates.');</script>";

			}
			else{
			
				// See if updating task group - submission of edit form
				if (isset($_GET['save'])){
					
					// Extract details
					$saveID = mysql_real_escape_string($_GET['save']);
					$startDate = mysql_real_escape_string($_POST['start']);
					$endDate = mysql_real_escape_string($_POST['end']);
					
					//mysql_query("UPDATE `task_group` SET `startdate`='$startDate' WHERE id=$saveID", $db);
					//mysql_query("UPDATE `task_group` SET `enddate`='$endDate' WHERE id=$saveID", $db);
					
					$update = $db->prepare("UPDATE `task_group` SET `startdate`=:startDate WHERE id=:save");
					$update->bindValue(":startDate", $startDate);
					$update->bindValue(":save", $saveID, PDO::PARAM_INT);
					$update->execute();
					
					$update = $db->prepare("UPDATE `task_group` SET `enddate`=:endDate WHERE id=:save");
					$update->bindValue(":endDate", $endDate);
					$update->bindValue(":save", $saveID, PDO::PARAM_INT);
					$update->execute();
					
					// Update task group
				
				}else if ( isset($_GET['delete'] ) ){

					// See if aiming to delete
					// Probably need some sort of confirmation
					//$deleteID = mysql_real_escape_string($_GET['delete']);
					
					// Delete tasks attached to task group
					//mysql_query("DELETE FROM `task` WHERE `taskgroup`=$deleteID", $db);
					$delete = $db->prepare("DELETE FROM `task` WHERE `taskgroup`=:delete");
					$delete->bindValue(":delete", $_GET['delete'], PDO::PARAM_INT);
					$delete->execute();
					
					// Delete task group
					//mysql_query("DELETE FROM `task_group` WHERE `id`=$deleteID", $db);
					$delete = $db->prepare("DELETE FROM `task_group` WHERE `id`=:delete");
					$delete->bindValue(":delete", $_GET['delete'], PDO::PARAM_INT);
					$delete->execute();
				}
			}
			
			
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Display of current data
			
			// List all available tasks		
			if ( $_SESSION['showall'] ) $stmt = $db->prepare("SELECT `task_group`.`id`, `task_info`.`title`, `task_group`.`startdate`, `task_group`.`enddate`, COUNT(`task`.`id`) AS ingrp FROM `task_group` INNER JOIN `task_info` ON `task_group`.`task` = `task_info`.`id` INNER JOIN `task` ON `task_group`.id = `task`.`taskgroup` GROUP BY `task`.`taskgroup` ORDER BY `task_group`.`startdate`");
			else $stmt = $db->prepare("SELECT `task_group`.`id`, `task_info`.`title`, `task_group`.`startdate`, `task_group`.`enddate`, COUNT(`task`.`id`) AS ingrp FROM `task_group` INNER JOIN `task_info` ON `task_group`.`task` = `task_info`.`id` INNER JOIN `task` ON `task_group`.id = `task`.`taskgroup` WHERE `task_group`.`author`>0 GROUP BY `task`.`taskgroup` ORDER BY `task_group`.`startdate`");
				
			$stmt->execute();
			$numtasks = $stmt->rowCount();
			

			
			// Show/Hide system tasks option
			if (  $_SESSION['showall'] ) echo "<p><a href='task-schedule.php?showall=false'><img class='bullet' src='$stylePath/contract.png' title='Hide' alt='Hide' />Hide system tasks</a></p>";
			else echo "<p><a href='task-schedule.php?showall=true'><img class='bullet' src='$stylePath/expand.png' title='Show' alt='Show' />Show system tasks</a></p>";
			
			// Output schedule
			echo "<table border='1'>";
			echo "<tr><th>Task Name</th><th>Start Date</th><th>End Date</th><th>Targetted</th><th>Edit</th></tr>";
			
			// Loop and output as table
			while ( $schedule = $stmt->fetch() ){
			
				// Check if this one is being edited
				$beingEdited = false;
				if (isset($_GET['edit'])){
					if ( $_GET['edit'] == $schedule['id'] ) { $beingEdited = true; }
				}
				
				// If in edit mode then display edit options
				if ($beingEdited){
					echo "<form method='POST' action='task-schedule.php?save=".$schedule['id']."'>";
					echo "<tr><td>".$schedule['title']."</td>";
					echo "<td><input  type='text' name='start' size='10' class='auto-kal' value='".$schedule['startdate']."'></td>";
					echo "<td><input  type='text' name='end' size='10' class='auto-kal' value='".$schedule['enddate']."'></td>";
					echo "<td>".$schedule['ingrp']."</td>";
					echo "<td><input type='image' src='$stylePath/accept.png' value='Save' name='submit'></td></tr>";
					echo "</form>";
				} else {
					echo "<tr><td>".$schedule['title']."</td>";
					echo "<td>".$schedule['startdate']."</td>";
					echo "<td>".$schedule['enddate']."</td>";
					echo "<td><a href='task-targets.php?id=".$schedule['id']."'>".$schedule['ingrp']."</a></td>";
					echo "<td><a href='task-schedule.php?edit=".$schedule['id']."'><img src='$stylePath/edit.png' title='edit' alt='edit' /></a> <a href='javascript:void(0)' onclick='confirmDelete(\"task-schedule.php\", ".$schedule['id'].")'><img src='$stylePath/del.png' title='delete' alt='delete' /></a></td></tr>";
				}
				
			} // End of check for values

			if (!isset($_GET['edit'])){
				echo "<form method='POST' action='task-schedule.php'>";
				echo "<tr>";
				echo "<td>";
				echo "<select name='task'>";
				// List all available tasks
				$stmt = $db->prepare("SELECT `id`, `title` FROM `task_info` WHERE `author`>0");
				$stmt->execute();
				
				// Output list of tasks
				while ($task = $stmt->fetch()){
					echo "<option value=".$task['id'].">".$task['title']."</option>";
				}
				
				echo "</select>";
				echo "</td>";
				echo "<td><input  type='text' name='start' size='10' class='auto-kal'></td>";
				echo "<td><input id='end' type='text' name='end' size='10' class='auto-kal'></td>";
				echo "<td>";
				echo "<select name='forWho' id='forWho' onchange='selectWho();'>";
				echo "<option value='everyone'>Every Group</option>";
				echo "<option value='groupType'>Specific Group Types</option>";
				echo "<option value='selected'>Chosen Groups</option>";
				echo "</select>";
			
				?>
									
				<div id="grpTyp">
					<br />
					Types:<br />
					<div class="chkScroll">
					<?php
						// Display all groups in a box 
						//$result = mysql_query("SELECT `id`, `name` FROM `group_type`", $db);
						
						$types = $db->prepare("SELECT `id`, `name` FROM `group_type`");
						$types->execute();
						
						// Output list of groups
						while ($type = $types->fetch()){
							echo "<input type='checkbox' name='CsnTyp[]' value='".$type['id']."' />".$type['name']."<br />";
						}

					?>
					</div>
				</div>
				
				<div id="selGrp">
					<br />
					Groups:<br />
					<div class="chkScroll">
					<?php
						// Display all groups in a box 
						//$result = mysql_query("SELECT `id`, `name` FROM `group`", $db);
						
						$groups = $db->prepare("SELECT `id`, `name` FROM `group`");
						$groups->execute();
						
						// Output list of groups
						while ($group = $groups->fetch()){
							echo "<input type='checkbox' name='CsnGrp[]' value='".$group['id']."' />".$group['name']."<br />";
						}

					?>
					</div>
				</div>
				
				<?php 
				
				echo "</td>";
				echo "<td><input type='image' src='$stylePath/add.png' value='New' name='submit' title='New'></td>";
				echo "</tr>";
				echo "</form>";
			}
		
		echo "</table>";
			
		?>
			
				
	</div>
	
</body>

</html>