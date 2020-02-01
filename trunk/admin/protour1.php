<?php
	$BF = '../';
	$title = 'Pro Tour Special Events';
	require($BF. '_lib2.php');

	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root
		idType of 1 = Super user 
		idType of 2 = Editor
		idType of 3 = Corp Events
		idType of 4 = Store
		
		$pageaccess is array of user types that can access the page
	*/
	
	$pageaccess = array("1");
	if(!in_array($_SESSION['idType'],$pageaccess)) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: '. $BF ."nopermission.php"); die(); }
	// End Security Check


	include($BF. 'includes/meta2.php');

	//Check the post and then redirect
	if(count($_POST)) {
		$_SESSION['protourstores'] = $_POST['stores'];
		header("Location: protour2.php");
		die();
	}

	$stores = database_query("SELECT ID, chrName FROM Stores WHERE !bDeleted ORDER BY chrName","getting Stores");
	$totalCount = mysqli_num_rows($stores);
	$base = $totalCount/3;
	$count = ceil($base);
	$subtract = floor($base); //This is the number to find subtract
	if ((($base - $subtract) > .5) || (($base - $subtract) == 0)) {
		$incrementrow = 0;
	} else {
		$incrementrow = 1;
	}
	
?>

<style type="text/css">
.stores { width: 100%; }
.stores td { width: 33%; vertical-align: top; font-size:11px; }
</style>

<?
	include($BF. 'includes/top_admin2.php');
?>
				<form method='post' action=''>
					<div class="AdminTopicHeader">Pro Tour Special Event</div>
					<div class="AdminInstructions2">Choose all of the stores below that you want to add an event to and then click Next.</div>
					
					<table cellpadding="0" cellspacing="0" class='stores'>
						<tr>
							<td>
<?
	$cnt = 0;
	while($row = mysqli_fetch_assoc($stores)) { 
		if($cnt == $count) {
?>
							</td><td>
<?
		$cnt = $incrementrow;
		}
		$cnt = $cnt+1;
?>
							<div><input type='checkbox' name='stores[]' value='<?=$row['ID']?>' /> <?=$row['chrName']?></div>
<?
	}
?>
							</td>
						</tr>
					</table>
					
					<input style="margin-top:20px;" type='submit' value='Next' name='chrStores' />
				</form>
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>