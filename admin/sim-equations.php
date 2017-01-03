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
include 'param-parse.php';

$currentLocation = "Define Equations";
include 'standard-header.php';
?>
	
	<div class="content">
	
		<?php
		
		// See if updating values
		if ( isset($_GET['update']) ){

			$upeqid = $_GET['update'];

			// See if equation details are available
			// - also validates the supplied group type
			$name = "";
			$equation = "";
			if ( isset($_POST['update-name'.$upeqid]) ){
				$name = $_POST['update-name'.$upeqid];
			}
			if ( isset($_POST['update-equation'.$upeqid]) ){
				$equation = $_POST['update-equation'.$upeqid];
			}

			$target = $_POST['update-affparam'.$upeqid];
			
			// Add to DB if all required data is supplied
			if ( ($name != "")&&($equation!="") ){

				$insertEq = $db->prepare("UPDATE `sim_equation` SET `name`=:name WHERE `id`=:upeqid");
				$insertEq->bindValue(':upeqid', $upeqid, PDO::PARAM_INT);
				$insertEq->bindValue(':name', $name, PDO::PARAM_STR);
				$insertEq->execute();
				
				$insertEq = $db->prepare("UPDATE `sim_equation` SET `equation`=:equation WHERE `id`=:upeqid");
				$insertEq->bindValue(':upeqid', $upeqid, PDO::PARAM_INT);
				$insertEq->bindValue(':equation', $equation, PDO::PARAM_STR);
				$insertEq->execute();
				
				$insertEq = $db->prepare("UPDATE `sim_equation` SET `target`=:target WHERE `id`=:upeqid");
				$insertEq->bindValue(':upeqid', $upeqid, PDO::PARAM_INT);
				$insertEq->bindValue(':target', $target, PDO::PARAM_INT);
				$insertEq->execute();
			} 
			
		}
		
		// See if trying to create new
		if ( isset($_GET['addto']) ){

			// Which group type to add equation to
			$gtype = $_GET['addto'];
			
			// See if equation details are available
			// - also validates the supplied group type
			$name = "";
			$equation = "";
			if ( isset($_POST['name'.$gtype]) ){
				$name = $_POST['name'.$gtype];
			}
			if ( isset($_POST['equation'.$gtype]) ){
				$equation = $_POST['equation'.$gtype];
			}
			
			$target = $_POST['affparam'.$gtype];
			
			// Add to DB if all required data is supplied
			if ( ($name != "")&&($equation!="") ){

				if ($gtype == -1) $gtype = null;
				
				$insertEq = $db->prepare("INSERT INTO `sim_equation` (`groupType`, `name`, `target`, `equation`) VALUES (:grpType, :name, :target, :equation)");
				$insertEq->bindValue(':grpType', $gtype, PDO::PARAM_INT);
				$insertEq->bindValue(':name', $name, PDO::PARAM_STR);
				$insertEq->bindValue(':target', $target, PDO::PARAM_INT);
				$insertEq->bindValue(':equation', $equation, PDO::PARAM_STR);
				$insertEq->execute();
			}
		}
		
		// Deletion of equation
		if ( isset($_GET['delete']) ){
			
			$deleteEq = $db->prepare('DELETE FROM `sim_equation` WHERE `id`=:eqID');
			$deleteEq->bindValue(':eqID', $_GET['delete'], PDO::PARAM_INT);
			$deleteEq->execute();
		}
		
		// Editing an equation
		$edit = -1;
		if ( isset($_GET['edit']) ){
			$edit = $_GET['edit'];
		}
		
		// Display option to reload without the calculations
		$nocalc = false;
		if ( isset($_GET['nocalc']) ){
			$nocalc = true;
			echo "<a href='sim-equations.php'><img src='$stylePath/revert.png' title='No calculations' alt='No calculations' class='bullet'/> Reload with calculations</a>";
		} else {
			echo "<a href='sim-equations.php?nocalc=true'><img src='$stylePath/revert.png' title='With calculations' alt='With calculations' class='bullet'/> Reload without calculations</a>";
		}
		
		// Display common equations - have no parent
		echo "<h3 class='title'>Equations for All Group Types</h3>";
		
		$eqSearch = $db->query("SELECT `sim_equation`.*, `type_params`.`parameter` FROM `sim_equation` JOIN `type_params` ON `sim_equation`.`target`=`type_params`.`id` WHERE `groupType` IS NULL");
		
		// HTML for display
		echo "<table>";
		echo "<tr><th>Name</th><th>Equation</th><th>Actions</th></tr>";
		
		while ($commonEq = $eqSearch->fetch()){
			
			$parsedEq = swapParamsForValues($commonEq['equation'], -1, $db);
			
			if ($nocalc){
				$calcEq = "skipped";
			}else{
				$calcEq = calculate_string($parsedEq);
			}
			
			if ($edit == $commonEq['id']){
				echo "<form method='POST' action='sim-equations.php?update=$edit'>";
				echo "<tr><td><input name='update-name$edit' type='text' size='20' value='".$commonEq['name']."'>";
				echo "<br><select name='update-affparam$edit' id='affparam-1'>";
				// Search for all parameters
				$stmt = $db->prepare("SELECT `id`, `parameter` FROM `type_params`");
				$stmt->bindParam(':id', $tid, PDO::PARAM_INT);
				$stmt->execute();
				// Loop to output parameters
				while ($row = $stmt->fetch()){
					if ($commonEq['target'] == $row['id']) echo "<option value='".$row['id']."' selected='selected'>".$row['parameter']."</option>";
					else echo "<option value='".$row['id']."'>".$row['parameter']."</option>";
				}
				echo "</select></td>";
				echo "<td><input name='update-equation$edit' type='text' size='80' value='".$commonEq['equation']."'><br><hr class='subtleLine'>";
				echo "<img src='$stylePath/arrow-right.png' class='bullet' title='default' alt='default' /> $parsedEq = $calcEq</td>";
				echo "<td><input type='image' src='$stylePath/accept.png' value='Update' name='submit' title='Update'>";
				echo "</td></tr>";
			} else {
				echo "<tr><td>".$commonEq['name']."";
				echo "<br><img src='$stylePath/target.png' class='bullet' title='target' alt='target'> ".$commonEq['parameter']."</td>";
				echo "<td>".$commonEq['equation']."<br><hr class='subtleLine'>";
				echo "<img src='$stylePath/arrow-right.png' class='bullet' title='default' alt='default' /> $parsedEq = $calcEq</td>";
				if ($nocalc) echo "<td><a href='sim-equations.php?edit=".$commonEq['id']."&nocalc=true'><img src='$stylePath/edit.png' title='edit' alt='edit' /></a>";
				else echo "<td><a href='sim-equations.php?edit=".$commonEq['id']."'><img src='$stylePath/edit.png' title='edit' alt='edit' /></a>";
				echo "<a onclick='confirmDelete(\"sim-equations.php\", ".$commonEq['id'].")' href='javascript:void(0)'><img alt='delete' title='delete' src='$stylePath/del.png'></a>";
				echo "</td></tr>";
			}

		}
		// Create new equation form
		echo "<form method='POST' action='sim-equations.php?addto=-1'>";
		echo "<tr><td><input name='name-1' type='text' size='20'>";
		echo "<br><select name='affparam-1' id='affparam-1'>";
		// Search for all parameters
		$stmt = $db->prepare("SELECT `id`, `parameter` FROM `type_params`");
		$stmt->bindParam(':id', $tid, PDO::PARAM_INT);
		$stmt->execute();
		// Loop to output parameters
		while ($row = $stmt->fetch()){
			echo "<option value='".$row['id']."'>".$row['parameter']."</option>";
		}
		echo "</select></td>";
		echo "<td><input name='equation-1' type='text' size='80'></td>";
		echo "<td><a href='javascript:void(0)' onclick='displayParams();'><img src='$stylePath/view.png' title='View Parameters' alt='View Parameters' /></a><input type='image' src='$stylePath/add.png' value='Add New' name='submit' title='Add New'></td></tr>";
		echo "</form>";
		echo "</table>";
		
		
		// Loop through all business types
		$groupTypes = $db->query("SELECT * FROM `group_type`");
		
		// Loop through all types
		while ($groupType = $groupTypes->fetch()){

			echo "<h3>Type: ".$groupType["name"]."</h3>";
			
			// Extract type ID
			$typeID = $groupType["id"];
			
			// See if entries already exist
			$equations = $db->prepare( "SELECT `sim_equation`.*, `type_params`.`parameter` FROM `sim_equation` JOIN `type_params` ON `sim_equation`.`target`=`type_params`.`id` WHERE `groupType`=:typeID" );
			$equations->bindValue(':typeID', $typeID, PDO::PARAM_INT);
			$equations->execute();
			
			// Display current equations
			// HTML for display
			echo "<table>";
			echo "<tr><th>Name</th><th>Equation</th><th>Actions</th></tr>";
			
			while ($equation = $equations->fetch()){

				if ($nocalc){
					$parsedEq = "Calculations skipped";
					$calcEq = 0;
				}else{
					$parsedEq = swapParamsForValues($equation['equation'], -1, $db);
					$calcEq = calculate_string($parsedEq);
				}

				if ($edit == $equation['id']){
					echo "<form method='POST' action='sim-equations.php?update=$edit'>";
					echo "<tr><td><input name='update-name$edit' type='text' size='20' value='".$equation['name']."'>";
					echo "<br><select name='update-affparam$edit' id='update-affparam$edit'>";
					// Search for all parameters
					$stmt = $db->prepare("SELECT `id`, `parameter` FROM `type_params`");
					$stmt->bindParam(':id', $tid, PDO::PARAM_INT);
					$stmt->execute();
					// Loop to output parameters
					while ($row = $stmt->fetch()){
						if ($equation['target'] == $row['id']) echo "<option value='".$row['id']."' selected='selected'>".$row['parameter']."</option>";
						else echo "<option value='".$row['id']."'>".$row['parameter']."</option>";
					}
					echo "</select></td>";
					echo "<td><input name='update-equation$edit' type='text' size='80' value='".$equation['equation']."'><br><hr class='subtleLine'>";
					echo "<img src='$stylePath/arrow-right.png' class='bullet' title='default' alt='default' /> $parsedEq = $calcEq</td>";
					echo "<td><input type='image' src='$stylePath/accept.png' value='Update' name='submit' title='Update'>";
					echo "</td></tr>";
				} else {
					echo "<tr><td>".$equation['name']."";
					echo "<br><img src='$stylePath/target.png' class='bullet' title='target' alt='target'> ".$equation['parameter']."</td>";
					echo "<td>".$equation['equation']."<br><hr class='subtleLine'>";
					echo "<img src='$stylePath/arrow-right.png' class='bullet' title='default' alt='default' /> $parsedEq = $calcEq</td>";
					if ($nocalc) echo "<td><a href='sim-equations.php?edit=".$equation['id']."&nocalc=true'><img src='$stylePath/edit.png' title='edit' alt='edit' /></a>";
					else echo "<td><a href='sim-equations.php?edit=".$equation['id']."'><img src='$stylePath/edit.png' title='edit' alt='edit' /></a>";
					echo "<a onclick='confirmDelete(\"sim-equations.php\", ".$equation['id'].")' href='javascript:void(0)'><img alt='delete' title='delete' src='$stylePath/del.png'></a>";
					echo "</td></tr>";
				}
			
			}
			// Create new equation form
			echo "<form method='POST' action='sim-equations.php?addto=$typeID'>";
			echo "<tr><td><input name='name$typeID' type='text' size='20'>";
			echo "<br><select name='affparam$typeID' id='affparam$typeID'>";
			// Search for all parameters
			$stmt = $db->prepare("SELECT `id`, `parameter` FROM `type_params`");
			$stmt->bindParam(':id', $tid, PDO::PARAM_INT);
			$stmt->execute();
			// Loop to output parameters
			while ($row = $stmt->fetch()){
				echo "<option value='".$row['id']."'>".$row['parameter']."</option>";
			}
			echo "</select></td>";
			echo "<td><input name='equation$typeID' type='text' size='80'></td>";
			echo "<td><a href='javascript:void(0)' onclick='displayParams();'><img src='$stylePath/view.png' title='View Parameters' alt='View Parameters' /></a><input type='image' src='$stylePath/add.png' value='Add New' name='submit' title='Add New'></td></tr>";
			echo "</form>";
			echo "</table>";
			}

		?>
		
	</div>
	
</body>
</html>