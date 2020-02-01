<?php
	$BF = '../';
	$title = 'Mass Approve Calendar';
	
	// Checking request variables
	($_REQUEST['idStore'] == "" ? ErrorPage() : "" );
	require($BF. '_lib2.php');

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

	include($BF. 'includes/meta2.php');
	
	$split = split('-',$_REQUEST['idStore']);

	$storeName = fetch_database_query("SELECT chrName FROM Stores WHERE ID=". $split[0],"Getting Store Name");	
	
	// get the current month
	$intCurrentMonth = idate('m');
	$intCurrentYear = idate('Y');
	$current_month = (($intCurrentYear-2000)*12)+$intCurrentMonth-1;

	$intYear = 2000 + floor($split[1] / 12);
	$intMonth = ($split[1] % 12)+1;
	
	$monthName = date('F',strtotime($intYear .'-'. $intMonth .'-01'));
			
	$edit_mode = true;
	$approval_mode = false;
	$this_month_status = '';
	$is_retailevents_user = $_SESSION['idType'];
	$is_my_store = !$is_retailevents_user;

		
	$first_weekday = idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear)) . "<br />";
	$first_display_day = 1-$first_weekday . "<br />";
	$days_this_month = idate('t', mktime(0, 0, 0, $intMonth, 1, $intYear)) . "<br />";
	$number_of_weeks = ceil(($days_this_month+idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear))) / 7) . "<br />";



	$query = "SELECT Events.*, EventTypes.idEventCategory, EventTypeNames.chrEventTitle,
		DATE_FORMAT(dDate, '%W, %M %D') AS chrMonth, TIME_FORMAT(tBegin, '%H:%i %p') AS intStartHour, EventTypes.chrStyleClass, 
		DATE_FORMAT(dDate,'%e') as intDayOfMonth, TIME_FORMAT(tBegin, '%H') AS intStartHour, DATE_FORMAT(tBegin,'%l:%i %p') as tBegin, DATE_FORMAT(tEnd,'%l:%i %p') as tEnd
		FROM Events 
		JOIN EventTypes ON EventTypes.ID=Events.idEventType  AND (idEventCategory=1 OR idEventCategory=2)
		LEFT JOIN EventTypeNames ON EventTypeNames.ID=Events.idEventTitle
		WHERE idStore='" . $split[0] . "' AND MONTH(dDate)='" . $intMonth . "' AND YEAR(dDate)='" . $intYear . "' 
		ORDER BY dDate,tBegin ASC
	";
	$events = database_query($query, 'get events');



	//dtn:  This is the basic push to the retail events team.  This will approve events for PEGGY / Tommy (as of 4/26/2007) to review before official push

		if(@$_REQUEST['Confirmed'] == 1) {
			
			$intYear = 2000 + floor($_REQUEST['intDate'] / 12);
			$intMonth = ($_REQUEST['intDate'] % 12)+1;
			$date = $intYear .'-'.($intMonth < 10 ? '0'.$intMonth : $intMonth).'-%';

			$result = database_query("SELECT coeLastSubmit FROM Stores WHERE ID='" . $_REQUEST['idStore'] . "'", 'coeLastSubmit date');
			$coeCheck = mysqli_fetch_assoc($result);

			// Set all dissaproved by editor things back to 0
			$q = "UPDATE Events SET bDissaproved=0 WHERE idStore='". $_REQUEST['idStore'] ."' AND dDate LIKE '". $date ."'";
			database_query($q, 'update the stores');

			$attempt=1;
			if($coeCheck['coeLastSubmit'] == '') {
				$coeLastSubmit = '1:'.strftime('%B:%Y', mktime(0, 0, 0, $intMonth, 1, $intYear));
				database_query("UPDATE Stores SET coeLastSubmit='" . $coeLastSubmit . "' WHERE ID='" . $_REQUEST['idStore'] . "'", 'replace coeLastSubmit');
				echo "update made";
			} else {
				list($attempt,$mnt,$yr) = split(":",$coeCheck['coeLastSubmit']);
				if(strftime('%B %Y', mktime(0, 0, 0, $intMonth, 1, $intYear)) == $mnt . " " . $yr)
				{
					$attempt++;
					$coeLastSubmit = $attempt . ':' . strftime('%B:%Y', mktime(0, 0, 0, $intMonth, 1, $intYear));
					database_query("UPDATE Stores SET coeLastSubmit='" . $coeLastSubmit . "' WHERE ID='" . $_REQUEST['idStore'] . "'", 'replace coeLastSubmit');
				} else {
					$coeLastSubmit = '1:'.strftime('%B:%Y', mktime(0, 0, 0, $intMonth, 1, $intYear));				
				}
			}

			$Headers = "From: " . $_SESSION['chrFirstName'] . " " . $_SESSION['chrLastName'] . " <" . $_SESSION['chrEmail'] . ">\n\n";
			$Subject = $attempt . " - COE ". strftime('%B %Y', mktime(0, 0, 0, $intMonth, 1, $intYear)) . ", " . $store['chrName'] . " to Approve."; 

			$Message = "Requesting review for the ". $store['chrName'] . " store's Calendar of Events.

	Check this store with the following link:

	http://retailmarketing.apple.com/quickin.php?d=". base64_encode("idStore=" . $_REQUEST['idStore'] . "&intDate=" . $_REQUEST['intDate']);

			//mail('retailevents@apple.com', $Subject, $Message, $Headers);

			$result = database_query("REPLACE INTO StoreMonths SET idStore='" . $_REQUEST['idStore'] . "', intYear='" . $intYear . "', intMonth='" . $intMonth . "'", 'insert storemonth');


			if ($result) {
				header('Location: eventlist.php?intDate=' . $_REQUEST['intDate']);
				exit();


			} else {
				$error_messages[] = 'There was a database error.';
			}

		}





	/* If a post is found, do the mass approve checks.  This sends the information to KATHI */

	if(count($_POST)) {
		$daysChanged = "";
		$daysChangedCnt = 0;
	
		/* This sets everything for that event calendar in the DB as either approved or dissaproved, depening on what was checked */
		while($row = mysqli_fetch_assoc($events)) {
			if($row['bApproved'] == 0 && $row['bApproved'] != "") {
				$daysChanged .= $row['chrTitle'] .": ". $row['chrMonth'] ."\n\n";
				$daysChangedCnt++;
			}
			database_query("UPDATE Events SET bApproved=" . (isset($_POST['chk'.$row['ID']]) ? 0 : 1) . " WHERE ID='" . $row['ID'] . "'", "update record");
		}
				
		/* This is a count to see how many records were displayed as dissaproved */
		$q = "SELECT count(bApproved) as intCount
			FROM Events 
			JOIN EventTypes ON EventTypes.ID=idEventType  AND (idEventCategory=1 OR idEventCategory=2)
			WHERE idStore='" . $split[0] . "' AND MONTH(dDate)='" . $intMonth . "' AND YEAR(dDate)='" . $intYear . "' AND bApproved=0
			ORDER BY dDate ASC";
		$check = mysqli_fetch_assoc(database_query($q, "checking disapproves"));
				
				
		// If there were no disapproved stores, mark the calendar as Approved, otherwise mark it as Rejected.
		$storeMonthChk = database_query("SELECT * FROM StoreMonths WHERE idStore='" . $split[0] . "' AND intMonth='" . $intMonth . "' AND intYear='" . $intYear . "'","check store months");
		if(mysqli_num_rows($storeMonthChk) > 0) {
			database_query("UPDATE StoreMonths SET enStatus='" . ($check['intCount'] == 0 ? 'Approved' : 'Rejected') . "' WHERE idStore='" . $split[0] . "' AND intMonth='" . $intMonth . "' AND intYear='" . $intYear . "'","update StoreMonths"); 
		} else {
			database_query("INSERT INTO StoreMonths SET enStatus='" . ($check['intCount'] == 0 ? 'Approved' : 'Rejected') . "',idStore='" . $split[0] . "',intMonth='" . $intMonth . "',intYear='" . $intYear . "'","update StoreMonths"); 
		}
		
		//Send e-mail to store if calander has been rejected
		if ($check['intCount'] > 0) {
		
			//Grab Store Information 
			$q = "SELECT ID, chrName, chrEmail
					FROM Stores
					WHERE ID=". $split[0];
			$tmpstoreinfo = fetch_database_query($q, "Getting Store Information for E-mail");
			
			// The To: Emails.
			$today = date('Y-m-d',strtotime("today"));
				
			$to      	= $tmpstoreinfo['chrEmail']; //Store E-mail Address
			
			$subject    = 'COE Calendar Rejected for '. date('F Y', strtotime('01-'.$intMonth.'-'.$intYear)) .'!';  //Subject
	
			$headers = 'To: '.$tmpstoreinfo['chrEmail'] . "\r\n";
			$headers .= 'From: retailevents@apple.com' . "\r\n";
			$headers .= 'Bcc: programmers@techitsolutions.com' . "\r\n";

			$Message    = "Please be advised that your submitted Calendar of Events for ". date('F Y', strtotime('01-'.$intMonth.'-'.$intYear)) ." has been rejected.". "\r\n" . "\r\n" .
						  "Please login and correct any items and resubmit the Calendar"  . "\r\n" . "\r\n" .
						  "http://retailmarketing.apple.com/" . "\r\n" . "\r\n" .
						  "Retail Marketing ".$today; //Message to store
			
			mail($to, $subject, $Message, $headers);
		}
	
	
				// If there was no dissaprovals....
		if($check['intCount'] == 0) {					   
			$query = "SELECT Events.*, EventTypes.idEventCategory, txtEventDescription, 
				DATE_FORMAT(dDate, '%W, %M %D') AS chrMonth, TIME_FORMAT(tBegin, '%H:%i %p') AS intStartHour, EventTypes.chrStyleClass
				FROM Events 
				JOIN EventTypes ON EventTypes.ID=Events.idEventType  AND (idEventCategory=1 OR idEventCategory=2)
				LEFT JOIN EventTypeNames ON EventTypeNames.ID=Events.idEventTitle
				WHERE idStore='" . $split[0] . "' AND MONTH(dDate)='" . $intMonth . "' AND YEAR(dDate)='" . $intYear . "' 
				ORDER BY dDate,tBegin ASC
			";
			$event = database_query($query, 'get events INSIDE');

# ---------------------------------------------------------------------------------------
# This is the connection to the Corporate Web Servers (Per Chad Little clittle@apple.com)
# ---------------------------------------------------------------------------------------

/*	We want to move all of the approved events to the corporate web server so that Kathy Rose
	can review the approved events. We have to enter the information into a table called "retail.RetailEvents"
	We also have to set a switch value in a table called "retail.stores.bUploaded = 1" everytime we upload
	any new events.*/
					
			
			$attachments = array();
			$attachment_events = array();
		
			$q = "INSERT INTO RetailEvents (ID,idStore,dDate,tBegin,tEnd,idEventType,chrTitle,chrDescription,intSeries) VALUES";
			$cnt=0;
			while($row = mysqli_fetch_assoc($event)) {
				$q .= ($cnt++ == 0 ? '' : ',')."(". $row['ID'] . ",". $row['idStore'] . ",'". $row['dDate'] . "','". $row['tBegin'] . "',
				'". $row['tEnd'] . "',". $row['idEventType'] . ",'". addslashes(decode(($row['chrEventTitle'] == "" ? $row['chrTitle'] : $row['chrEventTitle']))) . "','". ($row['chrDescription'] != "" ? addslashes(decode($row['chrDescription'])) : addslashes(decode($row['txtEventDescription']))) . "','". $row['intSeries'] ."')";
				$idStore = $row['idStore'];
				
				if($row['chrImageName'] != "") { $attachments[] = $BF.'eventimages/'.$row['chrImageName']; $attachment_events[] = $row['ID']; }
			}
			
			if($connection = @mysqli_connect('weblab11.apple.com', 'techit', 'dollap')) {
				if(@mysqli_select_db($connection, 'retail')) {
					mysqli_query($connection,"DELETE FROM RetailEvents WHERE idStore=". $idStore ." AND dDate like '". $intYear ."-". ($intMonth < 10 ? '0'.$intMonth : $intMonth) ."%'");
					mysqli_query($connection, $q);
					mysqli_query($connection, "UPDATE stores SET bUploaded=1 WHERE idAltStore=". $split[0]);
				}
			}			
			
			
			
		    $headers = 'From: retailevents@apple.com' . "\r\n";
			$to = 'programmers@techitsolutions.com,retailevents@apple.com';
			//$to = 'dnitsch@techitsolutions.com';
			if($daysChangedCnt > 0) {

				$msg2 = "For the month of ". $monthName .", ". $daysChangedCnt ." events have been changed since the calendar for the store ". $storeName['chrName'] ." was last approved.  The following events have changed:\n\n" . $daysChanged;
		
				mail($to, decode('COE-Changed Events - '. $intMonth .'/'. $intYear .' - '. $storeName['chrName']), decode($msg2), $headers);				
			} else {
				$msg = "The Calendar of Events for ". $storeName['chrName'] ." has been approved.";
				mail(' programmers@techitsolutions.com', decode('Approved COE - '. $intMonth .'/'. $intYear .' - '. $storeName['chrName']), decode($msg), $headers);				
			}

			/*
			if(count($attachments) > 0) {
				// This is added so that the Pear module can differentiate between HTML emails and plain text emails.
				include('Mail.php');
				include('Mail/mime.php');
				
				mb_internal_encoding('UTF-8');	
				$crlf = "\n";
			
				$mime = new Mail_mime($crlf);
				$hdrs = array('From'    => 'retailevents@apple.com',
							  'Subject' => "Approved Special Events with Images"
							  );
		
				$mime->_build_params['html_charset'] = "UTF-8";
				
				$Message = "<p>The Following image(s) were uploaded to be added to the official site on the right hand side.  In order of attachment, they are for the following events: </p>";
				
				foreach($attachment_events as $ae) {
					$evnt = mysqli_fetch_assoc(database_query("SELECT chrTitle, chrImageName, Stores.chrName as chrStore, DATE_FORMAT(Events.dDate,'%M %D, %Y') as dDate 
						FROM Events JOIN Stores ON Stores.ID=Events.idStore WHERE Events.ID=".$ae,"getting event info"));
					$Message .= " ". $evnt['chrStore'] . ": ". $evnt['chrTitle'] ." (". $evnt['dDate'] .").  -- Image Name: ". $evnt['chrImageName'] . "<br />";
				}
				
				foreach($attachments as $at) {
					$mime->addAttachment($at);
				}
				
				$mime->setHTMLBody($Message);
					
				$body = $mime->get();
				$hdrs = $mime->headers($hdrs);
				
				$mail =& Mail::factory('mail');
				$mail->send($to, $hdrs, $body);		
				
			}			
			*/
			
			header("Location: eventlist.php?intDate=". $split[1]);
			die();
		} else {
			header("Location: ma-reasons.php?idStore=" . $split[0] . "&intMonth=" . $intMonth . "&intYear=" . $intYear);
			die();
		}						
	}


	
include($BF. 'includes/top_admin2.php');	
?>

					<div class="AdminTopicHeader">COE Calendar Approval</div>
					<div class="AdminInstructions">Choose a store from the drop down menu above to review its calendar before further approval.</div>

					<table class="AdminUtilityBar">
						<tr>
						<form method='get' action='' id='Form'>
							<td valign="center">Dates:
								<select name='idStore' onchange='this.form.submit();'>
									<option value="">-Select Store-</option>
<?
	$rows = database_query("SELECT StoreMonths.*, Stores.chrName as chrStore, ((intYear-2000)*12)+intMonth-1 AS intDate
					FROM StoreMonths 
					JOIN Stores ON Stores.ID=StoreMonths.idStore
					WHERE enStatus='Submitted'
					ORDER BY intYear DESC, intMonth DESC,Stores.chrName ASC
					", 'get current months');
				
	while($row = mysqli_fetch_assoc($rows)) { ?>
							<option value='<?=$row['idStore']?>-<?=$row['intDate']?>' <?=($_REQUEST['idStore'] == $row['idStore'].'-'.$row['intDate'] ? 'selected="selected"' : '')?>><?=strftime('%B %Y', mktime(0, 0, 0, $row['intMonth'], 1, $row['intYear']))?> (<?=$row['chrStore']?>)</option>
<?
	}
?>
								</select>								
								<input type='submit' value='Go' />
							</td>						
						</form>
						</tr>
						</table>
						<table class="NormalTable">
						<tr>
						<td>
							
<form action='' method='post'>

	<table id="calendarmonth" cellpadding='0' cellspacing='0' style='width: 100%;'>
		<tr class="days">
			<th style='width: 14.25%'>Sunday</th>
			<th style='width: 14.25%'>Monday</th>
			<th style='width: 14.25%'>Tuesday</th>
			<th style='width: 14.25%'>Wednesday</th>
			<th style='width: 14.25%'>Thursday</th>
			<th style='width: 14.25%'>Friday</th>
			<th style='width: 14.25%'>Saturday</th>
		</tr>
		<tr>
<?	$weekDayInt = 0;
	 $intMonthDay = 0;
	 $tmpDate = '';
	
	while($first_display_day != 1) { ?>
			<td>&nbsp;</td>
<?			$weekDayInt++;
			$first_display_day += 1;
	} ?>
<?	while($row = mysqli_fetch_assoc($events)) { 
		while($tmpDate != $row['dDate']) { 
				
			if($weekDayInt == 7) { $weekDayInt = 0; ?> </tr><tr> <? } 
			$weekDayInt++; 
				
			$intMonthDay++; 
			$tmpDate = $intYear . '-' . ($intMonth < 10 ? '0'.$intMonth : $intMonth) . '-' . ($intMonthDay < 10 ? '0'.$intMonthDay : $intMonthDay);
				
?>
				</td><td style='vertical-align: top;'><div class='top'><?=$intMonthDay?></div>
	<? }
		
		?>
			<div class="<?=$row['chrStyleClass']?> info" style='height: 100px;'>			
					<div style='background: <?=($row['chrStyleClass'] == 'ws' ? '#73BD75' : '#FEFFAF')?>; color: black; padding-top: 2px; margin: 0 1px 0 0;'><input type='checkbox' name='chk<?=$row['ID']?>' style='margin-top: -1px;' />Disapprove</div>
				<?=	(($row['bApproved'] == 0) && ($row['bApproved'] != "") ? "<div style='color: red'>New/Altered</div>" : '')?>
				<a href="../events/editevent.php?id=<?=$row['ID']?>&idStore=<?=$split[0]?>&intDate=<?=$split[1]?>" title="<?=stripslashes($row['chrDescription'])?>" class="<?=$row['chrStyleClass']?>"><?=stripslashes(($row['chrEventTitle'] == "" ? $row['chrTitle'] : $row['chrEventTitle']))?></a>
				<p class="start"><?=$row['tBegin']?> to</p>
				<p class='end'><?=$row['tEnd']?></p>
		
			</div>

<?		}

	while($intMonthDay < $days_this_month) { 
	
	 if($weekDayInt == 7) { $weekDayInt = 0; ?> </tr><tr> <? } 
			$weekDayInt++; 
				
			$intMonthDay++; 
			$tmpDate = $intYear . '-' . ($intMonth < 10 ? '0'.$intMonth : $intMonth) . '-' . ($intMonthDay < 10 ? '0'.$intMonthDay : $intMonthDay);
				
?>
				</td><td style='vertical-align: top;'><div class='top'><?=$intMonthDay?></div>
<? }?>
		</td>
		</tr>
	</table>

<div style='padding: 0 10px 10px 0;'>

	<input name="SubmitMAL" type="submit" value="Push To Web" style='margin-top: 0px;' />
	<input name="Review" type="button" value="Push To Copy Editor" style='margin-top: 0px;' onclick='location.href="?idStore=<?=$split[0]?>&amp;intDate=<?=$split[1]?>&amp;Confirmed=1";' />
	<input name='idStore' type='hidden' value='<?=$_REQUEST['idStore']?>' />
	<input name='storeID' type='hidden' value='<?=$split[0]?>' />

</form>
	<table width="100%" id="calendarlegend" style='margin-top: 0.8em;'>
		<tbody>
			<tr>
				<td rowspan="2">Calendar legend:</td>
				<td class="ws">Workshops</td>
				<td class="pd">Business Day</td>
				<td class="ss">Studio Series</td>
				<td class="mm">Made on a Mac</td>
				<td class="wm">Works on a Mac</td>
				</tr>
			<tr>
				<td class="pw">Pro Workshops</td>
				<td class="sn">School Night</td>
				<td class="se">Special Events</td>
				<td class="gu">Genius Unplugged</td>
				<td class="ug">User Groups</td>
				</tr>
			</tbody>
		</table>
	</td>
	</tr>
	</table>
</div>

<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>