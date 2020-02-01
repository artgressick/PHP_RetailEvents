<?php
	$BF = '../';
	$title = 'Store List';
	require($BF. '_lib2.php');
	
	if($_SESSION['idType'] == 4) { //if store user redirect to events else redirect to admin
		header('Location: ' . $BF . 'events/index.php');
		die();
	} else if($_SESSION['idType'] == 2) {
		header('Location: ' . $BF . 'admin/reviews.php');
		die();
	} else if($_SESSION['idType'] == 3) {
		header('Location: ' . $BF . 'admin/recaplist.php');
		die();
	}
	
	include($BF. 'includes/meta2.php');

	// Getting rid of the notices/warning for the following thing(s)
	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ""; }

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
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrName"; }
	
	$q = "SELECT Stores.ID, Stores.chrName, chrEmail, chrCity, chrState, chrCountry, Regions.chrName as chrRegion 
		FROM Stores 
		LEFT JOIN Regions ON Regions.ID=Stores.idRegion
		WHERE !Stores.bDeleted AND Stores.idLocalization IN (".$_SESSION['chrLoc'].")
		" . ($_REQUEST['chrSearch'] != '' ? " AND Stores.chrName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%'" : '') . " 
		ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	

	$result = database_query($q,"getting stores");

?>

<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>admin/_sorttable.js"></script>

<?
	// Set the title, and add the doc_top
	include($BF . 'includes/top_admin2.php');
	//You need the next 2 lines if you are going to be using the DELETE function. This will bring in the overlay and also send the TABLE to remove from
	$DeleteTable = "Stores"; //This is the table that the overlay will use.
	include($BF. 'includes/overlay.php');
?>
					<div class="AdminTopicHeader">Stores</div>
					<div class="AdminInstructions">These are the stores that have access to the website. Make sure that apple.com is notified when you add a new store. Also do not remove a store unless you notify Retail Workshop/Events team.</div>
					
					<table class="AdminUtilityBar">
						<tr>
							<td><input type='button' value='Add Store' onclick="location.href='addstore.php'" /></td>
							<form id="search" method="get" action="">
							<td style='width: 100%; white-space: nowrap; text-align: right;'>
								<input type="search" placeholder="Search Stores" autosave="Stores" results='5' name="chrSearch" value='<?=$_REQUEST['chrSearch']?>' /></td>
							</form>
						</tr>
					</table>
					
					<table id='sortable' class='sortable' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
							<th>Store Name</th>
							<th>Email</th>
							<th>City</th>
							<th>State</th>
							<th>Country</th>
							<th>Region</th>
							<th><img src="<?=$BF?>images/options.gif"></th>
						</tr>
<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) { 
		$link = 'location.href="editstore.php?id='. $row["ID"] .'";';
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrName']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrEmail']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrCity']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrState']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrCountry']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrRegion']?></td>
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