#!/usr/local/php5/bin/php
<? 
	$BF = '';
	$auth_not_required = 1;
	require('_lib2.php');
	
	$year = date('Y',strtotime("+1 Month"));
	$month = date('m',strtotime("+1 Month"));
		
	$q = "SELECT ID, chrName, chrEmail
		FROM Stores
		WHERE !bDeleted AND chrCountry='US'
			AND ID NOT IN (SELECT ID FROM StoreMonths WHERE idStore=Stores.ID 
			AND intMonth=". $month ." AND intYear=". $year ." AND (enStatus!='Approved' OR enStatus!='Submitted'))
		ORDER BY Stores.chrName
		";
							
	$result = database_query($q, "Not Approved ");
	
    $subject = 'Your Calender of Events needs to be submitted';
    $headers = 'From: retailevents@apple.com' . "\r\n";

	$msg = "";

	while($info = mysqli_fetch_assoc($result)) {
	    $to      = $info['chrEmail'];
		$Message = $info['chrName']. ",\n\nThis is a notice to remind you it is the 20th of ". date("F Y",strtotime("today")) ." and your store has not yet finalized and submitted a Calendar of Events for ". date("F",strtotime("+1 Month")) ." ". $year .".";
		
	
		if(mail($to, $subject, $Message, $headers)) {
			$msg .= "Email was sent to ". $info['chrName'] .".\n";
		}
	}	

	if($msg != '') { 
		$msg2 = "The follow stores did not submit a calendar as of the 20th of ". 	date("F",strtotime("today")) . " for the ". date("F Y",strtotime("+1 Month")) ." COE\n\n" . $msg;
		mail('t.nguyen@apple.com,manderson@apple.com,drone@techitsolutions.com', 'Day 20 COE Reminder List', $msg2, $headers);
		//mail('dnitsch@techitsolutions.com', '10 Day COE Reminder List', $msg2, $headers);
	}
?>
