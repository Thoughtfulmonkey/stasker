<?php include 'login-check.php'; ?>

<div style="text-align:left; padding:10px; background-color: #FFFFFF">

<?php 

include 'admin-style.php';
include '../dbconnect.php';
include 'param-parse.php';

// -------------------------------------------------------------------------------------------------------------------------------------
// Display of current data
	
// Loop through all business types
$groupTypes = $db->query("SELECT * FROM `group_type`");

while ($groupType = $groupTypes->fetch()){

	// Extract type ID
	$typeID = $groupType["id"];

	//echo "<p>Group type: ".$groupType["name"]."</p>";
	
	// Find groups of this type
	$groups = $db->query("SELECT * FROM `group` WHERE `type`='$typeID'");
	
		
	if ($groups->rowCount() > 0){
			
		// How many groups of this type
		$numGroups = $groups->rowCount();
		
		echo "<table>";
		echo "<tr><th>Type</th><th>Group name</th><th>Balance</th>";
		
		// Loop to display headings for shared equations
		$sharedEq = $db->query("SELECT `sim_equation`.*, `type_params`.`parameter` FROM `sim_equation` JOIN `type_params` ON `sim_equation`.`target`=`type_params`.`id` WHERE `groupType` IS NULL");
		$allShared = $sharedEq->fetchAll();
		for ($lp=0; $lp<count($allShared); $lp++){
			echo "<th>".$allShared[$lp]["name"]."</th>";
		}
		
		// Loop to display headings for unique equations
		$uniqueEq = $db->query("SELECT `sim_equation`.*, `type_params`.`parameter` FROM `sim_equation` JOIN `type_params` ON `sim_equation`.`target`=`type_params`.`id` WHERE `groupType` = $typeID");
		$allUnique = $uniqueEq->fetchAll();
		for ($lp=0; $lp<count($allUnique); $lp++){
			echo "<th>".$allUnique[$lp]["name"]."</th>";
		}
		
		echo "</tr>";
		
		// Loop through all groups of this type
		while ($group = $groups->fetch()){
			
			echo "<tr>";
			
			echo "<td>".$groupType["name"]."</td>";
			
			echo "<td>".$group["name"]."</td>";
			
			// Find current balance
			$balanceSearch = $db->query("SELECT `value` FROM `group_param` INNER JOIN `type_params` ON `group_param`.`type` = `type_params`.`id` WHERE `group`='".$group["id"]."' AND `parameter`='Balance'");
			$balance = 0; 
			if ($balanceSearch){
				if ($balanceSearch->rowCount() > 0){ 
					$found = $balanceSearch->fetch();
					$balance = $found["value"]; 
				}
			}  

			if ($balance>0)	echo "<td class='inblack'>&pound;$balance</td>";
			else echo "<td class='inred'>&pound;$balance</td>";
			
			// Loop to display calculated values for shared equations
			for ($lp=0; $lp<count($allShared); $lp++){
				$parsed = swapParamsForValues($allShared[$lp]["equation"], $group["id"], $db);
				$calc = calculate_string($parsed);
				echo "<td>+".$calc."</td>";
			}
			
			// Loop to dispaly calculated values for unique equations
			for ($lp=0; $lp<count($allUnique); $lp++){
				$parsed = swapParamsForValues($allUnique[$lp]["equation"], $group["id"], $db);
				$calc = calculate_string($parsed);
				echo "<td>+".$calc."</td>";
			}
			
			/*
			// Swap params for values of this group
			$parsedInEq = swapParamsForValues($incomeEq, mysql_result($groups, $glp, "id"));
			$parsedExEq = swapParamsForValues($expenseEq, mysql_result($groups, $glp, "id"));
			
			// Find actual values
			if ($parsedInEq != "") $actualIncome = calculate_string($parsedInEq);
			else $actualIncome = "0";
			
			if ($parsedExEq != "") $actualExpense = calculate_string($parsedExEq);
			else $actualExpense = "0";
			
			echo "<td>&pound;$actualIncome</td><td>&pound;$actualExpense</td>";
			
			if ($actualIncome-$actualExpense > 0) echo "<td class='inblack'>&pound;".($actualIncome-$actualExpense)."</td>";
			else echo "<td class='inred'>&pound;".($actualIncome-$actualExpense)."</td>";
					
			if ($balance+($actualIncome-$actualExpense) > 0) echo "<td class='inblack'>&pound;".($balance+($actualIncome-$actualExpense))."</td>";
			else echo "<td class='inred'>&pound;".($balance+($actualIncome-$actualExpense))."</td>";
			*/
			echo "</tr>";
		}
		
		echo "</table>";
		echo "<br>";
	}
		
}
	

?>

</div>
