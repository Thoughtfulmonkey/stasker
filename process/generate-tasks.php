<?php


echo "<h2>Generating Automatic Tasks</h2>";


echo "<p>Checking date from $gameDate + $dayStep days</p>";

// See if any auto tasks are closing within this range
// - current game date + day_step
// - extra check needed if gone past end of the month
if ($nextDom > $lastDom){
	$autoTask = mysql_query("SELECT * FROM `task_auto` WHERE ((`dom` >= $pastDom) AND (`dom` <= $lastDom)) OR ((`dom` >= 1) AND (`dom` <= $nextDom-$lastDom))");
}else{
	$autoTask = mysql_query("SELECT * FROM `task_auto` WHERE (`dom` >= $pastDom) AND (`dom` <= $nextDom)");
}
	

// Are any automatic tasks scheduled for this cycle?
if ($autoTask){
	$numOptions = mysql_num_rows($autoTask);
	
	echo "<p>$numOptions auto-tasks due this cycle</p>";
	
	// All bills affect balance.  Find it's param id
	$balSearch = mysql_query("SELECT `id` FROM `type_params` WHERE `parameter`='Balance'");
	$balanceID = mysql_result($balSearch, 0, "id");
	
	// Loop through tasks
	for ($tlp=0; $tlp<$numOptions; $tlp++){
	
		echo "<h3>Payment: ".mysql_result($autoTask, $tlp, "title")."</h3>";
		
		// Fetch group information
		// - filter based on group type
		$groups = mysql_query("SELECT * FROM `group` WHERE `type`='".mysql_result($autoTask, $tlp, "group_type")."'");
		
		// Almost certain to be some groups, but may as well check
		if ($groups){
			$numGroups = mysql_numrows($groups);
			
			echo "<p>Found $numGroups for this task.</p>";
			
			// Loop through all groups
			for ($glp=0; $glp<$numGroups; $glp++){
				
				$grpID = mysql_result($groups, $glp, "id");
				
				// calculate this group's bill
				$parsedEq = swapParamsForValues(mysql_result($autoTask, $tlp, "calc"), $grpID);
				$calcValue = calculate_string($parsedEq);
				
				echo "<p>Amount for group $grpID is &pound;$calcValue</p>";
				
				// Is it a bill or a payment?
				if ( mysql_result($autoTask, $tlp, "type") == "bill"){
					
					// Define text for task
					$taskTitle = mysql_result($autoTask, $tlp, "title")." bill due";
					$taskText = "<p>A bill has arrived for you to pay.  This is for:</p><p>".mysql_result($autoTask, $tlp, "description")."</p><p>Amount: &pound;$calcValue</p>";
					
					// Create a task info
					mysql_query("INSERT INTO `task_info` (`title`, `description`) VALUES ('$taskTitle', '$taskText')");
					$tskinfo = mysql_query("SELECT LAST_INSERT_ID() AS last");
					$chosenTask = mysql_result($tskinfo, 0, "last");
					
					// Create a task group
					// - Use 1 to indicate content is a bill
					mysql_query("INSERT INTO `task_group` (`delivery`, `content`, `author`, `task`, `startdate`, `enddate`) VALUES ('2', '1', '0', '$chosenTask', CURDATE(), ADDDATE(CURDATE(),2))");
					$tskgrp = mysql_query("SELECT LAST_INSERT_ID() AS last");
								
					// Create a task
					mysql_query("INSERT INTO `task` (`taskgroup`, `group`, `taskinfo`) VALUES ('".mysql_result($tskgrp, 0, "last")."', '$grpID', '$chosenTask')");
					
					
					// Define options
					// 1) pay the amount
					// 2) run script - which will set a reminder
					// task info, description
					$opRes = mysql_query("INSERT INTO `option` (`taskinfo`, `description`) VALUES ('$chosenTask', 'Pay the bill')");
					$opRes = mysql_query("SELECT LAST_INSERT_ID() AS last");
					mysql_query("INSERT INTO `effect` (`option`, `type`, `choice`, `value`) VALUES ('".mysql_result($opRes, 0, "last")."', 'parameter', '$balanceID', '-$calcValue')");
				
					mysql_query("INSERT INTO `option` (`taskinfo`, `description`) VALUES ('$chosenTask', 'Ignore')");
					$opRes = mysql_query("SELECT LAST_INSERT_ID() AS last");
					mysql_query("INSERT INTO `effect` (`option`, `type`, `choice`, `value`) VALUES ('".mysql_result($opRes, 0, "last")."', 'script', 'ScheduleReminder', '$calcValue')");
				
				} else { // Incoming money :D
					
					$taskTitle = mysql_result($autoTask, $tlp, "title")." payment";
					$taskText = "<p>You have received a payment.  This is for:</p><p>".mysql_result($autoTask, $tlp, "description")."</p><p>Amount: &pound;$calcValue</p>";
						
					// Create a task info
					mysql_query("INSERT INTO `task_info` (`title`, `description`) VALUES ('$taskTitle', '$taskText')");
					$tskinfo = mysql_query("SELECT LAST_INSERT_ID() AS last");
					$chosenTask = mysql_result($tskinfo, 0, "last");
						
					// Create a task group
					// - Use 2 to indicate content is a payment
					// - Task opens and closes yesterday: should be processed imediately
					mysql_query("INSERT INTO `task_group` (`delivery`, `content`, `author`, `task`, `startdate`, `enddate`) VALUES ('2', '2', '0', '$chosenTask', SUBDATE(CURDATE(),1), SUBDATE(CURDATE(),1))");
					$tskgrp = mysql_query("SELECT LAST_INSERT_ID() AS last");
					
					// Create a task
					mysql_query("INSERT INTO `task` (`taskgroup`, `group`, `taskinfo`) VALUES ('".mysql_result($tskgrp, 0, "last")."', '$grpID', '$chosenTask')");
						
						
					// Define option
					// - only 1: to collect the payment
					$opRes = mysql_query("INSERT INTO `option` (`taskinfo`, `description`) VALUES ('$chosenTask', 'Paid by bank transfer')");
					$opRes = mysql_query("SELECT LAST_INSERT_ID() AS last");
					mysql_query("INSERT INTO `effect` (`option`, `type`, `choice`, `value`) VALUES ('".mysql_result($opRes, 0, "last")."', 'parameter', '$balanceID', '+$calcValue')");
					
				}
				
				// Could easily handle cheques that need to be cashed.
				
			}
		}
		
	}// End of task loop
}


?>