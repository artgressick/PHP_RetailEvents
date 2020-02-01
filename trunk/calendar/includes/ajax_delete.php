<?
	require('retailevents-conf.php');

	$connection = @mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	unset($host, $user, $pass, $db);
	
	if(@$_REQUEST['postType'] == "bcalaccess") {
		$q = "UPDATE ". $_REQUEST['tbl'] ." SET bCalAccess=0 WHERE ID=".$_REQUEST['id'];
		mysql_query($q);
	}

	if(@$_REQUEST['postType'] == "permDelete") {
		$q = "DELETE FROM ". $_REQUEST['tbl'] ." WHERE ID=".$_REQUEST['id'];
		mysql_query($q);
	}
	echo 1;
?>
