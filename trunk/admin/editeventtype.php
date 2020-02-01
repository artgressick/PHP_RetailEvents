<?php
	$BF = '../';
	$title = 'Edit Workshop/Event Type';
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
		
	$info = fetch_database_query("SELECT * FROM EventTypes WHERE ID=". $_REQUEST['id'],"Getting Event Type");
		
	// if this is a form submission
	if(isset($_POST['chrName'])) {

		$table = 'EventTypes';
		$mysqlStr = '';
		$audit = '';

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'idEventCategory',$info['idEventCategory'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrName',$info['chrName'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'idLocalization',$info['idLocalization'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrStyleClass',$info['chrStyleClass'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'bEditorReview',$info['bEditorReview'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'bShow',$info['bShow'],$audit,$table,$_POST['id']);
		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }

		if($connection = @mysqli_connect('weblab11.apple.com', 'techit', 'dollap')) {
			if(@mysqli_select_db($connection, 'retail')) {
			
				$query = "UPDATE EventTypes SET 
					chrName='". addslashes(decode($_POST['chrName'])) ."',		
					chrStyleClass='". addslashes(decode($_POST['chrStyleClass'])) ."'
					WHERE ID=".$_POST['id'];

				
					
				if (mysqli_query($connection, $query)) { 
					//Send a notification
						
					$subject = 'ALERT! - An Event Type has been edited and pushed to the official Server.';
					$headers = 'From: retailevents@apple.com' . "\r\n";
					$to = 'programmers@techitsolutions.com';
					mail($to, $subject, "An Event Type has been edited and pushed to the official Server.  Event Type Name=".$_POST['chrName']." | Event Type ID=".$_POST['id'], $headers);
				
				}
	
			}			
	
		}
		



		// When the page is done updating, move them back to whatever the list page is for the section you are in.
		header("Location: eventtypes.php");
		die();
	}
	
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('idEventCategory', "You must select an Workshop/Event Category.");
		total += ErrorCheck('chrName', "You must enter an Workshop/Event Type Name.");
		total += ErrorCheck('idLocalization', "You must select an Localization.");
		total += ErrorCheck('chrStyleClass', "You must enter a Style Sheet Tag.");
		total += ErrorCheck('bEditorReview', "You must select a Editor Review Status.");
		

		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>
<?

	include($BF. 'includes/top_admin2.php');
	
?>
					<div class="AdminTopicHeader">Edit Workshop/Event Type</div>
					<div class="AdminInstructions2">You are about to change a Type. You should not change anything unless you have checked with Retail Workshop/Events Team.</div>
					
					<form id='idForm' name='idForm' method='post' action=''>

					<div id='errors'></div>

					<table class='AdminTwoColumns'>
						<tr>
							<td class="Left" style="padding-bottom:15px;">

								<div class='form'>
									<div class='formHeader'>Workshop/Event Category <span class='Required'>(Required)</span></div>
									<select name='idEventCategory' id='idEventCategory'>
										<option value=''>Please choose an Workshop/Event Category</option>
		<?	
			$eventcategory = database_query("SELECT ID, chrCategory FROM EventCategory ORDER BY ID","getting Event Categories");
			while($row = mysqli_fetch_assoc($eventcategory)) { 
		?>								
										<option <?=($row['ID'] == $info['idEventCategory'] ? ' selected' : '')?> value='<?=$row['ID']?>'><?=$row['chrCategory']?></option>
		<?
			}
		?>
									</select>
								</div> 
								
								<div class='form'>
									<div class='formHeader'>Workshop/Event Type Name <span class='Required'>(Required)</span></div>
									<input type='text' size='40' maxlength='80' name='chrName' id='chrName' value='<?=$info['chrName']?>' />
								</div>
							<div class='form'>										
								<div class='formHeader'>Localization <span class='Required'>(Required)</span></div>
									<select name='idLocalization' id='idLocalization'>
										<option value=''>Please choose an Localization</option>
		<?	
			$localization = database_query("SELECT ID, chrLocalization FROM Localization WHERE !bDeleted AND ID IN (".$_SESSION['chrLoc'].") ORDER BY ID","getting Event Categories");
			while($row = mysqli_fetch_assoc($localization)) { 
		?>								
										<option <?=($row['ID'] == $info['idLocalization'] ? ' selected' : '')?> value='<?=$row['ID']?>'><?=$row['chrLocalization']?></option>
		<?
			}
		?>
									</select>
							</div> 
																	
										
							</td>
				
							<td class="Right">
							
								<div class='form'>
									<div class='formHeader'>Style Sheet Tag <span class='Required'>(Required)</span></div>
									<input type='text' size='6' maxlength='8' name='chrStyleClass' id='chrStyleClass' value='<?=$info['chrStyleClass']?>' />
								</div>
								
								<div class='form'>
									<div class='formHeader'>Requires Editor Approval <span class='Required'>(Required)</span></div>
									<select name='bEditorReview' id='bEditorReview'>
										<option value=''>Please choose</option>
										<option value='0' <?=($info['bEditorReview'] == 0 ? ' selected' : '')?>>No</option>
										<option value='1' <?=($info['bEditorReview'] == 1 ? ' selected' : '')?>>Yes</option>
									</select>
								</div>
								
								<div class='form'>
									<div class='formHeader'>Show <span class='Required'>(Required)</span></div>
									<select name='bShow' id='bShow'>
										<option value='1' <?=($info['bShow'] == 1 ? ' selected="selected"' : '')?>>Yes (Show)</option>
										<option value='0'<?=($info['bShow'] == 0 ? ' selected="selected"' : '')?>>No (Hide)</option>
									</select>
								</div>
							
							</td>
						</tr>
					</table>

					<div class='FormButtons'>
						<input type='button' name='SubmitAddUser' value='Update Workshop/Event Type' onclick='error_check()' />
						<input type='hidden' name='id' value='<?=$_REQUEST["id"]?>' />
					</div>
			
					</form>
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>