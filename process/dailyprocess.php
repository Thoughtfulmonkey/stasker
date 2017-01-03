<?php

include '../dbconnect.php';
include '../admin/param-parse.php';

// Create logfile name
//$fname = date("Y-m-d-H-i").".txt";
//$file = fopen($fname, 'a');

// Get current sim information
$simSearch = $db->prepare("SELECT `day_step`, `sim_date`, DAYOFMONTH(`sim_date`) AS dom, DAYOFMONTH(LAST_DAY(`sim_date`)) AS ldom FROM `sim` WHERE `last_process` < CURDATE()");
//$simSearch = $db->prepare("SELECT * FROM `sim` WHERE `last_process` < CURDATE()");
$simSearch->execute();


if ( $simSearch->rowCount()>0 ){

	$simData = $simSearch->fetch();
	
	$simDate = $simData["sim_date"];
	$dayStep = $simData["day_step"];
	$pastDom = $simData["dom"];
	$lastDom = $simData["ldom"];
	
	//$nextDom = $pastDom + $dayStep; // Don't care about going over 31 days.
									// SHOULD CARE. What if key date is at start of the month
	
	// Read in the security key
	$keySearch = $db->prepare("SELECT `process_key` FROM sim");
	$keySearch->execute();
	$processResult = $keySearch->fetch();
	$processKey = $processResult["process_key"];
	
	// Check database key against supplied key
	$correctKey = false;
	if ( isset($_GET['key']) ){
		if ($processKey == $_GET['key']){
			$correctKey = true;
		}
	}
	
	// Check if in simulation only mode
	$testrun = false;
	if ( isset($_GET['testrun']) ){
		if ( $_GET['testrun'] == "true") $testrun = true;
	}
	
	
	// Mode to trigger individual steps
	if ( isset($_GET['mode']) ) $mode = $_GET['mode'];
	else $mode = 0;
	
	// Store current parameters
	if (($mode==0) || ($mode==1)) include 'save-params.php';
	
	// Generate system tasks - no more system tasks?
	//if (($mode==0) || ($mode==2)) include 'generate-tasks.php';
	
	// Generate tasks for loan repayment
	//if (($mode==0) || ($mode==6)) include 'loan-repayment.php';
	
	// Process choices for closing tasks
	if (($mode==0) || ($mode==3)) include 'closing-tasks.php';
	
	// Apply equations
	if (($mode==0) || ($mode==4)) include 'apply-equations.php';
	
	// Close the log file
	//fclose($file);
	
	// Update the simulation data
	
	if (($mode==0) || ($mode==5)) {
		
		echo "<h2>Updating dates</h2>";
		
		if (!$testrun){
			$db->query("UPDATE `sim` SET `sim_date`=ADDDATE(`sim_date`, $dayStep) WHERE 1");
		
			// Store date of last run to prevent running again
			$db->query("UPDATE `sim` SET `last_process`=CURDATE() WHERE 1");
		
		} else echo "<p> - Updated date not saved</p>";
	}

} else echo "<p>Process has already run today</p>";

if ( isset($_GET['manual']) ) echo "<p>Return to <a href='index.php?key=".$_GET['key']."'>manual processing</a></p>";

?>