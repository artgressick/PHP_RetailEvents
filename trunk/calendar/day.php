<?php
	$BF = '../';
	$title = 'Calendar Page';
	$curPage = "";
	require($BF. '_lib2.php');

	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }
	$_SESSION['calSection'] = 'day';
	$_SESSION['calDate'] = @$_REQUEST['dBegin'];

	# Setting up initial variables to be used
	if(!isset($_REQUEST['dBegin']) || $_REQUEST['dBegin'] == '') {
		$intCurDay = date('d'); 	# int value of current day (ex: 30)
		$intCurMonth = date('m');	# int value of current month (ex: 7)
		$intCurYear = date('Y');	# int value of current year (ex: 2007)

		header("Location: ". $BF ."calendar/day.php?dBegin=".$intCurYear.$intCurMonth.$intCurDay);
		die();
	} else {
		$intDay = idate('d',strtotime($_REQUEST['dBegin']));
		$intMonth = idate('m',strtotime($_REQUEST['dBegin']));
		$intYear = idate('Y',strtotime($_REQUEST['dBegin']));
	}
	
	if(!isset($_SESSION['idCalTypes'])) { $_SESSION['idCalTypes'] = ""; }
	if(isset($_POST['idCalTypes']) && $_POST['idCalTypes'] != "") { $_SESSION['idCalTypes'] = implode(',',$_POST['idCalTypes']); }

	$results = database_query("SELECT CalendarEvents.chrKEY, chrCalendarEvent, tBegin, tEnd, chrColorBG, chrColorText 
			FROM CalendarEvents 
			JOIN CalendarTypes ON CalendarTypes.ID=CalendarEvents.idCalendarType
			WHERE dBegin='". date('Y-m-d',strtotime($_REQUEST['dBegin'])) ."' AND !bAllDay
			". ($_SESSION['idCalTypes'] != "" ? " AND idCalendarType IN (". $_SESSION['idCalTypes'] .") " : '') ."
			ORDER BY dBegin,tBegin,tEnd
		", "getting events");

	# Used later to find the total number of concurrent events
	$intEvents = mysqli_num_rows($results);  
	
	$b = array(); 		# Begin Time array
	$other = array();	# End Time, Text Color, Background Color array
	$i = 1;				# Counter for Array
	while($row = mysqli_fetch_assoc($results)) {
		$b[$i] = $row['tBegin'];
		$other[$i]['tEnd'] = $row['tEnd'];
		$other[$i]['chrKEY'] = $row['chrKEY'];
		$other[$i]['chrCalendarEvent'] = $row['chrCalendarEvent'];
		$other[$i]['chrColorBG'] = $row['chrColorBG'];
		$other[$i]['chrColorText'] = $row['chrColorText'];
		$i++;
	}



	if(count($b)) {
		# Find out what the most amount of events are that happen at the same time.	
		$intMaxEvents = 1;
		$i = 1;
		$latest = 0;
		while($i <= ($intEvents+1)) {
			if(isset($b[$i])) {
				$tmpb = strtotime($b[$i]);
				$tmpe = strtotime($other[$i]['tEnd']);
				if($latest < $tmpe) { $latest = $tmpe; $latestTime = $other[$i]['tEnd']; }
				$match = 0;
				$j = 1;
				while($j <= ($i + 1)) {
					if(isset($b[$j])) { 
						$testb = strtotime($b[$j]);
						$teste = strtotime($other[$j]['tEnd']);
			
						if($testb >= $tmpb && $tmpb <= $teste) { $match++; }
					}
					$j++;
				}
				if($match > $intMaxEvents) { $intMaxEvents = $match; }
			}
			$i++;
		}
		$earliestTime = $b[1];
	} else {
		$earliestTime = "07:00:00";
		$latestTime = "19:00:00";
		$intMaxEvents = 1;
	}

	include($BF. 'calendar/includes/meta.php');

?>
<link href="<?=$BF?>calendar/includes/calendar.css" rel="stylesheet" type="text/css" />
<?
	include($BF. 'calendar/includes/topcal.php');
?>

				<table cellspacing="0" cellpadding="0" class="calmenubar">
					<tr style='background: url(images/cap-middle.gif) repeat-x; width: 100%;'>
						<td class='sides'><img src="images/cap-left.gif" /></td>
						<td>
							<div class='datetime'><a href='day.php?dBegin=<?=date('Ymd',strtotime($_REQUEST['dBegin']. " - 1 day"))?>'>&lt;</a> <?=date('l, F jS, Y',strtotime($_REQUEST['dBegin']))?> <a href='day.php?dBegin=<?=date('Ymd',strtotime($_REQUEST['dBegin']. " + 1 day"))?>'>&gt;</a>
						</td>
			
						<td class='quickjump'>
							Go to: 
			<?	$today = date('Y').date('m').date('d'); ?>				
							<select id='goto' name='goto' onchange="window.location.href=this.value">
								<option value=''>-Go To-</option>
								<option value='day.php?dBegin=<?=$today?>'>Today's Date</option>
								<option value='week.php?dBegin=<?=$today?>'>This Week</option>
								<option value='month.php?dBegin=<?=$today?>'>This Month</option>
								<option value='year.php?dBegin=<?=$today?>'>This Year</option>
							</select>
						</td>
						<td class='currentdates'>
							<img src="images/cal_day.gif" /> 
							<a href='<?=$BF?>calendar/week.php?dBegin=<?=$_REQUEST['dBegin']?>'><img src="images/cal_week.gif" /></a>  
							<a href='<?=$BF?>calendar/month.php?dBegin=<?=$_REQUEST['dBegin']?>'><img src="images/cal_month.gif" /></a> 
							<a href='<?=$BF?>calendar/year.php?dBegin=<?=$_REQUEST['dBegin']?>'><img src="images/cal_year.gif" /></a>
						</td>
						<td class='sides' style='text-align: right;'><img src="images/cap-right.gif" /></td>
					</tr>
				</table>
				
				
<?
	$dailyresults = database_query("SELECT CalendarEvents.chrKEY, chrCalendarEvent, chrColorBG, chrColorText 
			FROM CalendarEvents 
			JOIN CalendarTypes ON CalendarTypes.ID=CalendarEvents.idCalendarType
			WHERE dBegin='". date('Y-m-d',strtotime($_REQUEST['dBegin'])) ."' AND bAllDay AND !CalendarEvents.bDeleted
			". ($_SESSION['idCalTypes'] != "" ? " AND idCalendarType IN (". $_SESSION['idCalTypes'] .") " : '') ."
			ORDER BY dBegin,tBegin,tEnd
		", "getting events");
	if(mysqli_num_rows($dailyresults)) { 
?>
				<div class='header3'>All Day Events</div>
				<div style='border: 1px solid gray;'>

<?		while($row = mysqli_fetch_assoc($dailyresults)) { ?>
					<div style='padding: 1px 3px; background: <?=$row['chrColorBG']?>;'><a style='color: <?=$row['chrColorText']?>;' href='viewevent.php?key=<?=$row['chrKEY']?>&from=d&dBegin=<?=$_REQUEST['dBegin']?>'><?=$row['chrCalendarEvent']?></a></div>
<?		}
	} ?>	
				</div>



				<div class='header3'>Hourly Events</div>
				<table cellspacing="0" cellpadding="0" class='calweekday'>
<?	$bb = 0;	# Border bottom counter for when we are in the "hour" vs "15 minute" areas
	if(strtotime($earliestTime) < strtotime('07:00:00')) { $time = $earliestTime; } else { $time = "07:00:00"; }
	if(strtotime($latestTime) < strtotime('19:00:00')) { $latestTime = '19:00:00'; }
				
	$tcnt = 0;
	$colCnt = array();
	while($time != $latestTime) {
		$timecss = (($bb % 4) == 0 ? 'hour' : 'minute');
		$colCntVal = count($colCnt);
		if($colCntVal) { 
			foreach($colCnt as $k => $v) { 
				$colCnt[$k] -= 1; 
				if($colCnt[$k] < 1) { unset($colCnt[$k]); } 
			}
		}
		if(in_array($time, $b)) { 
			#$innerCnt = 0;
			$pos = array_search($time, $b);
?>
						<tr>
							<td class='time <?=$timecss?>'><?=$time?></td>
							<td class='addcalevent <?=$timecss?>'><a href='addevent.php?dBegin=<?=$_REQUEST['dBegin']?>&tBegin=<?=$b[$pos]?>'><img src="<?=$BF?>/calendar/images/plus_button.png" /></a></td>
			
<?			while($pos) { 
				#$innerCnt++;
				$rowSize = ((strtotime($other[$pos]['tEnd'])-strtotime($b[$pos]))/60)/15;
				if(!isset($colCnt[$time])) { $colCnt[$time] = $rowSize; }
?>
							<td colspan='1' align="left" valign="top" rowspan='<?=$rowSize?>' class='<?=$timecss?>' style='background: <?=$other[$pos]['chrColorBG']?>; width: <?=floor(840/$intMaxEvents)?>px;'><a style='color: <?=$other[$pos]['chrColorText']?>;' href='viewevent.php?dBegin=<?=$_REQUEST['dBegin']?>&key=<?=$other[$pos]['chrKEY']?>&from=d'><?=$other[$pos]['chrCalendarEvent']?></a></td>
<?				unset($b[$pos]);unset($other[$pos]);
				$pos = array_search($time, $b);
			} 
						
			/*if($innerCnt != $intMaxEvents) { ?>
				<td colspan='1' class='<?=$timecss?>' >&nbsp;</td>
<?			}*/ ?>
					</tr>
			
						
<?		} else { ?>
						<tr>
							<td class='time <?=$timecss?>'><?=$time?></td>
							<td class='addcalevent <?=$timecss?>'><a href='addevent.php?dBegin=<?=$_REQUEST['dBegin']?>&tBegin=<?=$time?>&from=d'><img src="<?=$BF?>/calendar/images/plus_button.png" /></a></td>
<?				if(($intMaxEvents - $colCntVal) > 0) { ?>
							<td colspan="<?=($intMaxEvents - $colCntVal)?>" class='<?=$timecss?>' style='width: 840px;'>&nbsp;</td>
<?				} ?>
						</tr>
<?		}
		$time = date('H:i:00',strtotime($time." + 15 minutes"));
		$bb++;
	} ?>
					</table>

<?
	include($BF. 'calendar/includes/bottom.php');
	
?>