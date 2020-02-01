<?php
	$BF = '../../';
	$title = 'My Report';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');

	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root
		idType of 1 = Super user 
		idType of 2 = Editor
		idType of 3 = Corp Events
		idType of 4 = Store
		
		$pageaccess is array of user types that can access the page
	*/
	
	$pageaccess = array("1","2","3");
	if(!in_array($_SESSION['idType'],$pageaccess)) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: '. $BF ."nopermission.php"); die(); }
	// End Security Check
		
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "dtStamp"; $_REQUEST['ordCol'] = ""; }
	
		$q = "SELECT ID, chrName, dtStamp, DATE_FORMAT(dtStamp,'%M %D, %Y %l:%i %p') as dtStampFormat
			FROM CustomReports
			WHERE idUser='". $_SESSION['idUser'] ."'
			ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	

	$result = database_query($q,"Getting Report Information");

	include($BF. 'includes/top_admin2.php');
?>
					<div class="AdminTopicHeader">My Reports Report</div>
					<div class="AdminInstructions">This is a list of reports which you saved from the Super Report section.  Click on any of them to see that specific report.</div>
					
					
					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
							<? sortList('Report Name', 'chrName'); ?>
							<? sortList('Report Date', 'dtStampFormat'); ?>
						</tr>
<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) {
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
							onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td onclick='location.href="superreport2.php?id=<?=$row['ID']?>"'><?=$row['chrName']?></td>
							<td onclick='location.href="superreport2.php?id=<?=$row['ID']?>"'><?=$row['dtStampFormat']?></td>
						</tr>
<?	} ?>
					</table>
<?
	if($count == 0) { 
?>
					<div style='padding: 3px; border: 1px solid gray; border-top: none; text-align:center'>You have not created any custom reports.</div>
<?
	}
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>