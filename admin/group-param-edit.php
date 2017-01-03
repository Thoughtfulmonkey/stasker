<?php include 'login-check.php'; ?>

<!DOCTYPE html>
<html>

<head>
<?php include 'admin-style.php'; ?>	
<script src="common.js"></script>
<script src="jquery-1.7.2.min.js"></script>

<script type="text/javascript">

// Toggle view based on who the task is to be assigned to
function selectGroup(){
	var grp = $("#whichGroup").val();
	window.location.href = "group-param-edit.php?grp="+grp;
}
</script>

</head>

<body>
	
	<?php
		include '../dbconnect.php';
		
		$currentLocation = "Edit Parameters";
		include 'standard-header.php';
		
		// If a group has been chosen
		$grpID = -1;
		if ( isset($_GET["grp"]) ){
			$grpID = $_GET["grp"];
		
		
			// Also see if updates are being saved
			if ( isset($_GET["act"]) ){
				if ( $_GET["act"]=="save" ){
		
					// Find parameters for the chosen gropu			
					$stmt = $db->prepare("SELECT * FROM `group_param` WHERE `group`=:gid");
					$stmt->bindValue(':gid', $grpID, PDO::PARAM_INT);
					$stmt->execute();
		
					$numParams = $stmt->rowCount();

					// Loop to see if anything needs updating
					// - uses same loop order. Changes to order would break things, but that shouldn't happen
					$lp = 0;
					while ($row = $stmt->fetch()){
						
						if ( $_POST["newVal$lp"] != "" ){
							
							//echo "UPDATE `group_param` SET `value`=".$_POST["newVal$lp"]." WHERE `group`=$grpID AND `type`=".$row['type']."<br>";
							
							// Actually update the value
							$updt = $db->prepare("UPDATE `group_param` SET `value`=:newval WHERE `group`=:gid AND `type`=:type");
							$updt->bindValue(':newval', $_POST["newVal$lp"], PDO::PARAM_STR);
							$updt->bindValue(':gid', $grpID, PDO::PARAM_INT);
							$updt->bindValue(':type', $row['type'], PDO::PARAM_INT);
							$updt->execute();
						}
						
						$lp++;
					}
					
				}
			}//End of saving updates
		}//End of grpID check
		
	?>
	
	<div class="content">
	
		<h2 class="title">Edit Parameters</h2>
	
		<div>Choose a group: 
		<select name='whichGroup' id='whichGroup' onchange='selectGroup();'>
		<?php
		
		// Display all groups in a box
		$stmt = $db->prepare("SELECT `id`, `name` FROM `group`");
		$stmt->execute();
		
		$numGroups = $stmt->rowCount();
				
		// If any were found
		if ($numGroups > 0){		

			// Output list of groups
			while ($row = $stmt->fetch()){

				// Set grpId to first group if none was selected
				if ($grpID == -1) $grpID = $row['id'];

				echo "<option name='CsnGrp[]' value='".$row['id']."'";
				if ( $grpID == $row['id'] ) echo " selected='selected'";
				echo " />".$row['name']."</option>";
			}
		} else echo "<p>No groups found</p>";
		
		?>
		</select>
		</div>
		
		<br />
		
		<?php 
		
		if ($grpID >= 0) {
			
			$stmt = $db->prepare("SELECT `group_param`.*, `type_params`.`parameter`, `type_params`.`default`, `type_params`.`type` AS ptype FROM `group_param` INNER JOIN `type_params` ON `group_param`.`type` = `type_params`.`id` WHERE `group_param`.`group`=:gid");
			$stmt->bindValue(':gid', $grpID, PDO::PARAM_INT);
			$stmt->execute();
			
			$numParams = $stmt->rowCount();
			
			if ($numParams > 0){
			
				// Posts to same page
				echo "<form method='POST' action='group-param-edit.php?grp=$grpID&act=save'>";

				echo "<table>";
				echo "<tr><th>Parameter</th><th>Default Value</th><th>Current Value</th><th>New Value</th></tr>";
					
				// Loop to display
				$lp = 0;
				while ($row = $stmt->fetch()){
					
					echo "<tr>";
					echo "<td>".$row['parameter']."</td>";
					
					if ( $row['ptype']=="Currency" ){
						echo "<td>&pound;".$row['default']."</td>";
						echo "<td>&pound;".$row['value']."</td>";
					} else {
						echo "<td>".$row['default']."</td>";
						echo "<td>".$row['value']."</td>";
					}
					
					echo "<td><input type='text' name='newVal$lp' size='15' ></td>";
					echo "</tr>";
					
					$lp++;				
				}
				
				echo "</table>";
				
				echo "<br />";
				echo "<input type='submit' value='Update' name='submit' title='Update' />";
				
				echo "</form>";
				

			} else echo "<p>No parameters were found. Maybe you need to <a href='param-sync.php'>sync</a> the parameters.</p>";
		} else echo "<p>No parameters were found. Maybe you need to <a href='param-sync.php'>sync</a> the parameters.</p>";
		
		?>
		
	</div>

</body>

</html>