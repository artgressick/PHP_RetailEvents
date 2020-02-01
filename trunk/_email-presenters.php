#!/usr/local/php5/bin/php
<? 
	$BF = "";
	$auth_not_required = 1;
	require('_lib2.php');
	
	$today = date('Y-m-d',strtotime("-1 day"));
	$yearMonth = date('Y-m-',strtotime("today"));
	
	/*
		---------------------------
			This is for the US
		---------------------------
	*/
	
	$q = "SELECT DISTINCT Events.ID, Events.chrTitle, Events.chrDescription, DATE_FORMAT(dDate, '%M %D, %Y') as dDate2, DATE_FORMAT(tBegin, '%l:%i %p') as tBegin, 
			Stores.chrName, Stores.ID as idStore, Presenters.chrName as chrPresenter, Presenters.chrCompanyLabel, EventTypes.chrName as chrEventType, EventTypes.ID as idEventType
		FROM Events
		JOIN StoreMonths ON StoreMonths.idStore=Events.idStore AND dDate LIKE '". $yearMonth ."%'
		JOIN Stores ON Stores.ID=StoreMonths.idStore
		JOIN EventPresenters ON EventPresenters.intEventSeries=Events.intSeries
		JOIN Presenters ON Presenters.ID=EventPresenters.idPresenter
		JOIN EventTypes ON Events.idEventType=EventTypes.ID
		WHERE StoreMonths.enStatus='Approved' AND bEmailSent!=1 AND Events.bApproved='1' AND (Stores.chrCountry='US' OR Stores.chrCountry='CA')
		ORDER BY dDate";
							
	$result = database_query($q, "Getting list of store presenters");
	
	// The To: Emails.
    $to      = 'abarney@apple.com,cschrader@apple.com,manderson@apple.com,aamundsen@apple.com,t.nguyen@apple.com, pegw@ix.netcom.com,robynj@apple.com,tucker@apple.com,sapsford@apple.com';
	$TPEto   = 'manderson@apple.com, wpfeffer@apple.com, rjackman@apple.com,t.nguyen@apple.com, drichardson@apple.com, sapsford@apple.com';
	$HPEto   = 'manderson@apple.com,arackoff@apple.com,makiko.fujikawa@asia.apple.com,t.nguyen@apple.com';
	//$to      = 'dnitsch@techitsolutions.com';
	//$TPEto   = 'dnitsch@techitsolutions.com';
	//$HPEto   = 'dnitsch@techitsolutions.com';
	
    $subject    = 'Approved US/CA Events, '. $today;
   	$TPEsubject = 'Approved US/CA WOAM/Author Events, '. $today;
	$HPEsubject = 'Approved HP Events, '. $today;

	$headers 	.= "MIME-Version: 1.0\r\n";
	$headers 	.= "Content-Type: text/html; charset=UTF-8\r\n";
   	$headers    .= 'From: retailevents@apple.com' . "\r\n";

	$Message    = ""; // Daily approved email.
	$TPEMessage = ""; // Third-party events email message
	$HPEMessage = ""; // High profile events email message

	$sameEvent = 0;
	$cnt = 0;
	
	$TPEarray = array(2,3);
	$HPEarray = array(61,77,59,141,32,34);
	
	while($row = mysqli_fetch_assoc($result)) {
		$cnt++; // counter to check if anything went into the message
		
		database_query("UPDATE Events SET bEmailSent=1 WHERE ID=". $row['ID'], "update Events bSentEmail");
	
       if($sameEvent == $row['ID']) {
	    	$cnt--;
			$tmpMsg = "<br />Additional Presenter: ". decode($row['chrPresenter']) . ($row['chrCompanyLabel'] != "" ? ", ". decode($row['chrCompanyLabel']) : "");

				$Message    .= $tmpMsg ; // Daily approved email.
				$TPEMessage .= (in_array($row['idEventType'],$TPEarray) ? $tmpMsg  : ''); // Third-party events email message
				$HPEMessage .= (in_array($row['idStore'],$HPEarray) ? $tmpMsg  : ''); // High profile events email message
		} else {

		        // This just adds spaces into the email.
                if($cnt > 1) {
  	    			$tmpMsg = "</p>
-------------------------------------------------------------------------------------------------------------	<br />
";
		        }
				
		$sameEvent = $row['ID'];

		$tmpMsg2 = "<p style='background: ". (($cnt%2)==0 ? '' : '') ."; padding: 3px;'>
Apple Store, ". $row['chrName'] ."<br />
Date and Time: ". $row['dDate2'] . " - ". $row['tBegin'] ."<br />
Title: ". $row['chrTitle'] ."<br />
Event Type: ". $row['chrEventType'] ."<br />
Description: ". $row['chrDescription'] ."<br />
Presenter: ". $row['chrPresenter'] . ($row['chrCompanyLabel'] != "" ? ", ". decode($row['chrCompanyLabel']) : "");

               $Message    .= $tmpMsg . $tmpMsg2 ; // Daily approved email.
               $TPEMessage .= (in_array($row['idEventType'],$TPEarray) ? ($TPEMessage != "" ? $tmpMsg . $tmpMsg2 :  $tmpMsg2 )  : ''); // Third-party events email message
               $HPEMessage .= (in_array($row['idStore'],$HPEarray) ? ($HPEMessage != "" ? $tmpMsg . $tmpMsg2 :  $tmpMsg2 )  : ''); // High profile events email message
         }
    }
	
	// If no information was added, don't send the email
	if($cnt > 0) {
		if(mail($to, $subject, $Message, $headers)) {
			echo "US Mail Send Successful to: ". $to ."\n";
		}
		
		if($TPEMessage != "") {
			if(mail($TPEto, $TPEsubject, $TPEMessage, $headers)) {
				echo "US Third Party Mail Send Successful to: ". $TPEto ."\n";
			}
		}
		if($HPEMessage != "") {
			if(mail($HPEto, $HPEsubject, $HPEMessage, $headers)) {
				echo "US High Profile Mail Send Successful to: ". $HPEto ."\n";
			}
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
