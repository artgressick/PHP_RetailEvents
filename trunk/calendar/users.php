<?php
	$BF = '../';
	$title = 'Calendar Users';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');

	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }

	// Getting rid of the notices/warning for the following thing(s)
	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ""; }

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLastName, chrFirstName"; }
	
	$q = "SELECT Users.ID, chrFirstName, chrLastName, chrEmail
		FROM Users 
		WHERE !bDeleted AND bCalAccess ";
	
	if ($_REQUEST['chrSearch'] != '') {
		$string = str_replace(' ','%',$_REQUEST['chrSearch']);
		$q .= " AND (Users.chrFirstName LIKE '%".$string."%' OR Users.chrLastName LIKE '%".$string."%') ";
	}
	$q .= "ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		
	$result = database_query($q,"getting users");
?>

<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	// Set the title, and add the doc_top
	include($BF .'calendar/includes/top.php');
	//You need the next 2 lines if you are going to be using the DELETE function. This will bring in the overlay and also send the TABLE to remove from
	$DeleteTable = "Users"; //This is the table that the overlay will use.
	include($BF. 'calendar/includes/overlay.php');
?>
<div style='padding: 10px;'>
					<div class="AdminTopicHeader">Calendar Access</div>
					<div class="AdminInstructions">These are the users of the calendar website. If you need to give access to users on the "Add User" button.</div>
					
					<table class="AdminUtilityBar">
						<tr>
							<td><input type='button' value='Add User' onClick="location.href='adduser.php'" /></td>
							<form id="search" method="get" action="">
								<td style='width: 100%; white-space: nowrap; text-align: right;'>Search Users: 
									<input type="textbox" name="chrSearch" value='<?=$_REQUEST['chrSearch']?>' />
									 <input type="submit" name="submit" value="Filter" />
								</td>
							</form>
						</tr>
					</table>
					
					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
<?							 sortList('First Name', 'chrFirstname');
							 sortList('Last Name', 'chrLastname');
							 sortList('Email', 'chrEmail');

?>
							
							<th><img src="<?=$BF?>images/options.gif"></th>
						</tr>
<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) { 
		$link = 'location.href="edituser.php?id='. $row["ID"] .'";';
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrFirstName']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrLastName']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrEmail']?></td>
							<td class='options'><div class='deleteImage' onmouseover='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete_on.png"' onmouseout='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete.png"'>
							<a href="javascript:warning(<?=$row['ID']?>,'<?=encode($row['chrFirstName'],amp)?> ' + '<?=encode($row['chrLastName'],amp)?>');"><img id='deleteButton<?=$row['ID']?>' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a>
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