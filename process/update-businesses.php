<?php

echo "<h2>Updating Businesses</h2>";


// Loop through all business types
$groupTypes = mysql_query("SELECT * FROM `group_type`");

if ($groupTypes){
	$numGroupTypes = mysql_num_rows($groupTypes);
		
	// Loop through all types
	for ($tlp=0; $tlp < $numGroupTypes; $tlp++){

		// Extract type ID
		$typeID = mysql_result($groupTypes, $tlp, "id");

		echo "<p>-----------------------------------------------</p>";
		echo "<p>Group type: ".mysql_result($groupTypes, $tlp, "name")."</p>";
		
		// Find groups of this type
		$groups = mysql_query("SELECT * FROM `group` WHERE `type`='$typeID'");
		
		// Check that the equations exist
		$equations = mysql_query( "SELECT * FROM `sim_equation` WHERE `grouptype`='$typeID'" );
		$found = false;
		if ($equations) {
			if ( mysql_num_rows($equations) > 0 ) $found = true;
		}
		
		
		// If there are equations
		if ($found){
			
			echo "<p>Equations exist</p>";
			
			// Find which way around the equations are (income and expenditure)
			if ( mysql_result($equations, 0, "eqtype")==1 ) { $inID = 0; $exID = 1; }
			else { $inID = 1; $exID = 0; }
			
			// Store the equation strings
			$incomeEq = mysql_result($equations, $inID, "equation");
			$expenseEq = mysql_result($equations, $exID, "equation");
			
			if ($groups){
				
				// How many groups of this type
				$numGroups = mysql_num_rows($groups);
				echo "<p>$numGroups of this type</p>";
				
				// Loop through all groups of this type
				for ($glp=0; $glp < $numGroups; $glp++){
					
					$groupID = mysql_result($groups, $glp, "id");
					
					// Swap params for values of this group
					$parsedInEq = swapParamsForValues($incomeEq, $groupID);
					$parsedExEq = swapParamsForValues($expenseEq, $groupID);
					
					// Find actual values
					if ($parsedInEq != "") $actualIncome = calculate_string($parsedInEq);
					else $actualIncome = "0";
					
					if ($parsedExEq != "") $actualExpense = calculate_string($parsedExEq);
					else $actualExpense = "0";
					
					// Multiply income by day step
					$actualIncome *= $dayStep;
					$actualExpense *= $dayStep;
					
					echo "<p>Group $groupID, income &pound;$actualIncome, expense &pound;$actualExpense for $dayStep days</p>";
					
					// Update the groups' balance - maybe stock levels as well
					$profit = $actualIncome - $actualExpense;
					mysql_query("UPDATE `group_param` INNER JOIN `type_params` ON `group_param`.`type` = `type_params`.`id` SET `value` = `value` + $profit WHERE `group`='$groupID' AND `parameter`='Balance'");
					
					// Update archived params for income and expense
					mysql_query("UPDATE `group_history` INNER JOIN `type_params` ON `group_history`.`type` = `type_params`.`id` SET `value` = '$actualIncome' WHERE `group`='$groupID' AND `parameter`='Income' AND `date`='$gameDate'");
					mysql_query("UPDATE `group_history` INNER JOIN `type_params` ON `group_history`.`type` = `type_params`.`id` SET `value` = '$actualExpense' WHERE `group`='$groupID' AND `parameter`='Expenses'AND `date`='$gameDate'");
					
				}// End of group loop
			}
			
		} else echo "<p>There are no equations available for this group type</p>";

		
		
	}// End of group type loop
	
}// End of check for group types


?>