<?php

// Most of the parameters come from the php page that includes this one.

require_once 'bill-class.php';
//include '../../dbconnect.php';

$reminder = new Bill;
$reminder->setDB($db);

// Set parameters
$reminder->setTaskDetails("County Court Judgement", "<p>Failure to pay the outstanding bill of £".$params[0]." has led to legal action being taken. You now have a CCJ lodged against you requiring you to pay.</p><p>Legal fees of £400 have been added, taking the total now due to £".($params[0]+400)."</p>");
$reminder->setDaysToPay(2);
$reminder->setOptions($params[0] + 400, "Bailiffs");

if (!$testrun) $reminder->generate($targetGroup);
else echo "<p> - not generated</p>";


echo "<p>Legal action taken.</p>";

?>