<?
	$result = mysql_query($query);
	if ($result === false) {
		_error_debug(array('error' => mysql_error(), 'query' => $query), "MySQL ERROR: " . $description, __LINE__, __FILE__, LOG_ERR);
	} else {
		_error_debug(array('query' => $query), "MySQL: " . $description, __LINE__, __FILE__);
	}
	$rows = array();
	while($row = mysql_fetch_assoc($result)) {
		if($by_id) {
			$rows[$row['ID']] = $row;
		} else {
			$rows[] = $row;
		}
	}
?>