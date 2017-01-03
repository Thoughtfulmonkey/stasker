<?php


echo "<h2>Dealing with Loan Repayments</h2>";


echo "<p>Checking date from $gameDate + $dayStep days (max $lastDom)</p>";

// What day are loan repayments??
$loanDay = 1;
$isLoanDay = false;

// See if any auto tasks are closing within this range
// - current game date + day_step
if ($nextDom > $lastDom){
	if ((($loanDay >= $pastDom) && ($loanDay <= $lastDom)) || (($loanDay >= 1) && ($loanDay <= $nextDom-$lastDom))) $isLoanDay= true;
}else{
	if (($loanDay >= $pastDom) && ($loanDay <= $nextDom)) $isLoanDay = true;
}


// Are any automatic tasks scheduled for this cycle?
if ($isLoanDay){
	
	echo "<p>It's loan day</p>";
	
	// Payment affects balance
	$balSearch = mysql_query("SELECT `id` FROM `type_params` WHERE `parameter`='Balance'");
	$balanceID = mysql_result($balSearch, 0, "id");
	
	// Payment also affects outstanding loan amount
	$loanSearch = mysql_query("SELECT `id` FROM `type_params` WHERE `parameter`='Start up loan'");
	$loanID = mysql_result($loanSearch, 0, "id");
	
	// All groups need to pay off their loans
	$groups = mysql_query("SELECT * FROM `group`");
	
	// Almost certain to be some groups, but may as well check
	if ($groups){
		$numGroups = mysql_numrows($groups);
		
		echo "<p>Found $numGroups to repay loans.</p>";
		
		// Loop through all groups
		for ($glp=0; $glp<$numGroups; $glp++){
			
			$grpID = mysql_result($groups, $glp, "id");
			
			// calculate this group's bill
			$repay = mysql_query("SELECT `value` FROM `group_param` INNER JOIN `type_params` ON `group_param`.`type` = `type_params`.`id` WHERE `type_params`.`parameter` = 'Start Up Loan Repayment' AND `group_param`.`group`=$grpID");
			$repayAmount = mysql_result($repay, 0, "value");
			
			echo "<p>Amount for group $grpID is &pound;$repayAmount</p>";
			
							
			// Define text for task
			$taskTitle = "Loan Repayment";
			$taskText = "<p>A bill for the repayment of your loan has arrived.</p><p>Amount: &pound;$repayAmount</p>";
			
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
			mysql_query("INSERT INTO `effect` (`option`, `type`, `choice`, `value`) VALUES ('".mysql_result($opRes, 0, "last")."', 'parameter', '$balanceID', '-$repayAmount')");
			mysql_query("INSERT INTO `effect` (`option`, `type`, `choice`, `value`) VALUES ('".mysql_result($opRes, 0, "last")."', 'parameter', '$loanID', '-$repayAmount')");
		
			mysql_query("INSERT INTO `option` (`taskinfo`, `description`) VALUES ('$chosenTask', 'Ignore')");
			$opRes = mysql_query("SELECT LAST_INSERT_ID() AS last");
			mysql_query("INSERT INTO `effect` (`option`, `type`, `choice`, `value`) VALUES ('".mysql_result($opRes, 0, "last")."', 'script', 'ScheduleReminder', '$repayAmount')");

		}
		
	}// End of task loop
}
else { echo "<p>Not loan day</p>"; }


?>