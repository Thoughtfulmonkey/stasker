<?php

echo "<h2>Applying equations</h2>";


//--------------------------------------------------------------------------------------------------------------
// Entry point

if ($correctKey){

	// Search for group types
	$groupTypes = $db->query("SELECT * FROM `group_type`");
	
	// Loop through all types
	while ($groupType = $groupTypes->fetch() ){
		
		// Extract type ID
		$typeID = $groupType["id"];
		
		echo "<p>-----------------------------------------------</p>";
		echo "<p>Group type: ".$groupType["name"]."</p>";
		
		
		// Loop twice - once for shared equations, once for group type specific
		for ($eqloop=0; $eqloop<2; $eqloop++){
		
			// Which set of equations
			if ($eqloop == 0){
				// Find shared equations
				$eqSearch = $db->query("SELECT * FROM `sim_equation` WHERE `groupType` IS NULL");
			} else {
				// Run through equations specifically for this group type
				$eqSearch = $db->query("SELECT * FROM `sim_equation` WHERE `groupType` = $typeID");
			}
		
			// Loop through shared equations
			while ($equation = $eqSearch->fetch() ){
		
				if ($eqloop == 0){
					echo "<p>Shared equation: ".$equation['name']."</p>";
				} else {
					echo "<p>Group type equation: ".$equation['name']."</p>";
				}
				
				// Find groups of this type
				$groups = $db->query("SELECT * FROM `group` WHERE `type`='$typeID'");
									
				// Loop through the groups
				while ($group = $groups->fetch()){
					
					echo "<p>";
					
					$groupID = $group["id"];
					
					// Swap params for values of this group
					$eqString = swapParamsForValues($equation['equation'], $groupID, $db);
					
					// Find actual values
					if ($eqString != "") $calcValue = calculate_string($eqString);
					else $calcValue = "0";
					
					// Multiply value by day step (equation should calculate value per day)
					$calcValue *= $dayStep;
					
					echo "Group $groupID:<br>&nbsp; ".$equation['equation']."<br>&nbsp; $eqString<br>&nbsp; calculated value $calcValue";
					
					// Find the target variable to update
					$targetSearch = $db->prepare("SELECT `value` FROM `group_param` WHERE `group`=:group AND `type`=:target");
					$targetSearch->bindValue(":group", $groupID, PDO::PARAM_INT);
					$targetSearch->bindValue(":target", $equation['target'], PDO::PARAM_INT);
					$targetSearch->execute();
					
					$targetVal = $targetSearch->fetch();
					
					// Update the group's variable
					$newVal = $targetVal['value'] + $calcValue;
					if (!$testrun) {
						$update = $db->prepare("UPDATE `group_param` SET `value`='$newVal' WHERE `group`=:group AND `type`=:target");
						$update->bindValue(":group", $groupID, PDO::PARAM_INT);
						$update->bindValue(":target", $equation['target'], PDO::PARAM_INT);
						$update->execute();
					}
					
					echo "<br>&nbsp; ".$targetVal['value']." + $calcValue = $newVal";
					
					// Also archive the value
					if (!$testrun){
						
						$db->query("UPDATE `group_history` INNER JOIN `type_params` ON `group_history`.`type` = `type_params`.`id` SET `value` = '$calcValue' WHERE `group`='$groupID' AND `parameter`='".$equation['name']."' AND `date`='$simDate'");
						
						//$db->query("UPDATE `group_history` SET `value` = '$calcValue' WHERE `group`='$groupID' AND `type`='".$equation['target']."' AND `date`='$simDate'");
					}
	
					echo "</p>";
					
				}// End of group loop
				
			}// End of equation loop		 
		
		}// End of equation split
			
	}// End of group type loop	

}// Incorrect key

?>