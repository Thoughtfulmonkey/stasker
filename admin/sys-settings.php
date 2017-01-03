<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>

</head>

<body>
	
	<?php
		include '../dbconnect.php';
		
		$currentLocation = "System Settings";
		include 'standard-header.php';
	?>
	
	<div class="content">
	
		<h2 class="title">System Settings</h2>
	
		<img class='bullet' src='<?php echo $stylePath; ?>/warning.png' alt='Warning' />These settings are important.<br /><br />
	
		<?php
			
			// Check for details being posted
			if ( isset($_POST['conf']) ){
				
				$numFields = sizeof($_POST);
				
				// Extract names of fields
				$posted = array_keys($_POST);
				
				// Loop through each posted variable
				foreach ($posted as $var){
				
					// update if not submit button or hidden field
					if ( ($var!="conf") && ($var!="submit") ) {
						
						// Sanitise field name (although should be safe)
						$field = filter_var($var, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
						
						// Update value
						$stmt = $db->prepare("UPDATE `sim` SET `$field`=:value WHERE 1");
						$stmt->bindValue(':value', $_POST[$var], PDO::PARAM_STR);
						$stmt->execute();
					}
				}
				
				echo "<p>Settings have been updated</p>";
				
			}
		
		
			// Retrieve all sim parameters
			$stmt = $db->query("SELECT * FROM `sim`");
			
			// Must be a single row, otherwise things have gone badly wrong
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			
			// Extract names of fields - there's a mysql function to do this as well
			$varNames = array_keys($row);
			
			echo "<form method='POST' action='sys-settings.php'>";
			echo "<input type='hidden' name='conf' value='true' />";
			
			echo "<table border='1'>";
			echo "<tr><th>Setting</th><th>Value</th></tr>";
			
			// Loop through all keys to display values
			foreach ($varNames as $var){

				// Output everything except the ID
				if ($var!="id") echo "<tr><td>$var</td><td><input type='text' value='".$row[$var]."' name='$var' /></td></tr>";
			}
			
			echo "</table>";
			
			echo "<br /><input type='submit' name='submit' value='Update Values' />";
			
			echo "</form>";
		?>
		
	</div>
	
</body>

</html>