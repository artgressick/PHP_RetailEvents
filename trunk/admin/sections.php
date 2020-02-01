<?php
	$BF = '../';
	require("../_lib2.php");	
	$title = 'Sections Index';
	include($BF. 'includes/meta2.php');
	
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
		
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "cd.chrSectionname"; }
			
	// Search for the sections 
	$q = "SELECT cd.ID, cd.idOrder, cd.idStatus, u.chrFirstName, u.chrLastName, cd.chrSectionName, cd.dtUpdated, cs.chrStatus,
		DATE_FORMAT(cd.dtAdded,'%Y %M, %D %l:%i %p') as dtAdded
		FROM Content_Dynamic as cd
		JOIN Users as u ON cd.idUser = u.ID
		JOIN Content_Status as cs ON cd.idStatus = cs.ID
		WHERE !cd.bDeleted and bSection = 1
		ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		
	$result = database_query($q,"getting sections");

?>

<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>

<?
	include($BF. 'includes/top_admin2.php');
	//You need the next 2 lines if you are going to be using the DELETE function. This will bring in the overlay and also send the TABLE to remove from
	$DeleteTable = "Content_Dynamic"; //This is the table that the overlay will use.
	include($BF. 'includes/overlay.php');
?>


	<div class="AdminTopicHeader">Sections</div>
	<div class="AdminInstructions">Click on any of the pages to edit the information on a page. Click on Add Section to add another section.</div>

	<table class="AdminUtilityBar">
		<tr>
			<td><input type='button' value='Add Section' onclick="location.href='addsection.php'" /></td>
		</tr>
	</table>

	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<? sortList('Section Name', 'chrSectionName'); ?>
			<? sortList('Added by', 'chrLastName, chrFirstName'); ?>
			<? sortList('Date Added', 'dtAdded'); ?>
			<? sortList('Date Updated', 'dtUpdated'); ?>
			<? sortList('Status', 'chrStatus'); ?>
			<th><img src="<?=$BF?>images/options.gif"></th>
		</tr>

<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) { 
		$link = 'location.href="editsection.php?id='. $row["ID"] .'";';
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrSectionName']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrLastName']?>, <?=$row['chrFirstName']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['dtAdded']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['dtUpdated']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrStatus']?></td>	
							<td class='options'><div class='deleteImage' onmouseover='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete_on.png"' onmouseout='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete.png"'>
							<a href="javascript:warning(<?=$row['ID']?>,'<?=encode($row['chrSectionName'])?>');"><img id='deleteButton<?=$row['ID']?>' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a>
							</div></td>			
						</tr>
<?
	}
?>
					</table>
<?
	if($count == 0) { 
?>
					<div style='padding: 3px; border: 1px solid gray; border-top: none; text-align:center'>There are no sections at this time. Click on the add button above to add a new section.</div>
<?
	}
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>