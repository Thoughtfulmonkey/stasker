<?php include 'login-check.php'; ?>


<?php	

	include 'dbconnect.php';

	// Find user id
	$usrSearch = $db->prepare("SELECT id FROM user WHERE login=:user");
	$usrSearch->bindValue(":user", $_SESSION['username'], PDO::PARAM_STR);
	$usrSearch->execute();
	
	$usrEntries = $usrSearch->rowCount();
	$usr = $usrSearch->fetch();
	$usrID = $usr["id"];
	
	// Find user's group id
	$grpID = $_SESSION['groupnum'];
	
	// If the user is part of a group
	if ($grpID != 0){
		
		// Find game date
		$simData = $db->prepare("SELECT `sim_date` FROM `sim`");
		$simData->execute();
		$simResult = $simData->fetch();
		$gameDate = $simResult['sim_date'];
				
		// Get dates for stored hitorical params
		$historySearch = $db->prepare("SELECT `date` FROM `group_history` WHERE `group`=:grpID GROUP BY `date` ORDER BY `date` DESC");
		$historySearch->bindValue(":grpID", $grpID, PDO::PARAM_INT);
		$historySearch->execute();
		
		$pastItems = $historySearch->rowCount();
	
		echo "Parameter,";
		
		$date = $historySearch->fetchAll();
		for ($hlp=count($date)-1; $hlp>=0; $hlp--) { echo $date[$hlp]['date'].","; }
		echo $gameDate.",\n"; 
		
		// Find parameters for this group
		$paramSearch = $db->prepare("SELECT `type_params`.`id`,`type_params`.`parameter`, `type_params`.`type`, `group_param`.`value` FROM `group_param` INNER JOIN `type_params` ON `group_param`.`type` = `type_params`.`id` WHERE `group_param`.`group` = :grpID");
		$paramSearch->bindValue(":grpID", $grpID, PDO::PARAM_INT);
		$paramSearch->execute();
		
		// Output all parameters
		// Loop and output as table
		$oddRow = true;
		while ( $param = $paramSearch->fetch() ){
			echo $param["parameter"].",";
			$oddRow = !$oddRow;
			
			/*
			if ($pastItems) {
				// Historical data
				$paramHistory = $db->prepare("SELECT `type_params`.`parameter`, `type_params`.`type`, `group_history`.`value` FROM `group_history` INNER JOIN `type_params` ON `group_history`.`type` = `type_params`.`id` WHERE `group_history`.`group`=:grpID AND `type_params`.`parameter`=:param ORDER BY `date` DESC");
				$paramHistory->bindValue(":grpID", $grpID, PDO::PARAM_INT);
				$paramHistory->bindValue(":param", $param["parameter"], PDO::PARAM_STR);
				$paramHistory->execute();
				
				$historicalParam = $paramHistory->fetchAll();
				for ($hlp=count($pastItems); $hlp>=0; $hlp--) {

					if ($historicalParam[$hlp]["type"] == "Currency") echo "£".$historicalParam[$hlp]["value"].",";
					else echo $historicalParam[$hlp]["value"].","; 
				}
			}
			*/
			
			// Loop through all dates
			for ($hlp=count($date)-1; $hlp>=0; $hlp--) { 
			
				// Look for some data that matches - group, param type and date
				$query = "SELECT `value` FROM `group_history` WHERE `group`=$grpID AND `type`=".$param["id"]." AND `date`='".$date[$hlp]['date']."'";
				$paramHistory = $db->prepare($query);
				$paramHistory->execute();
				
				if ($paramHistory->rowCount()==1){
				
					$historyVal = $paramHistory->fetch();
				
					if ($param["type"] == "Currency") echo "£".$historyVal["value"].",";
					else echo $historyVal["value"].",";
				
				} else {
					echo ","; // No value recorded
					//echo $query;
				}
				
				// Add current values to end
				$currentParam = $db->prepare("SELECT `value` FROM `group_param` WHERE `group`=:group AND `type`=:type");
				$currentParam->bindValue(":group", $grpID, PDO::PARAM_INT);
				$currentParam->bindValue(":type", $param["id"], PDO::PARAM_INT);
				$currentParam->execute();
			}
			
			if ($currentParam->rowCount() == 1){
					
				$currentVal = $currentParam->fetch();
				
				if ($param["type"] == "Currency") echo "£".$currentVal["value"].",";
				else echo $currentVal["value"].",";
			}
			
			echo "\n";
		}
		
		/*
		// Find game date
		$dateSearch = $db->query("SELECT `sim_date` FROM `sim`");
		$dateFound = $dateSearch->fetch();
		$simDate = $dateFound["sim_date"];
	
		// Get dates for stored hitorical params
		$history = $db->query("SELECT `date` FROM `group_history` WHERE `group`='$grpID' GROUP BY `date` ORDER BY `date` DESC");
		if ($history) $pastItems = $history->rowCount(); else $pastItems = 0;
	
		// Output table headings
		echo "Parameter,";
		while($date = $history->fetch()) { echo "".$date["date"].","; }
		echo "Closing\n";
		
		// Find parameters for this group
		//$result = $db->query("SELECT `type_params`.`parameter`, `type_params`.`type`, `group_param`.`value` FROM `group_param` INNER JOIN `type_params` ON `group_param`.`type` = `type_params`.`id` WHERE `group_param`.`group` = $grpID");
		//if ($result) $entries = $result->rowCount();
		//else $entries = 0;

		/*
		// Output all parameters
		// Loop and output as csv
		while ($entry = $result->fetch()){
			
			echo $entry["parameter"].",";
			
			// Historical data
			$query = "SELECT `type_params`.`parameter`, `type_params`.`type`, `group_history`.`value` FROM `group_history` INNER JOIN `type_params` ON `group_history`.`type` = `type_params`.`id` WHERE `group_history`.`group`='$grpID' AND `type_params`.`parameter`='".$entry["parameter"]."' ORDER BY `date` DESC";
			if ($pastItems) $paramSet = $db->query($query);
			$fullData = $paramSet->fetchAll();
			
			for ($hlp=count($fullData); $hlp>=0; $hlp--) {
	
				if ($fullData[$hlp]["type"] == "Currency") echo "£".$fullData[$hlp]["value"].",";
				else echo "".$fullData[$hlp]["value"].","; 
			}
			
			if ($fullData[$hlp]["type"] == "Currency") echo "£".$fullData[$hlp]["value"];
			else echo "".$fullData[$hlp]["value"];
			
			echo "\n";
		}
		
		
		// Find parameters for this group
		$paramSearch = $db->prepare("SELECT `type_params`.`parameter`, `type_params`.`type`, `group_param`.`value` FROM `group_param` INNER JOIN `type_params` ON `group_param`.`type` = `type_params`.`id` WHERE `group_param`.`group` = :grpID");
		$paramSearch->bindValue(":grpID", $grpID, PDO::PARAM_INT);
		$paramSearch->execute();
		
		// Output all parameters
		while ( $param = $paramSearch->fetch() ){
			echo $param["parameter"].",";
				
			if ($pastItems) {
				// Historical data
				$paramHistory = $db->prepare("SELECT `type_params`.`parameter`, `type_params`.`type`, `group_history`.`value` FROM `group_history` INNER JOIN `type_params` ON `group_history`.`type` = `type_params`.`id` WHERE `group_history`.`group`=:grpID AND `type_params`.`parameter`=:param ORDER BY `date` DESC LIMIT 5");
				$paramHistory->bindValue(":grpID", $grpID, PDO::PARAM_INT);
				$paramHistory->bindValue(":param", $param["parameter"], PDO::PARAM_STR);
				$paramHistory->execute();
		
				$historicalParam = $paramHistory->fetchAll();
				for ($hlp=count($pastItems); $hlp>=0; $hlp--) {
		
					if ($historicalParam[$hlp]["type"] == "Currency") echo "£".$historicalParam[$hlp]["value"].",";
					else echo "".$historicalParam[$hlp]["value"];
				}
			}
				
			if ($param["type"] == "Currency") echo "£".$historicalParam[$hlp]["value"];
			else echo "".$historicalParam[$hlp]["value"];
		}
		
		echo "</table>";
		*/
		
	}
	
?>
