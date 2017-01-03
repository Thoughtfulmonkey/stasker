<?php

// Check if logged in
include 'login-check.php';

?>

<html>

<head>
<?php include 'user-style.php'; ?>
<title>Statistics</title>
</head>

<body>
	<?php
		include 'dbconnect.php';
		include 'student-header.php';
		
		// Find user id
		$usrResult = $db->prepare("SELECT id FROM user WHERE `login`=:user");
		$usrResult->bindValue(":user", $_SESSION['username'], PDO::PARAM_STR);
		$usrResult->execute();
		$firstResult = $usrResult->fetch();
		$usrID = $firstResult['id'];
		
		// Find user's group id
		$grpID = $_SESSION['groupnum'];
		
		// If the user is part of a group
		if ($grpID != 0){
		
			// Find game date
			$simData = $db->prepare("SELECT `game_date` FROM `sim`");
			$simData->execute();
			$simResult = $simData->fetch();
			$gameDate = $simResult['game_date'];

		?>
			
		<div class='content'>
		
			<h2 class="title">User Activity</h2>
			<?php 
			
			// Search for all of the voting activity of members of this group
			$activitySearch = $db->prepare("SELECT `user`.`display_name`, COUNT(`vote`.`id`) AS `votenum` , `user`.`group` FROM `user` LEFT JOIN `vote` ON `user`.`id` = `vote`.`user` WHERE `user`.`group` = :grpID GROUP BY `user`.`id` ORDER BY `votenum` DESC");
			$activitySearch->bindValue(":grpID", $grpID, PDO::PARAM_INT);
			$activitySearch->execute();

			// If user details found - and they should be
			if ( $activitySearch->rowCount() ){
			
				echo "<table>";
				echo "<tr><th>Member</th><th>Number of Votes</th></tr>";
				
				$oddRow = true;
				while ( $activityDetails = $activitySearch->fetch() ){
					if ($oddRow) {
						echo "<tr class='oddRow'>";
					} else {
						echo "<tr class='evenRow'>";
					}
					$oddRow = !$oddRow;
				
					echo "<td>".$activityDetails["display_name"]."</td>";
					
					echo "<td>".$activityDetails["votenum"]."</td>";
					
					echo "</tr>";
				}
				echo "</table>";
				
			} else echo "<p> - No users (somehow)</p>";
			
			?>
		
			</div>
			
		
			<div class='content'>
		
			<h2 class="title">Consensus</h2>
			<?php 
			
			//$tskSearch = $db->prepare("SELECT `task`.`id` FROM `task` WHERE `task`.`group` = :grpID");
			$tskSearch = $db->prepare("SELECT `task`.`id` FROM  `task` INNER JOIN `task_group` ON `task`.`taskgroup`=`task_group`.`id` INNER JOIN `task_info` ON `task_group`.`task`=`task_info`.`id` WHERE (`task`.`group`=:grpID AND `task_group`.`startdate` <= CURDATE())");
			$tskSearch->bindValue(":grpID", $grpID, PDO::PARAM_INT);
			$tskSearch->execute();
					

			$noVotes = 0;
			$undecided = 0;
			$consensus = 0;
			$openTasks = 0;
			
			
			if ( $tskSearch->rowCount() ){
			
				// Loop through all tasks
				while ($task = $tskSearch->fetch() ){
					
					// Find votes for this tasks
					$voteSearch = $db->prepare("SELECT *, COUNT(`option`) AS votes FROM `vote` WHERE `task` = :tskID GROUP BY `option` ORDER BY `votes` DESC");
					$voteSearch->bindValue(":tskID", $task['id'], PDO::PARAM_INT);
					$voteSearch->execute();
					

					// If only votes for one option (has to be at least 1 vote?)
					if ( $voteSearch->rowCount()==1 ){
						
						$voteDetails = $voteSearch->fetch();

						if ( $voteDetails["votes"] > 1 ) $consensus++; 		// Everyone voted same
						else if ($voteDetails["user"] > 0) $consensus++;	// Only one person voted
						else $noVotes++;									// Only system voted

					} else {
						// Multiple items voted for
						if ( $voteSearch->rowCount()>0 ) $undecided++; 		// Votes on multiple
						else $openTasks++; 							   		// Unprocessed no vote tasks
					}
				}
				
			} else echo "<p> - No tasks found</p>";
			
			echo "<table>";
			
			echo "<tr class='oddRow'><td>Number of tasks:</td><td>".$tskSearch->rowCount()."</td></tr>";
			
			echo "<tr class='evenRow'><td>Tasks with consensus (everyone voted for the same option):</td><td>$consensus</td></tr>";
			
			echo "<tr class='oddRow'><td>Tasks without consensus (people voted for different options):</td><td>$undecided</td></tr>";
			
			echo "<tr class='evenRow'><td>Tasks with no votes:</td><td>$noVotes</td></tr>";
			
			// Find tasks still open
			$openSearch = $db->prepare("SELECT `task`.`id` FROM  `task` INNER JOIN `task_group` ON `task`.`taskgroup`=`task_group`.`id` INNER JOIN `task_info` ON `task_group`.`task`=`task_info`.`id` WHERE (`task`.`group`=:grpID AND `task_group`.`startdate` <= CURDATE() AND `task_group`.`enddate` > CURDATE())");
			$openSearch->bindValue(":grpID", $grpID, PDO::PARAM_INT);
			$openSearch->execute();
			
			echo "<tr class='oddRow'><td>Tasks still open:</td><td>".$openSearch->rowCount()."</td></tr>";
						
			echo "</table>";
			
			?>
		
		</div>

			<div class='content'>
		
			<h2 class="title">Download Data</h2>
			
			<a href="csv-stats.php">Right-click here</a> and choose to save as a file with a .csv extension.  You can then open the file in Excel.
		
		</div>
		
		<div class='content'>
		
			<h2 class="title">Overview Graph (not compatible with IE8)</h2>
			
			<br />	
				
			<?php	
			
				// Get dates for stored hitorical params
				$historySearch = $db->prepare("SELECT `date` FROM `group_history` WHERE `group`=:grpID GROUP BY `date` ORDER BY `date` DESC");
				$historySearch->bindValue(":grpID", $grpID, PDO::PARAM_INT);
				$historySearch->execute();
				$pastItems = $historySearch->rowCount();
			
						
				// Find parameters for this group
				$paramSearch = $db->prepare("SELECT `type_params`.`id`, `type_params`.`parameter`, `type_params`.`type`, `group_param`.`value` FROM `group_param` INNER JOIN `type_params` ON `group_param`.`type` = `type_params`.`id` WHERE `group_param`.`group` = :grpID AND `type_params`.`type`!='Text'");
				$paramSearch->bindValue(":grpID", $grpID, PDO::PARAM_INT);
				$paramSearch->execute();
				$entries = $paramSearch->rowCount();
				
				//$result = mysql_query("SELECT `type_params`.`id`, `type_params`.`parameter`, `type_params`.`type`, `group_param`.`value` FROM `group_param` INNER JOIN `type_params` ON `group_param`.`type` = `type_params`.`id` WHERE `group_param`.`group` = $grpID", $db);
				//if ($result) $entries = mysql_num_rows($result);
				//else $entries = 0;
				
				echo "<form action='stats.php#graph' method='get'>";
				
				if ( isset($_GET['p']) ) $pid=$_GET['p']; else $pid=1;
				
				// Loop and provide drop-down box
				echo "Choose the parameter to display: <select name='p'>";
				while ($param = $paramSearch->fetch() ){
					
					echo "<option value='".$param["id"]."'";
					if ($param["id"]==$pid) echo " selected='selected'";
					echo ">".$param["parameter"]."</option>";
				}
				echo "</select>";
				
				echo "<input type='submit' value='View' />";
				
				echo "</form>";		
			?>
			
			<p id="graph">
    			<canvas id="graphArea" width="800" height="400"></canvas>
    		</p>
			
		</div>

		<?php 
		
			} else { // Not in a group
		
			?>
				<div class="content">
						<p>You are not currently part of any group. Your tutor will be in touch once you have been added to one.</p>
				</div>	
			<?php
		
			}
		
		?>
	
</body>

<?php 

// Trigger the graph drawing
if ( isset($_GET['p']) ){

	$valueList = "";

	$dataSearch = $db->prepare("SELECT `group_history`.`value` FROM `group_history` INNER JOIN `type_params` ON `group_history`.`type` = `type_params`.`id` WHERE `group_history`.`group`=:grpID AND `type_params`.`id`=:pid ORDER BY `date` DESC");
	$dataSearch->bindValue(":grpID", $grpID, PDO::PARAM_INT);
	$dataSearch->bindValue(":pid", $pid, PDO::PARAM_INT);
	$dataSearch->execute();
	
	$paramSet = $dataSearch->fetchAll();
	for ($hlp=count($paramSet)-1; $hlp>=0; $hlp--) {
	
		$valueList = $valueList.$paramSet[$hlp]["value"].",";
	}
	
	// Add current value
	$currentSearch = $db->prepare("SELECT `value` FROM `group_param` WHERE `type`=:type AND `group`=:group");
	$currentSearch->bindValue(":type", $pid, PDO::PARAM_INT);
	$currentSearch->bindValue(":group", $grpID, PDO::PARAM_INT);
	$currentSearch->execute();
	
	// Must be a current value
	// - but if a ?, then just re-use last value.
	$value = $currentSearch->fetch();
	if ($value["value"] != "?") $valueList = $valueList.$value["value"];
	else $valueList = $valueList.$paramSet[0]["value"];
	
} else $valueList = "";
	
?>
	<script>
	
		// Graphics context
		var c = document.getElementById("graphArea");
		var ctx = c.getContext("2d");

		ctx.fillStyle="#EEEEEE";
		ctx.fillRect(0,0,800,400);
	
		// Draw Axis
		ctx.lineWidth = 2;
		ctx.beginPath();		// X
		ctx.moveTo(80, 380);
		ctx.lineTo(780, 380);
		ctx.stroke();
		ctx.beginPath();		// Y
		ctx.moveTo(80, 380);
		ctx.lineTo(80, 20);
		ctx.stroke();

		var values = [<?php echo $valueList; ?>];

		// Find min and max
		var min = values[0];
		var max = values[0];
		for (var lp=1; lp<values.length; lp++){

			if (values[lp]<min) min = values[lp];
			if (values[lp]>max) max = values[lp];
		}

		// Config variables
		var span = max - min;
		if (span==0){
			max += 10;
			min -= 10;
			span = 20;
		}
		var step = 700 / (values.length-1);
		var unit = 360/span;

		// Loop to display
		ctx.beginPath();
		ctx.lineWidth = 2;
		ctx.strokeStyle = '#0000DD';
		ctx.moveTo( 80, 380-(unit*(values[0]-min)) );
		for (var lp=1; lp<values.length; lp++){

			ctx.lineTo( 80+(lp*step), 380-(unit*(values[lp]-min)) );
		}
		ctx.stroke();

		// Label the axis
		ctx.font = 'normal 12pt Arial';
		ctx.fillStyle="#000000";
		ctx.textAlign = 'right';
		ctx.fillText(max.toString(), 70, 30);
		ctx.fillText(min.toString(), 70, 380);
		
	</script>

</html>