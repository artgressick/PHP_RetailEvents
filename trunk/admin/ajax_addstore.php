<?
	require('retailevents-conf.php');

	$connection = @mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	unset($host, $user, $pass, $db);
	
	if(@$_POST['postType'] == "insert") {

		$q = "INSERT INTO ACL SET
			idUser='". $_POST['idUser'] ."',
			idItem='". $_POST['idItem'] ."',
			enType='Stores',
			enPermission='". $_POST['enPermission'] ."'
		";
		mysql_query($q);
 	}

	if(@$_REQUEST['postType'] == "quickInsert") {

		$q = "UPDATE ACL SET
			enPermission='". $_REQUEST['enPermission'] ."'
			WHERE ID=". $_REQUEST['ID'];
		mysql_query($q);
 	}

	echo "1";
?>
