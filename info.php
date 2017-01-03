<?php

// Check if logged in
include 'login-check.php';

?>

<html>

<head>
<?php include 'user-style.php'; ?>
<title>Statistics</title>
</head>

<body>
	<?php
		include 'dbconnect.php';
		include 'student-header.php';
		
		// Find user id
		$usrResult = $db->prepare("SELECT id FROM user WHERE `login`=:user");
		$usrResult->bindValue(":user", $_SESSION['username'], PDO::PARAM_STR);
		$usrResult->execute();
		$firstResult = $usrResult->fetch();
		$usrID = $firstResult['id'];
		
		// Find user's group id
		$grpID = $_SESSION['groupnum'];
		
		// If the user is part of a group
		if ($grpID != 0){
		
			// Find game date
			$simData = $db->prepare("SELECT `sim_date` FROM `sim`");
			$simData->execute();
			$simResult = $simData->fetch();
			$gameDate = $simResult['sim_date'];

		?>
		
		<div class='content'>
		
			<div class="animHolder">
				<p>Simulation Date: <?php echo $gameDate; ?></p>	
				<embed width="400" height="150" src="./FactoryView/animation.swf" />
			</div>
		
		</div>
		
		
		<div class='content'>
		
			<h2 class="title">Statistics</h2>
			
			<br />	
				
			<?php	
			
				// Get dates for stored hitorical params
				$historySearch = $db->prepare("SELECT `date` FROM `group_history` WHERE `group`=:grpID GROUP BY `date` ORDER BY `date` DESC LIMIT 5", array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$historySearch->bindValue(":grpID", $grpID, PDO::PARAM_INT);
				$historySearch->execute();
				
				$pastItems = $historySearch->rowCount();
				
				//echo "<p>Past items: $pastItems</p>";
			
				// Output table headings
				echo "<table border='1'>";
				echo "<tr><th>Parameter</th>";
				
				$date = $historySearch->fetchAll();
				for ($hlp=count($date)-1; $hlp>=0; $hlp--) { echo "<th>".$date[$hlp]['date']."</th>"; }
				echo "<th>Current</th>"; 
				
				// Find parameters for this group
				$paramSearch = $db->prepare("SELECT `type_params`.`parameter`, `type_params`.`type`, `group_param`.`value` FROM `group_param` INNER JOIN `type_params` ON `group_param`.`type` = `type_params`.`id` WHERE `group_param`.`group` = :grpID");
				$paramSearch->bindValue(":grpID", $grpID, PDO::PARAM_INT);
				$paramSearch->execute();
				
				// Output all parameters
				// Loop and output as table
				$oddRow = true;
				while ( $param = $paramSearch->fetch() ){
					if ($oddRow) echo "<tr class='oddRow'><td>".$param["parameter"]."</td>";
					else echo "<tr class='evenRow'><td>".$param["parameter"]."</td>";
					$oddRow = !$oddRow;
					
					if ($pastItems) {
						// Historical data
						$paramHistory = $db->prepare("SELECT `type_params`.`parameter`, `type_params`.`type`, `group_history`.`value` FROM `group_history` INNER JOIN `type_params` ON `group_history`.`type` = `type_params`.`id` WHERE `group_history`.`group`=:grpID AND `type_params`.`parameter`=:param ORDER BY `date` DESC LIMIT 5");
						$paramHistory->bindValue(":grpID", $grpID, PDO::PARAM_INT);
						$paramHistory->bindValue(":param", $param["parameter"], PDO::PARAM_STR);
						$paramHistory->execute();
						
						$historicalParam = $paramHistory->fetchAll();
						for ($hlp=$pastItems-1; $hlp>=0; $hlp--) {
		
							if ($historicalParam[$hlp]["type"] == "Currency") echo "<td>&pound;".$historicalParam[$hlp]["value"]."</td>";
							else echo "<td>".$historicalParam[$hlp]["value"]."</td>"; 
						}
					}
					
					if ($param["type"] == "Currency") echo "<td>&pound;".$param["value"]."</td></tr>";
					else echo "<td>".$param["value"]."</td></tr>";
				}
				
				echo "</table>";
			?>
			
		</div>
		
		
		<?php 
		
			} else { // Not in a group
		
			?>
				<div class="content">
						<p>You are not currently part of any group. Your tutor will be in touch once you have been added to one.</p>
				</div>	
			<?php
		
			}
		
		?>
	
</body>

</html>