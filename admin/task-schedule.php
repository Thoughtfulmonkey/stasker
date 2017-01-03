<?php include 'login-check.php'; ?>

<?php 

function displayWhenOptions(){

	echo "<td>";
	echo "<select name='when' id='when' onchange='selectWhen();'>";
	echo "	<option value='date'>Date Window</option>";
	echo "	<option value='repeat'>Repeating</option>";
	echo "</select>";
	
	echo "<div id='whenWindow'>";
	echo "	<br />";
	echo "	Opens:<input  type='text' name='from' size='10' class='auto-kal'> &gt;";
	echo "	Closes:<input id='end' type='text' name='to' size='10' class='auto-kal'>";
	echo "</div>";
					
	echo "<div id='whenRepeat'>";
	echo "	<br />";
	echo "	First:<input  type='text' name='start' size='10' class='auto-kal'> &gt;";
	echo "	Ends:<input id='end' type='text' name='end' size='10' class='auto-kal'> ";
	echo "	<br /><br />";
	echo "	Repeat every <input  type='text' name='frequency' size='2'>";
	echo "	<select name='step' id='step'>";
	echo "		<option value='D'>Day/s</option>";
	echo "		<option value='W'>Week/s</option>";
	echo "		<option value='M'>Month/s</option>";
	echo "	</select>";
	echo "	<br /><br />";
	echo "	Each task open for <input  type='text' name='openfor' size='2'> days";
	echo "</div>";
	echo "</td>";

}


function displayWhenEditOptions($db, $schedule){
	
	echo "<td>";
/*	
	if ($schedule['recurID'] != null){
		
		$repdat = $db->prepare("SELECT * FROM `task_recurring` WHERE `id`=:recurID");
		$repdat->bindValue(':recurID', $schedule['recurID'], PDO::PARAM_INT);
		$repdat->execute();
		
		$rep = $repdat->fetch();
		
		echo"Repeating<br>";
		echo "	<br>";
		echo "	First:<input  type='text' name='start' size='10' class='auto-kal' value='".$rep['from']."'> &gt;";
		echo "	Ends:<input id='end' type='text' name='end' size='10' class='auto-kal'value='".$rep['to']."'> ";
		echo "	<br /><br />";
		echo "	Repeat every <input  type='text' name='frequency' size='2' value='".$rep['interval']."'>";
		echo "	<select name='step' id='step'>";
		echo "		<option value='D'>Day/s</option>";
		echo "		<option value='W'>Week/s</option>";
		echo "		<option value='M'>Month/s</option>";
		echo "	</select>";
		echo "	<br /><br />";
		echo "	Each task open for <input  type='text' name='openfor' size='2' value='".$rep['interval']."'> days";
		
	} else {
		echo "Date Window<br>";
		echo "	<br>";
		echo "	Opens:<input  type='text' name='from' size='10' class='auto-kal' value='".$schedule['startdate']."'> &gt;";
		echo "	Closes:<input id='end' type='text' name='to' size='10' class='auto-kal' value='".$schedule['enddate']."'>";
	}
*/	
	
	echo "Opens:<input  type='text' name='from' size='10' class='auto-kal' value='".$schedule['startdate']."'> &gt;";
	echo "Closes:<input id='end' type='text' name='to' size='10' class='auto-kal' value='".$schedule['enddate']."'>";
	
	echo "</td>";
	
}

?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
<link rel="stylesheet" href="../kalendae/kalendae.css" type="text/css">
<script src="../kalendae/kalendae.js" type="text/javascript"></script>
<script src="jquery-1.7.2.min.js"></script>
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

//Toggle view based on how the task is scheduled
function selectWhen(){
	var when = $("#when").val();

	if (when == "date"){				// Available between two dates
		$('#whenWindow').show();		
		$('#whenRepeat').hide();
	}
	else if (when == "repeat"){		// Repeating at a set interval
		$('#whenWindow').hide();		
		$('#whenRepeat').show();
	}
}

// Hide selection areas for types/groups on page load
$(document).ready(function(){
	$('#grpTyp').hide();
	$('#selGrp').hide();

	$('#whenRepeat').hide();
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
		
			function findIntervalsInRange ($start, $end, $f, $s){
				
				$repeats = 0;
				$startAsDate = new DateTime($start);
				$endAsDate = new DateTime($end);
				$interval = $startAsDate->diff($endAsDate);
				
				if ($f>0){
					// Check that dates were supplied
					if ($startAsDate && $endAsDate){
					
						if ($s == "D"){
							// How many frequency repeats in time period?
							$repeats = floor($interval->format('%a') / $f);
						
						} else if ($s == "W") {
							// How many frequency x 7 repeats in time period?
							$repeats = floor( $interval->format('%a') / ($f*7) );
						
						} else if ("$s" == "M") {
							// How many full months between the steps?
							$repeats = floor( $interval->m / $f );
						}
					}
										
					return $repeats+1;	// Add one to include the first on start date.
				}
				else return 0;
			}
			
		
			// Toggle of presentation settings for tasks
			if ( isset($_POST['hidden-criteria']) ){
				
				$_SESSION['showsys'] = false;
				$_SESSION['showrep'] = false;
				$_SESSION['showold'] = false;
				
				if ( isset($_POST['display']) ) $settings = $_POST['display'];
				else $settings = null;
				
				for ($slp=0; $slp<count($settings); $slp++){
					
					if ( $settings[$slp]=="system" ) $_SESSION['showsys'] = true;
					if ( $settings[$slp]=="repeat" ) $_SESSION['showrep'] = true;
					if ( $settings[$slp]=="past" ) $_SESSION['showold'] = true;
				}
			}
			if ( !isset($_SESSION['showsys']) ) $_SESSION['showsys'] = false;
			if ( !isset($_SESSION['showrep']) ) $_SESSION['showrep'] = true;
			if ( !isset($_SESSION['showold']) ) $_SESSION['showold'] = false;

			
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Dealing with changes to the data - forms submissions etc.
		
			// If there are post details from the 'Add schedule' form
			if ( ISSET($_POST['task']) ){

				// fixed date window or recurring?
				$taskType = $_POST['when'];
												
				// Set delivery value based on drop-down option chosen
				$forWho =$_POST['forWho'];
				$delivery = '0';											// all groups
				if ($forWho == "groupType") $delivery = '1';				// group type
				if ($forWho == "selected") $delivery = '2';					// selected groups
			
				// Store chosen task
				$chosenTask = $_POST['task'];
				
				// How many task groups to create?
				$groupsToMake = 0;
				$recurId = null;
				if ($taskType == "date"){
					$groupsToMake = 1;		// Only one to create
				} else {
					// Recurring.  Calculate how many are needed
					$startDate = $_POST['start'];
					$endDate = $_POST['end'];
					$frequency = $_POST['frequency'];
					$step = $_POST['step'];
					
					$groupsToMake = findIntervalsInRange($startDate, $endDate, $frequency, $step);
					
					// Also create an entry in the task_recurring table
					$stmt = $db->prepare("INSERT INTO `task_recurring` (`from`, `to`, `interval`, `step`) VALUES (:from, :to, :interval, :step)");
					$stmt->bindValue(':from', $startDate);
					$stmt->bindValue(':to', $endDate);
					$stmt->bindValue(':interval', $frequency, PDO::PARAM_INT);
					$stmt->bindValue(':step', $step, PDO::PARAM_STR);
					$stmt->execute();
					
					$recurId = $db->lastInsertId();
				}

				
				// Loop the required number of times
				for ($glp = 0; $glp < $groupsToMake; $glp++){
					
					$fromDate = "";
					$toDate = "";
					
					// If fixed date, then only one loop
					if ($taskType == "date"){
						
						$fromDate = $_POST['from'];	// Just use supplied dates
						$toDate = $_POST['to'];
					}
					else {
						// Interval from initial start date varies depending on loop
						
						$workingDate = new DateTime($_POST['start']);												// Create new date and add time interval
						if ($glp>0) $workingDate->add( new DateInterval("P".($_POST['frequency']*$glp).$_POST['step']) );
						
						$fromDate = $workingDate->format("Y/m/d");
						
						if ($_POST['openfor'] == '') $dur = 0; else $dur = $_POST['openfor'];
						$workingDate->add( new DateInterval("P".$dur."D") );
						
						$toDate = $workingDate->format("Y/m/d");
					}
				
					// Check that dates have been set
					if ( ($fromDate != "") && ($toDate != "") ){
					
						// Create task group entry, and find it's id
						$stmt = $db->prepare("INSERT INTO `task_group` (`delivery`, `content`, `recurID`, `author`, `task`, `startdate`, `enddate`) VALUES (:delivery, '0', :recurId, '1', :tskid, :start, :end)");
						$stmt->bindValue(':delivery', $delivery);
						$stmt->bindValue(':recurId', $recurId);
						$stmt->bindValue(':tskid', $chosenTask, PDO::PARAM_INT);
						$stmt->bindValue(':start', $fromDate);
						$stmt->bindValue(':end', $toDate);
						$stmt->execute();
						
						// Find the entry's id
						$last = $db->lastInsertId();
	
						$groupsFound = false;
						
						//echo "<p>Delivery mode: $delivery</p>";
						
						// Add individual tasks, based on selection
						if ($delivery == 0){
							
							// Search for every group
							$groups = $db->prepare("SELECT `id` FROM `group`");
							$groups->execute();
	
							// Create individual tasks
							while ($group = $groups->fetch()){

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
								$groups = $db->prepare("SELECT `id` FROM `group` WHERE `type`=:type");
								$groups->bindValue(":type", $chnTypes[$lp], PDO::PARAM_INT);
								$groups->execute();
								
								// Create entries
								while($group = $groups->fetch()){

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
							
							echo "<p>Number of groups chosen: $numChosen</p>";
							
							// Loop through chosen groups
							for ($lp = 0; $lp < $numChosen; $lp++){
		
								// Create entry for each group
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

							$delete = $db->prepare("DELETE FROM `task_group` WHERE `id`=:last");
							$delete->bindValue(":last", $last, PDO::PARAM_INT);
							$delete->execute();
							
							echo "<script type='text/javascript'>alert('Task not scheduled. No groups matched criteria.');</script>";
						}
				
					} else echo "<script type='text/javascript'>alert('You need to supply both start and end dates.');</script>";
				
				} // End of loop
			}
			else{
			
				// See if updating task group - submission of edit form
				if (isset($_GET['save'])){
					
					// Extract details
					$saveID = $_GET['save'];
					$startDate = $_POST['from'];
					$endDate = $_POST['to'];
									
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

					// Delete tasks attached to task group
					$delete = $db->prepare("DELETE FROM `task` WHERE `taskgroup`=:delete");
					$delete->bindValue(":delete", $_GET['delete'], PDO::PARAM_INT);
					$delete->execute();
					
					// Delete task group
					$delete = $db->prepare("DELETE FROM `task_group` WHERE `id`=:delete");
					$delete->bindValue(":delete", $_GET['delete'], PDO::PARAM_INT);
					$delete->execute();
				}
			}
			
			
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Display of current data
			
			// List all available tasks		
			//if ( $_SESSION['showall'] ) $stmt = $db->prepare("SELECT `task_group`.`id`, `task_info`.`title`, `task_group`.`startdate`, `task_group`.`enddate`, `task_group`.`recurID`, COUNT(`task`.`id`) AS ingrp FROM `task_group` INNER JOIN `task_info` ON `task_group`.`task` = `task_info`.`id` INNER JOIN `task` ON `task_group`.id = `task`.`taskgroup` GROUP BY `task`.`taskgroup` ORDER BY `task_group`.`startdate`");
			//else $stmt = $db->prepare("SELECT `task_group`.`id`, `task_info`.`title`, `task_group`.`startdate`, `task_group`.`enddate`, `task_group`.`recurID`, COUNT(`task`.`id`) AS ingrp FROM `task_group` INNER JOIN `task_info` ON `task_group`.`task` = `task_info`.`id` INNER JOIN `task` ON `task_group`.id = `task`.`taskgroup` WHERE `task_group`.`author`>0 GROUP BY `task`.`taskgroup` ORDER BY `task_group`.`startdate`");
			
			$stmt = $db->prepare("SELECT `task_group`.`id`, `task_info`.`title`, `task_group`.`startdate`, `task_group`.`enddate`, `task_group`.`recurID`, COUNT(`task`.`id`) AS ingrp, `task_group`.`author` FROM `task_group` INNER JOIN `task_info` ON `task_group`.`task` = `task_info`.`id` INNER JOIN `task` ON `task_group`.id = `task`.`taskgroup` GROUP BY `task`.`taskgroup` ORDER BY `task_group`.`startdate`");
			
			$stmt->execute();
			$numtasks = $stmt->rowCount();
			
			
			if ( $_SESSION['showsys'] ) $showsys = "checked"; else $showsys = "";
			if ( $_SESSION['showrep'] ) $showrep = "checked"; else $showrep = "";
			if ( $_SESSION['showold'] ) $showold = "checked"; else $showold = "";
			
			// Show the display options
			echo "<form method='POST' action='task-schedule.php'>";
			echo "Show: <input type='checkbox' name='display[]' value='system' $showsys>System generated &nbsp;";
			echo "<input type='checkbox' name='display[]' value='repeat' $showrep>Repeating &nbsp;";
			echo "<input type='checkbox' name='display[]' value='past' $showold>Expired &nbsp;";
			echo "<input type='hidden' name='hidden-criteria' value='hidden'>";
			echo "<input type='submit' value='Update'>";
			echo "</form><br>";
			
			// Output schedule
			echo "<table border='1' style='width:100%'>";
			echo "<tr><th>Task Name</th><th>When</th><th>Targetted</th><th>Edit</th></tr>";
			
			// Obtain current date
			$currentDate = new DateTime('Now');
			$now = $currentDate->format("Y-m-d");
			
			// For adding 'now' row
			$nowMarkAdded = false;
			
			// Loop and output as table
			while ( $schedule = $stmt->fetch() ){
							
				// Decide whether or not to display based on filter settings
				$display = true;
				
				// If author is system - see if should display system tasks
				if ( ($schedule['author']=="0") && (!$_SESSION['showsys']) ) $display = false;
				
				// If expired - see if should show expired
				if ( ($schedule['enddate']<$now) && (!$_SESSION['showold']) ) $display = false;
				
				// If has a recuring ID - see if recuring tasks should be displayed
				if ( ($schedule['recurID']!="") && (!$_SESSION['showrep']) ) $display = false;
				
				// Only display if meet filtering criteria
				if ($display){
				
					// See if currently active
					$trclass = "inactive-task";
					if ( ($schedule['startdate']<=$now) && ($schedule['enddate']>$now) ) $trclass = "active-task";

					// Check if this one is being edited
					$beingEdited = false;
					if (isset($_GET['edit'])){
						if ( $_GET['edit'] == $schedule['id'] ) { $beingEdited = true; }
					}
					
					// Check to add 'now' row
					if ( ($schedule['startdate']>$now) && (!$nowMarkAdded) ){
						echo "<tr id='nowmarker'><td colspan='4'>Today's date: ".$now."</td></tr>";
						$nowMarkAdded = true;
					}
					
					// If in edit mode then display edit options
					if ($beingEdited){
						echo "<form method='POST' action='task-schedule.php?save=".$schedule['id']."'>";
						echo "<tr class='".$trclass."'><td>".$schedule['title']."</td>";
	
						displayWhenEditOptions($db, $schedule);
						
						echo "<td>".$schedule['ingrp']."</td>";
						echo "<td><input type='image' src='$stylePath/accept.png' value='Save' name='submit'><a href='task-schedule.php' ><img src='$stylePath/revert.png' title='cancel' alt='cancel'></a></td></tr>";
						echo "</form>";
					} else {
						echo "<tr class='".$trclass."'><td>".$schedule['title']."</td>";
						echo "<td>".$schedule['startdate']." &gt; ";
						echo "".$schedule['enddate'];
						if ($schedule['recurID'] != null) {
							$repeatImg = "repeat-small.png";
							if ( isset($_GET['recur']) ){
								if ($_GET['recur'] == $schedule['recurID']) $repeatImg = "repeat-small-h.png";
							}
							echo "<a href='task-schedule.php?recur=".$schedule['recurID']."'><img class='bullet' style='margin-left:5px;' src='$stylePath/".$repeatImg."' title='Repeats' alt='Repeats' /></a>";
						}
						echo "</td>";
						echo "<td><a href='task-targets.php?id=".$schedule['id']."'>".$schedule['ingrp']."</a></td>";
						echo "<td><a href='task-schedule.php?edit=".$schedule['id']."'><img src='$stylePath/edit.png' title='edit' alt='edit' /></a> <a href='javascript:void(0)' onclick='confirmDelete(\"task-schedule.php\", ".$schedule['id'].")'><img src='$stylePath/del.png' title='delete' alt='delete' /></a></td></tr>";
					}
					
				}// End of display check
				
			} // End of check for values

			// If 'now' row was not added in loop, then add here
			if ( (!$nowMarkAdded) ){
				echo "<tr id='nowmarker'><td colspan='4'>Today's date: ".$now."</td></tr>";
				$nowMarkAdded = true;
			}
			
			if (!isset($_GET['edit'])){
				echo "<form method='POST' action='task-schedule.php'>";
				echo "<tr>";
				echo "<td>";
				echo "<select name='task' style='max-width:210px;'>";
				// List all available tasks
				$stmt = $db->prepare("SELECT `id`, `title` FROM `task_info` WHERE `author`>0");
				$stmt->execute();
				
				// Output list of tasks
				while ($task = $stmt->fetch()){
					echo "<option value=".$task['id'].">".$task['title']."</option>";
				}
				
				echo "</select>";
				echo "</td>";
				

				// Function to display the more complicated "when" options
				// - it's a function because it was going to be re-used, but it never was
				displayWhenOptions();
				
 
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