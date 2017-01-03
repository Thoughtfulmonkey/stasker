<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<?php
include '../dbconnect.php';
include 'admin-style.php';
?>

<body>

<?php
function dbRes ($result, $message, $stylePath){
	
	if ($result){
		echo " - <img class='bullet' src='$stylePath/accept.png' alt='success' />$message</p>";
	} else {
		echo " - <img class='bullet' src='$stylePath/danger.png' alt='failure' />$message</p>";
	}
}


$result = mysql_query("UPDATE `task` SET `group`=12 WHERE `group`=3");
dbRes($result, "Updated group 12", $stylePath);

$result = mysql_query("UPDATE `task` SET `group`=16 WHERE `group`=6");
dbRes($result, "Updated group 16", $stylePath);

$result = mysql_query("UPDATE `task` SET `group`=15 WHERE `group`=11");
dbRes($result, "Updated group 15", $stylePath);

$result = mysql_query("UPDATE `task` SET `group`=14 WHERE `group`=9");
dbRes($result, "Updated group 14", $stylePath);

$result = mysql_query("UPDATE `task` SET `group`=13 WHERE `group`=10");
dbRes($result, "Updated group 13", $stylePath);

?>
	
</body>

</html>