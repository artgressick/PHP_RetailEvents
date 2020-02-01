<?php
	$BF = '../';
	$title = 'View Calendar Event';
	$curPage = "";
	require($BF. '_lib2.php');

	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }

	if(!isset($_REQUEST['key']) || strlen($_REQUEST['key']) != 40) {
		header('Location: '.$BF.'calendar/error.php'); 
		die();
	}
	
	$info = fetch_database_query("SELECT CalendarEvents.*, chrCalendarType
		FROM CalendarEvents 
		JOIN CalendarTypes ON CalendarTypes.ID=CalendarEvents.idCalendarType
		WHERE CalendarEvents.chrKey='". $_REQUEST['key'] ."'
	","Getting info");

	include($BF. 'includes/meta2.php');
	include($BF. 'calendar/includes/top.php');
?>

	<div style='margin: 10px;'>

	<div class="AdminTopicHeader">View Event</div>
	<table cellspacing="0" cellpadding="0" class="AdminInstructions" style='width: 880px;'>
		<tr>
			<td>
				Unless you created this event, you only have view access to it.
			</td>
<? if($info['idUser'] == $_SESSION['idUser']) { ?>
			<td style='text-align: right; padding: 0; margin: 0;'>
				<input type='button' value='Edit Event' onclick='window.location.href="editevent.php?dBegin=<?=$_REQUEST['dBegin']?>&key=<?=$_REQUEST['key']?>&from=<?=$_REQUEST['from']?>"' />
				<input type='button' value='Delete Event' onclick='window.location.href="deleteevent.php?dBegin=<?=$_REQUEST['dBegin']?>&key=<?=$_REQUEST['key']?>&from=<?=$_REQUEST['from']?>"' />
			</td>
<?	} ?>
		</tr>
	</table>

	<form id='idForm' name='idForm' method='post' action=''>

		<div id='errors'></div>
		<table class='OneColumn' cellspacing="0" cellpadding="0">
			<tr>
				<td style='padding: 5px;'>
					
					<div class='formHeader'>Event Name</div>
					<div><?=$info["chrCalendarEvent"]?></div>
					<br />

					<div class='formHeader'>Date</div>
					<div><?=date('m/d/Y',strtotime($info["dBegin"]))?></div>
					<br />
<? if($info['bAllDay']) { ?>
					<div class='formHeader'>This is an All Day Event</div>
					<br />
<?	} else { ?>
					<table cellspacing="0" cellpadding="0" style='margin-left: -5px; padding: 0;'>
						<tr>
							<td class='formHeader' style='width: 150px;'>Begin Time <span class='Required'>(Required)</span></td>
							<td class='formHeader'>End Time <span class='Required'>(Required)</span></td>
						</tr>
						<tr>
							<td><?=date('H:i',strtotime($info["tBegin"]))?></td>
							<td><?=date('H:i',strtotime($info["tEnd"]))?></td>
						</tr>
					</table>
					<br />
<? } ?>

<? 	if($info['dBegin'] != $info['dEnd']) { ?>
					<div class='formHeader'>This is an Multiple Day Event</div>
					<br />
<?	} ?>
					<div class='formHeader'>Calendar Type</div>
					<div><?=$info["chrCalendarType"]?></div>
					<br />

					<div class='formHeader'>Page Information</div>
					<div><?=$info["txtContent"]?></div>
		
				</td>
			</tr>
		</table>
		
		
	</form>

	</div>		
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom.php');
?>