<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>	
</head>

<body>
	
	<?php
		include '../dbconnect.php';
		
		$currentLocation = "Task List > Create a Task";
		include 'standard-header.php';
	?>
	
	<div class="content">
	
		<h2 class="title">Create a Task</h2>
	
		<?php

			$added = false;
		
			// If there are post details, then the forms must have been submitted.
			if (isset($_POST['title'])){

				// Extract details and write to database
				$title = $_POST['title'];
				$description = $_POST['description'];
				
				if ($title != ""){
				
					$stmt = $db->prepare("INSERT INTO task_info (author, title, description) VALUES ('1', :title, :description)");
					$stmt->bindValue(':title', $title, PDO::PARAM_STR);
					$stmt->bindValue(':description', $description);
					$stmt->execute();
				
					$added = true;
				
				// Display confirmation and links to add options or return to admin page
		?>

		<div align='center'>
			<p>Task added</p>
			<p><a href="task-list.php">Click here</a> to return to the list of tasks, or <a href="task-create.php">add another</a></p>
		</div>

		<?php
		
				} else echo "<script type='text/javascript'>alert('You need to include a title.');</script>";
			}
			
			if (!$added){
			
				// If there were not any post details, then show the form to add a task
		?>

		<form method='POST' action='task-create.php'>
			<div>Title:<input type="text" name="title" size="25"></div>
			<br />
			<textarea name="description"><?php if (isset($_POST['description'])){ echo $_POST['description']; } ?></textarea>
			<br />
			<div><input type="submit" value="Add New" name="submit"></div>
		</form>

		<script type="text/javascript">
			CKEDITOR.replace( 'description' );
		</script>
		
		<?php
			}
		?>
		
	</div>

</body>

</html>