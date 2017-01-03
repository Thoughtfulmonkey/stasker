<?php

include '../dbconnect.php';

// Function taken from http://www.website55.com/php-mysql/2010/04/how-to-calculate-strings-with-php.html
function calculate_string( $mathString )    {
	$mathString = trim($mathString);     // trim white spaces
	$mathString = str_replace ('[^0-9\+-\*\/\(\) ]', '', $mathString);    // remove any non-numbers chars; exception for math operators

	$compute = create_function("", "return (" . $mathString . ");" );
	return 0 + $compute();
}


// Parses the equation, swapping variables for values
function calcBillAmount($equation, $groupID){

	// Add a bit to the start of the equation so that zero index is never returned for [ location
	// - just multiply by 1
	$equation = "1*(".$equation.")";
	
	// See if it is an equation or an amount
	$brIndex = strpos($equation, "[");

	// If there are no square brackets, then it just skips to calculation
	while ($brIndex) {

		// Find end bracket
		$endIndex = strpos($equation, "]");

		// Split equation into 3 parts
		// - before variable, variable name, after variable
		$left = substr($equation, 0, $brIndex);
		$variable = substr($equation, $brIndex+1, $endIndex-$brIndex-1);
		$right = substr($equation, $endIndex+1, strlen($equation)-($endIndex+1) );

		// Search for varaible's value for this group
		$search = mysql_query("SELECT `value` FROM `group_param` INNER JOIN `type_params` ON `group_param`.`type` = `type_params`.`id` WHERE `group`='$groupID' AND `parameter`='$variable'");
		$value = mysql_result($search, 0, "value");

		// Reconstruct the equation - now with value instead of variable
		$equation = $left.$value.$right;

		// Find next one
		$brIndex = strpos($equation, "[");
	}

	// All variables should now be swapped
	// - call function to calculate result of the equation
	$billAmount = calculate_string($equation);
	
	return $billAmount;
}


calcBillAmount("([Balance]*2)+[Morale]", 5);

?>