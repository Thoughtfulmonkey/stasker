<?php

// Most of the parameters come from the php page that includes this one.

require_once 'bill-class.php';
//include '../../dbconnect.php';

if ($params[0] > 0){

	$reminder = new Bill;
	
	// Set parameters
	if (count($params) > 1){
		$reminder->setTaskDetails("Payment Due", "<p>You have received a bill for ".$params[1].", to the amount of £".$params[0]."</p>");
	} else {
		$reminder->setTaskDetails("Payment Due", "<p>You have received a bill for the amount of £".$params[0]."</p>");
	}
	$reminder->setDaysToPay(2);
	$reminder->setOptions($params[0], "ScheduleReminder");
	
	$reminder->generate($targetGroup);
	
	
	echo "<p>Reminder scheduled</p>";
	
} else echo "<p>Unpaid bill for £0 ignored</p>";

?>