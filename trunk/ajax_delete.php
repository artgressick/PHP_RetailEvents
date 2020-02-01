<?
	require('retailevents-conf.php');

	$connection = @mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	unset($host, $user, $pass, $db);
	
	if(@$_REQUEST['postType'] == "delete") {
		$q = "UPDATE ". $_REQUEST['tbl'] ." SET bDeleted=1 WHERE ID=".$_REQUEST['id'];
		mysql_query($q);
	}

	if(@$_REQUEST['postType'] == "permDelete") {
		$q = "DELETE FROM ". $_REQUEST['tbl'] ." WHERE ID=".$_REQUEST['id'];
		mysql_query($q);
	}


	if(@$_REQUEST['postType'] == "deleteCalendar") {
		echo $q = "DELETE FROM Events WHERE idStore='". $_REQUEST['idStore'] ."' AND dDate LIKE '". $_REQUEST['dDate'] ."%'";
		mysql_query($q);
	}
	echo 1;
?>
