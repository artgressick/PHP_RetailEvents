<?php
	$BF = '../';
	$title = 'Add Calendar Type';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');
	
	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }
	
	// if this is a form submission
	if(isset($_POST['chrCalendarType'])) {
	
		$q = "INSERT INTO CalendarTypes SET
			chrKEY='". makekey() ."',
			chrCalendarType='". encode($_POST['chrCalendarType']) ."',
			chrColorText='". encode($_POST['chrColorText']) ."',
			chrColorBG='". encode($_POST['chrColorBG']) ."'
		";
		database_query($q, "insert Calendar Type");

		header("Location: caltypes.php");
		die();
	}

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
<div style='padding: 10px;'>
		<div class="AdminTopicHeader">Add Calendar Type</div>
		<div class="AdminInstructions2">Your are about to add a new calendar type to the database..</div>
		
		<form id='idForm' name='idForm' method='post' action=''>

		<div id='errors'></div>

		<div class='form'>
			<div class='formHeader'>Calendar Type <span class='Required'>(Required)</span></div>
			<input type='text' name='chrCalendarType' id='chrCalendarType' />
		</div>

		<table cellspacing="0" cellpadding="0" style='margin: 0; padding: 0;'>
			<tr>
				<td class='formHeader' style='width: 150px;'>Background Color</td>
				<td class='formHeader' style='width: 150px;'>Text Color</td>
				<td rowspan='2'>
					<div id='colortest' style='border: 1px solid gray; height: 25px; font-size: 14px; text-align: center; width: 100px; color: #333; background: #ccc;'>Test</div>
				</td>
			</tr>
			<tr>
				<td><input type='text' size='10' maxlength='20' name='chrColorBG' id='chrColorBG' value='#CCCCCC' onchange='colortest();' /></td>
				<td><input type='text' size='10' maxlength='20' name='chrColorText' id='chrColorText' value='#333333' onchange='colortest();' /></td>
			</tr>
		</table>

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

		<br />
		<div class='FormButtons'>
			<input type='button' value='Add Calendar Type' onclick='error_check()' />
		</div>

		</form>
</div>
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom.php');
?>