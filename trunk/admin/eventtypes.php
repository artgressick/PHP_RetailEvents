<?php
	$BF = '../';
	$title = 'Workshop/Event Types';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');

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
		
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrCategory, chrName"; }
	
	$q = "SELECT EventTypes.ID, EventTypes.chrName, EventCategory.chrCategory, chrStyleClass, bEditorReview, chrLocalization, EventTypes.bShow
		FROM EventTypes
		JOIN EventCategory ON EventTypes.idEventCategory = EventCategory.ID
		JOIN Localization ON EventTypes.idLocalization=Localization.ID
		WHERE !EventTypes.bDeleted AND Localization.ID IN (SELECT GROUP_CONCAT(Lo.ID) FROM Localization as Lo JOIN Users ON Users.ID=".$_SESSION['idUser']." WHERE FIND_IN_SET(Lo.ID,chrLoc) GROUP BY Lo.ID)
		ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	

	$result = database_query($q,"getting stores");

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top_admin2.php');
	//You need the next 2 lines if you are going to be using the DELETE function. This will bring in the overlay and also send the TABLE to remove from
	$DeleteTable = "EventTypes"; //This is the table that the overlay will use.
	include($BF. 'includes/overlay.php');
?>
					<div class="AdminTopicHeader">Workshop/Event Types</div>
					<div class="AdminInstructions">This is a list of Workshop/Event Types that appears in the COE when users add/edit events. The style sheet is defined by the Apple.com web team and should not be changed unless they have the correct styles on their end. None of these should be changed unless you have permission from Retail Workshop/Events Team.</div>
					
					<table class="AdminUtilityBar">
						<tr>
							<td><input type='button' value='Add Workshop/Event Type' onclick="location.href='addeventtype.php'" /></td>
						</tr>
					</table>

					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
							<? sortList('Category', 'chrCategory'); ?>
							<? sortList('Type', 'chrName'); ?>
							<? sortList('Localization','chrLocalization'); ?>
							<? sortList('Style Sheet', 'chrStyleClass'); ?>
							<? sortList('Requires Editor Review', 'bEditorReview'); ?>
							<? sortList('Shown', 'bShow'); ?>
							<th><img src="<?=$BF?>images/options.gif"></th>
						</tr>
<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) { 
		$link = 'location.href="editeventtype.php?id='. $row["ID"] .'";';
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrCategory']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrName']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrLocalization']?></td>						
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrStyleClass']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=($row['bEditorReview'] == 1 ? 'Yes' : 'No') ?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=($row['bShow'] == 1 ? 'Shown' : 'Hidden') ?></td> 
							<td class='options'><div class='deleteImage' onmouseover='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete_on.png"' onmouseout='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete.png"'>
							<a href="javascript:warning(<?=$row['ID']?>,'<?=encode($row['chrName'],amp)?>');"><img id='deleteButton<?=$row['ID']?>' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a>
							</div></td>			
						</tr>
<?
	}
?>
					</table>
<?
	if($count == 0) { 
?>
					<div style='padding: 3px; border: 1px solid gray; border-top: none; text-align:center'>No records to display</div>
<?
	}
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>