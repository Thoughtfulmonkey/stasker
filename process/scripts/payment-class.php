<?php

class Payment {
	
	var $bid;
	var $title;
	var $description;
	var $amount;
	var $tlimit;
	
	public function generate ($group){
		
		// All bills affect balance.  Find it's param id
		$balSearch = mysql_query("SELECT `id` FROM `type_params` WHERE `parameter`='Balance'");
		$balanceID = mysql_result($balSearch, 0, "id");
		
		// Create a task info
		mysql_query("INSERT INTO `task_info` (`title`, `description`) VALUES ('$this->title', '$this->description')");
		$tskinfo = mysql_query("SELECT LAST_INSERT_ID() AS last");
		$chosenTask = mysql_result($tskinfo, 0, "last");
		
		// Create a task group
		// - Use 2 to indicate content is a payment
		mysql_query("INSERT INTO `task_group` (`delivery`, `content`, `author`, `task`, `startdate`, `enddate`) VALUES ('2', '2', '0', '$chosenTask', CURDATE(), ADDDATE(CURDATE(),$this->tlimit))");
		$tskgrp = mysql_query("SELECT LAST_INSERT_ID() AS last");
		
		// Create a task
		mysql_query("INSERT INTO `task` (`taskgroup`, `group`, `taskinfo`) VALUES ('".mysql_result($tskgrp, 0, "last")."', '$group', '$chosenTask')");
		
		
		// Define options
		mysql_query("INSERT INTO `option` (`taskinfo`, `description`) VALUES ('$chosenTask', 'Payment pending')");
		$opRes = mysql_query("SELECT LAST_INSERT_ID() AS last");
		mysql_query("INSERT INTO `effect` (`option`, `type`, `choice`, `value`) VALUES ('".mysql_result($opRes, 0, "last")."', 'parameter', '$balanceID', '+$this->amount')");
		
		if ($this->consequenceScript != ""){
			mysql_query("INSERT INTO `option` (`taskinfo`, `description`) VALUES ('$chosenTask', 'Ignore')");
			$opRes = mysql_query("SELECT LAST_INSERT_ID() AS last");
			mysql_query("INSERT INTO `effect` (`option`, `type`, `choice`, `value`) VALUES ('".mysql_result($opRes, 0, "last")."', 'script', '$this->consequenceScript', '$this->amount')");
		}
		
	}
	
	
	// Details for the task info
	public function setTaskDetails($title, $description){
		
		$this->title = $title;
		$this->description = $description;
	}
	
	// How long have they got?
	public function setDaysToClaim($days){
		
		$this->tlimit = $days;
	}
	
	// Details for the options
	public function setOptions($amount, $script=''){
		
		$this->amount = $amount;
		$this->consequenceScript = $script;
	}
}

?>