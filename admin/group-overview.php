<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>	
<script src="common.js"></script>
</head>

<body>
	
	<?php
		include '../dbconnect.php';
		
		
		include 'standard-header.php';
		include 'param-parse.php';
	?>
	
	<div class="content">
	
		<h2 class="title">Group Overview</h2>
	
		<p>Projected values are those predicted for tomorrow.  Zero values may mean that no information could be loaded</p>
	
		<iframe src="group-overview-table.php" width='100%' height='600px'></iframe>
		
	</div>
	
</body>

</html>