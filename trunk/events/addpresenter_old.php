<?php
	require_once('../_lib.php');

	/* Includes to get the countries and states */
	include(BASE_FOLDER . 'includes/states.php');
	include(BASE_FOLDER . 'includes/countries.php');

	$error_messages = array();

/*	if($_SESSION['idType']) {		
		header('Location: ' . BASE_FOLDER . 'index.php');
		die();
	}
*/
	// if this is a form submission

	if(count($_POST)) {
		// Check the fields for completeness/formatting
		//   If something is wrong, add an entry to the $error_messages array

		if($_POST['chrName'] == '') {
			$error_messages["name"] = "You must enter a Name/Artist.";
		}
		
		if($_POST['chrOfficePhone'] == '') {
			$error_messages["officephone"] = "You must enter a Primary Phone Number.";
		}
		
		if($_POST['chrCompanyLabel'] == '') {
			$error_messages["companylabel"] = "You must enter a Company Name/Label.";
		}
		
		if($_POST['chrCredentials'] == '') {
			$error_messages["credentials"] = "You must enter a Presenter/Artist Credentials.";
		}

		// if everything is cool, create the new record
		if(count($error_messages) == 0) {
			
			$store = mysql_fetch_assoc(do_mysql_query("SELECT idItem FROM ACL WHERE idUser='" . $_SESSION['idUser'] . "'",'getting store id'));
			
			do_mysql_query("START TRANSACTION", 'start transaction');

			$database_error = false;

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

				$_SESSION['InfoMessage'][] = 'New presenter <span class="Specific">' . encode($_POST['chrName']) . '</span> has been saved.';
				
				header("Location: presenters.php");
				die();
			} else {
				do_mysql_query("ROLLBACK", 'rollback');

				$error_messages[] = 'There was a problem creating the new presenter.';
			}
			// if there is an error, copy all of the submitted form data so that the form can fill it in.
			$info = $_POST;
		}		
	}
	
	if(!isset($info)) { $info = 0; } 
	
	
// Set the title, and add the doc_top
$title = "Add Presenter";
require(BASE_FOLDER . 'docpages/doc_meta_events.php');
include(BASE_FOLDER . 'docpages/doc_top_events.php');
	
?>


				
<div style='margin: 10px;'>
<div class="AdminTopicHeader">Add Presenter</div>
	<div class="AdminDirections" style='width: 865px;'>Add info here.</div>
	

<? if(count($error_messages)) { ?>
		<div class="Messages">
<?	 	if(count($error_messages)) {
			foreach($error_messages as $error) { ?>
				<p class='ErrorMessage'><?=$error?></p>
<?			}
		} ?>
	</div>
<?	} ?>
	
	</div>

	<div class='sectionInfo'>
		<div class='noHeader' style='padding: 0 10px;'>

	<form id='Form' method='post' action='' enctype="multipart/form-data">

		<table class='TwoColumns' style='margin: 0;'>
			<tr>
				<td class='Left'>
					<div class='sectionInfo'>
						<div class='sectionHeader'>Personal Information</div>
						<div class='sectionContent'>

						<div class='form'>
							<div class='formHeader'>Name/Artist <span class='Required'>(Required)</span></div>
							<input type='text' size='30' maxlength='80' name='chrName' value='<?=@$info['chrName']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>Job Title</div>
							<input type='text' size='30' maxlength='80' name='chrJobTitle' value='<?=@$info['chrJobTitle']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>Company Name/Label <span class='Required'>(Required)</span></div>
							<input type='text' size='30' maxlength='80' name='chrCompanyLabel' value='<?=@$info['chrCompanyLabel']?>' />
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
							<input type='text' size='14' maxlength='20' name='chrOfficePhone' value='<?=@$info['chrOfficePhone']?>' /> <span class='Example'>(ex: 408-555-1212)</span>
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
							<input type='text' size='35' maxlength='80' name='chrEmail' value='<?=@$info['chrEmail']?>' />
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
							<div class='formHeader'>Expertise <input type='button' value='Add...' onclick='newwin = window.open("select-expertise.php?d=<?=urlencode(base64_encode('functioncall=topic_add'))?>","new","width=600,height=400,resizable=1,scrollbars=1"); newwin.focus();'/></div>
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
							<textarea cols='40' rows='14' wrap="virtual" name='chrCredentials'><?=@$info['chrCredentials']?></textarea>
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
			<input type='submit' name='SubmitAddPresenter' value='Save New Presenter' />
		</div>

	</form>

</div>
<?
	include(BASE_FOLDER . 'docpages/doc_bottom.php');
?>
