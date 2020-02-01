<?
	require('retailevents-conf.php');

	$connection = @mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	unset($host, $user, $pass, $db);
	
	if($_REQUEST['postType'] == "insert") {

		$q = "INSERT INTO ACL SET
			idUser='". $_REQUEST['idUser'] ."',
			idItem='". $_REQUEST['idItem'] ."',
			enType='Stores',
			enPermission='". $_REQUEST['enPermission'] ."'
		";
		mysql_query($q);
		
		echo $newID = mysql_insert_id();
 	}

	if($_REQUEST['postType'] == "quickInsert") {

		$q = "UPDATE ACL SET
			enPermission='". $_REQUEST['enPermission'] ."'
			WHERE ID=". $_REQUEST['ID'];
		mysql_query($q);
		echo "1";
 	}


?>
