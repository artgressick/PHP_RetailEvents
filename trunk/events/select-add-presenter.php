<?php
	require_once('../_lib.php');

	include(BASE_FOLDER . 'includes/states.php');
	/* Weeknames for the date checks */


	$error_messages = array();

	// if this is a form submission
	if(count($_POST)) {
		// Check the fields for completeness/formatting
		//   If something is wrong, add an entry to the $error_messages array
		
		if($_POST['chrEmail'] == '') {
			$error_messages[] = "You must enter an email address.";
		} else {		
			$result = do_mysql_query("SELECT ID FROM Presenters WHERE chrEmail='" . $_POST['chrEmail'] . "' AND !bDeleted", 'check for email dups');
			if(mysql_num_rows($result)) {
				$error_messages[] = "A presenter already exists with the email address you entered.";
			}
		}
		if($_POST['chrName'] == '') { $error_messages["name"] = "You must enter a Name/Artist."; }
		if($_POST['chrOfficePhone'] == '') { $error_messages["officephone"] = "You must enter a Primary Phone Number.";	}
		if($_POST['chrCompanyLabel'] == '') { $error_messages["companylabel"] = "You must enter a Company Name/Label.";	}
		if($_POST['chrCredentials'] == '') { $error_messages["credentials"] = "You must enter Presenter/Artist Credentials.";}
		
		// if everything is cool, create the new record
		if(count($error_messages) == 0) {
			
			do_mysql_query("START TRANSACTION", 'start transaction');

			$database_error = false;

			$q = "INSERT INTO Presenters SET
				idStore='" . encode($_REQUEST['idStore']) . "',
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
				chrEmail='" . encode($_REQUEST['chrEmail']) . "',
				chrWebsite='" . encode($_REQUEST['chrWebsite']) . "',
				chrCredentials='" . encode($_REQUEST['chrCredentials']) . "'";
			
			if(is_uploaded_file($_FILES['blobPhoto']['tmp_name'])) {
				$data = file_get_contents($_FILES['blobPhoto']['tmp_name']);
				$q .= ",blobPhoto='" . addslashes($data) . "'";
			}

			$database_error += !do_mysql_query($q, 'insert presenter');

			$new_id = mysql_insert_id();

			// save the expertise
			$ids = explode(',', $_POST['idExpertise']);
			// look for additions
			foreach($ids as $expertise_id) {
				$database_error += !do_mysql_query("INSERT INTO PresenterExpertise SET idPresenter='" . $new_id . "', idExpertise='" . $expertise_id . "'
					", 'insert expertise');
			}

			if(!$database_error) {
				do_mysql_query("COMMIT", 'commit');

				header("Location: select-presenter.php?d=" . urlencode($_POST['d']) . "&idSelected=" . $new_id . '&chrSelected=' . $_POST['chrName']);
				die();
			} else {
				do_mysql_query("ROLLBACK", 'rollback');

				$error_messages[] = 'There was a problem creating the new presenter.';
			}
		}

		// if there is an error, copy all of the submitted form data so that the form can fill it in.
		//  this is so that the form will have the (invalid/incomplete) form data that they already filled in
		foreach($_POST as $k => $v) {
			$t = 'f_' . $k;
			$$t = $v;
		}
	} else {

		foreach($_REQUEST as $k => $v) {
			$t = 'f_' . $k;
			$$t = $v;
		}
	}

	$title = "Add Presenter";
	require(BASE_FOLDER . 'docpages/doc_meta.php');
	include(BASE_FOLDER . 'docpages/doc_top_poppup.php');	
?>

	<div style='width: 450px;'>
	
	<table class='Tabs' style='margin-bottom: -3px;'>
		<tr>
			<td class=''><a href='select-presenter.php?idStore=<?=$_REQUEST['idStore']?>&d=<?=urlencode($_REQUEST['d'])?>'>Presenters</a></td>
			<td class='Current'><a href='#'>Create New Presenter</a></td>
			<td class='TheRest'></td>
		</tr>
	</table>
	<div class='sectionInfo' style='width: 375px;'>
		<div class='sectionHeader'>Add Presenter</div>
		<div class='sectionContent' style='background: white;'>

		<div class='WithinTabs'>
			<form id='Form' method='post' action='' enctype="multipart/form-data">

				<div class='Messages'>
<? if(count($error_messages)) {
		foreach($error_messages as $error) { ?>
				<p class='ErrorMessage'><?=$error?></p>
<?		}
	} ?>
				</div>

					<div class='sectionInfo' style='width: 300px;'>
						<div class='sectionHeader'>Personal Information</div>
						<div class='sectionContent'>
	
						<div class='Field'>
							<div class='L10'>Name/Artist <span class='Required'>(Required)</span></div>
							<input type='text' size='30' maxlength='80' name='chrName' value='<?=@$f_chrName?>' />
							</div>
						
						<div class='Field'>
							<div class='L10'>Job Title</div>
							<input type='text' size='30' maxlength='80' name='chrJobTitle' value='<?=@$f_chrJobTitle?>' />
							</div>
						
						<div class='Field'>
							<div class='L10'>Company Name/Label <span class='Required'>(Required)</span></div>
							<input type='text' size='30' maxlength='80' name='chrCompanyLabel' value='<?=@$f_chrCompanyLabel?>' />
							</div>
						
						<div class='Field'>
							<div class='L10'>Address</div>
							<input type='text' size='30' maxlength='80' name='chrAddress1' value='<?=@$f_chrAddess1?>' /><br />
							<input type='text' size='30' maxlength='80' name='chrAddress2' value='<?=@$f_chrAddess2?>' /><br />
							<input type='text' size='30' maxlength='80' name='chrAddress3' value='<?=@$f_chrAddess3?>' />
							</div>

						<div class='Field'>
							<div class='L10'>City</div>
							<input type='text' size='30' maxlength='80' name='chrCity' value='<?=@$f_chrCity?>' />
							</div>
						
						<div class='Field'>
							<div class='L10'>State/Province <span class='Required'>(US &amp; Canada)</span></div>
							<select name='chrState'>
								<option></option>
<?	foreach($states as $st => $name) { ?>
								<option value='<?=@$st?>'<?=(@$f_chrState==$st?' selected="selected"':'')?>><?=$name?></option>
<?	} ?>
								</select>
							</div>
					
						<div class='Field'>
							<div class='L10'>Zip Code</div>
							<input type='text' size='30' maxlength='80' name='chrPostalCode' value='<?=@$f_chrPostalCode?>' />
							</div>

						<div class='Field'>
							<div class='L10'>Office Phone <span class='Required'>(Required)</span></div>
							<input type='text' size='14' maxlength='20' name='chrOfficePhone' value='<?=@$f_chrOfficePhone?>' /> <span class='Example'>(ex: 408-555-1212)</span>
							</div>
						
						<div class='Field'>
							<div class='L10'>Mobile Phone</div>
							<input type='text' size='14' maxlength='20' name='chrMobilePhone' value='<?=@$f_chrMobilePhone?>' /> <span class='Example'>(ex: 408-555-1212)</span>
							</div>
						
						<div class='Field'>
							<div class='L10'>Fax Number</div>
							<input type='text' size='14' maxlength='20' name='chrFax' value='<?=@$f_chrFax?>' /> <span class='Example'>(ex: 408-555-1212)</span>
							</div>
						
						<div class='Field'>
							<div class='L10'>Email <span class='Required'>(Required)</span></div>
							<input type='text' size='35' maxlength='80' name='chrEmail' value='<?=@$f_chrEmail?>' />
							</div>
						
						<div class='Field'>
							<div class='L10'>Website</div>
							<input type='text' size='35' maxlength='80' name='chrWebsite' value='<?=@$f_chrWebsite?>' />
							</div>
					</div>
				</div>

					<div class='sectionInfo' style='width: 350px;'>
						<div class='sectionHeader'>Qualifying Information</div>
						<div class='sectionContent'>	
						
									
						<div class='Field'>
							<input type='hidden' id='idExpertise' name='idExpertise' value='<?=$f_idExpertise?>' />
							<input type='hidden' id='chrExpertise' name='chrExpertise' value='<?=$f_chrExpertise?>' />
							<div class='L10'>Expertise <input type='button' value='Add...' onclick='newwin = window.open("<?=BASE_FOLDER?>events/select-expertise.php?d=<?=urlencode(base64_encode('functioncall=topic_add'))?>","presenterexpertise","width=600,height=400,resizable=1,scrollbars=1"); newwin.focus();'/></div>
							<table class='list' id='ListExpertise' style='width: 100%;'>
								<tbody>
<?			if($f_idExpertise != '') { ?>
<?
				$ids = explode(',', $f_idExpertise);
				$chrs = explode(',', $f_chrExpertise);
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

					<div class='Field'>
						<div class='L10'>Presenter/Artist Credentials <span class='Required'>(Required)</span></div>
						<textarea cols='40' rows='8' wrap="virtual" name='chrCredentials'><?=@$f_chrCredentials?></textarea>
						</div>			
				</div>
			</div>

			<div class='sectionInfo'  style='width: 300px;'>
				<div class='sectionHeader'>Photo</div>
				<div class='sectionContent'>
	
					<div class='Field'>
						<div class='L10'>Upload Photo</div>
						<input name="blobPhoto" type="file" />
						</div>

					</fieldset>
					
				</div>
			</div>
			<div class='FormButtons'>
				<input type='hidden' name='d' value='<?=$_REQUEST['d']?>' />
				<input type='hidden' name='idStore' value='<?=$_REQUEST['idStore']?>' />
				<input type='submit' name='SubmitAddPresenter' value='Save New Presenter' />
				</div>


				</form>
			</div>
		</div>
	</div>

