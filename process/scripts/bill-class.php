<?php

class Bill {
	
	var $localDB;
	var $bid;
	var $title;
	var $description;
	var $amount;
	var $consequenceScript;
	var $tlimit;
	
	public function generate ($group){
		
		// All bills affect balance.  Find it's param id
		$balSearch = $this->localDB->query("SELECT `id` FROM `type_params` WHERE `parameter`='Balance'");
		$balDetails = $balSearch->fetch();
		$balanceID = $balDetails["id"];
		
		echo "<p>Found ID: $balanceID</p>";
		
		// Create a task info
		$this->localDB->query("INSERT INTO `task_info` (`author`, `title`, `description`) VALUES (0, '$this->title', '$this->description')");
		$chosenTask = $this->localDB->lastInsertId();
		
		// Create a task group
		// - Use 1 to indicate content is a bill
		$this->localDB->query("INSERT INTO `task_group` (`delivery`, `content`, `author`, `task`, `startdate`, `enddate`) VALUES ('2', '1', '0', '$chosenTask', CURDATE(), ADDDATE(CURDATE(),$this->tlimit))");
		$tskgrpID = $this->localDB->lastInsertId();
		
		// Create a task
		$this->localDB->query("INSERT INTO `task` (`taskgroup`, `group`, `taskinfo`) VALUES ('$tskgrpID', '$group', '$chosenTask')");
		
		
		// Define options
		$this->localDB->query("INSERT INTO `option` (`taskinfo`, `description`) VALUES ('$chosenTask', 'Pay the bill')");
		$opResID = $this->localDB->lastInsertId();
		$this->localDB->query("INSERT INTO `effect` (`option`, `type`, `choice`, `value`) VALUES ('$opResID', 'parameter', '$balanceID', '-$this->amount')");
		
		if ($this->consequenceScript != ""){
			$this->localDB->query("INSERT INTO `option` (`taskinfo`, `description`) VALUES ('$chosenTask', 'Ignore')");
			$opResID = $this->localDB->lastInsertId();
			$this->localDB->query("INSERT INTO `effect` (`option`, `type`, `choice`, `value`) VALUES ('$opResID', 'script', '$this->consequenceScript', '$this->amount')");
		}
		
	}
	
	
	// Assign the db to connect on
	public function setDB($db){
	
		$this->localDB = $db;
	}
	
	// Details for the task info
	public function setTaskDetails($title, $description){
		
		$this->title = $title;
		$this->description = $description;
	}
	
	// How long have they got?
	public function setDaysToPay($days){
		
		$this->tlimit = $days;
	}
	
	// Details for the options
	public function setOptions($amount, $script){
		
		$this->amount = $amount;
		$this->consequenceScript = $script;
	}
}

?>