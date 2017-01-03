<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
</head>

<body>

<?php
	include '../dbconnect.php';
	
	$currentLocation = "<a href='group-params.php'>Define Parameters</a> > Sync";
	include 'standard-header.php'; 
?>

<div class="content">
	
	<h2 class="title">Synchronising Parameters</h2>

	<?php 	
	
		// Search for parameters
		$params = $db->prepare("SELECT `id`, `parameter`, `type`, `default` FROM `type_params`");
		$params->execute();
		$numParams = $params->rowCount();
		
		
		echo "<h3 class='title'>Adding New Parameters</h3>";
		
		// Loop through parameters
		while ($param = $params->fetch()){
	
			echo "<h4>Updating parameter: ".$param['parameter']."</h4>";
			
			// Search for groups
			$groups = $db->prepare("SELECT `id` FROM `group`");
			$groups->execute();
			$numGroups = $groups->rowCount();
			
			// Loop through all groups
			while ($group = $groups->fetch()){
				
				// Search to see if the param already exists
				$groupParams = $db->prepare("SELECT `id` FROM `group_param` WHERE `type`=:type AND `group`=:group");
				$groupParams->bindValue(':type', $param['id'], PDO::PARAM_INT);
				$groupParams->bindValue(':group', $group['id'], PDO::PARAM_INT);
				$groupParams->execute();
				$numGroupParams = $groupParams->rowCount();
				
				// If is does then leave it, otherwise add it
				if ( $numGroupParams > 0 ) echo " - Aready exists for group ".$group['id']."<br />";
				else{
					
					$groupParams = $db->prepare("INSERT INTO `group_param` (`type`, `group`, `value`) VALUES (:type, :group, :default)");
					$groupParams->bindValue(':type', $param['id'], PDO::PARAM_INT);
					$groupParams->bindValue(':group', $group['id'], PDO::PARAM_INT);
					$groupParams->bindValue(':default', $param['default']);
					$groupParams->execute();

					echo " - Adding default value for group ".$group['id']."<br />";
				}
					
			}
		}

				
		// Remove all other parameters
		echo "<h3 class='title'>Purging Unused Parameters</h3>";
		
		// How many parameters before purge?
		// Search for groups
		$before = $db->prepare("SELECT `id` FROM `group_param`");
		$before->execute();
		$numBefore = $before->rowCount();
		echo "<p>$numBefore parameters before purge</p>";
		
		// Actual purge
		$db->query("DELETE FROM `group_param` WHERE `type` NOT IN (SELECT `id` FROM `type_params`)");
		
		// How many parameters after purge?
		$after = $db->prepare("SELECT `id` FROM `group_param`");
		$after->execute();
		$numAfter = $after->rowCount();
		echo "<p>$numAfter parameters after purge</p>";
		
	?>
	
</div>

</body>

</html>