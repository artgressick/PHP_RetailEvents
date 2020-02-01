#!/usr/local/php5/bin/php
<? 
	$BF = '';
	$auth_not_required = 1;
	require('_lib2.php');
	
	function strip($val) {
		return stripslashes(stripslashes(stripslashes($val)));
	}
	
	$today = date('Y-m-d',strtotime("-1 day"));
	$yearMonth = date('Y-m-',strtotime("today"));
	
	/*
		---------------------------
			This is for the US
		---------------------------
	*/
	
	$q = "SELECT DISTINCT Recaps.ID, Recaps.*, Events.chrTitle, chrDescription, DATE_FORMAT(dDate, '%W, %M %D, %Y') as dDateFormat, DATE_FORMAT(tBegin, '%l:%i %p') as tBeginFormat, DATE_FORMAT(tEnd, '%l:%i %p') as tEndFormat, Stores.chrName as chrName, 
			Stores.chrEmail, EventTypes.chrName as chrEventType
			FROM Events
			JOIN Stores ON Stores.ID=Events.idStore
			JOIN EventTypes ON EventTypes.ID=Events.idEventType
			JOIN Recaps ON Recaps.idEvent = Events.ID
			LEFT JOIN EventTypeNames ON EventTypeNames.idEventType=Events.idEventType
			WHERE Recaps.bEmailSent!='1' AND Stores.id NOT IN (140,166,167,168) AND !Stores.bDeleted AND Events.bApproved";
	
	$result = database_query($q,"Getting Recaps that have not been e-mailed");

	$q = "SELECT ID, idEvent, chrName
			FROM RecapImages
			ORDER BY ID";

	$recapimages = database_query($q, "Grabbing All Images");
	
	while ($row = mysqli_fetch_assoc($recapimages)) {
		$images[$row['idEvent']][$row['ID']]['chrName'] = $row['chrName'];
	}

	
	// The To: Emails.
	$to      = 'manderson@apple.com,t.nguyen@apple.com,arackoff@apple.com,robynj@apple.com,aamundsen@apple.com,makiko.fujikawa@apple.com,drone@techitsolutions.com,cschrader@apple.com,tucker@apple.com,dibene@apple.com,gee.r@euro.apple.com';
//	$to	   = 'jsummers@techitsolutions.com'; //Test Email
	
    $subject    = 'Recaps US/CA/UK Events, Submitted '. $today;
	$headers 	= "MIME-Version: 1.0\r\n";
	$headers 	.= "Content-Type: text/html; charset=UTF-8\r\n";
   	$headers    .= 'From: retailevents@apple.com' . "\r\n";
	$Message    = ""; // Daily approved email.

	$cnt = 0;
	

	while($row = mysqli_fetch_assoc($result)) {
		$cnt++; // counter to check if anything went into the message
		
		database_query("UPDATE Recaps SET bEmailSent='1' WHERE ID=". $row['ID'], "update Recaps bSentEmail");
	

	// This just adds spaces into the email.
	$tmpMsg = "";
	if($cnt > 1) {
  		$tmpMsg = "
-------------------------------------------------------------------------------------------------------------		
"; }
				

		$tmpMsg2 = "<p style='background: ". (($cnt%2)==0 ? '' : '') ."; padding: 3px;'>
". $row['chrName'] ."<br />
". $row['chrEventType'] ."<br />
". $row['chrTitle'] ."<br />
". $row['dDateFormat'] . " - ". $row['tBeginFormat'] . " to ". $row['tEndFormat'] ."<br />
Event Description: ". $row['chrDescription'] ."<br />
Event Recap: ". $row['txtFeedback'] ."<br />
Customer Attendance: ". $row['chrAttendance'] ."<br />
Incremental Daily Sales: ". $row['chrSales'] ."<br />
Overall Event Success (1-10): ". $row['rSuccess'] ."<br />
Customer Enjoyment (1-10): ". $row['rEnjoy'] ."<br />
What to do differently: ". $row['txtImproveEvent'] ."<br />
Technical Issues: ". $row['txtIssues'] ."<br />
Host again: ". $row['chrRehost'] ."<br />
Additional staffing required: ". $row['chrAddstaff'] ."<br />
How Promoted: ". $row['chrApple'] ."<br />
How presenter promoted: ". $row['chrPresenters']. ($row['chrOtherExplain'] != '' ? ": ". $row['chrOtherExplain'] : "")."<br />
Store Contact: ". $row['chrEmail'] . "<br />".
($row['txtSpecialThanks'] != '' ? "Special Thanks: ". $row['txtSpecialThanks']."<br />" : "") .
($row['txtCustQuote'] != '' ? "Customer Quote: ". $row['txtCustQuote']."<br />" : "").
($row['txtSpecQuote'] != '' ? "Mac Specialist Quote: ". $row['txtSpecQuote']."<br />" : "") ."
Submitted By: ". $row['chrFirstName'] .", ". $row['chrLastName'] .", ". $row['chrEmail']."<br />";

if(isset($images[$row['idEvent']])) {
	$tmpMsg2 .= "Images: <br />";
	foreach ($images[$row['idEvent']] as $image) {
		$tmpMsg2 .= "<a href='http://retailmarketing.apple.com/recapimages/".$image['chrName']."' target='_blank'>http://retailmarketing.apple.com/recapimages/".$image['chrName']."</a><br />";
	}
}
	$tmpMsg2 .= "</p>";



               $Message    .= $tmpMsg . $tmpMsg2 ; // Daily approved email.
    }
	
	// If no information was added, don't send the email
	if($cnt > 0) {
		if(mail($to, $subject, $Message, $headers)) {
			echo "US Mail Send Successful to: ". $to ."\n";
		} 
		
?>
	<div>
		<div>US<br />--</div>
		<div>To: <?=$to?></div>
		<div>To: <?=$subject?></div>
		<div>To: <?=$Message?></div>
		<div>To: <?=$headers?></div>
	</div>
<?
	}
?>
