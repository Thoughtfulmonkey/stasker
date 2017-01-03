<?php

// Function taken from http://www.website55.com/php-mysql/2010/04/how-to-calculate-strings-with-php.html
function calculate_string( $mathString )    {

	$mathString = trim($mathString);     // trim white spaces
	//$mathString = str_replace ('[^0-9\+-\*\/\(\) ]', '', $mathString);    // remove any non-numbers chars; exception for math operators

	if ( !strpos($mathString, "class='unknown'") ){
		$mathString = preg_replace ('~[^0-9+-/(/)*]~', '', $mathString);    // remove any non-numbers chars; exception for math operators
		
		$compute = create_function("", "return (" . $mathString . ");" );

		return round(0 + $compute(), 2);
		
	} else return 0;	
}


function calculate_string_safe( $mathString ) {
	
	$result = 0;
	
	try {
		$result = calculate_string($mathString);
	} catch (Exception $e) {
		echo "<p>Error with equation</p>";
	}
	
	return $result;
}


// Parses the equation, swapping variables for values
function swapParamsForValues($equation, $groupID, $db){

	// Add a bit to the start of the equation so that zero index is never returned for [ location
	// - just multiply by 1
	//$equation = "1*(".$equation.")";

	// See if it is an equation or an amount
	$brIndex = strpos($equation, "[");

	// If there are no square brackets, then it just skips to calculation
	while ( ($brIndex) || (substr($equation, 0, 1)=="[") ) {

		// Find end bracket
		$endIndex = strpos($equation, "]");

		// Split equation into 3 parts
		// - before variable, variable name, after variable
		$left = substr($equation, 0, $brIndex);
		$variable = substr($equation, $brIndex+1, $endIndex-$brIndex-1);
		$right = substr($equation, $endIndex+1, strlen($equation)-($endIndex+1) );
		
		// Search for varaible's value for this group
		// Or from default values if id is -1
		if ($groupID != -1){
			$search = $db->query("SELECT `value` FROM `group_param` INNER JOIN `type_params` ON `group_param`.`type` = `type_params`.`id` WHERE `group`='$groupID' AND `parameter`='$variable'");
			if ( $search->rowCount() == 1 ) { $entry=$search->fetch(); $value = $entry["value"]; }
			else  $value = "<span class='unknown'>".$variable."</span>";
		} else {
			$search = $db->query("SELECT `default` FROM `type_params` WHERE `parameter`='$variable'");
			if ( $search->rowCount() == 1 ) { $entry=$search->fetch(); $value = $entry["default"]; }
			else $value = "<span class='unknown'>".$variable."</span>";
		}

		// Reconstruct the equation - now with value instead of variable
		$equation = $left.$value.$right;

		// Find next one
		$brIndex = strpos($equation, "[");
	}

	
	return $equation;
}

?>