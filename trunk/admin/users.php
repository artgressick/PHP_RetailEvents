<?php
	$BF = '../';
	$title = 'Users';
	require($BF. '_lib2.php');
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
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLastName, chrFirstName"; }
	
	$q = "SELECT Users.ID, chrFirstName, chrLastName, chrEmail, idType, chrType 
		FROM Users 
		LEFT JOIN UserTypes ON Users.idType=UserTypes.ID
		WHERE !bDeleted ";
	
	if ($_REQUEST['chrSearch'] != '') {
		$string = str_replace(' ','%',$_REQUEST['chrSearch']);
		$q .= "AND (Users.chrFirstName LIKE '%".$string."%' OR Users.chrLastName LIKE '%".$string."%') ";
	}
	if ($_REQUEST['idType'] != '') {
		$q .= "AND Users.idType = ".$_REQUEST['idType']." ";
	}
	
	$q .= "ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		
	$result = database_query($q,"getting users");
?>

<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>

<?
	// Set the title, and add the doc_top
	include($BF . 'includes/top_admin2.php');
	//You need the next 2 lines if you are going to be using the DELETE function. This will bring in the overlay and also send the TABLE to remove from
	$DeleteTable = "Users"; //This is the table that the overlay will use.
	include($BF. 'includes/overlay.php');
?>
					<div class="AdminTopicHeader">Apple Store Employees</div>
					<div class="AdminInstructions">These are the users of the website. If you need to give access to users click on the user and then edit their information or add a new user.</div>
					
					<table class="AdminUtilityBar">
						<tr>
							<td><input type='button' value='Add User' onClick="location.href='adduser.php'" /></td>
							<form id="search" method="get" action="">
								<td style='width: 100%; white-space: nowrap; text-align: right;'>Search Users: 
									<input type="textbox" name="chrSearch" value='<?=$_REQUEST['chrSearch']?>' />
									Filter By Type: 
<?
	$UserTypes = database_query("SELECT ID, chrType FROM UserTypes ORDER BY chrType","Getting All Types");
?>
									<select name='idType' id='idType'>
										<option value=''>-Show All Types-</option>
<?
									while ($row = mysqli_fetch_assoc($UserTypes)) {
?>
										<option<?=($row['ID'] == @$_REQUEST['idType'] ? ' selected="selected"' : '')?> value="<?=$row['ID']?>"><?=$row['chrType']?></option>
<?
									}
?>
									</select>									
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
							 sortList('User Type', 'chrType');

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
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrType']?></td>
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
<?
	}
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>