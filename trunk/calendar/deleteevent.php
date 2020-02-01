<?php
	$BF = '../';
	$title = 'Delete Calendar Event';
	$curPage = "";
	require($BF. '_lib2.php');

	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }

	if(!isset($_REQUEST['key']) || strlen($_REQUEST['key']) != 40) {
		header('Location: '.$BF.'calendar/error.php'); 
		die();
	}

	$info = fetch_database_query("SELECT * FROM CalendarEvents WHERE CalendarEvents.chrKey='". $_REQUEST['key'] ."'","getting delete info");
	
	// if this is a form submission. Any required field may be used to check.
	if(count($_POST)) {
		if($_POST['bDeleteAll'] == "on") {
			database_query("UPDATE CalendarEvents SET bDeleted=1 WHERE chrSeries='". $info['chrSeries'] ."'","mass delete");
		} else {
			database_query("UPDATE CalendarEvents SET bDeleted=1 WHERE chrKEY='". $_POST['key'] ."'","delete single");
		}
			
		header("Location: ". $_SESSION['calSection'] .".php?dDate=". $_SESSION['calDate']);
		die();
	}

	include($BF. 'includes/meta2.php');
	include($BF. 'calendar/includes/top.php');
?>

	<div style='margin: 10px;'>

	<div class="AdminTopicHeader">Delete Event: <span class='Required' style='font-size: 14px'>(<?=$info['chrCalendarEvent']?>)</span></div>
	<div class="AdminInstructions">Deleting these events will cause them to permanently dissapear.  Please make sure this is what you want to do.</div>

	<form id='idForm' name='idForm' method='post' action=''>
	
	<div style='border: 1px solid gray; padding: 10px; text-align: center;'>
		
		<div align="center" style='margin: 0 auto; width: 320px; border: 1px solid red; text-align: center; padding: 10px;'>	
			<div style='font-size: 20px; background: red; color: white; margin: -10px -10px 10px; padding: 5px;'>!! WARNING !!</div>
			<div>You are about to permanently erase the event:</div>
			<br />
			<div><span style="color: blue;">Name:</span> <?=$info['chrCalendarEvent']?></div>
			<div><span style="color: blue;">Date:</span> <?=date('m/d/Y',strtotime($info['dBegin']))?></div>
<?	if($info['chrSeries'] != "") { ?>
			<div style='margin-top: 10px;'><span style="color: blue;">This is a Multi-Day Event.</span></div>
			<div><input type='checkbox' name='bDeleteAll' /> Delete all Events in Series</div>
<?	} ?>

			<div style='margin-top: 10px;'><input type='submit' value='Delete Event<?=($info['chrSeries'] != "" ? '(s)' : '')?>'>
			<input type='hidden' name='key' value='<?=$_REQUEST['key']?>' />
			<input type='hidden' name='from' value='<?=$_REQUEST['from']?>' />
		</div>
		
	</div>
		
	</form>

	</div>		
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom.php');
?>