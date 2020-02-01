<?
	$newval = encode($newval);

	$result = do_mysql_query("SELECT $column FROM $table WHERE ID='$id' AND ($column!='$newval' OR $column IS NULL)", "update_record('$table','$column','$id','$newval'), select");
	if(!$result) {
		return(false);
	}
	// if the value is the same, return, but there was no error
	if(!mysql_num_rows($result)) {
		return(true);
	}
	$row = mysql_fetch_array($result);
	
	if (!(do_mysql_query("UPDATE $table SET $column='$newval' WHERE ID='$id'", "update_record('$table','$column','$id','$newval'), update"))) {
		return(false);
	}

	$result = do_mysql_query("INSERT INTO Audit SET 
			dtDateTime=NOW(), 
			chrTableName='$table', 
			idRecord='$id', 
			chrColumnName='$column', 
			txtOldValue='" . encode($row[$column]) . "', 
			idUser='" . $_SESSION['idUser'] . "'",
		"update_record('$table','$column','$id'), insert audit");
	if(!$result) {
		return(false);
	}
?>
