<?
	
	
	$begin_time = microtime(true);
	$result = mysql_query($query);
	$end_time = microtime(true);
	
	if ($result === false) {
		_error_debug(array('error' => mysql_error(), 'query' => $query), "MySQL ERROR: " . $description, __LINE__, __FILE__, LOG_ERR);
	} else {
		_error_debug(array('query' => $query), "MySQL (" . (round(($end_time-$begin_time)*1000)/1000) . " sec): " . $description, __LINE__, __FILE__);
		//_error_debug(array('query' => $query), "MySQL: " . $description, __LINE__, __FILE__);
	}
?>
