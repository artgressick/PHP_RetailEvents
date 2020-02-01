<?php
	$BF = '../';
	$title = 'Edit Calendar Event';
	$curPage = "";
	require($BF. '_lib2.php');

	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }

	if(!isset($_REQUEST['key']) || strlen($_REQUEST['key']) != 40) {
		header('Location: '.$BF.'calendar/error.php'); 
		die();
	}
	
	$info = fetch_database_query("SELECT * FROM CalendarEvents WHERE chrKey='". $_REQUEST['key'] ."'","Getting info");
	// if this is a form submission
	if(isset($_POST['chrCalendarEvent'])) {

		$table = 'CalendarEvents';
		$mysqlStr = '';
		$audit = '';

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCalendarEvent',$info['chrCalendarEvent'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs_date($mysqlStr,'dBegin',$info['dBegin'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs_time($mysqlStr,'tBegin',$info['tBegin'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs_time($mysqlStr,'tEnd',$info['tEnd'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'idCalendarType',$info['idCalendarType'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'txtContent',$info['txtContent'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs_checkbox($mysqlStr,'bAllDay',$info['bAllDay'],$audit,$table,$_POST['id']);

		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }

		/*
		if($info['chrReoccur'] != "" && $_POST['bReoccur'] != "on") {
			database_query("DELETE FROM CalendarEvents 
				WHERE dBegin > '". $info['dBegin'] ."' AND chrSeries='". $info['chrSeries'] ."'
			","delete all but first");
			database_query("UPDATE CalendarEvents SET chrSeries='',chrReoccur='' WHERE chrSeries='". $info['chrSeries'] ."'","update");
		} else if($info['dEnd'] != date('Y-m-d',strtotime($_POST['dRepeatEnd']))) {
		
			if($info['dEnd'] > date('Y-m-d',strtotime($_POST['dRepeatEnd']))) {
				database_query("DELETE FROM CalendarEvents 
					WHERE dBegin > '". date('Y-m-d',strtotime($_POST['dRepeatEnd'])) ."' AND chrSeries='". $info['chrSeries'] ."'
				","delete all but first");
			} else if($info['dEnd'] < date('Y-m-d',strtotime($_POST['dRepeatEnd']))) {

				$day = fetch_database_query("SELECT MAX(dBegin) as dBegin WHERE chrSeries='". $info['chrSeries'] ."' AND dBegin <= '". date('Y-m-d',strtotime($_POST['dRepeatEnd'])) ."'","getting max day");

				$lday = strtotime(date('Y-m-d',strtotime($_POST['dRepeatEnd'])));
				$fday = strtotime($day['dBegin']);
			
				while($fday <= $lday) {
					$q = "INSERT INTO CalendarEvents SET
							bAllDay='". ($_POST['bAllDay'] == "on" ? 1 : 0) ."',
							chrCalendarEvent='" . encode($_POST['chrCalendarEvent']) . "',
							dBegin='" . date('Y-m-d',$fday) . "',
							dEnd='" . date('Y-m-d',$lday) . "',
							tBegin='" . date('H:i:s',strtotime($_POST['tBegin'])) . "',
							tEnd='" . date('H:i:s',strtotime($_POST['tEnd'])) . "',
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

		}
		*/

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
	
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrCalendarEvent', "You must enter a Calendar Event Name.");
		total += ErrorCheck('dBegin', "You must enter a Date");
		if(total == 0) { document.getElementById('idForm').submit(); }
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
<script language="javascript" type="text/javascript">
function colortest() {
	document.getElementById('colortest').style.backgroundColor = document.getElementById('chrColorBG').value;
	document.getElementById('colortest').style.color = document.getElementById('chrColorText').value;
}
</script>
<?
	include($BF. 'calendar/includes/top.php');
?>

	<div style='margin: 10px;'>

	<div class="AdminTopicHeader">Edit Event</div>
	<div class="AdminInstructions">Please remember to use FireFox for editing this section. This does not affect the user viewing the page with Safari.</div>

	<form id='idForm' name='idForm' method='post' action=''>

		<div id='errors'></div>
		<table class='OneColumn' cellspacing="0" cellpadding="0">
			<tr>
				<td style='padding: 5px;'>
					
					<div class='formHeader'>Event Name <span class='Required'>(Required)</span></div>
					<div><input type='text' size='40' maxlength='80' name='chrCalendarEvent' id='chrCalendarEvent' value='<?=$info["chrCalendarEvent"]?>' /></div>
					<br />

					<div class='formHeader'>Dates <span class='Required'>(Required)</span></div>
					<table cellspacing="0" cellpadding="0" style='margin: 3px 0 0 -5px; padding: 0;'>
						<tr>
							<td class='formHeader' style='width: 200px;'>Begin Date</td>
							<td class='formHeader'>&nbsp;</td>
						</tr>
						<tr>
							<td><input type='text' size='10' maxlength='10' name='dBegin' id='dBegin' value='<?=date('m/d/Y',strtotime($info['dBegin']))?>' onchange="changedate()" /> <span class='Required'>(mm/dd/yyyy)</span></td>
							<td></td>
						</tr>
						<tr>
							<td class='formHeader'>Begin Time</td>
							<td class='formHeader'>End Time</td>
						</tr>
						<tr>
							<td><input type='text' size='10' maxlength='10' name='tBegin' id='tBegin' value='<?=(date('H:i',strtotime($info['tBegin'])))?>' /> <span class='Required'>(ex: 14:00)</span></td>
							<td><input type='text' size='10' maxlength='10' name='tEnd' id='tEnd' value='<?=(date('H:i',strtotime($info['tEnd'])))?>' / > <span class='Required'>(ex: 14:00)</span></td>
						</tr>
					</table>
					
					<div class='formHeader'><input<?=($info['bAllDay'] == 1 ? ' checked="checked"' : '')?> type='checkbox' name='bAllDay' /> All Day Event</div>
					<div class='formHeader'><input<?=($info['chrSeries'] != "" ? ' checked="checked"' : '')?> type='checkbox' name='bReoccur' id='bReoccur' onchange="showreoccuring()" /> Re-Occuring Event</div>
					<div id='reoccur' style='display: <?=($info['chrSeries'] != "" ? 'normal' : 'none')?>;'>
						<table style='margin-left: -5px;'>
							<tr>
								<td>Repeat</td>
								<td>
									<select name='chrReoccur' id='chrReocurr'>
										<option<?=($info['chrReoccur'] == 'day' ? ' selected="selected"' : '')?> value='day'>Daily</option>
										<option<?=($info['chrReoccur'] == 'week' ? ' selected="selected"' : '')?> value='week'>Weekly</option>
										<option<?=($info['chrReoccur'] == 'month' ? ' selected="selected"' : '')?> value='month'>Monthly</option>
										<option<?=($info['chrReoccur'] == 'year' ? ' selected="selected"' : '')?> value='year'>Yearly</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>Until</td>
								<td><input type='text' size='10' maxlength='10' name='dRepeatEnd' id='dRepeatEnd' value='<?=(date('m/d/Y',strtotime($info['dEnd'])))?>' /> <span class='Required'>(mm/dd/yyyy)</span></td>
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
							<option<?=($info['idCalendarType'] == $row['ID'] ? ' selected="selected"' : '')?> value='<?=$row['ID']?>'><?=$row['chrCalendarType']?></option>
<?	} ?>
						</select>
					</div>
					<br />
					<br />

					<div class='formHeader'>Page Information</div>
					<div><textarea name="txtContent" id='txtContent' cols="115" rows="40" wrap="virtual" class="formField"></textarea></div>
					<br />

		
					<input type='button' name='updateInfo' value='Update Information' onclick='error_check()' />
					<input type='hidden' name='key' value='<?=$_REQUEST['key']?>'>
					<input type='hidden' name='id' value='<?=$info['ID']?>'>
					<input type='hidden' name='from' value='<?=$_REQUEST['from']?>'>
				</td>
			</tr>
		</table>
		
		
	</form>

	</div>		
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom.php');
?>