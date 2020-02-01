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
	
	$daynum = date('w',strtotime("today"));
	
	if ($daynum == "1") { $intDays = "-4"; }
	else if ($daynum == "4") { $intDays = "-3"; }
	
	$q = "SELECT DISTINCT Events.ID, Stores.chrName AS chrStore, DATE_FORMAT(concat(Events.dDate,' ',Events.tBegin),'%M %D, %Y %l:%i %p') as dtEvent, chrTitle, EventTypes.chrName AS chrType, EventTypeNames.chrEventTitle AS chrCanned
		FROM Events
		JOIN EventTypes ON Events.idEventType=EventTypes.ID
		JOIN Stores ON Events.idStore=Stores.ID
		LEFT JOIN EventTypeNames ON Events.idEventTitle=EventTypeNames.ID
		WHERE DATE_FORMAT(Events.dtCreated,'%Y-%m-%d') >= DATE_FORMAT(adddate(now(),".$intDays."),'%Y-%m-%d') 
		AND Stores.idLocalization=1 AND (lower(chrTitle) LIKE '%business%' OR lower(EventTypes.chrName) LIKE '%business%' OR lower(EventTypeNames.chrEventTitle) LIKE '%business%')
		ORDER BY chrStore, dtCreated, Events.dDate, Events.tBegin";
							
	$result = database_query($q, "Getting list Business Workshops");
	
	// The To: Emails.
    $to      = 'aamundsen@apple.com,t.nguyen@apple.com';
	//$to		   = 'dnitsch@techitsolutions.com';
	
    $subject    = 'Newly Created Business Events, '. $today;
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
Apple Store: ". $row['chrStore'] ."<br />
       Date: ". $row['dtEvent'] ."<br />
      Title: ". ($row['chrTitle'] != "" ? $row['chrTitle'] : $row['chrCanned']) ."<br />
       Type: ". $row['chrType'];

	         $Message    .= $tmpMsg . $tmpMsg2 ; // Daily approved email.
    }
	
	// If no information was added, don't send the email
	if($cnt > 0) {
		if(mail($to, $subject, $Message, $headers)) {
			echo "New Business Events Email Send Successful to: ". $to ."\n";
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
