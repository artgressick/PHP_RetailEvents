<?php
	$BF = '../';
	$title = 'Edit Presenter';
	$curPage='presenters';
	require($BF. '_lib2.php');

	$info = fetch_database_query("SELECT * FROM Presenters WHERE ID='" . $_REQUEST['id'] . "'","getting presenters");
	$results = database_query("SELECT Expertise.*
			FROM PresenterExpertise
			JOIN Expertise ON PresenterExpertise.idExpertise=Expertise.ID
			WHERE PresenterExpertise.idPresenter='" . $_REQUEST['id'] . "'
		", 'get expertise');
	$info['Expertise'] = array();
	while($row = mysqli_fetch_assoc($results)) {
		$info['Expertise'][$row['ID']] = $row;
	}

	// if this is a form submission
	if(isset($_POST['chrName'])) {

		$table = 'Presenters';
		$mysqlStr = '';
		$audit = '';

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrName',$info['chrName'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrJobTitle',$info['chrJobTitle'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCompanyLabel',$info['chrCompanyLabel'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress1',$info['chrAddress1'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress2',$info['chrAddress2'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress3',$info['chrAddress3'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCity',$info['chrCity'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrState',$info['chrState'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrPostalCode',$info['chrPostalCode'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrOfficePhone',$info['chrOfficePhone'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrMobilePhone',$info['chrMobilePhone'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrFax',$info['chrFax'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrEmail',$info['chrEmail'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrWebsite',$info['chrWebsite'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCredentials',$info['chrCredentials'],$audit,$table,$_POST['id']);
		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }
		echo $str;

		if(is_uploaded_file($_FILES['blobPhoto']['tmp_name'])) {
			$data = file_get_contents($_FILES['blobPhoto']['tmp_name']);
			database_query("UPDATE Presenters SET blobPhoto='". addslashes($data) ."' WHERE ID=".$_POST['id'], "update photo");
		}

		database_query("DELETE FROM PresenterExpertise WHERE idPresenter='" . $_POST['id'] . "'","delete old expertise");
		// update the expertises
		if($_POST['idExpertise'] != '') {
			$ids = explode(',', $_POST['idExpertise']);
			// look for additions
			foreach($ids as $expertise_id) {
				if(!isset($presenter['Expertise'][$expertise_id])) {
					database_query("INSERT INTO PresenterExpertise SET idPresenter='" . $_POST['id'] . "', idExpertise='" . $expertise_id . "'", 'insert expertise');
				}
			}
		}
		
		$_SESSION['InfoMessage'][] = 'Presenter <span class="Specific">' . encode($_POST['chrName']) . '</span> has been updated.';
		header("Location: presenters.php");
		die();
		
	} else {
	
		// copy expertise to the form
		$info['idExpertise'] = implode(',', array_keys($info['Expertise']));
		$t_names = array();
		foreach($info['Expertise'] as $ass) {
			$t_names[] = str_replace(',', '&#44;', $ass['chrName']);
		}
		$info['chrExpertise'] = implode(',', $t_names);
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
	<div class="AdminTopicHeader">Edit Presenter</div>
	<div class="AdminInstructions2">Type the name of the product and the type and then click "Update Information"</div>
					
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
							<input type='text' size='30' maxlength='80' name='chrName' id='chrName' value='<?=@$info['chrName']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>Job Title</div>
							<input type='text' size='30' maxlength='80' name='chrJobTitle' value='<?=@$info['chrJobTitle']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>Company Name/Label <span class='Required'>(Required)</span></div>
							<input type='text' size='30' maxlength='80' name='chrCompanyLabel' id='chrCompanyLabel' value='<?=@$info['chrCompanyLabel']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>Address</div>
							<input type='text' size='30' maxlength='80' name='chrAddress1' value='<?=@$info['chrAddress1']?>' /><br />
							<input type='text' size='30' maxlength='80' name='chrAddress2' value='<?=@$info['chrAddress2']?>' /><br />
							<input type='text' size='30' maxlength='80' name='chrAddress3' value='<?=@$info['chrAddress3']?>' />
							</div>

						<div class='form'>
							<div class='formHeader'>City</div>
							<input type='text' size='30' maxlength='80' name='chrCity' value='<?=@$info['chrCity']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>State/Province <span class='Required'>(US &amp; Canada)</span></div>
							<select name='chrState'>
								<option></option>
<?	foreach($states as $st => $name) { ?>
								<option value='<?=@$st?>'<?=(@$info['chrState']==$st?' selected="selected"':'')?>><?=$name?></option>
<?	} ?>
								</select>
							</div>
					
						<div class='form'>
							<div class='formHeader'>Zip Code</div>
							<input type='text' size='30' maxlength='80' name='chrPostalCode' value='<?=@$info['chrPostalCode']?>' />
							</div>

						
						<div class='form'>
							<div class='formHeader'>Office Phone <span class='Required'>(Required)</span></div>
							<input type='text' size='14' maxlength='20' name='chrOfficePhone' id='chrOfficePhone' value='<?=@$info['chrOfficePhone']?>' /> <span class='Example'>(ex: 408-555-1212)</span>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Mobile Phone</div>
							<input type='text' size='14' maxlength='20' name='chrMobilePhone' value='<?=@$info['chrMobilePhone']?>' /> <span class='Example'>(ex: 408-555-1212)</span>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Fax Number</div>
							<input type='text' size='14' maxlength='20' name='chrFax' value='<?=@$info['chrFax']?>' /> <span class='Example'>(ex: 408-555-1212)</span>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Email <span class='Required'>(Required)</span></div>
							<input type='text' size='35' maxlength='80' name='chrEmail' id='chrEmail' value='<?=@$info['chrEmail']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>Website</div>
							<input type='text' size='35' maxlength='80' name='chrWebsite' value='<?=@$info['chrWebsite']?>' />
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
							<input type='hidden' id='idExpertise' name='idExpertise' value='<?=$info['idExpertise']?>' />
							<input type='hidden' id='chrExpertise' name='chrExpertise' value='<?=$info['chrExpertise']?>' />
							<div class='formHeader'>Expertise <input type='button' value='Add...' onclick='newwin = window.open("select-expertise.php?d=<?=urlencode(base64_encode('functioncall=topic_add'))?>","new","width=450,height=400,resizable=1,scrollbars=1"); newwin.focus();'/></div>
							<table class='list' id='ListExpertise' style='width: 100%;'>
								<tbody>
<?			if($info['idExpertise'] != '') { ?>
<?
				$ids = explode(',', $info['idExpertise']);
				$chrs = explode(',', $info['chrExpertise']);
				$count = 0;
				foreach($ids as $topic_id) { 
					list($key, $chr) = each($chrs);
?>
									<tr class='<?=(++$count%2?'odd':'even')?>'>
										<td><?=$chr?></td>
										<td style='width: 1%;' class='alignright'><input type='button' value='Remove' onclick="list_remove('ListExpertise', 'idExpertise', 'chrExpertise', <?=$topic_id?>, this); " /></td>
										</tr>
<?				} ?>
<?			} ?>
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
							<textarea cols='40' rows='14' wrap="virtual" name='chrCredentials' id='chrCredentials'><?=@$info['chrCredentials']?></textarea>
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
			<input type='button' value='Update Information' onclick='error_check()' />
			<input type='hidden' name='id' value='<?=$info["ID"]?>' />
		</div>

	</form>

<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>