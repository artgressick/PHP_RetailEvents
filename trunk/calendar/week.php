<?php
	$BF = '../';
	$title = 'Calendar Page';
	$curPage = "";
	require($BF. '_lib2.php');

	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }
	$_SESSION['calSection'] = 'week';
	$_SESSION['calDate'] = @$_REQUEST['dBegin'];

	# Setting up initial variables to be used
	if(!isset($_REQUEST['dDate']) || $_REQUEST['dDate'] == '') {
		$intCurDay = date('d'); 	# int value of current day (ex: 30)
		$intCurMonth = date('m');	# int value of current month (ex: 7)
		$intCurYear = date('Y');	# int value of current year (ex: 2007)

		header("Location: ". $BF ."calendar/day.php?dDate=".$intCurYear.$intCurMonth.$intCurDay);
		die();
	} else {
		$intDay = idate('d',strtotime($_REQUEST['dDate']));
		$intMonth = idate('m',strtotime($_REQUEST['dDate']));
		$intYear = idate('Y',strtotime($_REQUEST['dDate']));

		# Getting the first and last days of the week DB style (yyyy-mm-dd)
		$tmp = idate('w',strtotime($_REQUEST['dDate']));
		$fdow = date('Y-m-d',strtotime($_REQUEST['dDate']." - ". $tmp ." days"));	# First day of week - sunday
		$ldow = date('Y-m-d',strtotime($fdow." + 6 days"));						 	# Last day of week - saturday
	}
	
	$results = database_query("SELECT chrCalendarEvent,dDate,tBegin,tEnd,chrColorBG,chrColorText,
				(SELECT MIN(tBegin) FROM CalendarEvents as CE WHERE CE.dDate BETWEEN '". $fdow ."' AND '". $ldow ."') as earliest,
				(SELECT MAX(tEnd) FROM CalendarEvents as CE WHERE CE.dDate BETWEEN '". $fdow ."' AND '". $ldow ."') as latest
			FROM CalendarEvents
			JOIN CalendarTypes ON CalendarTypes.ID=CalendarEvents.idCalendarType
			WHERE dDate BETWEEN '". $fdow ."' AND '". $ldow ."'
			ORDER BY dDate,tBegin,tEnd
		", "getting events");

	# Used later to find the total number of concurrent events
	$intEvents = mysqli_num_rows($results);  
	
	$b = array(); 		# Begin Time array
	$other = array();	# End Time, Text Color, Background Color array
	$i = 1;				# Counter for Array
	while($row = mysqli_fetch_assoc($results)) {
		$tmpDate = idate('w',strtotime($row['dDate'])) + 1;
		$b[$tmpDate][$i] = $row['tBegin'];
		$other[$tmpDate][$i]['tEnd'] = $row['tEnd'];
		$other[$tmpDate][$i]['chrColorBG'] = $row['chrColorBG'];
		$other[$tmpDate][$i]['chrColorText'] = $row['chrColorText'];
		$i++;
		$earliestTime = $row['earliest'];
		$latestTime = $row['latest'];
	}

	if(count($b)) {
		# Find out what the most amount of events are that happen at the same time.	
		$intMaxEvents = array();
		$i = 1;
		$latest = 0;
		$tmpDate = 1;
		while($tmpDate <= 7) {
			$intMaxEvents[$tmpDate] = 1;
			if(isset($b[$tmpDate])) {			
				while($i <= (count($b[$tmpDate]))) {
					if(isset($b[$tmpDate][$i])) {
						$tmpb = strtotime($b[$tmpDate][$i]);
						$tmpe = strtotime($other[$tmpDate][$i]['tEnd']);
		
						$match = 1;
						$j = 1;
						while($j <= ($i + 1)) {
							if(isset($b[$tmpDate][$j])) { 
								if($i != $j) {
									$testb = strtotime($b[$tmpDate][$j]);
									$teste = strtotime($other[$tmpDate][$j]['tEnd']);
					
									if($testb >= $tmpb && $tmpb <= $teste) { $match++; }
								}
							}
							$j++;
						}
						if($match > $intMaxEvents[$tmpDate]) { $intMaxEvents[$tmpDate] = $match; }
					}
					$i++;
				}
			}
			$tmpDate++;
		}
		$intMaxEventsTotal = array_sum($intMaxEvents);
	} else {
		$earliestTime = "07:00:00";
		$latestTime = "19:00:00";
		$i = 0;
		while($i <= 7) {
			$other[$i] = array();
			$intMaxEvents[$i++] = 1;
		}
		$intMaxEventsTotal = 7;
	}

/*
	echo "<pre>";
	print_r($b);
	print_r($other);
	print_r($intMaxEvents);
	echo "<pre>";

*/
	include($BF. 'includes/meta2.php');

?><link href="<?=$BF?>calendar/includes/calendar.css" rel="stylesheet" type="text/css" /><?

	include($BF. 'calendar/includes/top.php');
?>

	<div style='margin: 10px;'>

	<table cellspacing="0" cellpadding="0" class="calmenubar">
		<tr>
			<td>
				<div class='datetime'><a href='week.php?dDate=<?=date('Ymd',strtotime($_REQUEST['dDate']. " - 1 week"))?>'>&lt;</a> <?=date('F jS',strtotime($fdow))." - ".date('F jS, Y',strtotime($ldow))?> <a href='week.php?dDate=<?=date('Ymd',strtotime($_REQUEST['dDate']. " + 1 week"))?>'>&gt;</a>
			</td>
			<td class='quickjump'>
				Go to: 
<?	$today = date('Y').date('m').date('d'); ?>				
				<select id='goto' name='goto' onchange="window.location.href=this.value">
					<option value=''>-Go To-</option>
					<option value='day.php?dDate=<?=$today?>'>Today's Date</option>
					<option value='week.php?dDate=<?=$today?>'>This Week</option>
					<option value='month.php?dDate=<?=$today?>'>This Month</option>
					<option value='year.php?dDate=<?=$today?>'>This Year</option>
				</select>
			</td>
			<td class='currentdates'>
				<a href='<?=$BF?>calendar/day.php?dDate=<?=$_REQUEST['dDate']?>'>Day</a> | <strong>Week</strong> | <a href='<?=$BF?>calendar/month.php?dDate=<?=$_REQUEST['dDate']?>'>Month</a> | <a href='<?=$BF?>calendar/year.php?dDate=<?=$_REQUEST['dDate']?>'>Year</a>
			</td>
		</tr>
	</table>


	<table cellspacing="0" cellpadding="0" class='calweekday'>
		<tr>
			<th></th>
			<th colspan='<?=$intMaxEvents[1]+1?>'>Sunday</th>
			<th colspan='<?=$intMaxEvents[2]+1?>'>Monday</th>
			<th colspan='<?=$intMaxEvents[3]+1?>'>Tuesday</th>
			<th colspan='<?=$intMaxEvents[4]+1?>'>Wednesday</th>
			<th colspan='<?=$intMaxEvents[5]+1?>'>Thursday</th>
			<th colspan='<?=$intMaxEvents[6]+1?>'>Friday</th>
			<th colspan='<?=$intMaxEvents[7]+1?>'>Saturday</th>
		</tr>
		<tr>
		
<?	$bb = 0;	# Border bottom counter for when we are in the "hour" vs "15 minute" areas
	if(strtotime($earliestTime) < strtotime('07:00:00')) { $time = $earliestTime; } else { $time = "07:00:00"; }
	if(strtotime($latestTime) < strtotime('19:00:00')) { $latestTime = '19:00:00'; }
	
	$tcnt = 0;
	$colCnt = array();
	$used = 0;
	while($time != $latestTime) {
		$tmpDate = 0;
		if(!isset($colCnt[$tmpDate])) { $colCnt[$tmpDate] = 0; }
		$timecss = (($bb % 4) == 0 ? 'hour' : 'minute');
?>			<tr><td class='time <?=$timecss?>'><?=$time?></td> <?
		while($tmpDate <= 7) {
			$colCntVal = (isset($colCnt[$tmpDate]) ? count($colCnt[$tmpDate]) : 0);
			if($colCntVal > 0 && $colCnt[$tmpDate] != 0) { 
				foreach($colCnt[$tmpDate] as $k => $v) { 
					$colCnt[$tmpDate][$k] -= 1; 
					if($colCnt[$tmpDate][$k] < 1) { unset($colCnt[$tmpDate][$k]); $colCntVal -= 1;} 
				}
			}

			if(isset($b[$tmpDate]) && in_array($time, $b[$tmpDate])) { 
				$pos = array_search($time, $b[$tmpDate]);

?>					<td style='width: 1px;' class='<?=$timecss?>'>[+]</td> <?
				while($pos) { 
					$rowSize = ((strtotime($other[$tmpDate][$pos]['tEnd'])-strtotime($b[$tmpDate][$pos]))/60)/15;
					if(!isset($colCnt[$tmpDate][$time])) { $colCnt[$tmpDate][$time] = $rowSize; }
?>
					<td colspan='1' align="left" valign="top" rowspan='<?=$rowSize?>' class='<?=$timecss?>' style='background: <?=$other[$tmpDate][$pos]['chrColorBG']?>; color: <?=$other[$tmpDate][$pos]['chrColorText']?>; width: <?=floor(800/$intMaxEventsTotal)?>px;'><?=$b[$tmpDate][$pos]?></td>
<?					unset($b[$tmpDate][$pos]);unset($other[$tmpDate][$pos]);
					$pos = array_search($time, $b[$tmpDate]);
					$used++;
				}
				if((($intMaxEvents[$tmpDate] - $used) - $colCntVal) > 0) {
					while($used-- >= 0) { ?>
					<td colspan='1' class='<?=$timecss?>'>Max:<?=$intMaxEvents[$tmpDate]?>-Cnt:<?=$colCntVal?></td>
<?					}
				}
			} else { 
				if(isset($intMaxEvents[$tmpDate])) { ?>
					<td style='width: 1px;' class='<?=$timecss?>'>[+]</td>
<?					if(($intMaxEvents[$tmpDate] - $colCntVal) > 0) { ?>
						<td colspan="<?=($intMaxEvents[$tmpDate] - $colCntVal)?>" class='<?=$timecss?>' style='width: <?=floor(800/$intMaxEventsTotal)?>px;'>&nbsp;</td>
<?					}	
				} ?>
<?			
			}
			$tmpDate++;
		}
?>				</tr> <?

		$time = date('H:i:00',strtotime($time." + 15 minutes"));
		$bb++;
	} ?>
		</table>



	</div>

<?
	include($BF. 'includes/bottom.php');
	
?>