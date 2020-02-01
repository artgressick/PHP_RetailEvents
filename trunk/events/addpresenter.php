<?php
	$BF = '../';
	$title = 'Add Presenter';
	$curPage='presenters';
	require($BF. '_lib2.php');
		
	// if this is a form submission
	if(isset($_POST['chrName'])) {
	
		$store = fetch_database_query("SELECT idItem FROM ACL WHERE idUser='" . $_SESSION['idUser'] . "'",'getting store id');
		
		$q = "INSERT INTO Presenters SET
			chrName='" . encode($_REQUEST['chrName']) . "',
			chrJobTitle='" . encode($_REQUEST['chrJobTitle']) . "',
			chrCompanyLabel='" . encode($_REQUEST['chrCompanyLabel']) . "',
			chrAddress1='" . encode($_REQUEST['chrAddress1']) . "',
			chrAddress2='" . encode($_REQUEST['chrAddress2']) . "',
			chrAddress3='" . encode($_REQUEST['chrAddress3']) . "',
			chrCity='" . encode($_REQUEST['chrCity']) . "',
			chrState='" . encode($_REQUEST['chrState']) . "',
			chrPostalCode='" . encode($_REQUEST['chrPostalCode']) . "',
			chrOfficePhone='" . encode($_REQUEST['chrOfficePhone']) . "',
			chrMobilePhone='" . encode($_REQUEST['chrMobilePhone']) . "',
			chrFax='" . encode($_REQUEST['chrFax']) . "',
			chrEmail='" . $_REQUEST['chrEmail'] . "',
			chrWebsite='" . encode($_REQUEST['chrWebsite']) . "',
			chrCredentials='" . encode($_REQUEST['chrCredentials']) . "',
			idStore='" . $store['idItem'] . "'
		";
		
		if(is_uploaded_file($_FILES['blobPhoto']['tmp_name'])) {
			$data = file_get_contents($_FILES['blobPhoto']['tmp_name']);
			$q .= ",blobPhoto='" . addslashes($data) . "'";
		}

		database_query($q, 'insert presenter');

		global $mysqli_connection;
		$new_id = mysqli_insert_id($mysqli_connection);

		// save the expertise
		$ids = explode(',', $_POST['idExpertise']);
		// look for additions
		foreach($ids as $expertise_id) {
			database_query("INSERT INTO PresenterExpertise SET idPresenter='" . $new_id . "', idExpertise='" . $expertise_id . "'", 'insert expertise');
		}

		$_SESSION['InfoMessage'][] = 'New presenter <span class="Specific">' . encode($_POST['chrName']) . '</span> has been saved.';
		header("Location: presenters.php");
		die();
	}


	include($BF. 'includes/meta2.php');
	include($BF .'includes/states.php');

	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrName', "You must enter a Name/Artist.");
		total += ErrorCheck('chrOfficePhone', "You must enter a Primary Phone Number.");
		total += ErrorCheck('chrCompanyLabel', "You must enter a Company Name/Label.");
		total += ErrorCheck('chrEmail', "You must enter an Email Address.","email");
		total += ErrorCheck('chrCredentials', "You must enter Presenter/Artist Credentials.");

		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>

<?

	include($BF. 'includes/top_events.php');
?>
	<div class="AdminTopicHeader">Add Presenter</div>
	<div class="AdminInstructions2">Type the name of the product and the type and then click "Save Presenter"</div>
					
	<form id='idForm' method='post' action='' enctype="multipart/form-data">

	<div id='errors'></div>
		<table class='TwoColumns' style='margin: 0;'>
			<tr>
				<td class='Left'>
					<div class='sectionInfo'>
						<div class='sectionHeader'>Personal Information</div>
						<div class='sectionContent'>

						<div class='form'>
							<div class='formHeader'>Name/Artist <span class='Required'>(Required)</span></div>
							<input type='text' size='30' maxlength='80' name='chrName' id='chrName' />
						</div>
						
						<div class='form'>
							<div class='formHeader'>Job Title</div>
							<input type='text' size='30' maxlength='80' name='chrJobTitle' />
						</div>
						
						<div class='form'>
							<div class='formHeader'>Company Name/Label <span class='Required'>(Required)</span></div>
							<input type='text' size='30' maxlength='80' name='chrCompanyLabel' id='chrCompanyLabel' />
						</div>
						
						<div class='form'>
							<div class='formHeader'>Address</div>
							<input type='text' size='30' maxlength='80' name='chrAddress1' /><br />
							<input type='text' size='30' maxlength='80' name='chrAddress2' /><br />
							<input type='text' size='30' maxlength='80' name='chrAddress3' />
						</div>

						<div class='form'>
							<div class='formHeader'>City</div>
							<input type='text' size='30' maxlength='80' name='chrCity' />
						</div>
						
						<div class='form'>
							<div class='formHeader'>State/Province <span class='Required'>(US &amp; Canada)</span></div>
							<select name='chrState'>
								<option></option>
<?	foreach($states as $st => $name) { ?>
								<option value='<?=@$st?>'><?=$name?></option>
<?	} ?>
								</select>
							</div>
					
						<div class='form'>
							<div class='formHeader'>Zip Code</div>
							<input type='text' size='30' maxlength='80' name='chrPostalCode' />
						</div>

						
						<div class='form'>
							<div class='formHeader'>Office Phone <span class='Required'>(Required)</span></div>
							<input type='text' size='14' maxlength='20' name='chrOfficePhone' id='chrOfficePhone' /> <span class='Example'>(ex: 408-555-1212)</span>
						</div>
						
						<div class='form'>
							<div class='formHeader'>Mobile Phone</div>
							<input type='text' size='14' maxlength='20' name='chrMobilePhone' /> <span class='Example'>(ex: 408-555-1212)</span>
						</div>
						
						<div class='form'>
							<div class='formHeader'>Fax Number</div>
							<input type='text' size='14' maxlength='20' name='chrFax' /> <span class='Example'>(ex: 408-555-1212)</span>
						</div>
						
						<div class='form'>
							<div class='formHeader'>Email <span class='Required'>(Required)</span></div>
							<input type='text' size='35' maxlength='80' name='chrEmail' id='chrEmail' />
						</div>
						
						<div class='form'>
							<div class='formHeader'>Website</div>
							<input type='text' size='35' maxlength='80' name='chrWebsite' />
						</div>
						
					</div>
				</div>
				</td>
				<td class='Gutter'></td>
				<td class='Right'>				
					
					<div class='sectionInfo'>
						<div class='sectionHeader'>Qualifying Information</div>
						<div class='sectionContent'>
	
						<div class='form'>
							<input type='hidden' id='idExpertise' name='idExpertise' />
							<input type='hidden' id='chrExpertise' name='chrExpertise' />
							<div class='formHeader'>Expertise <input type='button' value='Add...' onclick='newwin = window.open("select-expertise.php?d=<?=urlencode(base64_encode('functioncall=topic_add'))?>","new","width=450,height=400,resizable=1,scrollbars=1"); newwin.focus();'/></div>
							<table class='list' id='ListExpertise' style='width: 100%;'>
								<tbody>
								</tbody>
							</table>

<script type="text/javascript">//<![CDATA[
function topic_add(id, chr) 
{ 
	list_add('ListExpertise', 'idExpertise', 'chrExpertise', id, chr); 
}
// ]]></script>
							</div>

						<div class='form'>
							<div class='formHeader'>Presenter/Artist Credentials <span class='Required'>(Required)</span></div>
							<textarea cols='40' rows='14' wrap="virtual" name='chrCredentials' id='chrCredentials'></textarea>
							</div>			
					</div>
				</div>


					<div class='sectionInfo'>
						<div class='sectionHeader'>Photo</div>
						<div class='sectionContent'>

						<div class='form'>
							<div class='formHeader'>Upload Photo</div>
							<input name="blobPhoto" type="file" />
							</div>

					</div>
				</div>

				</td>
			</tr>
		</table>		
	
		<div class='FormButtons'>
			<input type='button' value='Save Presenter' onclick='error_check()' />
		</div>

	</form>

<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>