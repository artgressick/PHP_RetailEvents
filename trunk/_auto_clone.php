#!/usr/local/php5/bin/php
<? 
	$BF = "";
	$auth_not_required = 1;
	require('_lib2.php');
	
	$chkyear = date('Y',strtotime("+1 month"));
	$chkmonth = date('m',strtotime("+1 month"));
	$intYear = date('Y',strtotime("today"));
	$intMonth = date('m',strtotime("today"));
		
	$days_this_month = idate('t', mktime(0, 0, 0, $intMonth, 1, $intYear)) . "<br />";
	$number_of_weeks = ceil(($days_this_month+idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear))) / 7) . "<br />";
		
	$storeList = array();
		
	$q = "SELECT Stores.ID, Stores.chrName,
		(SELECT count(Events.ID) FROM Events WHERE Events.dDate LIKE '". $chkyear ."-". $chkmonth ."-%' AND Events.idStore=Stores.ID) as intEvent
		FROM Stores
		WHERE (Stores.chrCountry='US' OR Stores.chrCountry='CA')
		having intEvent=0 
		ORDER BY Stores.chrName
		";
	$storeresult = database_query($q, "Not Approved ");
	
	$cnt = 0;
	while($stores = mysqli_fetch_assoc($storeresult)) {
	
		// Getting Events
		$query = "SELECT Events.*, DATE_FORMAT(dDate, '%e') AS intMonthDay, TIME_FORMAT(tBegin, '%H') AS intStartHour, bApproved, EventTypes.idEventCategory,
			TIME_FORMAT(tBegin, '%M0') AS intStartMinute, EventTypes.chrName AS chrEventTypeName, EventTypes.chrStyleClass, tBegin as tBegin2, tEnd as tEnd2,
			DATE_FORMAT(tBegin,'%l:%i %p') as tBegin, DATE_FORMAT(tEnd,'%l:%i %p') as tEnd,
			(SELECT count(idEvent) FROM EventPresenters WHERE EventPresenters.idEvent=Events.ID) as intPresenters
			FROM Events
			JOIN EventTypes ON EventTypes.ID=idEventType
			WHERE idStore='" . $stores['ID'] . "' AND MONTH(dDate)='" . $intMonth . "' AND YEAR(dDate)='" . $intYear . "'
				 AND EventTypes.bShow AND EventTypes.bEditorReview=0
			ORDER BY dDate,tBegin,tEnd,chrTitle ASC";
		$events = database_query($query, 'get events');
	
		$oldSeries = 0;
	
		$td = $intYear . '-' . $intMonth . '-01';
		if(($intMonth + 1) > 12) { $nm = ($intYear + 1) . '-01-01'; }
		else { $nm = $intYear . '-' . ($intMonth + 1) . '-01'; }

		//echo 'td: ' . $td . ' -- nm: ' . $nm . '<br/>'; 
		
		$d1 = date('N',strtotime($td));
		$d2 = date('N',strtotime($nm));
		if($d1 < $d2) {
			//echo "1<br>";
			$offset = $d2 - $d1;
		} else if($d1 == $d2) { 
			//echo "2<br>";
			$offset = 0;
		} else if($d1 > $d2) {
			//echo "3<br>";
			$offset = (0 - ($d1 - $d2));
		}
		
		//echo 'd1: ' . $d1 . ' -- d2: ' . $d2 . ' -- offset: '. $offset . '<br/>'; 
	
		while($row = mysqli_fetch_assoc($events)) {
			if($row['idEventCategory'] == 1) {
				
				$newDate = split('-',$row['dDate']);
			
				if(($newDate[1] + 1) > 12) { 	
					$year = $newDate[0] + 1; 
					$month = '01';
				} else {
					$year = $newDate[0];
					$month = $newDate[1] + 1;	
				}
			
				//echo $year . "-" . $month . "-" . $newDate[2] . ' --> ' . $year . "-" . $month . "-" . ($newDate[2] - $offset) . '<br/>';
			
				$inBounds = $newDate[2] - $offset;
				if($inBounds > 0 && $inBounds <= $days_this_month) {
		
					database_query("INSERT INTO Events SET
							chrTitle='" . encode($row['chrTitle']) . "',
							chrDescription='" . encode($row['chrDescription']) . "',
							idStore='" . $row['idStore'] . "',
							idEventType='" . $row['idEventType'] . "',
							dDate='" . $year . "-" . $month . "-" . ($newDate[2] - $offset) . "',
							tBegin='" . $row['tBegin2'] . "',
							tEnd='" . $row['tEnd2'] . "',
							bGiveaway='" . $row['bGiveaway'] . "',
							chrGiveawayProduct='" . encode($row['chrGiveawayProduct']) . "',
							chrGiveawayFrom='" . encode($row['chrGiveawayFrom']) . "',
							fFinalBudget='" . $row['fFinalBudget'] . "',
							chrBudgetQuarter='" . encode($row['chrBudgetQuarter']) . "',
							enEquipmentProvidedBy='" . $row['enEquipmentProvidedBy'] . "',
							enEquipmentAppleSource='" . $row['enEquipmentAppleSource'] . "',
							setMarketingMaterials='" . $row['setMarketingMaterials'] . "',
							intSeries='" . ($oldSeries == $row['intSeries'] ? $newID : '') . "'
					","cloning event");
					
					if($row['intSeries'] != $oldSeries) {
						if($row['intSeries'] != '') {
							$newID = mysql_insert_id();
							$oldSeries = $row['intSeries'];
							database_query("UPDATE Events SET intSeries='" . $newID. "' WHERE ID='" . $newID . "'","updating the series");
						} else {
							$oldSeries = '';
						}
					}
				} // inbounds check
			}
		}
	
		$coeLastSubmit = '1:'.strftime('%B:%Y', mktime(0, 0, 0, $intMonth, 1, $intYear));
		database_query("UPDATE Stores SET coeLastSubmit='" . $coeLastSubmit . "' WHERE ID='" . $stores['ID'] . "'", 'replace coeLastSubmit');
		
		$result = database_query("INSERT INTO StoreMonths SET idStore='" . $stores['ID'] . "', intYear='" . $chkyear . "', intMonth='" . $chkmonth . "', enStatus='Submitted'", 'insert storemonth');
		
		$storeList[] = $stores['chrName'];
	
		$cnt++;
		
		echo $stores['chrName'] . "<br />";
	}
	
	if(count($storeList) > 0) {
		echo "Sending";
		$Headers = "From: retailevents@apple.com\n";
		$Subject = "The following Store(s) have been auto-cloned."; 

		$Message = "The follow ".$cnt." Store(s) were auto cloned and are requesting a review and approval:\n\n";
		
		$Message .= implode("\n",$storeList);
	
		mail('robynj@apple.com,manderson@apple.com,t.nguyen@apple.com,programmers@techitsolutions.com', $Subject, $Message, $Headers);
	}
	
?>
