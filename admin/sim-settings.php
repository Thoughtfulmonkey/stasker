<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>

</head>

<body>
	
	<?php
		include '../dbconnect.php';
		include 'standard-header.php';
	?>
	
	<div class="content">
	
		<h2 class="title">Sim Settings</h2>
	
		<img class='bullet' src='<?php echo $stylePath; ?>/warning.png' alt='Warning' />These settings are important.<br /><br />
	
		<?php
			
			// Check for details being posted
			if ( isset($_POST['conf']) ){
				
				$numFields = sizeof($_POST);
				
				// Extract names of fields
				$posted = array_keys($_POST);
				
				// Loop through each posted variable
				foreach ($posted as $var){
				
					// update if not submit button of hidden field
					if ( ($var!="conf") && ($var!="submit") ) {
						
						$newVal = mysql_real_escape_string($_POST[$var]);
						mysql_query("UPDATE `sim` SET `$var`='$newVal' WHERE 1");
					}
				}
				
				echo "<p>Settings have been updated</p>";
				
			}
		
		
			// Retrieve all sim parameters
			$result = mysql_query("SELECT * FROM `sim`");
			
			// Must be a single row, otherwise things have gone badly wrong
			$row = mysql_fetch_assoc($result);
			
			// Extract names of fields - there's a mysql function to do this as well
			$varNames = array_keys($row);
			
			echo "<form method='POST' action='sim-settings.php'>";
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