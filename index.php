<?php

// Check if logged in
include 'login-check.php';

?>

<html>

<head>
<?php include 'user-style.php'; ?>
<title>Tasks</title>
</head>

<body>
	<?php
		include 'dbconnect.php';
		include 'student-header.php';
		include './admin/param-parse.php';
		
		// How many posts per page
		$ppp = 6;
		if ( isset($_GET['page']) ) $first = $_GET['page']*$ppp; else $first = 0;
		
		// Find user id
		$usrResult = $db->prepare("SELECT id FROM user WHERE `login`=:user");
		$usrResult->bindValue(":user", $_SESSION['username'], PDO::PARAM_STR);
		$usrResult->execute();
		$firstResult = $usrResult->fetch();
		$usrID = $firstResult['id'];
		
		// Find user's group id
		$grpResult = $db->prepare("SELECT `group` FROM `user` WHERE `id`=:user");
		$grpResult->bindValue(":user", $usrID, PDO::PARAM_INT);
		$grpResult->execute();
		$firstGroup = $grpResult->fetch();
		$grpID = $firstGroup["group"];
		
		//echo "<p>debug: ".$usrID."  ".$found."</p>";
		
		// If the user is assigned to a group
		if ($grpID != 0){
		
			// Cast vote if required
			if ( isset($_POST['options']) ){
				
				// Extract option id
				$optionID = $_POST['options'];
				$taskID = $_POST['taskID'];
				
				// See if this task already exists for this user
				$votes = $db->prepare("SELECT id FROM vote WHERE user=:usrID AND task=:taskID");
				$votes->bindValue(":usrID", $usrID, PDO::PARAM_INT);
				$votes->bindValue(":taskID", $taskID, PDO::PARAM_INT);
				$votes->execute();
				if ( $votes->rowCount() ){
					// Already exists = delete
					$votes = $db->prepare("DELETE FROM vote WHERE user=:usrID AND task=:taskID");
					$votes->bindValue(":usrID", $usrID, PDO::PARAM_INT);
					$votes->bindValue(":taskID", $taskID, PDO::PARAM_INT);
					$votes->execute();
				}
				
				// Add new vote
				$votes = $db->prepare("INSERT INTO vote (`user`, `option`, `task`) VALUES (:usrID, :optionID, :taskID)");
				$votes->bindValue(":usrID", $usrID, PDO::PARAM_INT);
				$votes->bindValue(":optionID", $optionID, PDO::PARAM_INT);
				$votes->bindValue(":taskID", $taskID, PDO::PARAM_INT);
				$votes->execute();
			}
			
			
			// Loop to view all tasks that have already started
			//  tasks now assigned to groups - users assigned to groups
			$tasks = $db->prepare("SELECT `task`.`taskinfo`, `task`.`id` AS tskID, `task_group`.*, `task_info`.* FROM  `task` INNER JOIN `task_group` ON `task`.`taskgroup`=`task_group`.`id` INNER JOIN `task_info` ON `task_group`.`task`=`task_info`.`id` WHERE (`task`.`group`=:grpID AND `task_group`.`startdate` <= CURDATE()) ORDER BY `task_group`.`startdate` DESC LIMIT :first, :ppp");
			$tasks->bindValue(":grpID", $grpID, PDO::PARAM_INT);
			$tasks->bindValue(":first", $first, PDO::PARAM_INT);
			$tasks->bindValue(":ppp", $ppp, PDO::PARAM_INT);
			$tasks->execute();
			
			
			// PHP 5.2
			$now = new DateTime();
			
			// PHP 5.1
			//$now = date("Y-m-d");
			
			// If some tasks have started
			$entries = $tasks->rowCount();
			if ($entries > 0){
				
				// Loop and output tasks
				while ( $task = $tasks->fetch() ){
					
					// Check if this task has closed
					$taskEnd = new DateTime( $task['enddate'] );  				// PHP 5.2
					//$taskEnd = $task['enddate'] 								// PHP 5.1
					
					//echo "<p>now: $now, db: $taskEnd</p>";
					
					if ($now > $taskEnd) $active = false;
					else $active = true;
				
					// Output actual task information
					echo "<div class='content'>";
					echo "<h2 class='sectionTitle'>".$task['title']."</h2>";
					
					// Say if active or not
					if ($active) echo "<p class='date'>Closes: ".$task['enddate']."</p>";
					else echo "<p class='date'>Closed: ".$task['enddate']."</p>";
					
					echo "<div class='taskDescription'>".$task['description']."</div>";
					
					
					// Search for options
					$optResult = $db->prepare("SELECT * FROM `option` WHERE `taskinfo`=:taskinfo");
					$optResult->bindValue(":taskinfo", $task['taskinfo'], PDO::PARAM_INT);
					$optResult->execute();
						
					// If there are some options
					if ($optResult->rowCount() > 0){
						// Loop and display options
						echo "<hr>";
						echo "<div class='optionBox'>";
						echo "<form method='post' action='index.php'>";
						
						// Use hidden field for task id
						echo "<input type='hidden' name='taskID' value='".$task['tskID']."'>";
						
						// Loop through options, displaying as radio buttons
						while ( $optEntry = $optResult->fetch() ){
							
							// See if option is a bill payment
							if ( strpos($optEntry["description"], "Pay £") === false){
								$description = $optEntry["description"];
							} else {
							
								// Separate the string
								$eqPart = substr($optEntry["description"], 5);
							
								// Swap params for values of this group
								$eqString = swapParamsForValues($eqPart, $grpID, $db);
								
								// Find actual values
								if ($eqString != "") $calcValue = calculate_string($eqString);
								else $calcValue = "0";
								
								$description = "Pay £".$calcValue;
							}
							
							// Search for votes
							$voteResult = $db->prepare("SELECT `vote`.*, `user`.`display_name` FROM `vote` INNER JOIN `user` ON `vote`.`user` = `user`.`id` WHERE `task`=:tskID AND `option`=:id");
							$voteResult->bindValue(":tskID", $task['tskID'], PDO::PARAM_INT);
							$voteResult->bindValue(":id", $optEntry['id'], PDO::PARAM_INT);
							$voteResult->execute();

							$voteEntries = $voteResult->rowCount();
							
							echo "<div class='voteOption'>";
							
							// If active, give choice of options, otherwise just display
							if ($active){
								echo "<input type='radio' name='options' value='".$optEntry["id"]."' />".$description;
							}else{
								echo $description;
							}
							
							// Loop and show votes for this option
							while ( $vote = $voteResult->fetch() ){
								echo "<img class='vote' src='$stylePath/tb.png' title='".$vote["display_name"]."'/>";
							}
							
							echo "</div>";
						}
						
						// Closing form and including submit button
						if ($active){ echo "<input type='submit' value='Choose' />"; }
						echo "</form>";
						echo "</div>";
					}
					
					echo "</div>"; // Close task div
					
				}
				
				// Add next and previous links if required
				if ( isset($_GET['page']) || $entries==$ppp ){
							
					echo "<div class='content'>";
					
					$page = 0;
					
					if ( isset($_GET['page']) ) {
						$page = $_GET['page'];
						if ($page>0) echo "<div id='leftBlock'><a href='index.php?page=".($page-1)."'>Newer</a></div>";
					}
					
					if ( $entries == $ppp ) echo "<div id='rightBlock'><a href='index.php?page=".($page+1)."'>Older</a></div>";
					echo "</div>";
				}
			
			}
			else {
				// No tasks were found
				?>
				<div class="content">
					<h2 class="title">No tasks were found.</h2>
				</div>	
				<?php
				
				if ( isset($_GET['page']) ){
					echo "<div class='content'>";
					$page = $_GET['page'];
					if ($page>0) echo "<div id='leftBlock'><a href='index.php?page=".($page-1)."'>Newer</a></div>";
					echo "</div>";
				}
			}
		
		} else { // Not in a group

			?>
			<div class="content">
				<p>You are not currently part of any group. Your tutor will be in touch once you have been added to one.</p>
			</div>	
			<?php

		}
			
	?>
	
</body>

</html>