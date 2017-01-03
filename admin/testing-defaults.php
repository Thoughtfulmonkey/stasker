<?php

	echo "<p>Dummy values for testing</p>";

	$result = mysql_query("INSERT INTO `group` (`type`, `name`) VALUES ('0', 'Dummy')");
	dbRes($result, "Creating dummy group", $stylePath);
	
	$group = mysql_query("SELECT `id` FROM `group` WHERE `name`='Dummy'");
	
	$gid = mysql_result($group, 0, "id");
	
	
	$result = mysql_query("INSERT INTO `user` (`login`, `display_name`, `password`, `group`) VALUES ('test1', 'Test User 1', 'password', $gid)");
	dbRes($result, "Test user 1", $stylePath);
	$result = mysql_query("INSERT INTO `user` (`login`, `display_name`, `password`, `group`) VALUES ('test2', 'Test User 2', 'password', $gid)");
	dbRes($result, "Test user 2", $stylePath);

?>