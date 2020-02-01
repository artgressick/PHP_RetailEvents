<?php
	$BF = '../';
	$title = 'Edit Calendar Type';
	$curPage = "";
	require($BF. '_lib2.php');

	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }

	if(!isset($_REQUEST['key']) || strlen($_REQUEST['key']) != 40) {
		header('Location: '.$BF.'calendar/error.php'); 
		die();
	}
	
	$info = fetch_database_query("SELECT * FROM CalendarTypes WHERE chrKey='". $_REQUEST['key'] ."'","Getting info");
	// if this is a form submission
	if(isset($_POST['chrCalendarType'])) {

		$table = 'CalendarTypes';
		$mysqlStr = '';
		$audit = '';

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCalendarType',$info['chrCalendarType'],$audit,$table,$_POST['id']);
	list($mysqlStr,$audit) = set_strs($mysqlStr,'chrColorBG',$info['chrColorBG'],$audit,$table,$_POST['id']);
	list($mysqlStr,$audit) = set_strs($mysqlStr,'chrColorText',$info['chrColorText'],$audit,$table,$_POST['id']);

		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }

		header("Location: caltypes.php");
		die();
	}


	include($BF. 'includes/meta2.php');
	
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrCalendarType', "You must enter a Calendar Type.");
		total += ErrorCheck('chrColorBG', "You must enter a Background Color.");
		total += ErrorCheck('chrColorText', "You must enter a Text Color.");
		
		if(total == 0) { document.getElementById('idForm').submit(); }
	}

function colortest() {
	document.getElementById('colortest').style.backgroundColor = document.getElementById('chrColorBG').value;
	document.getElementById('colortest').style.color = document.getElementById('chrColorText').value;
}

</script>
<script language="JavaScript" src="colorwheel.js"></script>
<script language="JavaScript" src="browser_detect.js"></script>

<?
	$bodyParams = "InitializeColorWheel();";
	include($BF. 'calendar/includes/top.php');
?>

	<div style='margin: 10px;'>

	<div class="AdminTopicHeader">Edit Calendar Type</div>
	<div class="AdminInstructions2">.</div>

	<form id='idForm' name='idForm' method='post' action=''>

		<div id='errors'></div>

		<div class='form'>
			<div class='formHeader'>Calendar Type <span class='Required'>(Required)</span></div>
			<input type='text' name='chrCalendarType' id='chrCalendarType' value='<?=$info['chrCalendarType']?>' />
		</div>

		<table cellspacing="0" cellpadding="0" style='margin: 0; padding: 0;'>
			<tr>
				<td class='formHeader' style='width: 150px;'>Background Color</td>
				<td class='formHeader' style='width: 150px;'>Text Color</td>
				<td rowspan='2'>
					<div id='colortest' style='border: 1px solid gray; height: 25px; font-size: 14px; text-align: center; width: 100px; color: <?=$info['chrColorText']?>; background: <?=$info['chrColorBG']?>;'>Test</div>
				</td>
			</tr>
			<tr>
				<td><input type='text' size='10' maxlength='10' name='chrColorBG' id='chrColorBG' value='<?=$info['chrColorBG']?>' onchange='colortest();' /></td>
				<td><input type='text' size='10' maxlength='10' name='chrColorText' id='chrColorText' value='<?=$info['chrColorText']?>' onchange='colortest();' /></td>
			</tr>
		</table>
		<br />

<div>


	<div id="wheel" style='position: absolute; width: 553px; height: 257px;'>
		<a href="javascript://" onclick="javascript:pickColor(); return false;">
			<img alt="color wheel (hsv)" src="blank.gif" style="width: 553px;height: 257px; border: 0; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='hsvwheel.png', sizingMethod='scale')" />
		</a>
	</div>
	<div style='height: 257px; width: 553px;'></div>
	<div style='padding: 5px 10px; background-color: width: .75in;' id='ColorDisplay'>
		<input type='text' size='7' maxlength='7' name='chrColor' id='chrColor' />
		<input type='button' value='Text Color' onClick='document.getElementById("chrColorText").value=document.getElementById("chrColor").value; colortest();' /> <input type='button' value='Background Color' onClick='document.getElementById("chrColorBG").value=document.getElementById("chrColor").value;colortest();' />
	</div>



</div>
		
		<input type='button' name='updateInfor' value='Update Information' onclick='error_check()' />
		<input type='hidden' name='key' value='<?=$_REQUEST['key']?>'>
		<input type='hidden' name='id' value='<?=$info['ID']?>'>
		
	</form>

	</div>		
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom.php');
?>