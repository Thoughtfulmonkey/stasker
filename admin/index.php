<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
<title>Business Simulation</title>
</head>

<body>
	<?php
		include 'standard-header.php';
	?>

	<div class="content">
	
		<div class="sectionGroup">
	
			<div class="sectionGroupTitle">System</div>
	
			<h2 class='sectionTitle'><a href="sys-settings.php">System Settings</a></h2>
			<p class="sectionDescription">Modify global system settings.</p>
	
			<h2 class='sectionTitle'><a href="group-types.php">Group Types</a></h2>
			<p class="sectionDescription">Define categories of groups.</p>
	
			<h2 class='sectionTitle'><a href="group-params.php">Define Parameters</a></h2>
			<p class="sectionDescription">Define the parameters that a group can have.</p>
			
			<h2 class='sectionTitle'><a href="db-reset.php">Reset Database</a></h2>
			<p class="sectionDescription">Returns database to default settings.</p>
			
			<h2 class='sectionTitle'><a href="db-dump.php">Dump Database</a></h2>
			<p class="sectionDescription">Output all of the database information.</p>
		
		</div>
	
		<div class="sectionGroup">
	
			<div class="sectionGroupTitle">Groups</div>
	
			<h2 class='sectionTitle'><a href="user-manage.php">Manage Users</a></h2>
			<p class="sectionDescription">Create user accounts.</p>
		
			<h2 class='sectionTitle'><a href="group-manage.php">Manage Groups</a></h2>
			<p class="sectionDescription">Create groups, and assign users to them.</p>
			
			<h2 class='sectionTitle'><a href="group-param-edit.php">Group Parameters</a></h2>
			<p class="sectionDescription">Manually adjust the parameters for specific groups.</p>
		
		</div>
		
		<div class="sectionGroup">
		
			<div class="sectionGroupTitle">Tasks</div>
		
			<h2 class='sectionTitle'><a href="task-list.php">Manage Tasks</a></h2>
			<p class="sectionDescription">Create, view and edit tasks. Add options for students to choose.</p>
		
			<h2 class='sectionTitle'><a href="task-schedule.php">Schedule Tasks</a></h2>
			<p class="sectionDescription">Choose when the tasks will be available to students.</p>
			
			
		</div>
		
		<div class="sectionGroup">
		
			<div class="sectionGroupTitle">Business Sim</div>
		
			<h2 class='sectionTitle'><a href="sim-equations.php">Define Equations</a></h2>
			<p class="sectionDescription">Define calculations for income, expense etc.</p>
			
			<h2 class='sectionTitle'><a href="group-overview.php">Group Overview</a></h2>
			<p class="sectionDescription">View the equation predictions for all groups.</p>
					
		</div>
		
		<div id="sectionFooter">v1.02 2013/10/09</div>
		
	</div>

</body>

</html>