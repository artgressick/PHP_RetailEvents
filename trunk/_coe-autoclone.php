#!/usr/local/php5/bin/php
<? 
	$auth_not_required = 1;
	require('_lib.php');
	
	$date = date('Y-m-',strtotime("+30 days"));
	$intMonth = date('m',strtotime("today"));
	$intYear = date('Y',strtotime("today"));
		
	$days_this_month = idate('t', mktime(0, 0, 0, $intMonth, 1, $intYear)) . "<br />";	
		
	$q = "SELECT Stores.ID,
			(SELECT count(ID) FROM Events WHERE dDate like '". $date ."%' AND idStore=Stores.ID) as intEventCount
			FROM Stores
			WHERE ID=65";
	$result = mysql_query($q);
	
	while($store = mysql_fetch_assoc($result)) {
		if($store['intEventCount'] == 0) {
	
			$query = "SELECT Events.*, DATE_FORMAT(dDate, '%e') AS intMonthDay, TIME_FORMAT(tBegin, '%H') AS intStartHour, bApproved, EventTypes.idEventCategory,
				TIME_FORMAT(tBegin, '%M0') AS intStartMinute, EventTypes.chrName AS chrEventTypeName, EventTypes.chrStyleClass, tBegin as tBegin2, tEnd as tEnd2,
				DATE_FORMAT(tBegin,'%l:%i %p') as tBegin, DATE_FORMAT(tEnd,'%l:%i %p') as tEnd,
				(SELECT count(idEvent) FROM EventPresenters WHERE EventPresenters.idEvent=Events.ID) as intPresenters
				FROM Events
				JOIN EventTypes ON EventTypes.ID=idEventType
				WHERE idStore='" . $store['ID'] . "' AND MONTH(dDate)='" . $intMonth . "' AND YEAR(dDate)='" . $intYear . "'
					AND !EventTypes.bDeleted AND EventTypes.bShow AND EventTypes.bEditorReview=0
				ORDER BY dDate,tBegin,tEnd,chrTitle ASC";
			$events = do_mysql_query($query, 'get events');
		
			$oldSeries = 0;
		
			$td = $intYear . '-' . $intMonth . '-01';
			if(($intMonth + 1) > 12) { $nm = ($intYear + 1) . '-01-01'; }
			else { $nm = $intYear . '-' . ($intMonth + 1) . '-01'; }
			
			$d1 = date('N',strtotime($td));
			$d2 = date('N',strtotime($nm));
			if($d1 < $d2) {
				$offset = $d2 - $d1;
			} else if($d1 == $d2) { 
				$offset = 0;
			} else if($d1 > $d2) {
				$offset = (0 - ($d1 - $d2));
			}
			
			while($row = mysql_fetch_assoc($events)) {
				if($row['idEventCategory'] == 1) {
					
					$newDate = split('-',$row['dDate']);
				
					if(($newDate[1] + 1) > 12) { 	
						$year = $newDate[0] + 1; 
						$month = '01';
					} else {
						$year = $newDate[0];
						$month = $newDate[1] + 1;	
					}
				
					$inBounds = $newDate[2] - $offset;
					if($inBounds > 0 && $inBounds <= $days_this_month) {
			
						do_mysql_query("INSERT INTO Events SET
								chrTitle='" . encode($row['chrTitle']) . "',
								chrDescription='" . encode($row['chrDescription']) . "',
								idStore='" . $row['idStore'] . "',
								idEventType='" . $row['idEventType'] . "',
								idEventTitle='". $row['idEventTitle'] ."',
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
								do_mysql_query("UPDATE Events SET intSeries='" . $newID. "' WHERE ID='" . $newID . "'","updating the series");
							} else {
								$oldSeries = '';
							}
						}
					} // inbounds check
				}
			}
			
		
		}
	}

?>
