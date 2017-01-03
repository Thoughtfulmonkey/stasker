<?php

//include '../admin/param-parse.php';


// See if there is an option to ignore
// - returns -1 if there isn't
// - returns index of option if there is
//
function checkForIgnoreOption($voteOptionArray, $availableOptions) {

	$ignoreIndex = -1;
	
	for ($olp = 0; $olp < $availableOptions; $olp++){

		if ( $voteOptionArray[$olp]["description"]=="Ignore" ) $ignoreIndex = $olp;
	}
	
	if ($ignoreIndex != -1) echo "Choosing default ignore<br>";
	
	return $ignoreIndex;
}


//--------------------------------------------------------------------------------------------------------------
// Returns the chosen vote option (option ID) under four conditions
// - one option has more votes that any other: pick that one
// - some options have same votes: pick random one from voted options
// - no votes at all: pick a random one from options for the task
// - no options for the tasks: return -1
//
function findChosenOption($voteSearch, $taskinfoRef, $taskID, $db, $testrun) {
	
	$chosenVote = 0;
	$spreadCount = $voteSearch->rowCount();
	$allVotes = $voteSearch->fetchAll();
	
	echo "<p>";
	
	echo "Rows in vote spread: $spreadCount<br>";
	
	// If no votes then pick randomly
	if ($spreadCount == 0){
		// No votes were recieved
		echo "Processing: No votes<br>";
		
		// Search for all available options for the task
		$possibleVotes = $db->prepare("SELECT `id`, `description` FROM `option` WHERE `taskinfo`=:taskInfoRef");
		$possibleVotes->bindParam(":taskInfoRef", $taskinfoRef, PDO::PARAM_INT);
		$possibleVotes->execute();
		$availableOptions = $possibleVotes->rowCount();
		
		$voteOptionArray = $possibleVotes->fetchAll();
		
		echo "Available options: $availableOptions<br>";
		
		// May not be any options at all
		if ($availableOptions == 0){
			$chosenVote = -1;
		}else{
			
			// See if one of the options is to ignore
			$ignore = checkForIgnoreOption($voteOptionArray, $availableOptions);
			
			if ($ignore == -1) $rndIndex = rand(0, $availableOptions-1);
			else $rndIndex = $ignore;
			
			echo "System voting for index: $rndIndex<br>";
			$chosenVote = $voteOptionArray[$rndIndex]["id"];
			
			// Insert system vote for chosen option
			if (!$testrun) $db->query("INSERT INTO `vote` (`user`, `option`, `task`) VALUES ('0', '$chosenVote', '$taskID')");
			else echo " - Vote not saved<br>";
		}
	
	} else if ($spreadCount == 1) {
		// Only one option received votes
		// Leave $chosenVote as 0
		
		echo "Processing: Only votes for one option<br>";
		
		
	} else {
		// Votes spread across the different options - more complicated
	
		echo "Processing: Vote spread<br>";
		
		// Find options with highest number of votes
		$highVote = $allVotes[0]["votes"];
		
		echo "Highest vote: $highVote<br>";
		
		// If no clear winner then randomly choose the winner from top voted options
		if ( ($highVote == $allVotes[1]["votes"]) && ($spreadCount>1) ){
		
			echo "Processing: No clear winner<br>";
			
			// Create array of all equally top votes
			$highVotes = array();
			for ($rvlp=0; $rvlp<$spreadCount; $rvlp++){
				
				if ( $highVote == $allVotes[$rvlp]["votes"] ) array_push($highVotes, $rvlp); // Add to set
			}
						
			// Randomly pick one from the set
			$chosenVote = $highVotes[rand(0, count($highVotes)-1)];
			
			// Add system vote for chosen winner
			echo("INSERT INTO `vote` (`user`, `option`, `task`) VALUES ('0', '".$allVotes[$chosenVote]["option"]."', '$taskID')<br>");
			if (!$testrun)$db->query("INSERT INTO `vote` (`user`, `option`, `task`) VALUES ('0', '".$allVotes[$chosenVote]["option"]."', '$taskID')");
			else echo " - Vote not saved<br>";
			
			echo "System vote for multiple: $chosenVote<br>";
		
		} else {
			// Top vote was clear winner
			// Leave $chosenVote as 0
			
			echo "Processing: Clear winner<br>";
		}
	}
	
	// Special case when $spreadCount is zero i.e. no votes were cast
	if ($spreadCount > 0) return $allVotes[$chosenVote]["option"];  				// Return an option from the vote set
	else return $chosenVote;														// Return an option from all available votes
	
	echo "</p>";
}


//--------------------------------------------------------------------------------------------------------------
// Checks if supplied value is within the min and max limits for that parameter
//
function checkLimits ($value, $paramID, $db){
	
	// Find if there are any min/max values
	$limitSearch = $db->prepare("SELECT `min`, `max` FROM `type_params` WHERE `id`='$paramID'");
	$limitSearch->execute();
	$limits = $limitSearch->fetch();
	
	// Minimum boundary
	$min = $limits["min"];
	if ( !is_null($min) ) {
		if ($value < $min) {
			echo "<p>Limiting $value to minimum: $min</p>";
			$value = $min;
		}
	}
	
	// Maximum boundary
	$max = $limits["max"];
	if ( !is_null($max) ) {
		if ($value > $max) {
			echo "<p>Limiting $value to maximum: $max</p>";
			$value = $max;
		}
	}
	
	return $value;
}


//--------------------------------------------------------------------------------------------------------------
// Adjusting a chosen parameter
// - $optionDetails holds parameter to change and value adjustment
// TO DO: include min/max values
function adjustParam($effectDetails, $targetGroup, $db, $testrun){
	
	echo "<p>";
	echo "Action: Adjusting parameter<br>";
	
	$paramID = $effectDetails["choice"];
	$effect = $effectDetails["value"];
	
	$effectType = substr($effect, 0, 1);
	
	// Check if there is a value to change
	$paramSearch = $db->prepare("SELECT `value` FROM `group_param` WHERE `type`='$paramID' AND `group`='$targetGroup'");
	$paramSearch->execute();
	
	if ( $paramSearch->rowCount() > 0 ){
		
		$currentParam = $paramSearch->fetch();
		
		// See if a maths symbol is involved
		$mathSymbols = array("+", "-", "*", "/");
		if ( in_array($effectType, $mathSymbols) ) {
	
			// Extract the current value of the parameter
			$currentValue = floatval( $currentParam["value"] );
			echo "Current value: $currentValue<br>";
			
			// Extract the value to modify it by
			echo "Modify equation: $effect<br>";
			$modEq = substr($effect, 1, strlen($effect)-1);
			
			$parsedExEq = swapParamsForValues($modEq, $targetGroup, $db);
			if ($parsedExEq != "") $modifier = calculate_string($parsedExEq);
			else $modifier = 0;
			echo "Modify amount: $modifier<br>";
		
			// Could use a switch, but the ifs seem neater
			if ($effectType == "+") $currentValue += $modifier;
			if ($effectType == "-") $currentValue -= $modifier;
			if ($effectType == "*") $currentValue *= $modifier;
			if ($effectType == "/") $currentValue /= $modifier;
			
			$currentValue = checkLimits($currentValue, $paramID, $db);	// Restrict to min/max limits
			echo "Modified value: $currentValue<br>";
			
			if (!$testrun) $db->query("UPDATE `group_param` SET `value`='$currentValue' WHERE `type`='$paramID' AND `group`='$targetGroup'");
			else echo " - New value not saved<br>";

		} else if ($effectType == "="){
			// Set the value to be the one supplied (ignoring the preceeding equals)
			$modifier = substr($effect, 1, strlen($effect)-1);
			
			echo "Setting value to: $modifier<br>";
			
			if (!$testrun) $db->query("UPDATE `group_param` SET `value`='$modifier' WHERE `type`='$paramID' AND `group`='$targetGroup'");
			else echo " - New value not saved<br>";
			
		} else {
			// Set the value to be the one supplied
			// - This is a catch all.  It just swaps in the value, whether it is a number or text
			echo "Setting value to: $effect<br>";
			
			if (!$testrun)$db->query("UPDATE `group_param` SET `value`='$effect' WHERE `type`='$paramID' AND `group`='$targetGroup'");
			else echo " - New value not saved<br>";
		}
		
	} else {
		echo "No parameter for this group<br>";
	}

	echo "</p>";
	
}


//--------------------------------------------------------------------------------------------------------------
// Trigger another task
// - currently duration of all triggered tasks is 7 days
// - $optionDetails holds id of task to trigger, and in how many days time
function triggerTasks($effectDetails, $targetGroup, $db, $testrun){

	echo "<p>Action: Triggering task";
	
	// Create task group
	$chosenTask = $effectDetails["choice"];
	$startDelay = intval( $effectDetails["value"]);
	$endDelay = $startDelay + 7;

	echo "Triggering task with id: $chosenTask<br>";

	// Create task group entry, and find it's id
	// - start date will be current date plus delay
	// - end date will be start date plus 7 days
	// - delivery is 2 for specific groups
	// - author is 0 for system
	if (!$testrun){
		$insertQ = $db->query("INSERT INTO `task_group` (`delivery`, `content`, `author`, `task`, `startdate`, `enddate`) VALUES ('2', '0', '0', '$chosenTask', ADDDATE(CURDATE(),$startDelay), ADDDATE(CURDATE(),$endDelay))");

		$idSearch = $db->lastInsertId();
		
		echo "ID of task group: ".$idSearch."<br>";
		
		$db->query("INSERT INTO `task` (`taskgroup`, `group`, `taskinfo`) VALUES ('$idSearch', '$targetGroup', '$chosenTask')");
	
	} else echo " - New task not scheduled<br>";
	
	echo "</p>";
}


//--------------------------------------------------------------------------------------------------------------
// Run a script
function runScript($effectDetails, $targetGroup, $db, $testrun){

	echo "<p>Action: Running script<br>";

	$scriptName = $effectDetails["choice"];
	
	echo "Running script: $scriptName</p>";
	
	// Create param array
	$params = explode(";", $effectDetails["value"]);
	
	include './scripts/'.$scriptName.'.php';
	
}



//--------------------------------------------------------------------------------------------------------------
// Entry point

if ($correctKey){

	echo "<h2>Closing tasks</h2>";
	
	// Search for all tasks ending YESTERDAY
	//$now = date("Y-m-d");

	$tasks = $db->prepare("SELECT `task`.* FROM `task_group` INNER JOIN `task` ON `task_group`.`id` = `task`.`taskgroup` WHERE DATEDIFF( `task_group`.`enddate`, CURDATE() ) = 0");
	$tasks->execute();



	$numClosed = $tasks->rowCount();
	echo ("<p>$numClosed Task/s closing today</p>");
	
	// Loop through closing.
	// If none closing today, then doesn't enter the loop
	while ( $task = $tasks->fetch() ) {
	
		echo "<p>-------------------------------------</p>";
		echo "<p>Processing task: ".$task["id"]."</p>";
		
		// Find votes for the task
		$voteSearch = $db->prepare("SELECT *, COUNT(`option`) AS votes FROM `vote` WHERE `task`=:id GROUP BY `option` ORDER BY `votes` DESC");
		$voteSearch->bindParam(":id", $task['id'], PDO::PARAM_INT);
		$voteSearch->execute();
		
		$chosenOption = findChosenOption($voteSearch, $task['taskinfo'], $task["id"], $db, $testrun);
		
		echo "<p>Chosen option: $chosenOption</p>";
		
		// If an option was chosen
		if ($chosenOption >= 0){
		
			// Actions based on option
			$effectSearch = $db->prepare("SELECT * FROM `effect` WHERE `option`=:chosen");
			$effectSearch->bindValue(":chosen", $chosenOption, PDO::PARAM_INT);
			$effectSearch->execute();

			while ( $effectDetails = $effectSearch->fetch() ){
				
				// If param modifier, then adjust parameter
				if ( $effectDetails["type"] == "parameter" ) adjustParam($effectDetails, $task["group"], $db, $testrun);
					
				// If task trigger, then schedule the task - standard 1 week duration
				if ( $effectDetails["type"] == "task" ) triggerTasks($effectDetails, $task["group"], $db, $testrun);
					
				// If script trigger then run the script
				if ( $effectDetails["type"] == "script" ) runScript($effectDetails, $task["group"], $db, $testrun);
			}
		}
		
	} // Next task
	
} // else incorrect key


?>