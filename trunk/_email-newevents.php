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
	
	$q = "SELECT E.ID, E.chrTitle, E.chrDescription, E.dDate, S.chrName, DATE_FORMAT(E.dDate, '%W, %M %D, %Y') as dDateFormat
			FROM Events AS E
			JOIN Stores AS S ON E.idStore=S.ID
			WHERE E.bApproved AND !S.bDeleted AND E.idStore IN (32,141,222)
			ORDER BY dDate";
	
	$result = database_query($q,"Getting New Events for Stores");

	// The To: Emails.
	$to      = 'mmarcotte@apple.com';
//	$to	   = 'jsummers@techitsolutions.com'; //Test Email
	
    $subject    = 'Upcoming Apple Store, SoHo, Fifth Avenue and West 14th Street Events ';
	$headers 	= "MIME-Version: 1.0\r\n";
	$headers 	.= "Content-Type: text/html; charset=UTF-8\r\n";
   	$headers    .= 'From: retailevents@apple.com' . "\r\n";
	$Message    = ""; // Daily approved email.

	$cnt = 0;
	

	while($row = mysqli_fetch_assoc($result)) {
		$cnt++; // counter to check if anything went into the message
		
		database_query("UPDATE Events SET bNewEmailSent='1' WHERE ID=". $row['ID'], "update event bNewEmailSent");
	

	// This just adds spaces into the email.
	$Message = "";
	if($cnt > 1) {
  		$Message .= "
-------------------------------------------------------------------------------------------------------------		
"; }
				

		$Message .= "<p style='background: ". (($cnt%2)==0 ? '' : '') ."; padding: 3px;'>
Store: ". $row['chrName'] ."<br />
Title: ". $row['chrTitle'] ."<br />
Description: ". $row['chrDescription'] ."<br />
Date: ". $row['dDateFormat'] ."</p>";
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
