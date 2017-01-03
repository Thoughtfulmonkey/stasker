<?php

// Most of the parameters come from the php page that includes this one.

require_once 'bill-class.php';
require_once './../admin/param-parse.php';
//include '../../dbconnect.php';

// Convert $params[0] to an actual value
// Swap params for values of this group


$eqString = swapParamsForValues($params[0], $targetGroup, $db);

// Find actual values
if ($eqString != "") $calcValue = calculate_string($eqString);
else $calcValue = "0";


if ($calcValue > 0){

	$reminder = new Bill;
	$reminder->setDB($db);
	
	// Set parameters
	$reminder->setTaskDetails("Payment Overdue", "<p>You have an outstanding payment for the amount of £".$calcValue."</p><p>Failure to pay will result in legal action being taken.</p>");
	$reminder->setDaysToPay(2);
	$reminder->setOptions($calcValue, "LegalAction");
	
	if (!$testrun) $reminder->generate($targetGroup);
	else echo "<p> - not generated</p>";
	
	
	echo "<p>Reminder scheduled for $calcValue</p>";
	
} else echo "<p>Unpaid bill for £0 ignored</p>";

?>