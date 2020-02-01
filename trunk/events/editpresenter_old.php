<?php
	require_once('../_lib.php');

	/* Includes to get the countries and states */
	include(BASE_FOLDER . 'includes/states.php');
	include(BASE_FOLDER . 'includes/countries.php');

	$presenter = mysql_fetch_assoc(do_mysql_query("SELECT * FROM Presenters WHERE ID='" . $_REQUEST['id'] . "'",""));
	
	$exp_result = do_mysql_query("
		SELECT Expertise.*
		FROM PresenterExpertise
		JOIN Expertise ON PresenterExpertise.idExpertise=Expertise.ID
		WHERE PresenterExpertise.idPresenter='" . $_REQUEST['id'] . "'
		", 'get expertise');
	$presenter['Expertise'] = array();
	while($row = mysql_fetch_assoc($exp_result)) {
		$presenter['Expertise'][$row['ID']] = $row;
	}

	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root */
	if($_SESSION['idType'] != 1 && !in_array($presenter['idStore'],$_SESSION['intStoreList'])) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: ' . BASE_FOLDER . "nopermission.php"); die(); }

	$error_messages = array();
/*
	if(!$_SESSION['idType']) {		
		header('Location: ' . BASE_FOLDER . 'index.php');
		die();
	}
*/

	// if this is a form submission
	if(count($_POST)) {
		// Check the fields for completeness/formatting
		//   If something is wrong, add an entry to the $error_messages array

		if($_POST['chrEmail'] == '') {
			$error_messages["email"] = "You must enter an email address.";
		} else {
			$conflicts = do_mysql_query("SELECT * FROM Presenters WHERE chrEmail='" . $_POST['chrEmail'] . "' AND ID!='" . $_POST['id'] . "' AND !bDeleted", 'check for conflicts');
			if(mysql_num_rows($conflicts)) {
				$error_messages["email"] = "A presenter already exists with the email address you entered..";
			}
		}

		if($_POST['chrName'] == '') {
			$error_messages["name"] = "You must enter a Name/Artist.";
		}
		
		if($_POST['chrOfficePhone'] == '') {
			$error_messages["chrOfficePhone"] = "You must enter a Primary Phone Number.";
		}
		
		if($_POST['chrCompanyLabel'] == '') {
			$error_messages["chrCompanyLabel"] = "You must enter a Company Name/Label.";
		}
		
		if($_POST['chrCredentials'] == '') {
			$error_messages["chrCompanyLabel"] = "You must enter Presenter/Artist Credentials.";
		}

		// if everything is cool, create the new record
		if(count($error_messages) == 0) {
			
			//do_mysql_query("START TRANSACTION", 'start transaction');

			$database_error = false;

			$database_error += !update_record('Presenters', 'chrName', $_POST['id'], encode($_POST['chrName']));
			$database_error += !update_record('Presenters', 'chrJobTitle', $_POST['id'], encode($_POST['chrJobTitle']));
			$database_error += !update_record('Presenters', 'chrCompanyLabel', $_POST['id'], encode($_POST['chrCompanyLabel']));
			$database_error += !update_record('Presenters', 'chrAddress1', $_POST['id'], encode($_POST['chrAddress1']));
			$database_error += !update_record('Presenters', 'chrAddress2', $_POST['id'], encode($_POST['chrAddress2']));
			$database_error += !update_record('Presenters', 'chrAddress3', $_POST['id'], encode($_POST['chrAddress3']));
			$database_error += !update_record('Presenters', 'chrCity', $_POST['id'], encode($_POST['chrCity']));
			$database_error += !update_record('Presenters', 'chrState', $_POST['id'], $_POST['chrState']);
			$database_error += !update_record('Presenters', 'chrPostalCode', $_POST['id'], $_POST['chrPostalCode']);
			$database_error += !update_record('Presenters', 'chrOfficePhone', $_POST['id'], $_POST['chrOfficePhone']);
			$database_error += !update_record('Presenters', 'chrMobilePhone', $_POST['id'], $_POST['chrMobilePhone']);
			$database_error += !update_record('Presenters', 'chrFax', $_POST['id'], $_POST['chrFax']);
			$database_error += !update_record('Presenters', 'chrEmail', $_POST['id'], $_POST['chrEmail']);
			$database_error += !update_record('Presenters', 'chrWebsite', $_POST['id'], $_POST['chrWebsite']);
			$database_error += !update_record('Presenters', 'chrCredentials', $_POST['id'], encode($_POST['chrCredentials']));

			if(is_uploaded_file($_FILES['blobPhoto']['tmp_name'])) {
				$data = file_get_contents($_FILES['blobPhoto']['tmp_name']);
				$database_error += !update_record('Presenters', 'blobPhoto', $_POST['id'], $data);
			}

			// update the expertises
			if($_POST['idExpertise'] != '') {
				$ids = explode(',', $_POST['idExpertise']);
				// look for additions
				foreach($ids as $expertise_id) {
					if(!isset($presenter['Expertise'][$expertise_id])) {
						$database_error += !do_mysql_query("INSERT INTO PresenterExpertise SET idPresenter='" . $_POST['id'] . "', idExpertise='" . $expertise_id . "'
							", 'insert expertise');
					}
				}
			} else {
				$ids = array();
			}
			// look for removals
			foreach($presenter['Expertise'] as $expertise_id => $expertise) {
				if(!in_array($expertise_id, $ids)) {
					$database_error += !do_mysql_query("DELETE FROM PresenterExpertise WHERE idPresenter='" . $_POST['id'] . "' AND idExpertise='" . $expertise_id . "'
						", 'delete expertise');
				}
			}

			if(!$database_error) {
				//do_mysql_query("COMMIT", 'commit');

				$_SESSION['InfoMessage'][] = 'Changes to the presenter <span class="Specific">' . $_POST['chrName'] . '</span> have been saved.';

				header("Location: presenters.php");
				die();
			} else {
				do_mysql_query("ROLLBACK", 'rollback');

				$error_messages[] = "There was a database error.";
			}
		}

			
		// if there is an error, copy all of the submitted form data so that the form can fill it in.
		$presenter = $_POST;
	} else {
	
		// copy expertise to the form
		$presenter['idExpertise'] = implode(',', array_keys($presenter['Expertise']));
		$t_names = array();
		foreach($presenter['Expertise'] as $ass) {
			$t_names[] = str_replace(',', '&#44;', $ass['chrName']);
		}
		$presenter['chrExpertise'] = implode(',', $t_names);
	}

	if(!isset($presenter)) { $presenter = 0; } 
	


// Set the title, and add the doc_top
$title = "Edit Presenter";
require(BASE_FOLDER . 'docpages/doc_meta_events.php');
include(BASE_FOLDER . 'docpages/doc_top_events.php');
	
?>

<div style='margin: 10px;'>


	<div class="AdminTopicHeader">Edit Presenter</div>
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
	
	<form id='Form' method='post' action='' enctype="multipart/form-data">
	
	<div class='sectionInfo'>
		<div class='noHeader'>

		<table class='TwoColumns' style='margin: 0;'>
			<tr>
				<td class='Left'>
					<div class='sectionInfo'>
						<div class='sectionHeader'>Personal Information</div>
						<div class='sectionContent'>

						<div class='form'>
							<div class='formHeader'>Name/Artist <span class='Required'>(Required)</span></div>
							<input type='text' size='30' maxlength='80' name='chrName' value='<?=@$presenter['chrName']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>Job Title</div>
							<input type='text' size='30' maxlength='80' name='chrJobTitle' value='<?=@$presenter['chrJobTitle']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>Company Name/Label <span class='Required'>(Required)</span></div>
							<input type='text' size='30' maxlength='80' name='chrCompanyLabel' value='<?=@$presenter['chrCompanyLabel']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>Address</div>
							<input type='text' size='30' maxlength='80' name='chrAddress1' value='<?=@$presenter['chrAddress1']?>' /><br />
							<input type='text' size='30' maxlength='80' name='chrAddress2' value='<?=@$presenter['chrAddress2']?>' /><br />
							<input type='text' size='30' maxlength='80' name='chrAddress3' value='<?=@$presenter['chrAddress3']?>' />
							</div>

						<div class='form'>
							<div class='formHeader'>City</div>
							<input type='text' size='30' maxlength='80' name='chrCity' value='<?=@$presenter['chrCity']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>State/Province <span class='Required'>(US &amp; Canada)</span></div>
							<select name='chrState'>
								<option></option>
<?	foreach($states as $st => $name) { ?>
								<option value='<?=@$st?>'<?=(@$presenter['chrState']==$st?' selected="selected"':'')?>><?=$name?></option>
<?	} ?>
								</select>
							</div>
					
						<div class='form'>
							<div class='formHeader'>Zip Code</div>
							<input type='text' size='30' maxlength='80' name='chrPostalCode' value='<?=@$presenter['chrPostalCode']?>' />
							</div>

						
						<div class='form'>
							<div class='formHeader'>Office Phone <span class='Required'>(Required)</span></div>
							<input type='text' size='14' maxlength='20' name='chrOfficePhone' value='<?=@$presenter['chrOfficePhone']?>' /> <span class='Example'>(ex: 408-555-1212)</span>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Mobile Phone</div>
							<input type='text' size='14' maxlength='20' name='chrMobilePhone' value='<?=@$presenter['chrMobilePhone']?>' /> <span class='Example'>(ex: 408-555-1212)</span>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Fax Number</div>
							<input type='text' size='14' maxlength='20' name='chrFax' value='<?=@$presenter['chrFax']?>' /> <span class='Example'>(ex: 408-555-1212)</span>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Email <span class='Required'>(Required)</span></div>
							<input type='text' size='35' maxlength='80' name='chrEmail' value='<?=@$presenter['chrEmail']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>Website</div>
							<input type='text' size='35' maxlength='80' name='chrWebsite' value='<?=@$presenter['chrWebsite']?>' />
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
							<input type='hidden' id='idExpertise' name='idExpertise' value='<?=$presenter['idExpertise']?>' />
							<input type='hidden' id='chrExpertise' name='chrExpertise' value='<?=$presenter['chrExpertise']?>' />
							<div class='formHeader'>Expertise <input type='button' value='Add...' onclick='newwin = window.open("select-expertise.php?d=<?=urlencode(base64_encode('functioncall=topic_add'))?>","new","width=600,height=400,resizable=1,scrollbars=1"); newwin.focus();'/></div>
							<table class='list' id='ListExpertise' style='width: 100%;'>
								<tbody>
<?			if($presenter['idExpertise'] != '') { ?>
<?
				$ids = explode(',', $presenter['idExpertise']);
				$chrs = explode(',', $presenter['chrExpertise']);
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
							<textarea cols='40' rows='14' wrap="virtual" name='chrCredentials'><?=@$presenter['chrCredentials']?></textarea>
							</div>			
					</div>
				</div>


					<div class='sectionInfo'>
						<div class='sectionHeader'>Photo</div>
						<div class='sectionContent'>
<?	if($presenter['blobPhoto'] != '') { ?>
						<div class='Field'>
							<a href='<?=BASE_FOLDER?>events/magic8ball.php?size=full&amp;id=<?=$_REQUEST['id']?>'><img class='Photo' src='<?=BASE_FOLDER?>events/magic8ball.php?size=thumb&amp;id=<?=$_REQUEST['id']?>' alt='Photo of Presenter' /></a>
							</div>
<?	} ?>
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
			<input type='submit' name='SubmitAddPresenter' value='Update Information' />
			<input type='hidden' name='id' value='<?=$_REQUEST['id']?>' />
		</div>
		
		</form>

<?
	include(BASE_FOLDER . 'docpages/doc_bottom.php');
?>
