<?php

//<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" ></script>

include '../dbconnect.php';

// Read in the security key
$keySearch = $db->prepare("SELECT `process_key` FROM sim");
$keySearch->execute();
$processResult = $keySearch->fetch();
$processKey = $processResult["process_key"];

// Check database key against supplied key
if ( isset($_GET['key']) ){
	if ($processKey == $_GET['key']){
		
		// Display the page
		?>

		<html>
		
		<head>
		<title>Manual Daily Processing</title>
		<script src="../admin/jquery-1.7.2.min.js" ></script>
		<script type="text/javascript">

			function triggerManual(){

				var key = "<?php echo $processKey ?>";
				
				var mode = $(".process:checked").val();					// Chosen mode
				var testrun = false;
				if ( $("#testrun").is(":checked") ) testrun = true;		// See if it should run purely as a simulation

				// Run the process
				window.location.href = "dailyprocess.php?key="+key+"&mode="+mode+"&testrun="+testrun+"&manual=true";
			}
			
		</script>
		</head>
		
		<body>

			<h3>Manual Daily Processing</h3>
			<form>
				<p>This process should be run once per day. It is recommended to do a test run first to check for any errors.</p>
			
				<p><input type="checkbox" name="testrun" value="do" id="testrun" checked>Perform a test run (no results saved)</p>
			
				<h4>Which processes should be run:</h4>

				&nbsp;<input type="radio" class="process" name="process" value="1" id="save">Save parameters<br>
				&nbsp;<input type="radio" class="process" name="process" value="2" id="genTasks">Generate tasks<br>
				&nbsp;<input type="radio" class="process" name="process" value="3" id="closeTasks">Close tasks<br>
				&nbsp;<input type="radio" class="process" name="process" value="4" id="update">Apply equations<br>
				&nbsp;<input type="radio" class="process" name="process" value="5" id="close">Record process as having run today<br>
				&nbsp;<input type="radio" class="process" name="process" value="0" id="all"><b>Run all processes</b><br>
				<br>
				
				<input type="button" value="Run process" onclick="triggerManual();">
			</form>
			
		</body>

		</html>
			
		<?php
	} else echo "<p>Incorrect key</p>";			// No html page structure, but they don't deserve it
} else echo "<p>No process key supplied</p>";

?>
