<?php include 'login-check.php'; ?>


<?php
include '../dbconnect.php';
?>
	
<?php

	// Loop through all tables
	$tbls = $db->prepare("SHOW TABLES");
	$tbls->execute();
	
	foreach ($tbls as $tbl){

		echo "-- <p>Displaying table ".$tbl[0]."</p>\n";
		
		// Find everything in the table
		$tbcon = $db->prepare("SELECT * FROM `".$tbl[0]."`");
		
		//$tbcon->bindValue(":table", $tbl[0], PDO::PARAM_STR);
		$tbcon->execute();
		
		echo "-- <p>Found ".$tbcon->rowCount()."</p>\n";
		
		// Output the data
		while ($row = $tbcon->fetch()){
			
			// Find field names
			$fieldString = "";
			$fields = array_keys($row);
			for ($fl=0; $fl< count($fields); $fl+=2){
				$fieldString = $fieldString."`".$fields[$fl]."`, ";
			}
			$fieldString = rtrim($fieldString, ", ");
			
			
			$valueString = "";
			for ($fl=0; $fl< count($row)/2; $fl++){
				if (is_null($row[$fl])) $row[$fl] = 'NULL';
				$valueString = $valueString."'".str_replace("'", "\'", $row[$fl])."', ";
			}
			$valueString = rtrim($valueString, ", ");
			
			echo "INSERT INTO `".$tbl[0]."` ($fieldString) VALUES ($valueString);\n";
			
		}
		
		echo "\n\n";
	
	}
	

?>