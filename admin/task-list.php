<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
</head>

<body>
	
	<?php
		include '../dbconnect.php';
		
		$currentLocation = "Task List";
		include 'standard-header.php';
	?>
	
	<div class="content">
		
		<h2 class="title">List Available Tasks</h2>
		
		<?php
			
			// Toggle of showing system tasks
			if ( isset($_GET['showall']) ){
				if ( $_GET['showall'] == 'true') $_SESSION['showall'] = true;
				else $_SESSION['showall'] = false;
			}
			if ( !isset($_SESSION['showall']) ) $_SESSION['showall'] = false;
		
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Dealing with changes to the data - forms submissions etc.
			
			// Delete if required
			if (isset($_GET['delete'])){
					
				$delete = $db->prepare("DELETE FROM `task_info` WHERE `id`=:delete");
				$delete->bindParam(":delete", $_GET['delete'], PDO::PARAM_INT);
			}
			
		
			// -------------------------------------------------------------------------------------------------------------------------------------
			// Display of current data
		
			// Search for all tasks
			if ( $_SESSION['showall'] ) $stmt = $db->prepare("SELECT `id`, `title`, `author` FROM `task_info`");
			else $stmt = $db->prepare("SELECT `id`, `title`, `author` FROM `task_info` WHERE `author`>0");

			$stmt->execute();
			$numtasks = $stmt->rowCount();
			
			
			// Show/Hide system tasks option
			if (  $_SESSION['showall'] ) echo "<p><a href='task-list.php?showall=false'><img class='bullet' src='$stylePath/contract.png' title='Hide' alt='Hide' />Hide system tasks</a></p>";
			else echo "<p><a href='task-list.php?showall=true'><img class='bullet' src='$stylePath/expand.png' title='Show' alt='Show' />Show system tasks</a></p>";
			
			// If any were found
			if ($numtasks > 0){
			
				// Create a table displaying them
				echo "<table border='1'>";
				echo "<tr><th>Task Name</th><th>Actions</th><th>Add Options</th></tr>";
			
				while ($task = $stmt->fetch()){
				
					echo "<tr>";
					echo "<td>".$task['title']."</td>";
					echo "<td><a href='task-edit.php?id=".$task['id']."'><img src='$stylePath/edit.png' title='edit' alt='edit' /></a>";
					
					// Deleting is more complicated
					//echo "<a href='task-list.php?delete=".mysql_result($result, $lp, "id")."'><img src='$stylePath/del.png' title='delete' alt='delete' /></a>";
					
					echo "</td>";
					echo "<td><a href='options-add.php?id=".$task['id']."'>Add options</a></td>";
					echo "</tr>";
				}
			
				echo "</table>";
			
			} else {
				echo "<p>No entries were found :( </p>";
			}
		?>
		
		<br /><div><a href='task-create.php'><img class='bullet' src='<?php echo $stylePath; ?>/add.png' />Create New Task</a></div>
		
	</div>
	
</body>

</html>