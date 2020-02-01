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
	while($row = mysql_fetch_assoc($exp_result)) {
		$presenter['Expertise'][$row['ID']] = $row;
	}
	
	// copy expertise to the form
	$presenter['idExpertise'] = implode(',', array_keys($presenter['Expertise']));
	$t_names = array();
	foreach($presenter['Expertise'] as $ass) {
		$t_names[] = str_replace(',', '&#44;', $ass['chrName']);
	}
	$presenter['chrExpertise'] = implode(',', $t_names);


	if(!isset($presenter)) { $presenter = 0; } 
	


// Set the title, and add the doc_top
$title = "Edit Presenter";
require(BASE_FOLDER . 'docpages/doc_meta_events.php');
include(BASE_FOLDER . 'docpages/doc_top_events.php');
	
?>

<div style='margin: 10px;'>


	<div class="AdminTopicHeader">View Presenter</div>
	
	<div class='sectionInfo'>
		<div class='noHeader'>

		<table class='TwoColumns' style='margin: 0;'>
			<tr>
				<td class='Left'>
					<div class='sectionInfo'>
						<div class='sectionHeader'>Personal Information</div>
						<div class='sectionContent'>

						<div class='form'>
							<div class='formHeader'>Name/Artist</div>
							<div class='formDisplay'><?=@$presenter['chrName']?></div>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Job Title</div>
							<div class='formDisplay'><?=@$presenter['chrJobTitle']?></div>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Company Name/Label</div>
							<div class='formDisplay'><?=@$presenter['chrCompanyLabel']?></div>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Address</div>
								<div class='formDisplay'><?=@$presenter['chrAddress1']?>
								<?=($presenter['chrAddress2'] != '' ? "<br />".$presenter['chrAddress2'] : "")?>
								<?=($presenter['chrAddress3'] != '' ? "<br />".$presenter['chrAddress3'] : "")?></div>
							</div>

						<div class='form'>
							<div class='formHeader'>City</div>
								<div class='formDisplay'><?=@$presenter['chrCity']?></div>
							</div>
						
						<div class='form'>
							<div class='formHeader'>State/Province</div>
								<div class='formDisplay'><?=$presenter['chrState']?></div>
								</select>
							</div>
					
						<div class='form'>
							<div class='formHeader'>Zip Code</div>
								<div class='formDisplay'><?=@$presenter['chrPostalCode']?></div>
							</div>

						
						<div class='form'>
							<div class='formHeader'>Office Phone</div>
								<div class='formDisplay'><?=@$presenter['chrOfficePhone']?></div>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Mobile Phone</div>
								<div class='formDisplay'><?=@$presenter['chrMobilePhone']?></div>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Fax Number</div>
								<div class='formDisplay'><?=@$presenter['chrFax']?></div>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Email</div>
								<div class='formDisplay'><?=@$presenter['chrEmail']?></div>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Website</div>
								<div class='formDisplay'><?=@$presenter['chrWebsite']?></div>
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
						<div class='formHeader'>Expertise</div>
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
										<td style='width: 1%;' class='alignright'></td>
										</tr>
<?				} ?>
<?			} ?>
									</tbody>
								</table>
	</div>

						<div class='form'>
							<div class='formHeader'>Presenter/Artist Credentials</div>
							<textarea disabled style='border: 0; background-color: white; color: black' cols='40' rows='14' wrap="virtual" name='chrCredentials'><?=@$presenter['chrCredentials']?></textarea>
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

					</div>
				</div>

				</td>
			</tr>
		</table>		

		</form>

<?
	include(BASE_FOLDER . 'docpages/doc_bottom.php');
?>
