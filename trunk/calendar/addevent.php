<?php
	$BF = '../';
	$title = 'Add Calendar Event';
	$curPage = "";
	require($BF. '_lib2.php');

	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }

	# Setting up initial variables to be used
	if(!isset($_REQUEST['dBegin']) || $_REQUEST['dBegin'] == '') {
		$intCurDay = date('d'); 	# int value of current day (ex: 30)
		$intCurMonth = date('m');	# int value of current month (ex: 7)
		$intCurYear = date('Y');	# int value of current year (ex: 2007)

		header("Location: ". $BF ."calendar/addevent.php?dBegin=".$intCurYear.$intCurMonth.$intCurDay);
		die();
	} else {
		$intDay = date('d',strtotime($_REQUEST['dBegin']));
		$intMonth = date('m',strtotime($_REQUEST['dBegin']));
		$intYear = date('Y',strtotime($_REQUEST['dBegin']));
	}
	
		
	// if this is a form submission. Any required field may be used to check.
	if(isset($_POST['chrCalendarEvent'])) {
	
		if(($_POST['dBegin'] == $_POST['dEnd']) && $_POST['bReoccur'] != "on") {
			$q = "INSERT INTO CalendarEvents SET
					bAllDay='". ($_POST['bAllDay'] == "on" ? 1 : 0) ."',
					chrCalendarEvent='" . encode($_POST['chrCalendarEvent']) . "',
					dBegin='" . date('Y-m-d',strtotime($_POST['dBegin'])) . "',
					dEnd='" . date('Y-m-d',strtotime($_POST['dBegin']. " + 1 day")) . "',
					tBegin='" . ($_POST['tBegin'] != "" ? date('H:i:s',strtotime($_POST['tBegin'])) : '') . "',
					tEnd='" . ($_POST['tEnd'] != "" ? date('H:i:s',strtotime($_POST['tEnd'])) : '') . "',
					idCalendarType='". $_POST['idCalendarType'] ."',
					txtContent='" . encode($_POST['txtContent']) . "',
					dtCreated=now(),
					chrKey='". makekey() ."',
					idUser='". $_SESSION['idUser'] ."'
				";		
			database_query($q, "insert calendar event");
		} else if(($_POST['dBegin'] != $_POST['dEnd']) && $_POST['bReoccur'] != "on") {
			$q = "INSERT INTO CalendarEvents SET
					bAllDay='". ($_POST['bAllDay'] == "on" ? 1 : 0) ."',
					chrCalendarEvent='" . encode($_POST['chrCalendarEvent']) . "',
					dBegin='" . date('Y-m-d',strtotime($_POST['dBegin'])) . "',
					dEnd='" . date('Y-m-d',strtotime($_POST['dEnd'])) . "',
					tBegin='" . ($_POST['tBegin'] != "" ? date('H:i:s',strtotime($_POST['tBegin'])) : '') . "',
					tEnd='" . ($_POST['tEnd'] != "" ? date('H:i:s',strtotime($_POST['tEnd'])) : '') . "',
					idCalendarType='". $_POST['idCalendarType'] ."',
					txtContent='" . encode($_POST['txtContent']) . "',
					dtCreated=now(),
					chrKey='". makekey() ."',
					idUser='". $_SESSION['idUser'] ."'
				";		
			database_query($q, "insert calendar event");
		} else {
			# make sure the begin date is first... 
			if(strtotime($_POST['dBegin']) < strtotime($_POST['dRepeatEnd'])) {
				$fday = strtotime($_POST['dBegin']);
				$lday = strtotime($_POST['dRepeatEnd']);
			} else {
				$lday = strtotime($_POST['dBegin']);
				$fday = strtotime($_POST['dRepeatEnd']);
			}
			$timeframe = $_POST['chrReoccur'];
			
			$series = makekey();
		
			while($fday <= $lday) {
				$q = "INSERT INTO CalendarEvents SET
						bAllDay='". ($_POST['bAllDay'] == "on" ? 1 : 0) ."',
						chrCalendarEvent='" . encode($_POST['chrCalendarEvent']) . "',
						dBegin='" . date('Y-m-d',$fday) . "',
						dEnd='" . date('Y-m-d',$fday) . "',
						tBegin='" . ($_POST['tBegin'] != "" ? date('H:i:s',strtotime($_POST['tBegin'])) : '') . "',
						tEnd='" . ($_POST['tEnd'] != "" ? date('H:i:s',strtotime($_POST['tEnd'])) : '') . "',
						idCalendarType='". $_POST['idCalendarType'] ."',
						txtContent='" . encode($_POST['txtContent']) . "',
						chrReoccur='" . $_POST['chrReoccur'] . "',
						chrSeries='". $series ."',
						dtCreated=now(),
						chrKey='". makekey() ."',
						idUser='". $_SESSION['idUser'] ."'
					";		
				database_query($q, "insert calendar event");
				echo $fday = date('Y-m-d',$fday);
				$fday = strtotime($fday ." + 1 ". $timeframe);
			}
		}


		header("Location: ". $_SESSION['calSection'] .".php?dBegin=". $_SESSION['calDate']);
		die();
	}


	include($BF. 'includes/meta2.php');
	
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" type="text/javascript" src="<?=$BF?>includes/forms.js"></script>
<script type="text/javascript" src="<?=$BF?>components/tiny_mce/tiny_mce_gzip.js"></script>
<script type="text/javascript">
tinyMCE_GZ.init({
	plugins : 'style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',
	themes : 'simple,advanced',
	languages : 'en',
	disk_cache : true,
	debug : false
});
</script>
<!-- Needs to be seperate script tags! -->
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		plugins : "style,layer,table,save,advhr,advimage,advlink,emotions,insertdatetime,preview,zoom,flash,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,filemanager",
		theme_advanced_buttons1_add : "fontselect,fontsizeselect",
		theme_advanced_buttons2_add : "separator,forecolor,backcolor",
		theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator",
		theme_advanced_buttons3_add : "emotions,flash,advhr,separator,print,separator,ltr,rtl,separator,fullscreen",
		theme_advanced_toolbar_location : "top",
		theme_advanced_path_location : "bottom",
		content_css : "/example_data/example_full.css",
	    plugin_insertdate_dateFormat : "%Y-%m-%d",
	    plugin_insertdate_timeFormat : "%H:%M:%S",
		extended_valid_elements : "hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
		external_link_list_url : "example_data/example_link_list.js",
		external_image_list_url : "example_data/example_image_list.js",
		flash_external_list_url : "example_data/example_flash_list.js",
		file_browser_callback : "mcFileManager.filebrowserCallBack",
		theme_advanced_resize_horizontal : false,
		theme_advanced_resizing : true,
		apply_source_formatting : true,
		
		filemanager_rootpath : "<?=realpath($BF . 'userfiles/'.$_SESSION['chrEmail'].'/')?>",
		filemanager_path : "<?=realpath($BF . 'userfiles/'.$_SESSION['chrEmail'].'/')?>",
		filemanager_extensions : "gif,jpg,htm,html,pdf,zip,txt,doc,xls",
		relative_urls : true,
		document_base_url : "http://retailmarketing.apple.com/"
	});
</script>

<script language="javascript" type="text/javascript">
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrCalendarEvent', "You must enter a Calendar Event Name");
		total += ErrorCheck('dBegin', "You must enter a Date");
		total += ErrorCheck('idCalendarType', "You must choose a Calendar Type");

		if(total == 0) { document.getElementById('idForm').submit(); }
	}

function colortest() {
	document.getElementById('colortest').style.backgroundColor = document.getElementById('chrColorBG').value;
	document.getElementById('colortest').style.color = document.getElementById('chrColorText').value;
}
/*
// Adds a day to the script.... 
function changedate() {
	var dates = document.getElementById('dBegin').value.split("/");
	var dt = new Date(dates[2],(dates[0] - 1),dates[1]);
	dt.setDate(dt.getDate() + 1)
	var m = (dt.getMonth() + 1);
	if(m < 10) { m = "0"+m; }
	var d = dt.getDate();
	if(d < 10) { d = "0"+d; }
	var y = (dt.getYear() + 1900);
	
	document.getElementById('dEnd').value = m+"/"+d+"/"+y
}
*/
function changedate() {
	document.getElementById('dEnd').value = document.getElementById('dBegin').value;
}

function showreoccuring() {
	if(document.getElementById('bReoccur').checked) {
		document.getElementById('reoccur').style.display = "";
	} else {
		document.getElementById('reoccur').style.display = "none";
	}
}
</script>
<?
	include($BF. 'calendar/includes/top.php');
?>

	<div style='margin: 10px;'>

	<div class="AdminTopicHeader">Add Event</div>
	<div class="AdminInstructions">Please remember to use FireFox for editing this section. This does not affect the user viewing the page with Safari.</div>

	<form id='idForm' name='idForm' method='post' action=''>

		<div id='errors'></div>
		<table class='OneColumn' cellspacing="0" cellpadding="0">
			<tr>
				<td style='padding: 5px;'>
					
					<div class='formHeader'>Event Name <span class='Required'>(Required)</span></div>
					<div><input type='text' size='40' maxlength='80' name='chrCalendarEvent' id='chrCalendarEvent' /></div>
					<br />

					<div class='formHeader'>Dates <span class='Required'>(Required)</span></div>
					<table cellspacing="0" cellpadding="0" style='margin: 3px 0 0 -5px; padding: 0;'>
						<tr>
							<td class='formHeader' style='width: 200px;'>Begin Date</td>
							<td class='formHeader'>End Date</td>
						</tr>
						<tr>
							<td><input type='text' size='10' maxlength='10' name='dBegin' id='dBegin' value='<?=date('m/d/Y',strtotime($_REQUEST['dBegin']))?>' onchange="changedate()" /> <span class='Required'>(mm/dd/yyyy)</span></td>
							<td><input type='text' size='10' maxlength='10' name='dEnd' id='dEnd' value='<?=date('m/d/Y',strtotime($_REQUEST['dBegin']))?>' /> <span class='Required'>(mm/dd/yyyy)</span></td>
						</tr>
						<tr>
							<td class='formHeader'>Begin Time</td>
							<td class='formHeader'>End Time</td>
						</tr>
						<tr>
							<td><input type='text' size='10' maxlength='10' name='tBegin' id='tBegin' value='<?=(isset($_REQUEST['tBegin']) && $_REQUEST['tBegin'] != "" ? date('H:i',strtotime($_REQUEST['tBegin'])) : '')?>' /> <span class='Required'>(ex: 14:00)</span></td>
							<td><input type='text' size='10' maxlength='10' name='tEnd' id='tEnd' value='<?=(isset($_REQUEST['tBegin']) && $_REQUEST['tBegin'] != "" ? date('H:i',strtotime($_REQUEST['tBegin']." + 1 hour")) : '')?>' / > <span class='Required'>(ex: 14:00)</span></td>
						</tr>
					</table>
					
					<div class='formHeader'><input checked type='checkbox' name='bAllDay' /> All Day Event</div>
					<div class='formHeader'><input type='checkbox' name='bReoccur' id='bReoccur' onchange="showreoccuring()" /> Re-Occuring Event</div>
					<div id='reoccur' style='display: none;'>
						<table style='margin-left: -5px;'>
							<tr>
								<td>Repeat</td>
								<td>
									<select name='chrReoccur' id='chrReocurr'>
										<option value='day'>Daily</option>
										<option value='week'>Weekly</option>
										<option value='month'>Monthly</option>
										<option value='year'>Yearly</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>Until</td>
								<td><input type='text' size='10' maxlength='10' name='dRepeatEnd' id='dRepeatEnd' /> <span class='Required'>(mm/dd/yyyy)</span></td>
							</tr>
						</table>
					</div>
					<br />

					<div class='formHeader'>Calendar Type <span class='Required'>(Required)</span></div>
						<select name='idCalendarType' id='idCalendarType'>
							<option value=''>-Choose Type-</option>
<?	$q = "SELECT ID,chrCalendarType FROM CalendarTypes WHERE !bDeleted ORDER BY chrCalendarType";
	$results = database_query($q,"getting calendar types"); 
	while($row = mysqli_fetch_assoc($results)) {
?>
							<option value='<?=$row['ID']?>'><?=$row['chrCalendarType']?></option>
<?	} ?>
						</select>
					</div>
					<br />
					<br />

					<div class='formHeader'>Page Information</div>
					<div><textarea name="txtContent" id='txtContent' cols="115" rows="40" wrap="virtual" class="formField"></textarea></div>
					<br />
		
					<input type='button' name='SubmitAddSection' value='Save New Section' onclick='error_check()' />
					<input type='hidden' name='from' value='<?=$_REQUEST['from']?>'>
					<input type='hidden' name='dBegin' value='<?=$_REQUEST['dBegin']?>'>
				</td>
			</tr>
		</table>
		
		
	</form>

	</div>		
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom.php');
?>