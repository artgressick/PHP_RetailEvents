<?php
	require_once('../_lib.php');
	
	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root */
	if($_SESSION['idType'] != 1 && !in_array($_REQUEST['idStore'],$_SESSION['intStoreList'])) {
		$_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: ' . BASE_FOLDER . "nopermission.php"); die();
	}	

	$error_messages = array();

	$user = mysql_fetch_assoc(do_mysql_query("SELECT ID, chrName FROM Presenters WHERE ID='" . $_REQUEST['id'] . "'","getting presenter"));

	if (@$_REQUEST['Confirmed']) {

		// mark the the database record deleted
		if (do_mysql_query("UPDATE Presenters SET bDeleted=1 WHERE ID='" . $_REQUEST['id'] . "'","bdeleted presenter") !== false) {
			$_SESSION['InfoMessage'][] = 'The user <span class="Specific">' . $user['chrName'] . '</span> has been deleted.';
			header('Location: presenters.php');
			exit();
		} else {
			$error_messages[] = 'There was a database error.';
		}
	}
	
// Set the title, and add the doc_top
$title = "Delete Presenter";
require(BASE_FOLDER . 'docpages/doc_meta_events.php');
include(BASE_FOLDER . 'docpages/doc_top_events.php');

?>

<div style='padding: 10px;'>

	<div class="AdminTopicHeader">Delete User: <span class='Specific'><?=$user['chrName']?></span></div>
				<div class="AdminDirections" style='width: 870px;'>To remove this presenter from the list of possible presenters, click on the "Delete" button.</div>
				
		<div class='Question'>Are you sure you want to delete '<?=$user["chrName"]?>'?</div>
			<div class='FormButtons'>
			<input type='button' onclick='location.href="?id=<?=$_REQUEST['id']?>&amp;Confirmed=1";' value='Delete' style='margin-top: 10px;' />
			</div>
		</div>
		
		</div>
		</div>
	</div>
<?

include(BASE_FOLDER . 'docpages/doc_bottom.php');

?>