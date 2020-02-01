<?php
	$BF = '../';
	$title = 'Calendar Types';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');

	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrCalendarType"; }
	
	$q = "SELECT ID,chrKEY,chrCalendarType,chrColorBG,chrColorText
		FROM CalendarTypes 
		WHERE !bDeleted ";
	$q .= "ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		
	$result = database_query($q,"getting users");
?>

<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	// Set the title, and add the doc_top
	include($BF .'calendar/includes/top.php');
	//You need the next 2 lines if you are going to be using the DELETE function. This will bring in the overlay and also send the TABLE to remove from
	$DeleteTable = "CalendarTypes"; //This is the table that the overlay will use.
	include($BF. 'includes/overlay.php');
?>
<div style='padding: 10px;'>
					<div class="AdminTopicHeader">Calendar Types</div>
					<div class="AdminInstructions">.</div>
					
					<table class="AdminUtilityBar">
						<tr>
							<td>
								<input type='button' value='Add Calendar Type' onClick="location.href='addcaltype.php'" />
							</td>
						</tr>
					</table>
					
					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
<?							 sortList('Calendar Type', 'chrCalendarType'); ?>
							 <th>Color Scheme</th>
							 <th class='options'><img src="<?=$BF?>images/options.gif"></th>
						</tr>
<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) { 
		$link = 'location.href="editcaltype.php?key='. $row["chrKEY"] .'";';
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrCalendarType']?></td>
							<td style='cursor: pointer; color: <?=$row['chrColorText']?>; background: <?=$row['chrColorBG']?>;' onclick='<?=$link?>'>Color Scheme</td>
							<td class='options'><div class='deleteImage' onmouseover='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete_on.png"' onmouseout='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete.png"'>
							<a href="javascript:warning(<?=$row['ID']?>,'<?=encode($row['chrCalendarType'],amp)?>');"><img id='deleteButton<?=$row['ID']?>' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a>
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
</div>
<?
	}
	//Include the bottom of the page.
	include($BF. 'includes/bottom.php');
?>