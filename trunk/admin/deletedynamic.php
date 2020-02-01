<?php
	require_once('../_lib.php');
	
	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root
		idType of 1 = Super user 
		idType of 2 = Editor
		idType of 3 = Corp Events
		idType of 4 = Store
		
		$pageaccess is array of user types that can access the page
	*/
	
	$pageaccess = array("1","3");
	if(!in_array($_SESSION['idType'],$pageaccess)) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: '. $BF ."nopermission.php"); die(); }
	// End Security Check
	
	$info = mysql_fetch_assoc(do_mysql_query("SELECT ID, chrPageTitle FROM Content_Dynamic WHERE ID='" . $_REQUEST['id'] . "'","getting dynamic info"));

	if (@$_REQUEST['Confirmed']) {
	
		// mark the the database record deleted
		do_mysql_query("UPDATE Content_Dynamic SET bDeleted=1 WHERE ID='" . $_REQUEST['id'] . "'","bdeleted dynamic content");
	
		$_SESSION['InfoMessage'][] = 'The store '. $info['chrPageTitle'] .' has been deleted.';
		header('Location: dynamic.php');
		exit();
	}
	
// Set the title, and add the doc_top
$title = "Delete Dynamic Content";
require(BASE_FOLDER . 'docpages/doc_meta_events.php');
include(BASE_FOLDER . 'docpages/doc_top_events.php');

?>

<div style='padding: 10px;'>

	<div class="AdminTopicHeader">Delete Dynamic Content: <span class='Specific'><?=$info['chrPageTitle']?></span></div>
				<div class="AdminDirections" style='width: 870px;'>To remove this dynamic content, click on the "Delete" button.</div>
				
		<div class='Question'>Are you sure you want to delete '<?=$info["chrPageTitle"]?>'?</div>
			<div class='FormButtons' style='margin-top: 10px;'>
			<input type='button' onclick='location.href="?id=<?=$_REQUEST['id']?>&amp;Confirmed=1";' value='Delete' />
			<input type='button' value='Cancel' onclick='history.back()' />
			</div>
		</div>
		
		</div>
		</div>
	</div>
<?

include(BASE_FOLDER . 'docpages/doc_bottom.php');

?>