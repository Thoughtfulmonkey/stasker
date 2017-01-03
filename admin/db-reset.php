<?php include 'login-check.php'; ?>
<?php 

function dbRes ($result, $message, $stylePath){
	
	if ($result){
		echo " - <img class='bullet' src='$stylePath/accept.png' alt='success' />$message</p>";
	} else {
		echo " - <img class='bullet' src='$stylePath/danger.png' alt='failure' />$message</p>";
	}
}

?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
</head>

<body>

	<?php include 'standard-header.php'; include '../dbconnect.php'; ?>

	<div class="content">
		
	<h2 class="title">Applying equations</h2>

	<?php if ( !isset($_POST['confirmDelete']) ){ ?>
	
		<form method='POST' action='db-reset.php'>
			
			<p>Select the tables to be wiped:</p>
			
			<?php
			
			// Loop through all tables
			$tbls = $db->prepare("SHOW TABLES");
			$tbls->execute();
			
			// Present option to wipe the tables
			foreach ($tbls as $tbl){
				if ($tbl[0] != 'admin') echo "&nbsp;<input type='checkbox' name='tables[]' value='".$tbl[0]."'>".$tbl[0]."<br>";
				else echo "&nbsp;<input type='checkbox' name='tables[]' value='".$tbl[0]."' disabled>".$tbl[0]." - <i>nobody could login if this was reset</i><br>";
			}
			
			?>
			
			<p>
			<img class='bullet' src='<?php echo $stylePath; ?>/danger.png' alt='Danger' />
			Are you sure? All current information will be lost.</p>
			
			<p><input type="hidden" name="confirmDelete" value="confirmDelete" /><input type="submit" value="Reset Database" title="Reset Database" /></p>

			
		</form>
		
		
	<?php } else { ?>			

		<p>Resetting...</p>
	
	<?php 
			
	  		// See if sim setting allows reset of the database
	  		$allowReset = $db->query("SELECT `allow_db_reset` FROM `sim` WHERE `allow_db_reset`='yes'");
	  		if ($allowReset->rowCount() > 0) $resetFound = true; else $resetFound = false;
			
	  		//$resetFound = true;
	  		
	  		if ($resetFound){
	  			
	  			$tables = $_POST['tables'];
	  			if( !empty($tables) ) {
	  				$N = count($tables);
	  			
	  				echo("You selected $N table(s): ");
	  				for($i=0; $i < $N; $i++)
	  				{
	  					echo "<p>Wiping ".$tables[$i];
	  					
	  					$db->query("DELETE FROM `".$tables[$i]."` WHERE 1");
	  					
	  					echo " - complete</p>";
	  				}
	  			}
	  			
	  			
	  			// Check that a NONE group is still there
	  			$typeSearch = $db->query("SELECT * FROM `group_type` WHERE `name` = 'None'");
	  			if ($typeSearch->rowCount() == 0){
	  				echo "<p>No default group type found, so attempting to add";
	  				
	  				$db->query("INSERT INTO `group_type` (`id`, `name`) VALUES (0, 'None')");
	  				$db->query("UPDATE `group_type` SET `id`=0 WHERE `name`='None'");
	  			}
	  			
	  			// Check that a System user is there
	  			$userSearch = $db->query("SELECT * FROM `user` WHERE `display_name` = 'System'");
	  			if ($userSearch->rowCount() == 0){
	  				echo "<p>No System user found, so attempting to add";
	  				
		  			$db->query("INSERT INTO `user` (`id`, `login`, `display_name`, `password`, `email`) VALUES
						(0, 'System', 'System', '1jdsiuf7sd7841sdf', '')");
		  			$db->query("UPDATE `user` SET `id`=0 WHERE `login`='System'");
	  			}
	  			
	  			
	  			echo "<br><p>Reset finished.  <a href='index.php'>Return to menu</a></p>";
	  			
		  		/*	
				// Do the actual resetting
				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating Simulation data table [sim]...</p>";
		
				$result = mysql_query("DROP TABLE `sim`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `sim` ( 
					`id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
					`startdate` date NOT NULL,
					`real_days` int(11) NOT NULL,
					`game_days` int(11) NOT NULL,
					`day_step` float NOT NULL,
					`game_date` date NOT NULL,
					`last_process` date NOT NULL DEFAULT '2012-02-16',
					`registration_open` VARCHAR(3) NOT NULL DEFAULT 'yes',
					`allow_db_reset` VARCHAR(3) NOT NULL DEFAULT 'yes',
					`process_key` VARCHAR(10) NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				
				
				dbRes($result, "Creating new...", $stylePath);
				
				
				$result = mysql_query("INSERT INTO `sim` (`id`, `startdate`, `real_days`, `game_days`, `day_step`, `game_date`, `process_key`) VALUES
					(1, '2012-07-27', 200, 600, 3, '2012-07-25', 'a8jh9plnm2')");
				
				dbRes($result, "Adding default values to sim table", $stylePath);
				
				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating Equations table [sim_equation]...</p>";
				
				$result = mysql_query("DROP TABLE `sim_equation`");
				dbRes($result, "Clearing table...", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `sim_equation` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`grouptype` int(10) unsigned NOT NULL,
					`eqtype` tinyint(3) unsigned NOT NULL,
					`equation` varchar(100) NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);
				
				$result = mysql_query("INSERT INTO `sim_equation` (`id`, `grouptype`, `eqtype`, `equation`) VALUES
					(1, '0', '1', '[Productivity]*[Morale]*[Management]*[Employees]*[Unit Price]'),
					(2, '0', '2', '([Productivity]*[Morale]*[Management]*[Employees]*[Wastage])*[Unit Cost]')");
				dbRes($result, "Adding default parameters", $stylePath);
				
				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating default parameter table [type_params]...</p>";
				
				$result = mysql_query("DROP TABLE `type_params`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `type_params` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`parameter` varchar(30) NOT NULL,
					`type` varchar(20) NOT NULL,
					`default` varchar(20) NOT NULL,
					`min` float NULL DEFAULT NULL,
					`max` float NULL DEFAULT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);
			
				
				$result = mysql_query("INSERT INTO `type_params` (`id`, `parameter`, `type`, `default`, `min`, `max`) VALUES
					(1, 'Balance', 'Currency', '5000', NULL, NULL),
					(2, 'Unit Price', 'Currency', '37', '32', '44'),
					(3, 'Unit Cost', 'Currency', '28', '24', '31'),
					(4, 'Productivity', 'Number', '10', '6', '12'),
					(5, 'Wastage', 'Number', '1.1', '1.05', '1.3'),
					(6, 'Morale', 'Number', '1', '0.8', '1.2'),
					(7, 'Management', 'Number', '1', '0.8', '1.1'),
					(8, 'Employees', 'Number', '10', NULL, NULL),
					(9, 'Building Size', 'Number', '250', NULL, NULL),
					(10, 'Income', 'Currency', '?', NULL, NULL),
					(11, 'Expenses', 'Currency', '?', NULL, NULL)");
				dbRes($result, "Adding default parameters", $stylePath);
				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating admin login table [admin]...</p>";
				
				$result = mysql_query("DROP TABLE `admin`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `admin` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`login` varchar(30) NOT NULL,
					`display_name` varchar(50) NOT NULL,
					`password` varchar(25) NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);
				

				$result = mysql_query("INSERT INTO `admin` (`id`, `login`, `display_name`, `password`) VALUES
					(0, 'admin', 'Default Admin', 'green9foxK')");
				dbRes($result, "Adding default admin", $stylePath);
				
				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating student user table [user]...</p>";
				
				$result = mysql_query("DROP TABLE `user`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `user` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`login` varchar(30) NOT NULL,
					`display_name` varchar(50) NOT NULL,
					`password` varchar(25) NOT NULL,
					`email` varchar(40) NULL,
					`group` int(10) unsigned NOT NULL DEFAULT '0',
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);
				
				
				
				
				$result = mysql_query("INSERT INTO `user` (`id`, `login`, `display_name`, `password`, `email`, `group`) VALUES
					(0, 'System', 'System', '1jdsiuf7sd7841sdf', '', 0)");
				dbRes($result, "Adding default system user...", $stylePath);
				
				$result = mysql_query("UPDATE `user` SET `id`=0 WHERE `login`='System'");
				dbRes($result, "Forcing zero id", $stylePath);
				
				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating group type table [group_type]...</p>";
				
				$result = mysql_query("DROP TABLE `group_type`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `group_type` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`name` varchar(30) NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);
				
				$result = mysql_query("INSERT INTO `group_type` (`id`, `name`) VALUES (0, 'None')");
				dbRes($result, "Adding default group type - none", $stylePath);
				
				$result = mysql_query("UPDATE `group_type` SET `id`=0 WHERE `name`='None'");
				dbRes($result, "Forcing zero id", $stylePath);
				
				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating the groups table [group]...</p>";
				
				$result = mysql_query("DROP TABLE `group`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `group` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`type` int(10) unsigned DEFAULT NULL,
					`name` varchar(30) NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);

				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating table for each group's parameters [group_param]...</p>";
				
				$result = mysql_query("DROP TABLE `group_param`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `group_param` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`type` int(10) unsigned NOT NULL,
					`group` int(10) unsigned NOT NULL,
					`value` varchar(20) NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);

				
				
				// --------------------------------------------------------------------------------------------------------------
					
				echo "<p>Creating table for group's historical parameters [group_history]...</p>";
					
				$result = mysql_query("DROP TABLE `group_history`");
				dbRes($result, "Deleting existing table", $stylePath);
					
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `group_history` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`type` int(10) unsigned NOT NULL,
					`group` int(10) unsigned NOT NULL,
					`value` varchar(20) NOT NULL,
					`date` DATE NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);
				
				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating table of task descriptions [task_info]...</p>";
				
				$result = mysql_query("DROP TABLE `task_info`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `task_info` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`author` tinyint(3) unsigned NOT NULL,
					`title` varchar(40) NOT NULL,
					`description` text NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);
				
				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating table for collections of scheduled tasks [task_group]...</p>";
				
				$result = mysql_query("DROP TABLE `task_group`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `task_group` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`delivery` tinyint(3) unsigned NOT NULL,
					`content` tinyint(3) unsigned NOT NULL,
					`author` int(10) unsigned NOT NULL,
					`task` int(10) unsigned NOT NULL,
					`startdate` date NOT NULL,
					`enddate` date NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);
				
				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Create table for tasks scheduled to groups [task]...</p>";
				
				$result = mysql_query("DROP TABLE `task`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `task` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`taskgroup` int(10) unsigned NOT NULL,
					`group` int(10) unsigned NOT NULL,
					`taskinfo` int(10) unsigned NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);
				
				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating table for a task's avaiable options [option]...</p>";
				
				$result = mysql_query("DROP TABLE `option`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `option` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`taskinfo` int(10) unsigned NOT NULL,
					`description` varchar(40) NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);

				
				// --------------------------------------------------------------------------------------------------------------
					
				echo "<p>Creating table for a effects connected to options [effect]...</p>";
					
				$result = mysql_query("DROP TABLE `effect`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `effect` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`option` int(10) unsigned NOT NULL,
					`type` varchar(20) NOT NULL,
					`choice` varchar(20) NOT NULL,
					`value` varchar(30) NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);
				
				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating table to store student votes on tasks [vote]...</p>";
				
				$result = mysql_query("DROP TABLE `vote`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `vote` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`user` int(10) unsigned NOT NULL,
					`option` int(10) unsigned NOT NULL,
					`task` int(10) unsigned NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
				dbRes($result, "Recreating table", $stylePath);

				
				// --------------------------------------------------------------------------------------------------------------
				
				echo "<p>Creating table for scheduled bills [task_auto]...</p>";
				
				$result = mysql_query("DROP TABLE `task_auto`");
				dbRes($result, "Deleting existing table", $stylePath);
				
				$result = mysql_query("CREATE TABLE IF NOT EXISTS `task_auto` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`group_type` int(10) unsigned NOT NULL,
					`title` varchar(20) NOT NULL,
					`type` varchar(10) NOT NULL,
					`description` text NOT NULL,
					`dom` tinyint(4) NOT NULL,
					`calc` varchar(30) NOT NULL,
					PRIMARY KEY (`id`)
					)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

				dbRes($result, "Recreating table", $stylePath);

				
				
				
				$result = mysql_query("INSERT INTO `task_auto` (`id`, `group_type`, `title`, `type`, `description`, `dom`, `calc`) VALUES
					(1, 0, 'Electricity', 'bill', 'Payment of electricity.', 26, '[Size]*1'),
					(2, 0, 'Rent', 'bill', 'Payment of rent on premises.', 26, '[Size]*2.3'),
					(3, 0, 'Water', 'bill', 'Payment of water rate on building.', 26, '30')");
				dbRes($result, "Adding default automatic tasks", $stylePath);
				
				// --------------------------------------------------------------------------------------------------------------
				
				// Defaults used for testing purposes
				//include 'testing-defaults.php';

				 */
	  		}
			else echo "<p>Resetting of database is turned off in <a href='sys-settings.php'>system settings</a></p>";
		}
	?>
	
</div>
	
</body>

</html>
