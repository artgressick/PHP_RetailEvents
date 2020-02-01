<?php
	$BF = '../';
	$title = 'Edit Product';
	require($BF. '_lib2.php');
	// Checking request variables
	($_REQUEST['id'] == "" || !is_numeric($_REQUEST['id']) ? ErrorPage() : "" );
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
		
	$info = fetch_database_query("SELECT * FROM Products WHERE ID=". $_REQUEST['id'],"Getting Product Info");
		
	// if this is a form submission
	if(isset($_POST['chrName'])) {

		$table = 'Products';
		$mysqlStr = '';
		$audit = '';

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrName',$info['chrName'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'idType',$info['idType'],$audit,$table,$_POST['id']);

		

		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }

		// When the page is done updating, move them back to whatever the list page is for the section you are in.
		header("Location: products.php");
		die();
	}

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrName"; }
	

	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrName', "You must enter a Product Name.");
		total += ErrorCheck('idType', "You must choose a Product Type.");

		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>
<?

	include($BF. 'includes/top_admin2.php');
	
?>
					<div class="AdminTopicHeader">Edit Product</div>
					<div class="AdminInstructions2">Please note that changing the name of product will change all past instances of this product. This means that if the product was used in past shows it will update those names.</div>
					
					<form id='idForm' name='idForm' method='post' action=''>

					<div id='errors'></div>

					<table class='AdminTwoColumns'>
						<tr>
							<td class="Left">

								<div class='form'>
									<div class='formHeader'>Product Name <span class='Required'>(Required)</span></div>
									<input type='text' size='40' maxlength='80' name='chrName' id='chrName' value='<?=$info['chrName']?>' />
								</div>
						
								<div class='form'>
									<div class='formHeader'>Product Type <span class='Required'>(Required)</span></div>
									<select name='idType' id='idType'>
										<option value=''>Please choose a type</option>
<?
	$ProductTypes = database_query("SELECT ID, chrType FROM ProductTypes ORDER BY ID","getting Product Types");
	while($row = mysqli_fetch_assoc($ProductTypes)) { ?>
										<option<?=($row['ID'] == $info['idType'] ? ' selected' : '')?> value='<?=$row['ID']?>'><?=$row['chrType']?></option>
<?
	}
?>
									</select>
								</div>
										
							</td>
				
							<td class="Right">
							
							
							</td>
						</tr>
					</table>

					<div class='FormButtons'>
						<input type='button' name='SubmitAddUser' value='Update Product' onclick='error_check()' />
						<input type='hidden' name='id' value='<?=$_REQUEST["id"]?>' />
					</div>
			
					</form>
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>