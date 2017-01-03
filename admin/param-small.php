<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>
<title>Available Parameters</title>
</head>

<body>
<div class="smallContent">

<?php 

include '../dbconnect.php';

// Search for parameters
$result = $db->query("SELECT parameter FROM `type_params`");

if ($result->rowCount() > 0){
	
	// Loop to display
	while ($param = $result->fetch()){
		
		echo "<div class='label'>[".$param["parameter"]."]</div>";

	}
	
}

?>

</div>
</body>

</html>