<?
	if($newval == 'NULL') {
		$test = " $column IS NOT NULL ";
	} else {
		$test = "($column!=" . $newval . " OR $column IS NULL)";
	}
	$newval = encode($newval); 

	$result = do_mysql_query("SELECT ID FROM $table WHERE ID='$id' AND " . $test, "update_record_unquoted('$table','$column','$id'), select");
	if(!$result) {
		return(false);
	}

	if(mysql_num_rows($result)) {
		$row = mysql_fetch_array($result);
		
		$result = do_mysql_query("INSERT INTO Audit SET 
				dtDateTime=NOW(), 
				chrTableName='$table', 
				idRecord='$id', 
				chrColumnName='$column', 
				txtOldValue=(SELECT $column FROM $table WHERE ID='$id'), 
				idUser='" . $_SESSION['idUser'] . "'",
			"update_record_unquoted('$table','$column','$id'), insert audit");
		if(!$result) {
			return(false);
		}
		
		if (!(do_mysql_query("UPDATE $table SET $column=$newval WHERE ID='$id'", "update_record_unquoted('$table','$column','$id'), update"))) {
			return(false);
		}
	}
?>