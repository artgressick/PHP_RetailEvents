<?
	global $auth_not_required;
//		$_SESSION['idLevel'] = "";

		if(defined(BASE_FOLDER)) { $BF = BASE_FOLDER; }

		if (isset($_REQUEST['chrLoginName'])) {  // check to see if this is a submission of the login form
			$auth_form_name = strtolower($_REQUEST['chrLoginName']);

				$query = "
				SELECT Users.*, ACL.enType, ACL.chrSpecial
				FROM Users 
				LEFT JOIN ACL On ACL.idUser=Users.ID
				WHERE chrEmail='" . $auth_form_name . "' AND !Users.bDeleted AND
				chrPassword=MD5('" . $_REQUEST['chrLoginPassword'] . "')";

			$result = do_mysql_query($query, "auth_check: verifying Email and Password against db.");
			
			if ($result) {
				if (mysql_num_rows($result)) {
						$row = mysql_fetch_assoc($result);
						
						$ary = array();
						$tmp = do_mysql_query("SELECT idItem FROM ACL JOIN Stores ON Stores.ID=ACL.idItem WHERE idUser='" . $row['ID'] . "' AND enType='Stores' ORDER BY Stores.chrName","getting store list");
						if(mysql_num_rows($tmp)) {
							while($row2 = mysql_fetch_assoc($tmp)) { $ary[] = $row2['idItem']; }
						}
						$_SESSION['intStoreList'] = $ary;
												
	
						//$_SESSION['idLevel'] = $row['idLevel'];
						//if($row['enType']=='Special' && $row['chrSpecial']=='Corporate') { $_SESSION['idLevel'] = 1; }
						$_SESSION['idType'] = $row['idType'];
						$_SESSION['chrEmail'] = $row["chrEmail"];
						$_SESSION['idUser'] = $row["ID"];
						$_SESSION['chrFirstName'] = $row["chrFirstName"];
						$_SESSION['chrLastName'] = $row["chrLastName"];
						$_SESSION['auto_logon'] = false;
						$_SESSION['chrLoc'] = $row['chrLoc'];
												
												
						if($_SESSION['idType'] == 4) { //if store user redirect to events else redirect to admin
							header('Location: ' . $BF . 'events/index.php');
							die();
						} else if($_SESSION['idType'] == 1) {
							header('Location: ' . $BF . 'admin/index.php');
							die();
						} else if($_SESSION['idType'] == 2) {
							header('Location: ' . $BF . 'admin/reviews.php');
							die();
						} else if($_SESSION['idType'] == 3) {
							header('Location: ' . $BF . 'admin/recaplist.php');
							die();
						}
					
				} else {
					$_SESSION['ErrorMessage'] = "Incorrect login/password combination.";
					$auth_error = "Authentication failed<!--(1)-->.";
				}
			} else {
				$_SESSION['ErrorMessage'] = "Incorrect login/password combination.";
				echo(mysql_error());
				$auth_error = "Authentication failed<!--(2)-->.";
			}
		
		}

		if (isset($_SESSION['idUser'])) {  // if this variable is set, they are now authenticated
			header("Location: " . $BF . "index.php");
			die();
		}
	
	if (!isset($auth_not_required)) $auth_not_required = false;

	if (!$auth && $auth_not_required != true) {  // if not authenticated, present the form
		header("Location: " . $BF . "index.php");
		die();
	}
?>
