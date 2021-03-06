<?php
	$BF = "../";
	require($BF. "_lib2.php");
	$curPage = "recaps";
	$title = 'Edit Recap';
	include($BF. 'includes/meta2.php');
	
	$error_messages = array();

	$chk = database_query("SELECT * FROM Recaps WHERE idEvent=". $_REQUEST['id'],"Getting recap info");
	if(mysqli_num_rows($chk) > 0) { 
		$update = 1;
		$info = mysqli_fetch_assoc($chk);
		$info['chkApple'] = explode(',',$info['chrApple']);
		$info['chkPresenters'] = explode(',',$info['chrPresenters']);
	} else {
		$info = 0;
		$update = 0;
	}

	if(isset($_POST['submitRecap'])) {
	
		if($_POST['chrAttendance'] == '') { $error_messages[] = "You must choose the Attendance Estimate"; }
		if($_POST['chrSales'] == '') { $error_messages[] = "You must choose the Sales Estimate"; }	
		if($_POST['rSuccess'] == '') { $error_messages[] = "You must rate the Success of the Workshop/Event"; }
		if($_POST['rEnjoy'] == '') { $error_messages[] = "You must rate the Enjoyment of the Customers"; }
		if($_POST['chrRehost'] == '') { $error_messages[] = "You must explain if you would Re-Host this event."; }
		if($_POST['chrAddstaff'] == '') { $error_messages[] = "You must explain if this event needed additional staffing"; }
		if($_POST['chkApple'] == '') { $error_messages[] = "You must choose at least 1 option explaining how Apple helped promote the event"; }
		if($_POST['chkPresenters'] == '') { $error_messages[] = "You must choose at least 1 option explaining how the Presenter helped promote the event"; }
		if($_POST['txtFeedback'] == '') { $error_messages[] = "You must supply an Workshop/Event Recap"; }
		if($_POST['chrFirstName'] == '') { $error_messages[] = "You must enter a First Name"; }
		if($_POST['chrLastName'] == '') { $error_messages[] = "You must enter a Last Name"; }
		if($_POST['chrEmail'] == '') { $error_messages[] = "You must enter an Email Address"; }				
					
		if(count($error_messages) == 0)  {
		
			$q = "INSERT INTO Recaps SET 
				idEvent=". $_POST['id'] .",
				rEnjoy=". $_POST['rEnjoy'] .",
				rSuccess=". $_POST['rSuccess'] .",
				chrRehost='". encode($_POST['chrRehost']) ."',
				chrAddstaff='". encode($_POST['chrAddstaff']) ."',
				chrStatus='Complete',
				chrLastName='". encode($_POST['chrLastName']) ."',
				chrFirstName='". encode($_POST['chrFirstName']) ."',
				chrEmail='". encode($_POST['chrEmail']) ."',
				chrAttendance='". encode($_POST['chrAttendance']) ."',
				chrSales='". encode($_POST['chrSales']) ."',
				chrApple='". implode(',',$_POST['chkApple']) ."',
				chrPresenters='". implode(',',$_POST['chkPresenters']) ."',
				chrOtherExplain='". encode($_POST['chrOtherExplain']) ."',
				txtFeedback='". encode($_POST['txtFeedback']) . "',
				txtSpecialThanks='". encode($_POST['txtSpecialThanks']) . "',
				txtCustQuote='". encode($_POST['txtCustQuote']) . "',
				txtIssues='". encode($_POST['txtIssues']) . "',
				txtImproveEvent='". encode($_POST['txtImproveEvent']) . "',						
				txtSpecQuote='". encode($_POST['txtSpecQuote']) . "'			
				";
			database_query($q,"Create Recap");
				
			if(is_uploaded_file($_FILES['chrPhoto']['tmp_name'])) {
				database_query("INSERT INTO RecapImages SET 
					idEvent=". $_POST['id'] .",
					intSize=". $_FILES['chrPhoto']['size'] .",
					chrName='-',
					chrType='". $_FILES['chrPhoto']['type'] ."'
					","insert image");
					
					global $mysqli_connection;
					$newID = mysqli_insert_id($mysqli_connection);
					database_query("UPDATE RecapImages SET chrName='". $newID ."-". basename($_FILES['chrPhoto']['name']) ."' WHERE ID=".$newID,"Updating name"); 
		
				$uploaddir = $BF . 'recapimages/';
				$uploadfile = $uploaddir . $newID .'-'. basename($_FILES['chrPhoto']['name']);
	
				move_uploaded_file($_FILES['chrPhoto']['tmp_name'], $uploadfile);
			}
			header("Location: recaps.php?idStore=". $_POST['idStore'] ."&intDate=". $_REQUEST['intDate']);
			die();
		} else {
			$info = $_POST;
		}
	}
	
	
	if(isset($_POST['updateRecap'])) {
		
		if($_POST['chrAttendance'] == '') { $error_messages[] = "You must choose the Attendance Estimate"; }
		if($_POST['chrSales'] == '') { $error_messages[] = "You must choose the Sales Estimate"; }	
		if($_POST['rSuccess'] == '') { $error_messages[] = "You must rate the Success of the Workshop/Event"; }
		if($_POST['rEnjoy'] == '') { $error_messages[] = "You must rate the Enjoyment of the Customers"; }
		if($_POST['chrRehost'] == '') { $error_messages[] = "You must explain if you would Re-Host this event."; }
		if($_POST['chrAddstaff'] == '') { $error_messages[] = "You must explain if this event needed additional staffing"; }
		if($_POST['chkApple'] == '') { $error_messages[] = "You must choose at least 1 option explaining how Apple helped promote the event"; }
		if($_POST['chkPresenters'] == '') { $error_messages[] = "You must choose at least 1 option explaining how the Presenter helped promote the event"; }
		if($_POST['txtFeedback'] == '') { $error_messages[] = "You must supply an Workshop/Event Recap"; }
		if($_POST['chrFirstName'] == '') { $error_messages[] = "You must enter a First Name"; }
		if($_POST['chrLastName'] == '') { $error_messages[] = "You must enter a Last Name"; }
		if($_POST['chrEmail'] == '') { $error_messages[] = "You must enter an Email Address"; }				
					
		if(count($error_messages) == 0)  {
	
			$q = "UPDATE Recaps SET 
			rEnjoy=". $_POST['rEnjoy'] .",
			rSuccess=". $_POST['rSuccess'] .",
			chrRehost='". $_POST['chrRehost'] ."',
			chrAddstaff='". $_POST['chrAddstaff'] ."',
			chrStatus='Complete',
			chrLastName='". encode($_POST['chrLastName']) ."',
			chrFirstName='". encode($_POST['chrFirstName']) ."',
			chrEmail='". $_POST['chrEmail'] ."',
			chrAttendance='". $_POST['chrAttendance'] ."',
			chrSales='". $_POST['chrSales'] ."',
			chrApple='". implode(',',$_POST['chkApple']) ."',
			chrPresenters='". implode(',',$_POST['chkPresenters']) ."',
			chrOtherExplain='". encode($_POST['chrOtherExplain']) ."',			
			txtFeedback='". encode($_POST['txtFeedback']) . "',
			txtSpecialThanks='". encode($_POST['txtSpecialThanks']) . "',
			txtCustQuote='". encode($_POST['txtCustQuote']) . "',
			txtIssues='". encode($_POST['txtIssues']) . "',
			txtImproveEvent='". encode($_POST['txtImproveEvent']) . "',						
			txtSpecQuote='". encode($_POST['txtSpecQuote']) . "'	
			WHERE idEvent=". $_POST['id'];
			
		database_query($q,"Create Recap");
	
	
		if(is_uploaded_file($_FILES['chrPhoto']['tmp_name'])) {
			database_query("INSERT INTO RecapImages SET 
				idEvent=". $_POST['id'] .",
				intSize=". $_FILES['chrPhoto']['size'] .",
				chrName='-',
				chrType='". $_FILES['chrPhoto']['type'] ."'
				","insert image");
				
			global $mysqli_connection;
			$newID = mysqli_insert_id($mysqli_connection);
			database_query("UPDATE RecapImages SET chrName='". $newID ."-". basename($_FILES['chrPhoto']['name']) ."' WHERE ID=".$newID,"Updating name"); 
	
			$uploaddir = $BF . 'recapimages/';
			$uploadfile = $uploaddir . $newID .'-'. basename($_FILES['chrPhoto']['name']);

			move_uploaded_file($_FILES['chrPhoto']['tmp_name'], $uploadfile);
		}
		header("Location: recaps.php?idStore=". $_POST['idStore'] ."&intDate=". $_REQUEST['intDate']);
		die();
		
		} else {
			$info = $_POST;
		}
		
	}
	
	$images = database_query("SELECT chrName FROM RecapImages WHERE idEvent=". $_REQUEST['id'],"Getting images for event");

	$q = "SELECT Events.ID, Events.chrTitle, chrDescription, DATE_FORMAT(dDate, '%D %M, %Y') as dDateFormat, Stores.chrName, txtEventDescription, EventTypes.chrName as chrEventType
			FROM Events
			JOIN Stores ON Stores.ID=Events.idStore
			JOIN EventTypes ON EventTypes.ID=Events.idEventType
			LEFT JOIN EventTypeNames ON EventTypeNames.idEventType=Events.idEventType
			WHERE Events.ID=". $_REQUEST['id'];
	$event = fetch_database_query($q,"Getting Event Info");

	// Set the title, and add the doc_top
	include($BF . 'includes/top_events.php');
	
?>
		<div class="AdminTopicHeader"><?=$event['chrTitle']?> Recap - <?=$event['chrName']?></div>
		<div class="Messages">
<?			 if(count($error_messages)) {
				foreach($error_messages as $error) { ?>
					<p class='ErrorMessage'><?=$error?></p>
<?				}
			} ?>
		</div>
		<div class="AdminInstructions2">Your feedback on workshops/events is very important. It will help us understand which workshops/events are successful with customers. Please be thorough in your recap. Daily event recap emails containing this feedback are sent to the Retail Marketing leadership team. Thank you!</div>
		<table style='border: 1px solid #999; width:100%; padding:10px;' cellpadding="0" cellspacing="0">
			<tr>
				<td width='150'><strong>Workshop/Event Type:</strong></td>
				<td><?=$event['chrEventType']?></td>
			</tr>	
			<tr>
				<td><strong>Workshop/Event Name:</strong></td>
				<td><?=$event['chrTitle']?></td>
			</tr>	
			<tr>
				<td><strong>Date and Time:</strong></td>
				<td><?=$event['dDateFormat']?></td>
			</tr>	
			<tr>
				<td><strong>Description:</strong></td>
				<td><?=$event['chrDescription']?></td>
			</tr>					
		</table>
	
		<form method='post' action='' enctype="multipart/form-data" style="padding-top:10px;">
	
		<div class='form'>
			<div class='formHeader'>Estimate Customer Attendance: 
            	<input type="text" name="chrAttendance" id="chrAttendance" size="5" maxlength="5" value="<?=$info['chrAttendance']?>" />
<? /*
					<select name='chrAttendance'>
						<option value=''>- Select Attendance -</option>
						<option value='0'<?=($info['chrAttendance'] == '0' ? ' selected' : '')?>>0</option>
						<option value='1-10'<?=($info['chrAttendance'] == '1-10' ? ' selected' : '')?>>1-10</option>
						<option value='11-20'<?=($info['chrAttendance'] == '11-20' ? ' selected' : '')?>>11-20</option>
						<option value='21-50'<?=($info['chrAttendance'] == '21-50' ? ' selected' : '')?>>21-50</option>
						<option value='51-99'<?=($info['chrAttendance'] == '51-99' ? ' selected' : '')?>>51-99</option>
						<option value='100+'<?=($info['chrAttendance'] == '100+' ? ' selected' : '')?>>100+</option>
					</select>
*/ ?>
			</div>
		</div>

		<div class='form'>
			<div class='formHeader'>Estimated Incremental Daily Sales generated from event: 
			<select name='chrSales' style='width: 125px;'>
				<option value=''>- Select Estimate -</option>
				<option value='0.'<?=($info['chrSales'] == '0' ? ' selected' : '')?>>$0</option>
				<option value='1-2.5'<?=($info['chrSales'] == '1-2.5' ? ' selected' : '')?>>$1-2.5K</option>
				<option value='2.5-5'<?=($info['chrSales'] == '2.5-5' ? ' selected' : '')?>>$2.5K-5K</option>
				<option value='5-10'<?=($info['chrSales'] == '5-10' ? ' selected' : '')?>>$5K-10K</option>
				<option value='10'<?=($info['chrSales'] == '10' ? ' selected' : '')?>>$10K+</option>
			</select>
			</div>
		</div>

		<div class='form'>
			<div class='formHeader'>Rate the overall success of this event (promotion, planning, organization, customer experience). Scale of 1 - 10, with 10 being the highest:</div>
			<table cellpadding="0" cellspacing="0" border="0" class='scale' style='text-align: center;'>
				<tr>
					<td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td>
				</tr>
				<tr>
					<td><input<?=($info['rSuccess'] == 1 ? ' checked' : '')?> type='radio' name='rSuccess' value='1' /></td>
					<td><input<?=($info['rSuccess'] == 2 ? ' checked' : '')?> type='radio' name='rSuccess' value='2' /></td>
					<td><input<?=($info['rSuccess'] == 3 ? ' checked' : '')?> type='radio' name='rSuccess' value='3' /></td>
					<td><input<?=($info['rSuccess'] == 4 ? ' checked' : '')?> type='radio' name='rSuccess' value='4' /></td>
					<td><input<?=($info['rSuccess'] == 5 ? ' checked' : '')?> type='radio' name='rSuccess' value='5' /></td>
					<td><input<?=($info['rSuccess'] == 6 ? ' checked' : '')?> type='radio' name='rSuccess' value='6' /></td>
					<td><input<?=($info['rSuccess'] == 7 ? ' checked' : '')?> type='radio' name='rSuccess' value='7' /></td>
					<td><input<?=($info['rSuccess'] == 8 ? ' checked' : '')?> type='radio' name='rSuccess' value='8' /></td>
					<td><input<?=($info['rSuccess'] == 9 ? ' checked' : '')?> type='radio' name='rSuccess' value='9' /></td>
					<td><input<?=($info['rSuccess'] == 10 ? ' checked' : '')?> type='radio' name='rSuccess' value='10' /></td>
				</tr>					
			</table>
		</div>		

		<div class='form'>
			<div class='formHeader'>Rate how much customers seemed to enjoy the workshop/event (10 being the highest): </div>
			<table cellpadding="0" cellspacing="0" border="0" class='scale' style='text-align: center;'>
				<tr>
					<td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td>
				</tr>
				<tr>
					<td><input<?=($info['rEnjoy'] == 1 ? ' checked' : '')?> type='radio' name='rEnjoy' value='1' /></td>
					<td><input<?=($info['rEnjoy'] == 2 ? ' checked' : '')?> type='radio' name='rEnjoy' value='2' /></td>
					<td><input<?=($info['rEnjoy'] == 3 ? ' checked' : '')?> type='radio' name='rEnjoy' value='3' /></td>
					<td><input<?=($info['rEnjoy'] == 4 ? ' checked' : '')?> type='radio' name='rEnjoy' value='4' /></td>
					<td><input<?=($info['rEnjoy'] == 5 ? ' checked' : '')?> type='radio' name='rEnjoy' value='5' /></td>
					<td><input<?=($info['rEnjoy'] == 6 ? ' checked' : '')?> type='radio' name='rEnjoy' value='6' /></td>
					<td><input<?=($info['rEnjoy'] == 7 ? ' checked' : '')?> type='radio' name='rEnjoy' value='7' /></td>
					<td><input<?=($info['rEnjoy'] == 8 ? ' checked' : '')?> type='radio' name='rEnjoy' value='8' /></td>
					<td><input<?=($info['rEnjoy'] == 9 ? ' checked' : '')?> type='radio' name='rEnjoy' value='9' /></td>
					<td><input<?=($info['rEnjoy'] == 10 ? ' checked' : '')?> type='radio' name='rEnjoy' value='10' /></td>
				</tr>					
			</table>
		</div>				

		<div class='form'>
			<div class='formHeader'>What would you do differently (if anything) to improve workshop/event success?</div>
			<textarea name='txtImproveEvent' cols='75' rows='8'><?=$info['txtImproveEvent']?></textarea>
		</div>

		<div class='form'>				
			<div class='formHeader'>Where there any unexpected technical issues that need to be noted?</div>
			<textarea name='txtIssues' cols='75' rows='8'><?=$info['txtIssues']?></textarea>
		</div>				

		<div class='form'>
			<div class='formHeader'>Would you like to host this workshop/event again?  
			<input<?=($info['chrRehost'] == 'yes' ? ' checked' : '')?> type='radio' name='chrRehost' value='yes' /> Yes <input<?=($info['chrRehost'] == 'no' ? ' checked' : '')?> type='radio' name='chrRehost' value='no' /> No
			</div>
		</div>

		<div class='form'>
			<div class='formHeader'>Did this workshop/event require additional staffing?  
			<input<?=($info['chrAddstaff'] == 'yes' ? ' checked' : '')?> type='radio' name='chrAddstaff' value='yes' /> Yes <input<?=($info['chrAddstaff'] == 'no' ? ' checked' : '')?> type='radio' name='chrAddstaff' value='no' /> No
			</div>
		</div>

		<div class='form'>
			<div class='formHeader'>How was this workshop/event promoted by Apple (check all that apply)? </div>
			<input<?=(isset($info['chkApple']) && in_array('Retail Website',$info['chkApple']) ? ' checked' : '')?> type='checkbox' name='chkApple[]' value='Retail Website' />Retail Website<br />
			<input<?=(isset($info['chkApple']) && in_array('Easel',$info['chkApple']) ? ' checked' : '')?> type='checkbox' name='chkApple[]' value='Easel' />Easel<br />
			<input<?=(isset($info['chkApple']) && in_array('HP Blast',$info['chkApple']) ? ' checked' : '')?> type='checkbox' name='chkApple[]' value='HP Blast' />HP Blast (HP Events Only)<br />
			<input<?=(isset($info['chkApple']) && in_array('HP COE',$info['chkApple']) ? ' checked' : '')?> type='checkbox' name='chkApple[]' value='HP COE' />HP Printed Calendar of Events<br />
			<input<?=(isset($info['chkApple']) && in_array('By Staff',$info['chkApple']) ? ' checked' : '')?> type='checkbox' name='chkApple[]' value='By Staff' />By staff during workshops and personal training sessions
		</div>

		<div class='form'>
			<div class='formHeader'>How was this workshop/event promoted by the Presenter (check all that apply)? </div>
			<input<?=(isset($info['chkPresenters']) && in_array('Presenters Website',$info['chkPresenters']) ? ' checked' : '')?> type='checkbox' name='chkPresenters[]' value='Presenters Website' />Presenter's Website<br />
			<input<?=(isset($info['chkPresenters']) && in_array('Not By Presenter',$info['chkPresenters']) ? ' checked' : '')?> type='checkbox' name='chkPresenters[]' value='Not By Presenter' />It was not promoted by presenter.<br />
			<input<?=(isset($info['chkPresenters']) && in_array('Other',$info['chkPresenters']) ? ' checked' : '')?> type='checkbox' name='chkPresenters[]' value='Other' />Other, describe:  <input type='textbox' name='chrOtherExplain' size='60' value='<?=$info['chrOtherExplain']?>' />					
		</div>

		<div class='form'>
			<div class='formHeader'>Workshop/Event Recap:</div>
			<textarea name='txtFeedback' cols='75' rows='8'><?=$info['txtFeedback']?></textarea>
		</div>

		<div class='form'>
			<div class='formHeader'>Special thanks go to: <span class='Required'>(Optional)</span></div>
			<textarea name='txtSpecialThanks' cols='75' rows='8'><?=$info['txtSpecialThanks']?></textarea>
		</div>

		<div class='form'>
			<div class='formHeader'>Customer quote of the night. <span class='Required'>(Optional)</span></div>
			<textarea name='txtCustQuote' cols='75' rows='8'><?=$info['txtCustQuote']?></textarea>
		</div>

		<div class='form'>
			<div class='formHeader'>Mac Specialist quote of the night. <span class='Required'>(Optional)</span></div>
			<textarea name='txtSpecQuote' cols='75' rows='8'><?=$info['txtSpecQuote']?></textarea>
		</div>

		<div class='form'>
			<div class='formHeader'>Upload Photos <span class='Required' style='font-size: 10px;'>(.jpg preferred, 2MB file size limit per photo)</span></div>
			<input name="chrPhoto" type="file" />
		</div>

<?
	if(mysqli_num_rows($images) > 0) {
		while($row = mysqli_fetch_assoc($images)) {
?>
		<span style='padding-left: 10px;'><a href='<?=$BF?>recapimages/<?=$row['chrName']?>' target='_blank'><img src='<?=$BF?>recapimages/<?=$row['chrName']?>' alt='<?=$row['chrName']?>' width='45' height='45' /></a></span>
<?
		}
	}
?>
	
		<h3>Submitted By: </h3>
		
		<table style='margin: -10px 0 10px;'>
			<tr>
				<td style='font-size: 11px; font-weight: bold; width: 50px;'>Last Name:</td>
				<td><input name="chrLastName" value='<?=$info['chrLastName']?>' /></td>
			</tr>
			<tr>
				<td style='font-size: 11px; font-weight: bold;'>First Name: </td>
				<td><input name="chrFirstName" value='<?=$info['chrFirstName']?>' /></td>
			</tr>
			<tr>
				<td style='font-size: 11px; font-weight: bold; white-space: nowrap;'>Email Address: </td>
				<td><input name="chrEmail" value='<?=$info['chrEmail']?>' /></td>
			</tr>		
		</table>

		<div class='FormButtons'>
			<input type='hidden' name='id' value='<?=$_REQUEST['id']?>' />
			<input type='hidden' name='idStore' value='<?=$_REQUEST['idStore']?>' />
			<input type='hidden' name='intDate' value='<?=$_REQUEST['intDate']?>' />
	<?	if($update == 1) { ?>
			<input type='submit' name='updateRecap' value='Update Information' />
	<?	} else { ?>
			<input type='submit' name='submitRecap' value='Submit Information' />
	<?	} ?>
		</div>
		
	
	</form>
	
<?
	include($BF. 'includes/bottom2.php');
?>
