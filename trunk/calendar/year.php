<?php
	$BF = '../';
	$title = 'Month Calendar Page';
	$curPage = "";
	require($BF. '_lib2.php');

	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }
	$_SESSION['calSection'] = 'year';
	$_SESSION['calDate'] = @$_REQUEST['dBegin'];

	# Setting up initial variables to be used
	if(!isset($_REQUEST['dBegin']) || $_REQUEST['dBegin'] == '') {
		$intCurDay = date('d'); 	# int value of current day (ex: 30)
		$intCurMonth = date('m');	# int value of current month (ex: 7)
		$intCurYear = date('Y');	# int value of current year (ex: 2007)

		header("Location: ". $BF ."calendar/year.php?dBegin=".$intCurYear.$intCurMonth.$intCurDay);
		die();
	} else {
		$intDay = date('d',strtotime($_REQUEST['dBegin']));
		$intMonth = date('m',strtotime($_REQUEST['dBegin']));
		$intYear = date('Y',strtotime($_REQUEST['dBegin']));
	}

	if(!isset($_SESSION['idCalTypes'])) { $_SESSION['idCalTypes'] = ""; }
	if(isset($_POST['idCalTypes']) && $_POST['idCalTypes'] != "") { $_SESSION['idCalTypes'] = implode(',',$_POST['idCalTypes']); }

	if(!isset($_SESSION['idCalUsers'])) { $_SESSION['idCalUsers'] = ""; }
	if(isset($_POST['idCalUsers']) && $_POST['idCalUsers'] != "") { $_SESSION['idCalUsers'] = implode(',',$_POST['idCalUsers']); }

	$results = database_query("SELECT CalendarEvents.ID,chrCalendarEvent,dBegin
		FROM CalendarEvents 
		JOIN Users ON Users.ID=CalendarEvents.idUser
		WHERE dBegin BETWEEN '". $intYear."-01-01' AND '". $intYear."-12-31' AND !CalendarEvents.bDeleted AND bCalAccess
		". ($_SESSION['idCalTypes'] != "" ? " AND idCalendarType IN (". $_SESSION['idCalTypes'] .") " : '') ."
		". ($_SESSION['idCalUsers'] != "" ? " AND Users.ID IN (". $_SESSION['idCalUsers'] .") " : '') ."
		ORDER BY dBegin,tBegin,tEnd
	","getting events");
	$events = array();
	while($row = mysqli_fetch_assoc($results)) {
		$events[$row['dBegin']] = 1;
	}
	
	include($BF. 'calendar/includes/meta.php');
?><link href="<?=$BF?>calendar/includes/calendar.css" rel="stylesheet" type="text/css" /><?

	include($BF. 'calendar/includes/topcal.php');
?>
			
				<table cellspacing="0" cellpadding="0" class="calmenubar">
					<tr style='background: url(images/cap-middle.gif) repeat-x; width: 100%;'>
						<td class='sides'><img src="images/cap-left.gif" /></td>
						<td>
							<div class='datetime'><a href='year.php?dBegin=<?=date('Ymd',strtotime($_REQUEST['dBegin']. " - 1 year"))?>'>&lt;</a> <?=$intYear?> <a href='year.php?dBegin=<?=date('Ymd',strtotime($_REQUEST['dBegin']. " + 1 year"))?>'>&gt;</a></div>
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
							<a href='<?=$BF?>calendar/month.php?dBegin=<?=$_REQUEST['dBegin']?>'><img src="images/cal_month.gif" /></a> 
							<img src="images/cal_year.gif" />
						</td>
						<td class='sides' style='text-align: right;'><img src="images/cap-right.gif" /></td>
					</tr>
				</table>
				
				
				<table style='width: 100%;'>
					<tr>
						<td><?=miniMonth($intYear."0101")?></td>
						<td><?=miniMonth($intYear."0201")?></td>
						<td><?=miniMonth($intYear."0301")?></td>
					</tr>
					<tr>
						<td><?=miniMonth($intYear."0401")?></td>
						<td><?=miniMonth($intYear."0501")?></td>
						<td><?=miniMonth($intYear."0601")?></td>
					</tr>
					<tr>
						<td><?=miniMonth($intYear."0701")?></td>
						<td><?=miniMonth($intYear."0801")?></td>
						<td><?=miniMonth($intYear."0901")?></td>
					</tr>
					<tr>
						<td><?=miniMonth($intYear."1001")?></td>
						<td><?=miniMonth($intYear."1101")?></td>
						<td><?=miniMonth($intYear."1201")?></td>
					</tr>
				</table>


<?
	include($BF. 'calendar/includes/bottom.php');

	function miniMonth($date) {
		global $BF,$events;

		$intDay = date('d',strtotime($date));
		$intMonth = date('m',strtotime($date));
		$intYear = date('Y',strtotime($date));
		
		$firstWeekday = idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear));
		$firstDisplayDay = 1-$firstWeekday;
		$daysThisMonth = idate('t', mktime(0, 0, 0, $intMonth, 1, $intYear));
		$daysLastMonth = idate('t', mktime(0, 0, 0, ($intMonth-1), 1, $intYear));

?>
	<table cellpadding='0' cellspacing='0' class='calminiyear' style=''>
		<tr>
			<th colspan='7'><a href='<?=$BF?>calendar/month.php?dBegin=<?=$intYear.$intMonth."01"?>' style='color: white;'><?=date('F',strtotime($date))?></a> <?=$intYear?></th>
		</tr>
		<tr>
			<td class="days">Sun</td>
			<td class="days">Mon</td>
			<td class="days">Tue</td>
			<td class="days">Wed</td>
			<td class="days">Thu</td>
			<td class="days">Fri</td>
			<td class="days">Sat</td>
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
?>				</td><td class='daybox'><div class='fleft'><a href='<?=$BF?>calendar/addevent.php?dBegin=<?=$intYear.$intMonth.$intMonthDay?>&from=y'>[+]</a></div><div class='fright'><?=$intMonthDay?></div>
<?
		if(isset($events[$intYear.'-'.$intMonth.'-'.($intMonthDay < 10 ? '0'.$intMonthDay : $intMonthDay)])) { ?>
				<div class='clear'><a style='color: blue;' href='day.php?dBegin=<?=$intYear.$intMonth.($intMonthDay < 10 ? '0'.$intMonthDay : $intMonthDay)?>'>X</a></div>
<?		}
	}

	$extraCnt = 1;
	while($weekDayInt++ < 7) { ?>
			<td class='diffmonth'><div class='dom'><?=$extraCnt++?></div></td>
<?	} ?>

		</td>
		</tr>
	</table>

<?
	}
?>