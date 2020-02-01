<?php
	$BF = '../';
	$title = 'Add User';
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
		
	// if this is a form submission
	if(isset($_POST['chrEmail'])) {
	
		$q = "INSERT INTO Users SET 
			chrFirstName='". encode($_POST['chrFirstName']) ."',
			chrLastName='". encode($_POST['chrLastName']) ."',
			chrEmail='". encode($_POST['chrEmail']) ."',
			chrPassword='". md5($_POST['chrPassword1']) . "',
			idType='". $_POST['idType'] ."',
			chrLoc='". implode(",", $_POST['chrLoc']) ."'
		";
			
		database_query($q, "insert User");
		global $mysqli_connection;
		$newID = mysqli_insert_id($mysqli_connection);

		if($_POST['idPresenters'] != '') {
			$ids = $_POST['idPresenters']==''?array():explode(',', $_POST['idPresenters']);
			$stores = "INSERT INTO ACL (idUser,enType,idItem) VALUES ";
			$cntStores=0; 
			foreach($ids as $id) { $stores .= ($cntStores++ == 0 ? '' : ',')."('". $newID ."','Stores','". $id ."')";  }
			database_query($stores,"inserting stores");
		}

		header("Location: users.php");
		die();
	}

	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrEmail', "You must enter a First Name.", "email");
		total += ErrorCheck('chrFirstName', "You must enter a First Name.");
		total += ErrorCheck('chrLastName', "You must choose a Last Name");
		total += ErrorCheck('idType', "You must select a User Right");
		total += matchPasswordsAdd('chrPassword1', 'chrPassword2');
		if (document.getElementById('idType').value != '4') {
			if ( document.getElementById('chrLoc1').checked == false &&
				 document.getElementById('chrLoc2').checked == false &&
				 document.getElementById('chrLoc3').checked == false &&
				 document.getElementById('chrLoc4').checked == false &&
				 document.getElementById('chrLoc5').checked == false &&
				 document.getElementById('chrLoc6').checked == false &&
				 document.getElementById('chrLoc7').checked == false &&
				 document.getElementById('chrLoc8').checked == false ) {

				 		total ++;
				 		document.getElementById('errors').innerHTML += "<div class='ErrorMessage'>You must select at least 1 Localization for this user type.</div>";
			}
		}
		
		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>
<?
	include($BF. 'components/list/sortList.php'); 
	include($BF. 'includes/top_admin2.php');
?>
					<div class="AdminTopicHeader">Add User</div>
					<div class="AdminInstructions2">Your are about to add a new user to the database. Once added you will need to send them their username and password.</div>
					
					<form id='idForm' name='idForm' method='post' action=''>

					<div id='errors'></div>

					<table class='AdminTwoColumns'>
						<tr>
							<td class="Left">

								<div class='form'>
									<div class='formHeader'>User Type <span class='Required'>(Required)</span></div>
									<select name='idType' id='idType'>
										<option value=''>Please choose a type</option>
<?
	$UserTypes = database_query("SELECT ID, chrType FROM UserTypes ORDER BY ID","getting User Types");
	while($row = mysqli_fetch_assoc($UserTypes)) {
?>
										<option value='<?=$row['ID']?>'><?=$row['chrType']?></option>
<?
	}
?>
									</select>
								</div>
								
								<div class='form'>
									<div class='formHeader'>Email Address <span class='Required'>(Required)</span></div>
									<input type='text' size='40' maxlength='80' name='chrEmail' id='chrEmail' />
								</div>
								
								<div class='form'>
									<div class='formHeader'>First Name <span class='Required'>(Required)</span></div>
									<input type='text' size='40' maxlength='80' name='chrFirstName' id='chrFirstName' />
								</div>
								
								<div class='form'>
									<div class='formHeader'>Last Name <span class='Required'>(Required)</span></div>
									<input type='text' size='40' maxlength='80' name='chrLastName' id='chrLastName' />
								</div>
								
								<div class='form'>
									<div class='formHeader'>Password <span class='Required'>(Required)</span></div>
									<input type='password' size='40' maxlength='80' name='chrPassword1' id='chrPassword1' /><br />
									<input type='password' size='40' maxlength='80' name='chrPassword2' id='chrPassword2' />
								</div>
										
							</td>
				
							<td class="Right">
							
								<div class='sectionInfo'>
									<div class='sectionHeader'>Store Access</div>
									<div class='sectionContent'>
										
										
							
																<div class='form'>
																	<div class='formHeader'>Select each store that this user will have access to.</div>
																	<input type='button' value='Add...' onclick='newwin = window.open("select-store.php?d=<?=urlencode(base64_encode('functioncall=presenters_add'))?>","new","width=600,height=400,resizable=1,scrollbars=1"); newwin.focus();'/>

																	<input type='hidden' id='idPresenters' name='idPresenters' />
																	<input type='hidden' id='chrPresenters' name='chrPresenters' />

																	<table class='List' id='Presenters' cellpadding="0" cellspacing="0" width="100%">
																			<tr>
																				<th>Store</th>
																				<th style='width: 1%;'></th>
																			</tr>
																			<tr id='presenterBody' name='presenterBody'>
																			</tr>
																		</table>

										<script type="text/javascript">//<![CDATA[
										function presenters_add(id, chr) 
										{ 
											list_add('Presenters', 'idPresenters', 'chrPresenters', id, chr); 
										}
										// ]]></script>

														<!-- End of the section -->
																</div>
															</div>

								<div class='sectionInfo'>
									<div class='sectionHeader'>Admin Localization Access</div>
									<div class='sectionContent'>
																			
															<div class='form'>
																<div class='formHeader'>Select Adminstrative Localization that this user will have access to.<br /><span class='Required'>(Must select at least 1)</span></div>
<?
																$localizations = database_query("SELECT * FROM Localization WHERE !bDeleted ORDER BY ID", "Getting Localizations");
?>
																	<table width="100%" cellpadding="0" cellspacing="0" border="0">
																		<tr>
																			<td width="50%">
<?
																$count=0;
																while ($row = mysqli_fetch_assoc($localizations)) { 
																	if ($count == 4) {
?>
																			</td>
																			<td width="50%">
<?
																	}
																	$count++
?>
																				<div><input type="checkbox" id="chrLoc<?=$count?>" name="chrLoc[]" value="<?=$row['ID']?>" <?=($row['ID'] == 1 ? "checked='checked'" : "" )?> /> <?=$row['chrLocalization']?></div>
<?
																}
?>
																			</td>
																		</tr>
																	</table>
																</div>
															</div>

							
							</td>
						</tr>
					</table>

					<div class='FormButtons'>
						<input type='button' name='SubmitAddUser' value='Save Users' onclick='error_check()' />
					</div>
			
					</form>
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>