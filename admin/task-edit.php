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
		
		$currentLocation = "<a href='task-list.php'>Task List</a> &gt; Edit Task";
		include 'standard-header.php';
	?>
	
	<div class="content">
	
		<h2 class="title">Edit a Task</h2>
	
		<?php
		
			// If there are post details, then the form has been submitted
			if (isset($_POST['title'])){
			
				// Extract details and write to database
				$title = $_POST['title'];
				$description = $_POST['description'];
				
				// Only update title if not blank
				if ($title != ""){
					$stmt = $db->prepare("UPDATE task_info SET title=:title WHERE id=:saveid");
					$stmt->bindValue(':title', $title, PDO::PARAM_STR);
					$stmt->bindValue(':saveid', $_GET['id'], PDO::PARAM_INT);
					$stmt->execute();
				}

				// Update description.  Could be blank; although that would be weird
				$stmt = $db->prepare("UPDATE task_info SET description=:description WHERE id=:saveid");
				$stmt->bindValue(':description', $description, PDO::PARAM_STR);
				$stmt->bindValue(':saveid', $_GET['id'], PDO::PARAM_INT);
				$stmt->execute();

			}
		
			$recoveredTitle = "";
			$recoveredDesc = "";
			
			// If an ID has been provided then extract details for the task
			if (ISSET($_GET['id'])){

				$stmt = $db->prepare("SELECT title, description FROM task_info WHERE id=:id");
				$stmt->bindValue(':id', $_GET['id']);
				$stmt->execute();
				$entries = $stmt->rowCount();
				
				$row = $stmt->fetch();
				
				if ($entries > 0){
					$recoveredTitle = $row['title'];
					$recoveredDesc = $row['description'];
					
					// Display the edit form
					?>
					
						<form method='POST' action='task-edit.php?id=<?php echo $_GET['id']; ?>'>
							<div>Title:<input type="text" name="title" size="25" <?php if ($recoveredTitle!="") echo "value='$recoveredTitle'"; ?> ></div>
							<br />
							<textarea name="description"><?php if ($recoveredTitle!="") echo $recoveredDesc; ?></textarea>
							<br />
							<div><input type="submit" value="Update" name="submit"></div>
						</form>
			
						<script type="text/javascript">
							CKEDITOR.replace( 'description' );
						</script>
					
					<?php	
					
				}else{
					// No ID provided
					echo "<p>The chosen task cannot be found.</p>";
				}
				
			}else{
				// No ID provided
				echo "<p>A task ID has not been provided.</p>";
			}
		?>
		
		<?php
			
			// If there are post details, then the form has been submitted
			if (isset($_POST['title'])){

		?>

		<div align='center'>
			<p>Task updated</p>
			<p><a href="task-list.php">Click here</a> to return to the list of tasks.</p>
		</div>

		<?php
			}
		?>
	
	</div>
	
</body>

</html>