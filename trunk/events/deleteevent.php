 <?php
	require_once('../_lib.php');

	if ($_POST['three_day_check'] == 2) {
	$_REQUEST = $_POST;
	}
	
	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root */
	if($_SESSION['idType'] != 1 && !in_array($_REQUEST['idStore'],$_SESSION['intStoreList'])) {
		$_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: ' . BASE_FOLDER . "nopermission.php"); die();
	}	

	$intYear = 2000 + floor($_REQUEST['intDate'] / 12);
	$intMonth = ($_REQUEST['intDate'] % 12)+1;

	
	$q = "SELECT chrTitle, chrDescription, txtEventDescription, txtRejection, Stores.chrName, StoreMonths.enStatus,
			DATE_FORMAT(dDate,'%M %D, %Y') as dDate, DATE_FORMAT(tBegin,'%l:%i %p') as tBegin, DATE_FORMAT(tEnd,'%l:%i %p') as tEnd
		FROM Events 
		JOIN Stores ON Stores.ID=Events.idStore
		LEFT JOIN EventTypeNames ON EventTypeNames.idEventType=Events.idEventType AND Events.chrTitle=EventTypeNames.chrEventTitle
		LEFT JOIN StoreMonths ON StoreMonths.idStore=Events.idStore AND intMonth='". $intMonth ."' AND intYear='". $intYear ."'
		WHERE intSeries='" . $_REQUEST['intSeries'] . "'";
	if($_REQUEST['eraseall'] == "") {
		$q .= " AND Events.ID=" . $_REQUEST['idEvent'];
	}	 
		
	$events = do_mysql_query($q,"getting event");


	if(@$_REQUEST['Confirmed'] && @$_REQUEST['three_day_check'] != 1) {


		if ($_REQUEST['three_day_check'] == 2) {
		
			$q = "SELECT chrName FROM Stores WHERE ID=".$_REQUEST['idStore'];
			
			$store = mysql_fetch_assoc(do_mysql_query($q, "Getting Store Info"));

				$msg = $_POST['message'];
				$subject = 'URGENT! Store '. $store['chrName'] .' has Deleted an Event within 72 hours of the event.';
				$headers = 'From: retailevents@apple.com' . "\r\n";
				$to = 'retailevents@apple.com';
				mail($to, $subject, "Apple Store ".$store['chrName']." has Deleted an event that is due to start within 72 hours.\n\nMessage from Store:\n\n" .$msg, $headers);
		}


		if($_REQUEST['eraseall'] == '') {
			do_mysql_query("DELETE FROM Events WHERE ID='" . $_REQUEST['idEvent'] . "'","delete single event");
			
			$msg = "";
			while($row = mysql_fetch_assoc($events)) { 
				if($row['enStatus'] == 'Approved') { 
					$msg .= $row['chrName']. ", Event: '". $row['chrTitle'] ."' on ". $row['dDate'] .".\n";
				}
			}
			if($msg != "") {
			    $subject = 'Deleted Events After an Approval';
				$headers = 'From: retailevents@apple.com' . "\r\n";
				$to = 'appbugreports@techitsolutions.com';
				mail($to, $subject, decode("The following event(s) were deleted after the calendar was submitted and approved: \n\n" .$msg), $headers);


				// This connects to the official DB and Erases this event IF the option has been approved
				if($connection = @mysql_connect('weblab11.apple.com', 'techit', 'dollap')) {
					if(@mysql_select_db('retail', $connection)) {
						do_mysql_query("DELETE FROM RetailEvents WHERE ID=". $_REQUEST['idEvent'],"Delete single event from official DB");
						do_mysql_query("UPDATE Stores SET bUploaded=1 WHERE idAltStore=". $_REQUEST['idStore'],"update bUploaded in Stores");

					}
				}
			}
					
		} else if ($_REQUEST['intSeries'] != "" || $_REQUEST['intSeries'] != 0) {
			do_mysql_query("DELETE FROM Events WHERE intSeries='" . $_REQUEST['intSeries'] . "' AND idStore='".$_REQUEST['idStore']."'","delete all events");
			
			$msg = "";
			while($row = mysql_fetch_assoc($events)) { 
				if($row['enStatus'] == 'Approved') { 
					$msg .= $row['chrName']. ", Event: '". $row['chrTitle'] ."' on ". $row['dDate'] .".\n";
				}
			}
			if($msg != "") {
			    $subject = 'Deleted Events After an Approval';
				$headers = 'From: retailevents@apple.com' . "\r\n";
				$to = 'retailevents@apple.com';
				mail($to, $subject, decode("The following event(s) were deleted after the calendar was submitted and approved: \n\n" .$msg), $headers);

				// This connects to the official DB and Erases this series IF the option has been approved
				if($connection = @mysql_connect('weblab11.apple.com', 'techit', 'dollap')) {
					if(@mysql_select_db('retail', $connection)) {
						do_mysql_query("DELETE FROM RetailEvents WHERE intSeries='". $_REQUEST['intSeries']."' AND idStore='".$_REQUEST['idStore']."'","Delete series of events from official DB");
						do_mysql_query("UPDATE Stores SET bUploaded=1 WHERE idAltStore=". $_REQUEST['idStore'],"update bUploaded in Stores");
					}
				}

			}
		}	
		header("Location: " . BASE_FOLDER . "events/index.php?idStore=" . $_REQUEST['idStore'] . "&intDate=" . $_REQUEST['intDate']);
		die();
	}

// Set the title, and add the doc_top
$title = "Delete Event(s)";
require(BASE_FOLDER . 'docpages/doc_meta_events.php');
include(BASE_FOLDER . 'docpages/doc_top_events.php');

if ($_REQUEST['three_day_check'] != 1 && $_REQUEST['three_day_check'] != 2) {

// Set variable for 72 hour checking
$future = strtotime("+3 days") ." ";
$present = strtotime("now");

?>
<div style='padding: 10px;'>

	<div class="AdminTopicHeader">Delete Event(s)</div>
				<div class="AdminDirections" style='width: 870px;'>To remove this (or all) workshops/events from the list, click on the "Delete" button.</div>
				
					<div class='Question'>Are you sure you want to delete the following workshop(s)/event(s): </div>
					<ul>
<?	while($row = mysql_fetch_assoc($events)) { ?>
						<div style='margin-top: 10px;'><strong><?=$row['chrTitle']?></strong></div>
						<div><?=$row['dDate']?> - <?=$row['tBegin']?> to <?=$row['tEnd']?></div>
						<div>Description: <?=($row['chrDescription'] != "" ? $row['chrDescription'] : $row['txtEventDescription'])?></div>
						<?=($row['txtRejection'] != '' ? "<div style='color: red;'>Reason for rejection: " . $row['txtRejection'] . "</div>" : "")?>
						<? $eventdate = strtotime($row['dDate']);
						if ($eventdate > $present && $eventdate < $future) { $three_day_check = 1; }
						?>
						
<?	} ?>
					</ul>
			<div class='FormButtons'>
			<input type='button' onclick='location.href="?intDate=<?=$_REQUEST['intDate']?>&idStore=<?=$_REQUEST['idStore']?>&amp;intSeries=<?=$_REQUEST['intSeries']?>&amp;Confirmed=1&amp;idEvent=<?=$_REQUEST['idEvent']?>&amp;eraseall=<?=$_REQUEST['eraseall']?>&amp;three_day_check=<?=$three_day_check?>";' value='Delete' style='margin-top: 10px;' />
			</div>
</div>
		
		</div>
		</div>
	</div>
<?

} else {
?>
<div style='padding: 10px;'>
<form method='post' action='' enctype="multipart/form-data">
	<div class="AdminTopicHeader">Urgent Notice</div>
		<input type='hidden' name='idEvent' value='<?=$_REQUEST['idEvent']?>' />
		<input type='hidden' name='idStore' value='<?=$_REQUEST['idStore']?>' />
		<input type='hidden' name='intDate' value='<?=$_REQUEST['intDate']?>' />
		<input type='hidden' name='intSeries' value='<?=$_REQUEST['intSeries']?>' />
		<input type='hidden' name='eraseall' value='<?=$_REQUEST['eraseall']?>' />
		<input type='hidden' name='Confirmed' value='1' />
		<input type='hidden' name='three_day_check' value='2' />
		
				<div class="AdminDirections" style='width: 870px;'>CAUTION!  One or more of the events being deleted is due to begin within 72 hours, Please provide details to send to the Administrator</div>
				<div class='Question'><textarea name="message" id="message" wrap="virtual" cols="70" rows="20">Please Push Changes Immediately.</textarea></div>
				<div class='FormButtons'>
				<input type='submit' value='Submit' style='margin-top: 10px;' />
				</div>
</form>
</div>
<? } ?>
<?

include(BASE_FOLDER . 'docpages/doc_bottom.php');

?>