<?php
	$BF = '../';
	$title = 'Edit User';
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
	
	$pageaccess = array("1");
	if(!in_array($_SESSION['idType'],$pageaccess)) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: '. $BF ."nopermission.php"); die(); }
	// End Security Check

		
	$info = fetch_database_query("SELECT * FROM Users WHERE ID=". $_REQUEST['id'],"Getting User Info");
		
	// if this is a form submission
	if(isset($_POST['chrFirstName'])) {

		$table = 'Users';
		$mysqlStr = '';
		$audit = '';
		
		$_POST['chrLoc'] = implode(",", $_POST['chrLoc']);

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrEmail',$info['chrEmail'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrFirstName',$info['chrFirstName'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrLastName',$info['chrLastName'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrLoc',$info['chrLoc'],$audit,$table,$_POST['id']);
		if( $_POST['chrPassword'] != '')
		{
			if( $_POST['chrPassword'] == $_POST['chrPassword2'])
			{
				list($mysqlStr,$audit) = set_strs_password($mysqlStr,'chrPassword',$info['chrPassword'],$audit,$table,$_POST['id']);
			}
		}
		list($mysqlStr,$audit) = set_strs($mysqlStr,'idType',$info['idType'],$audit,$table,$_POST['id']);

		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }

		// When the page is done updating, move them back to whatever the list page is for the section you are in.
		header("Location: users.php");
		die();
	} else {
		$result = database_query("SELECT Stores.ID, Stores.chrName
			FROM Stores
			JOIN ACL ON ACL.idItem=Stores.ID
			WHERE idUser='". $_REQUEST['id'] ."'
			", 'get stores');

		$count = 0;
		$info['chrPresenters'] = "";
		$info['idPresenters'] = "";
		while($row = mysqli_fetch_assoc($result)) {
			$info['chrPresenters'] .= ($count != 0 ? ','.$row['chrName'] : $row['chrName']);
			$info['idPresenters'] .= ($count++ != 0 ? ','.$row['ID'] : $row['ID']);
		}
	}

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLastName"; }
	


	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('idType', "You must select a User Type.");
		total += ErrorCheck('chrEmail', "You must choose a Region");
		total += ErrorCheck('chrFirstName', "You must enter a First Name.");
		total += ErrorCheck('chrLastName', "You must enter a Last Name.");		
		total += matchPasswords('chrPassword', "chrPassword2", "Passwords did not match.");
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
	include($BF. 'includes/top_admin2.php');
?>
					<div class="AdminTopicHeader">Edit User</div>
					<div class="AdminInstructions2">You are about to change the information for a user in the database. Please make sure that the user is aware of any changes you make.</div>
					
					<form id='idForm' name='idForm' method='post' action=''>

					<div id='errors'></div>

					<table class='AdminTwoColumns'>
						<tr>
							<td class="Left">

								<div class='formHeader'>User Type <span class='Required'>(Required)</span></div>
								<select name='idType' id='idType'>
									<option value=''>Please choose a type</option>
<?
	$UserTypes = database_query("SELECT ID, chrType FROM UserTypes ORDER BY ID","getting User Types");
	while($row = mysqli_fetch_assoc($UserTypes)) { 
?>								
									<option <?=($row['ID'] == $info['idType'] ? ' selected' : '')?> value='<?=$row['ID']?>'><?=$row['chrType']?></option>
<?
	}
?>
								</select>
								</div> 
								
								<div class='form'>
									<div class='formHeader'>Email <span class='Required'>(Required)</span></div>
									<input type='text' size='40' maxlength='80' name='chrEmail' id='chrEmail' value='<?=$info['chrEmail']?>' />
								</div>
					
								<div class='form'>
									<div class='formHeader'>First Name <span class='Required'>(Required)</span></div>
									<input type='text' size='20' maxlength='30' name='chrFirstName' id='chrFirstName' value='<?=$info['chrFirstName']?>' />
								</div>
								
								<div class='form'>
									<div class='formHeader'>Last Name <span class='Required'>(Required)</span></div>
									<input type='text' size='20' maxlength='40' name='chrLastName' id='chrLastName'value='<?=$info['chrLastName']?>' />
								</div>
											
								<div class='form'>
									<div class='formHeader'>Password <span class='Required'>(Only if resetting their password)</span></div>
									<input type='password' size='30' maxlength='40' name='chrPassword' id='chrPassword' value='' /><br />
									<input type='password' size='30' maxlength='40' name='chrPassword2' id='chrPassword2' value='' />
								</div>
										
							</td>
				
							<td class="Right">

						<div class='sectionInfo'>
							<div class='sectionHeader'>Store Access</div>
							<div class='sectionContent'>
<?	$results = database_query("SELECT ACL.ID,Stores.chrName,ACL.enPermission FROM ACL JOIN Stores ON Stores.ID=ACL.idItem WHERE ACL.idUser=". $_REQUEST['id'],"Getting Store Infomration"); ?>
									Add Store <input type='button' value='+' onclick='javascript:newwin = window.open("popup_addstore.php?tbl=store_assoc&idUser=<?=$_REQUEST['id']?>","new","width=500,height=400,resizable=1,scrollbars=1"); newwin.focus();' >

									<table id='store_assoc' class='List' cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<th>Store Name</th>
											<th>Role</th>
											<th><img src="<?=$BF?>images/options.gif"></th>
										</tr>
<?	$count=0;
	while($row = mysqli_fetch_assoc($results)) { ?>	
										<tr id='store_assoctr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
											onmouseover='RowHighlight("store_assoctr<?=$row['ID']?>");' onmouseout='UnRowHighlight("store_assoctr<?=$row['ID']?>");'>
											<td><?=$row['chrName']?></td>
											<td><select name='enPermission' id='enPermission' onchange="javascipt:quickassoc('ajax_addstore.php?postType=quickInsert&ID=<?=$row['ID']?>&enPermission='+this.value)"><option value='0'>-Select Role-</option><option<?=($row['enPermission'] == 'Store Manager' ? ' selected' : '')?> value="Store Manager">Store Manager</option><option<?=($row['enPermission'] == 'Theater Coordinator' ? ' selected' : '')?> value="Theater Coordinator">Theater Coordinator</option></select></td>
											<td class='options'><div class='deleteImage' onmouseover='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete_on.png"' onmouseout='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete.png"'>
											<a href="javascript:quickdel('<?=$BF?>ajax_delete.php?postType=permDelete&tbl=ACL&idUser=<?=$_SESSION['idUser']?>&id=<?=$row['ID']?>', <?=$row['ID']?>,'store_assoc');"><img id='deleteButton<?=$row['ID']?>' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a></td>
										</tr>
<?	} ?>
									</table>

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
																	$count++;
																	if ($row['ID'] == $info['chrLoc'] || preg_match('/(^'.$row['ID'].'\,)|(\,'.$row['ID'].'$)|(\,'.$row['ID'].'\,)/',$info['chrLoc'],$matches)) {
																		$matches=true;
																	} else {
																		$matches=false;
																	}
?>
									<div><input type="checkbox" id="chrLoc<?=$count?>" name="chrLoc[]" value="<?=$row['ID']?>" <?=($matches ? "checked='checked'" : "" )?> /> <?=$row['chrLocalization']?></div>
<?
																}
?>
																			</td>
																		</tr>
																	</table>
																</div>
															</div>
												
												
									</div>									
									</div>
								</div>
							
							</td>
						</tr>
					</table>

					<div class='FormButtons'>
						<input type='button' name='SubmitAddUser' value='Update User' onclick='error_check()' />
						<input type='hidden' name='id' value='<?=$_REQUEST["id"]?>' />
					</div>
			
					</form>
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>