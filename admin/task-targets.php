<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
<link rel="stylesheet" href="../kalendae/kalendae.css" type="text/css">


</head>

<body>
	<?php
		include '../dbconnect.php';
		
		$currentLocation = "<a href='task-schedule.php'>Scedule Tasks</a> &gt; Targetted Groups";
		include 'standard-header.php';
	?>

	<div class="content">
		<h2 class="title">Targetted Groups</h2>
		
		<?php
		
		if (isset($_GET['id'])) {
		
			// List all available tasks
			$targets = $db->prepare("SELECT `group`.`id`, `group`.`name` FROM `group` INNER JOIN `task` ON `group`.`id` = `task`.`group` WHERE `task`.`taskgroup` = :taskgroup");
			$targets->bindValue(":taskgroup", $_GET['id'], PDO::PARAM_INT);
			$targets->execute();
			
			
			// Output schedule
			echo "<table border='1'>";
			echo "<tr><th></th><th>Name</th></tr>";
		
			// Loop and output as table
			while($target = $targets->fetch()){
		
				echo "<tr><td><input type='checkbox' name='groups[]' value='".$target['id']."' checked='checked' /></td>";
				echo "<td>".$target['name']."</td></tr>";
			}
			echo "</table>";
			
			echo "<p><a href='task-schedule.php'>Return</a>";
		}
		
		?>
		
	</div>
	
</body>

</html>