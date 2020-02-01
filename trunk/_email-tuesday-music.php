#!/usr/local/php5/bin/php
<? 
	$BF = "";
	$auth_not_required = 1;
	require('_lib2.php');
	
	//dtn:  Currently Not used.  Wondering if this will be needed in the future?
	/*
	function changeTags($val) {
		$val = str_replace('<em>','<span style="font-style: italic;">',$val);
		$val = str_replace('</em>','</span>"',$val);
		$val = str_replace('<strong>','<span style="font-weight: bold;">',$val);
		$val = str_replace('</strong>','</span>"',$val);
		$val = str_replace('<u>','<span style="text-decoration: underline;">',$val);
		$val = str_replace('</u>','</span>"',$val);
		return $val;
	}
	*/
		
	$today = date('Y-m-d',strtotime("today"));
	
	/*	---------------------------
			This is for the US
		---------------------------
	*/
	
	$q = "SELECT DISTINCT Events.ID, Events.chrTitle, Events.chrDescription, DATE_FORMAT(dDate, '%M %D, %Y') as dDate2, DATE_FORMAT(tBegin, '%l:%i %p') as tBegin, 
			Stores.chrCity, Stores.chrState, Stores.chrName, Stores.ID as idStore, EventTypes.chrName as chrEventType, EventTypes.ID as idEventType
		FROM Events
		JOIN Stores ON Stores.ID=Events.idStore
		JOIN EventTypes ON Events.idEventType=EventTypes.ID
		WHERE (EventTypes.ID=21 OR EventTypes.ID=4) AND dDate > now() AND Stores.id NOT IN (140,166,167,168) AND !Stores.bDeleted AND bApproved
		ORDER BY dDate";
							
	$result = database_query($q, "Getting list of store presenters");
	
	// The To: Emails.
    $to      = 'manderson@apple.com,jrose@apple.com,makiko.fujikawa@asia.apple.com,t.nguyen@apple.com,abarney@apple.com,jroth@apple.com';
	//$to		   = 'dnitsch@techitsolutions.com';
	
    $subject    = 'Upcoming US Apple Retail Music Events, '. $today;
	$headers 	.= "MIME-Version: 1.0\r\n";
	$headers 	.= "Content-Type: text/html; charset=UTF-8\r\n";
   	$headers    .= 'From: retailevents@apple.com' . "\r\n";

	$Message    = ""; // Daily approved email.
	
	$cnt = 0;	
	$tmpMsg = "";
	while($row = mysqli_fetch_assoc($result)) {
		if($cnt++ > 0) { 
			$tmpMsg = "</p>
-------------------------------------------------------------------------------------------------------------<br />
";
		}
		
		$tmpMsg2 = "<p style='background: ". (($cnt%2)==0 ? '' : '') ."; padding: 3px;'>
Apple Store, ". $row['chrName'] ."<br />
". $row['chrCity'] .", ". $row['chrState'] ."<br />
". $row['dDate2'] . " - ". $row['tBegin'] ."<br />
Event Type: ". $row['chrEventType'] ."<br />
". $row['chrTitle'] ."<br />
". $row['chrDescription'];

	         $Message    .= $tmpMsg . $tmpMsg2 ; // Daily approved email.
    }
	
	// If no information was added, don't send the email
	if($cnt > 0) {
		if(mail($to, $subject, $Message, $headers)) {
			echo "Thursday Music Emails Send Successful to: ". $to ."\n";
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
