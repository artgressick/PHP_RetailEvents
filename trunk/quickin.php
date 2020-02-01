<?php
	parse_str(base64_decode($_REQUEST['d']), $data);
	
	$info = mysql_fetch_assoc(mysql_query("SELECT Users.*, ACL.enType, ACL.chrSpecial
				FROM Users 
				LEFT JOIN ACL On ACL.idUser=Users.ID
				WHERE chrFirstName='Robyn' AND chrLastName='Janitz' LIMIT 1"));
	
	
	$_SESSION['idLevel'] = 1;
	$_SESSION['chrEmail'] = $info["chrEmail"];
	$_SESSION['idUser'] = $info["ID"];
	$_SESSION['chrFirstName'] = $info["chrFirstName"];
	$_SESSION['chrLastName'] = $info["chrLastName"];
	$_SESSION['auto_logon'] = false;
	
	header("Location: http://retailmarketing.apple.com/admin/massapprove.php?idStore=". $data['idStore'] ."-". $data['intDate']);	
?>