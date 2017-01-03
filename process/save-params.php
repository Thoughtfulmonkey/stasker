<?php

	if ($correctKey){

		echo "<h2>Saving group parameters</h2>";
	
		// Loop through all groups
		$groups = $db->prepare("SELECT * FROM `group`");
		$groups->execute();

		
		while ( $group = $groups->fetch() ){
	
			echo "<p>-----------------------------------------------</p>";
			echo "<p>Saving params for group: ".$group["name"]."</p>";
			
			// Select all parameters
			$params = $db->prepare("SELECT * FROM `group_param` WHERE `group`='".$group["id"]."'");
			$params->execute();
			
			//for ($plp=0; $plp<$numParams; $plp++){
			while ( $param = $params->fetch() ){
				
				$query = "INSERT INTO `group_history` (`type`, `group`, `value`, `date`) VALUES ('".$param["type"]."', '".$param["group"]."', '".$param["value"]."', '$simDate')";
				echo "$query<br>";
				
				// Only update db if not running as simulation
				if (!$testrun) {
					$db->exec($query);
				}
				else echo " - not saved<br>"; 
			}
			
		}
		
	} // else incorrect key
	
?>