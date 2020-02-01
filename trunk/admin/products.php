<?php
	$BF = '../';
	require($BF . "_lib2.php");	
	$title = 'Products List';
	include($BF. 'includes/meta2.php');
	
	
	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root
		idType of 1 = Super user 
		idType of 2 = Editor
		idType of 3 = Corp Events
		idType of 4 = Store
		
		$pageaccess is array of user types that can access the page
	*/
	
	$pageaccess = array("1","2");
	if(!in_array($_SESSION['idType'],$pageaccess)) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: '. $BF ."nopermission.php"); die(); }
	// End Security Check
	
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrType, chrName"; }
	
	// This is needed to find out how to originally sort the list of people.  Currently it's sorting by the name of the store in asc order. The line below it is to save the error dugger from wanting to print 1 billion notices.
	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ''; }
	
	$q = "SELECT Products.ID, chrName, chrType 
		FROM Products 
		JOIN ProductTypes ON ProductTypes.ID=Products.idType
		WHERE !bDeleted 
		ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];

	$result = database_query($q,"getting products");
?>

<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>

<?
	// Set the title, and add the doc_top
	include($BF . 'includes/top_admin2.php');
	//You need the next 2 lines if you are going to be using the DELETE function. This will bring in the overlay and also send the TABLE to remove from
	$DeleteTable = "Products"; //This is the table that the overlay will use.
	include($BF. 'includes/overlay.php');
?>
					<div class="AdminTopicHeader">Products</div>
					<div class="AdminInstructions">These are the products that are used in the COE section. Adding and removing the products will affect new and existing calendars. Please make sure that the Events Team is aware of any changes.</div>
					
					<table class="AdminUtilityBar">
						<tr>
							<td><input type='button' value='Add Product' onclick="location.href='addproduct.php'" /></td>
						</tr>
					</table>

					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
							<? sortList('Product Name', 'chrName'); ?>
							<? sortList('Product Type', 'chrType'); ?>
							<th><img src="<?=$BF?>images/options.gif"></th>
						</tr>
<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) { 
		$link = 'location.href="editproduct.php?id='. $row["ID"] .'";';
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrName']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrType']?></td>
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