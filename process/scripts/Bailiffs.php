<?php

// Most of the parameters come from the php page that includes this one.

require_once 'bill-class.php';
//include '../../dbconnect.php';

$reminder = new Bill;
$reminder->setDB($db);

// Set parameters
$reminder->setTaskDetails("Bailiffs", "<p>Bailiffs have been called in to seize assets.</p><p>With bailiff fees added, a total of £".($params[0]+300)." will be taken</p>");
$reminder->setDaysToPay(1);
$reminder->setOptions($params[0] + 300, "");

if (!$testrun) $reminder->generate($targetGroup);
else echo "<p> - not generated</p>";


echo "<p>Bailiffs called in.</p>";

?>