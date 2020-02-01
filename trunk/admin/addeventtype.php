<?php
	$BF = '../';
	$title = 'Add Workshop/Event Type';
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
	if(isset($_POST['chrName'])) {
	
	$q = "INSERT INTO EventTypes SET 
			idEventCategory='". $_POST['idEventCategory'] ."',
			bEditorReview='". $_POST['bEditorReview'] ."',
			chrName='". encode($_POST['chrName']) ."',
			idLocalization='". $_POST['idLocalization'] ."',			
			chrStyleClass='". encode($_POST['chrStyleClass']) ."',
			bShow='".$_POST['bShow']."'
		";
				
	database_query($q, "insert User");


	global $mysqli_connection;
	$newID = mysqli_insert_id($mysqli_connection);

	if($connection = @mysqli_connect('weblab11.apple.com', 'techit', 'dollap')) {
		if(@mysqli_select_db($connection, 'retail')) {
		
			$query = "INSERT INTO EventTypes SET 
				ID='".$newID."',
				chrName='". addslashes(decode($_POST['chrName'])) ."',		
				chrStyleClass='". addslashes(decode($_POST['chrStyleClass'])) ."'
			";
		
			if (mysqli_query($connection, $query)) { 
				//Send a notification
					
				$subject = 'ALERT! - An Event Type has been pushed to the official Server.';
				$headers = 'From: retailevents@apple.com' . "\r\n";
				$to = 'programmers@techitsolutions.com';
				mail($to, $subject, "An Event Type has been pushed to the official Server.  Event Type Name=".$_POST['chrName']." ID=".$newID, $headers);
			
			}

		}			

	}
	
	


		header("Location: eventtypes.php");
		die();

	}
	
	$eventcategory = database_query("SELECT ID, chrCategory FROM EventCategory ORDER BY chrCategory","getting event category");

	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('idEventCategory', "You must choose an Event Category.");
		total += ErrorCheck('chrName', "You must enter an Event Type Name.");
		total += ErrorCheck('idLocalization', "You must select an Localization.");		
		total += ErrorCheck('chrStyleClass', "You must enter a Style Sheet Tag");
		total += ErrorCheck('bEditorReview', "You must select a Editor Review Status.");
		
		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>
<?
	include($BF. 'includes/top_admin2.php');
?>
					<div class="AdminTopicHeader">Add Workshop/Event Type</div>
					<div class="AdminInstructions2">You are entering an Workshop/Event Type. These show up in the COE under the Category drop down. Please check with Retail Workshop/Events Team before entering any new types.</div>
					
					<form id='idForm' name='idForm' method='post' action=''>

					<div id='errors'></div>

					<table class='AdminTwoColumns'>
						<tr>
							<td class="Left">

								<div class='form'>
									<div class='formHeader'>Workshop/Event Category <span class='Required'>(Required)</span></div>
									<select name='idEventCategory' id='idEventCategory'>
										<option value=''>Please choose an Event Category</option>
<?
	$eventcategory = database_query("SELECT ID, chrCategory FROM EventCategory ORDER BY chrCategory","getting event category");
	while($row = mysqli_fetch_assoc($eventcategory)) {
?>
										<option value='<?=$row['ID']?>'><?=$row['chrCategory']?></option>
<?
	}
?>
									</select>
								</div>
								
								<div class='form'>
									<div class='formHeader'>Workshop/Event Type Name <span class='Required'>(Required)</span></div>
									<input type='text' size='40' maxlength='80' name='chrName' id='chrName' />
								</div>
								
							<div class='form'>	
								<div class='formHeader'>Localization <span class='Required'>(Required)</span></div>
									<select name='idLocalization' id='idLocalization'>
										<option value=''>Please choose an Localization</option>
		<?	
			$localization = database_query("SELECT ID, chrLocalization FROM Localization WHERE !bDeleted AND ID IN (".$_SESSION['chrLoc'].") ORDER BY ID","getting Event Categories");
			while($row = mysqli_fetch_assoc($localization)) { 
		?>								
										<option value='<?=$row['ID']?>'><?=$row['chrLocalization']?></option>
		<?
			}
		?>
									</select>
							</div> 								
										
							</td>
				
							<td class="Right">
							
								<div class='form'>
									<div class='formHeader'>Style Sheet Tag <span class='Required'>(Required)</span></div>
									<input type='text' size='6' maxlength='4' name='chrStyleClass' id='chrStyleClass' />
								</div>
								
								<div class='form'>
									<div class='formHeader'>Requires Editor Approval <span class='Required'>(Required)</span></div>
									<select name='bEditorReview' id='bEditorReview'>
										<option value=''>Please choose</option>
										<option value='0'>No</option>
										<option value='1'>Yes</option>
									</select>
								</div>
								
								<div class='form'>
									<div class='formHeader'>Show <span class='Required'>(Required)</span></div>
									<select name='bShow' id='bShow'>
										<option value='1' selected="selected">Yes (Show)</option>
										<option value='0'>No (Hide)</option>
									</select>
								</div>
							
							</td>
						</tr>
					</table>

					<div class='FormButtons'>
						<input type='button' name='SubmitAddUser' value='Save Workshop/Event Type' onclick='error_check()' />
					</div>
			
					</form>
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>