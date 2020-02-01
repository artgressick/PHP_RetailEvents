<?php
	$BF = '../';
	$title = 'Add Product';
	require($BF. '_lib2.php');
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
		
	// if this is a form submission
	if(isset($_POST['chrName'])) {
	
		$q = "INSERT INTO Products SET 
			chrName='" . encode($_POST['chrName']) . "',
			enType=" . $_POST['idType'] . ",
			idType=" . $_POST['idType'] . "
			";
		
		database_query($q, "insert product");

		header("Location: products.php");
		die();
	}

	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrName', "You must enter a Product Name.");
		total += ErrorCheck('idType', "You must enter a Product Type.");

		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>

<?

	include($BF. 'includes/top_admin2.php');
?>
					<div class="AdminTopicHeader">Add Product</div>
					<div class="AdminInstructions2">Type the name of the product and the type and then click "Save Product"</div>
					
					<form id='idForm' name='idForm' method='post' action=''>

					<div id='errors'></div>

					<table class='AdminTwoColumns'>
						<tr>
							<td class="Left">

								<div class='form'>
									<div class='formHeader'>Product Name <span class='Required'>(Required)</span></div>
									<input type='text' size='40' maxlength='80' name='chrName' id='chrName' />
								</div>
						
								<div class='form'>
									<div class='formHeader'>Product Type <span class='Required'>(Required)</span></div>
									<select name='idType' id='idType'>
										<option value=''>Please choose a type</option>
<?
	$ProductTypes = database_query("SELECT ID, chrType FROM ProductTypes ORDER BY ID","getting Product Types");
	while($row = mysqli_fetch_assoc($ProductTypes)) {
?>
										<option value='<?=$row['ID']?>'><?=$row['chrType']?></option>
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
						<input type='button' name='SubmitAddUser' value='Save Product' onclick='error_check()' />
					</div>
			
					</form>
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>