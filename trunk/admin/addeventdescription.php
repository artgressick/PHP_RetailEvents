<?php
	$BF = '../';
	$title = 'Add Workshop/Event Description';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');

	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root
		idType of 1 = Super user 
		idType of 2 = Editor
		idType of 3 = Corp Events
		idType of 4 = Store
		
		$pageaccess is array of user types that can access the page
	*/
	
	$pageaccess = array("1","2");
	if(!in_array($_SESSION['idType'],$pageaccess)) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: '. $BF ."nopermission.php"); die(); }
	// End Security Check
		
	// if this is a form submission
	if(isset($_POST['chrEventTitle'])) {
	
	$q = "INSERT INTO EventTypeNames SET 
			idEventType='". $_POST['idEventType'] ."',
			chrEventTitle='". encode($_POST['chrEventTitle']) ."',
			txtEventDescription='". encode($_POST['txtEventDescription']) ."',
			bWeeklyRequired='". $_POST['bWeeklyRequired'] ."',
			bShow='".$_POST['bShow']."'
		";
			
		database_query($q, "insert event description");

		header("Location: eventdescriptions.php");
		die();
	}

	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('idEventType', "You must choose an Workshop/Event Type.");
		total += ErrorCheck('chrEventTitle', "You must enter an Workshop/Event Description Name.");
		total += ErrorCheck('bWeeklyRequired', "You must choose Weekly Status.");
		
		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>
<script type='text/javascript' language="javascript">
	function addLink() {
		var loc = document.getElementById('chrURL').value;
		var name = document.getElementById('chrURLName').value;
		
		document.getElementById('txtEventDescription').value += "<a href='" + loc + "'>" + name + "</a>";
	}
</script>

<?
	include($BF. 'includes/top_admin2.php');
?>
					<div class="AdminTopicHeader">Add Workshop/Event Description</div>
					<div class="AdminInstructions2">You are entering a Title &amp; Description for the COE section. You should not enter anything unless you have checked with Retail Workshop/Events Team.<br />PLEASE NOTE!! If you Hide this, Make Sure to Remove it as a Weekly Requirement</div>
					
					<form id='idForm' name='idForm' method='post' action=''>

					<div id='errors'></div>

					<table class='AdminTwoColumns'>
						<tr>
							<td class="Left">

								<div class='form'>
								<div class='formHeader'>Workshop/Event Type <span class='Required'>(Required)</span></div>
									<select name='idEventType' id='idEventType'>
										<option value=''>Please choose an Workshop/Event Type</option>
<?
	$eventtypes = database_query("SELECT EventTypes.ID, chrName, chrLocalization FROM EventTypes JOIN Localization ON EventTypes.idLocalization=Localization.ID WHERE Localization.ID IN (".$_SESSION['chrLoc'].") ORDER BY chrName, Localization.ID","getting event types");
	while($row = mysqli_fetch_assoc($eventtypes)) {
?>
										<option value='<?=$row['ID']?>'><?=$row['chrName']?> (<?=$row['chrLocalization']?>)</option>
<?
	}
?>
									</select>
								</div>
							
								<div class='form'>
									<div class='formHeader'>Workshop/Event Title <span class='Required'>(Required)</span></div>
									<input type='text' size='40' maxlength='80' name='chrEventTitle' id='chrEventTitle' />
									</div>
							
								<div class='form'>
									<div class='formHeader'>Required Weekly <span class='Required'>(Required)</span></div>
									<select name='bWeeklyRequired' id='bWeeklyRequired'>
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
									<div><span class='Required'>PLEASE NOTE!! If you Hide this, Make Sure to Remove it as a Weekly Requirement</span></div>
								</div>
										
							</td>
				
							<td class="Right">
							
								<div class='form'>
									<div class='formHeader'>Workshop/Event Description <span class='Required'>(Required)</span></div>
									<textarea name="txtEventDescription" id="txtEventDescription" cols="60" rows="10" wrap="virtual"></textarea>

									<div>
										<table cellspacing="0" cellpadding="0">
											<tr>
												<td class='formHeader'>Address</td>
												<td class='formHeader'>Name to Display</td>
											</tr>
											<tr>
												<td><input type='text' size='30' name='chrURL' id='chrURL' value="http://" /></td>
												<td><input type='text' size='15' name='chrURLName' id='chrURLName' /> <input type='button' value='Add Link' onclick='addLink()' /></td>
											</tr>
										</table>
									</div>

								</div>
							
							</td>
						</tr>
					</table>

					<div class='FormButtons'>
						<input type='button' name='SubmitAddUser' value='Save Workshop/Event Description' onclick='error_check()' />
					</div>
			
					</form>
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>