<?php
	$BF = '../';
	$title = 'Month Calendar Page';
	$curPage = "";
	require($BF. '_lib2.php');

	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }
	$_SESSION['calSection'] = 'month';
	$_SESSION['calDate'] = @$_REQUEST['dBegin'];

	# Setting up initial variables to be used
	if(!isset($_REQUEST['dBegin']) || $_REQUEST['dBegin'] == '') {
		$intCurDay = date('d'); 	# int value of current day (ex: 30)
		$intCurMonth = date('m');	# int value of current month (ex: 7)
		$intCurYear = date('Y');	# int value of current year (ex: 2007)

		header("Location: ". $BF ."calendar/month.php?dBegin=".$intCurYear.$intCurMonth.$intCurDay);
		die();
	} else {
		$intDay = date('d',strtotime($_REQUEST['dBegin']));
		$intMonth = date('m',strtotime($_REQUEST['dBegin']));
		$intYear = date('Y',strtotime($_REQUEST['dBegin']));
		
		$firstWeekday = idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear));
		$firstDisplayDay = 1-$firstWeekday;
		$daysThisMonth = idate('t', mktime(0, 0, 0, $intMonth, 1, $intYear));
		$daysLastMonth = idate('t', mktime(0, 0, 0, ($intMonth-1), 1, $intYear));
	}
	
	if(!isset($_SESSION['idCalTypes'])) { $_SESSION['idCalTypes'] = ""; }
	if(isset($_POST['idCalTypes']) && $_POST['idCalTypes'] != "") { $_SESSION['idCalTypes'] = implode(',',$_POST['idCalTypes']); }
	
	$q = "SELECT CalendarEvents.ID,CalendarEvents.chrKey,chrCalendarEvent,DAY(dBegin) as dDay,chrColorText,chrColorBG
		FROM CalendarEvents
		JOIN CalendarTypes ON CalendarTypes.ID=CalendarEvents.idCalendarType
		WHERE dBegin BETWEEN '". $intYear."-".$intMonth."-01' AND '". $intYear."-".$intMonth."-".$daysThisMonth ."' AND !CalendarEvents.bDeleted
		". ($_SESSION['idCalTypes'] != "" ? " AND idCalendarType IN (". $_SESSION['idCalTypes'] .") " : '') ."
		ORDER BY dBegin,tBegin,tEnd
	";
	$results = database_query($q,"getting events");

	tmp_val('chrICalQuery','set',$q);
	
	$events = array();
	while($row = mysqli_fetch_assoc($results)) {
		$events[$row['dDay']][$row['ID']]['chrCalendarEvent'] = $row['chrCalendarEvent'];
		$events[$row['dDay']][$row['ID']]['chrKey'] = $row['chrKey'];
		$events[$row['dDay']][$row['ID']]['chrColorBG'] = $row['chrColorBG'];
		$events[$row['dDay']][$row['ID']]['chrColorText'] = $row['chrColorText'];
	}
	
	include($BF. 'calendar/includes/meta.php');
?><link href="<?=$BF?>calendar/includes/calendar.css" rel="stylesheet" type="text/css" /><?
	include($BF. 'calendar/includes/topcal.php');
?>


				<table cellspacing="0" cellpadding="0" class="calmenubar">
					<tr style='background: url(images/cap-middle.gif) repeat-x; width: 100%;'>
						<td class='sides'><img src="images/cap-left.gif" /></td>
						<td>
							<div class='datetime'><a href='month.php?dBegin=<?=date('Ymd',strtotime($_REQUEST['dBegin']. " - 1 month"))?>'><img src="<?=$BF?>/calendar/images/arrow_left.png" /></a>
							<?=date('F',strtotime($_REQUEST['dBegin']))?> <?=$intYear?> 
							<a href='month.php?dBegin=<?=date('Ymd',strtotime($_REQUEST['dBegin']. " + 1 month"))?>'><img src="<?=$BF?>/calendar/images/arrow_right.png" /></a></div>
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
							<a href='<?=$BF?>calendar/day.php?dBegin=<?=$_REQUEST['dBegin']?>'><img src="images/cal_day.gif" /></a> 
							<a href='<?=$BF?>calendar/week.php?dBegin=<?=$_REQUEST['dBegin']?>'><img src="images/cal_week.gif" /></a>
							<img src="images/cal_month.gif" />
							<a href='<?=$BF?>calendar/year.php?dBegin=<?=$_REQUEST['dBegin']?>'><img src="images/cal_year.gif" /></a>
						</td>
						<td class='sides' style='text-align: right;'><img src="images/cap-right.gif" /></td>
					</tr>
				</table>


				<table cellpadding='0' cellspacing='0' class='calmonth'>
					<tr class="days">
						<th>Sunday</th>
						<th>Monday</th>
						<th>Tuesday</th>
						<th>Wednesday</th>
						<th>Thursday</th>
						<th>Friday</th>
						<th>Saturday</th>
					</tr>
					<tr>
<?	$weekDayInt = 0;
				$intMonthDay = 0;
	while($firstDisplayDay != 1) { ?>
						<td class='diffmonth'><div class='dom'><?=($daysLastMonth + $firstDisplayDay)?></div></td>
<?			$weekDayInt++;
			$firstDisplayDay += 1;
	}
				
	while($intMonthDay < $daysThisMonth) { 
				
		if($weekDayInt == 7) { $weekDayInt = 0; ?> </tr><tr> <? } 
			$weekDayInt++; 
			$intMonthDay++; 
?>				</td><td class='daybox'><div class="fleft"><a href='<?=$BF?>calendar/addevent.php?dBegin=<?=$intYear.$intMonth.($intMonthDay < 10 ? '0'.$intMonthDay : $intMonthDay)?>&from=m'>[+]</a></div><div class="fright"><a href='day.php?dBegin=<?=$intYear.$intMonth.($intMonthDay < 10 ? '0'.$intMonthDay : $intMonthDay)?>'><?=$intMonthDay?></a></div>
<? 	
		if(isset($events[$intMonthDay])) {
			foreach($events[$intMonthDay] as $k => $v) { ?>
							<div class='clear' style='color: <?=$v['chrColorText']?>; background: <?=$v['chrColorBG']?>'>- <a style='color: <?=$v['chrColorText']?>' href='viewevent.php?dBegin=<?=$_REQUEST['dBegin']?>&key=<?=$v['chrKey']?>&from=m'><?=$v['chrCalendarEvent']?></a></div>
<?			}
		}
	}
			
	$extraCnt = 1;
	while($weekDayInt++ < 7) { ?>
						<td class='diffmonth'><div class='dom'><?=$extraCnt++?></div></td>
<?	} ?>
			
					</td>
					</tr>
				</table>

<?
	include($BF. 'calendar/includes/bottom.php');
?>