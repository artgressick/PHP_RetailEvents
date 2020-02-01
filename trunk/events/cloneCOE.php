`<?php
	require("../_lib.php");
	
	// get the current month
	$intCurrentMonth = idate('m');
	$intCurrentYear = idate('Y');
	$current_month = (($intCurrentYear-2000)*12)+$intCurrentMonth-1;
	($_SESSION['idType'] == 4 && $_REQUEST['intDate'] > 99 ? ErrorPage() : ""); 
	$intYear = 2000 + floor($_REQUEST['intDate'] / 12);
	$intMonth = ($_REQUEST['intDate'] % 12)+1;
				
	$edit_mode = true;
	$approval_mode = false;
	$this_month_status = '';
	$is_retailevents_user = $_SESSION['idType'];
	$is_my_store = !$is_retailevents_user;

		
	$first_weekday = idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear)) . "<br />";
	$first_display_day = 1-$first_weekday . "<br />";
	$days_this_month = idate('t', mktime(0, 0, 0, $intMonth, 1, $intYear)) . "<br />";
	$number_of_weeks = ceil(($days_this_month+idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear))) / 7) . "<br />";

		// Getting Events
	$query = "SELECT Events.*, DATE_FORMAT(dDate, '%e') AS intMonthDay, TIME_FORMAT(tBegin, '%H') AS intStartHour, bApproved, EventTypes.idEventCategory,
		TIME_FORMAT(tBegin, '%M0') AS intStartMinute, EventTypes.chrName AS chrEventTypeName, EventTypes.chrStyleClass, tBegin as tBegin2, tEnd as tEnd2,
		DATE_FORMAT(tBegin,'%l:%i %p') as tBegin, DATE_FORMAT(tEnd,'%l:%i %p') as tEnd,
		(SELECT count(idEvent) FROM EventPresenters WHERE EventPresenters.idEvent=Events.ID) as intPresenters
		FROM Events
		JOIN EventTypes ON EventTypes.ID=idEventType
		WHERE idStore='" . $_REQUEST['idStore'] . "' AND MONTH(dDate)='" . $intMonth . "' AND YEAR(dDate)='" . $intYear . "'
			AND !EventTypes.bDeleted AND EventTypes.bShow AND EventTypes.bEditorReview=0
		ORDER BY dDate,tBegin,tEnd,chrTitle ASC";
	$events = do_mysql_query($query, 'get events');
	
	if(count($_POST['cloneCal'])) {
		$oldSeries = 0;
	
		$td = $intYear . '-' . $intMonth . '-01';
		if(($intMonth + 1) > 12) { $nm = ($intYear + 1) . '-01-01'; }
		else { $nm = $intYear . '-' . ($intMonth + 1) . '-01'; }

		//echo 'td: ' . $td . ' -- nm: ' . $nm . '<br/>'; 
		
		$d1 = date('N',strtotime($td));
		$d2 = date('N',strtotime($nm));
		if($d1 < $d2) {
			echo "1<br>";
			$offset = $d2 - $d1;
		} else if($d1 == $d2) { 
			echo "2<br>";
			$offset = 0;
		} else if($d1 > $d2) {
			echo "3<br>";
			$offset = (0 - ($d1 - $d2));
		}
		
		//echo 'd1: ' . $d1 . ' -- d2: ' . $d2 . ' -- offset: '. $offset . '<br/>'; 
	
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
			
				//echo $year . "-" . $month . "-" . $newDate[2] . ' --> ' . $year . "-" . $month . "-" . ($newDate[2] - $offset) . '<br/>';
			
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
	
	
			$_SESSION['InfoMessage'] = "Cloning Successful!";
			header("Location: index.php?idStore=" . $_POST['idStore'] . '&intDate=' . ($_POST['intDate'] + 1));
			die();

	}
	
	$monthChk = mysql_fetch_assoc(do_mysql_query("SELECT enStatus FROM StoreMonths WHERE intMonth='" . $intMonth . "' AND idStore='" . $_REQUEST['idStore'] . "'","get enStatus")); 
	
	 function insert_into_head() { 
?>
	
	<style type="text/css">
/* This is the drop down menu */
DIV.dropdownmenu ul { padding: 0; margin: 0; list-style: none; }
DIV.dropdownmenu li { float: left; position: relative; width: 10em; background-color: inherit; }
DIV.dropdownmenu li ul { display: none; position: absolute; top: 1em; margin-left: 2em; margin-top: -3em; left: 0; background-color: white; border: 1px solid black; padding: 5px; width: 150px; }
DIV.dropdownmenu li ul li { padding-bottom: 5px; }
     /* to override top and left in browsers other than IE, which will position to the top right of the containing li, rather than bottom left */
DIV.dropdownmenu li > ul { top: auto; left: auto; }
DIV.dropdownmenu li:hover ul, li.over ul { display: block; }
	</style>

 <script type="text/javascript"><!--//--><![CDATA[//><!--
	
// JavaScript DocumentstartList = function() {
	if (document.all&&document.getElementById) {
		navRoot = document.getElementById("nav");
		for (i=0; i<navRoot.childNodes.length; i++) {
			node = navRoot.childNodes[i];
			if (node.nodeName=="LI") {
				node.onmouseover=function() {
					this.className+=" over";
				}
				node.onmouseout=function() {
					this.className=this.className.replace(" over", "");
				}
			}
		}
	}


	window.onload=startList();
	//--><!]]></script>
<?
	}	
	
	
	
	
	// Set the title, and add the doc_top
	$title = "COE";
	require(BASE_FOLDER . 'docpages/doc_meta_events.php');
	include(BASE_FOLDER . 'docpages/doc_top_events.php');
?>

	<div style='margin: 10px;'>
				<div class="AdminTopicHeader">Clone Calendar</div>
				
				
		<div style=''>
				<div class="AdminDirections" style='width: 870px;'>Please remember that this cloning function will only clone Weekly Workshops/Events.  No special workshops/events will be cloned.</div>


<?	if(@count($_SESSION['InfoMessage'])) { ?>
	<div class='Messages'>
<?		foreach($_SESSION['InfoMessage'] as $msg) { ?>
						<p class='InfoMessage'><?=$msg?></p>
<?		}
		$_SESSION['InfoMessage'] = array(); ?>
	</div>
<?	}?>

				<!-- Tool Bar with the Add Store and Search button -->
				<div class="AdminToolBar" style='width: 880px;'>
					<table>
						<tr>
							<td style='width: 405px;'>
	
		<form action='' method="post">
														
<!-- This is the CLONE CALENDAR Button -->
								<div>
									<input type='submit' name='cloneCal' value='Clone Calendar' /><br />
									<input type='button' value='Cancel Clone' onclick='history.back();' /><br />
									<input type='hidden' name='idStore' value='<?=$_REQUEST['idStore']?>' />
									<input type='hidden' name='intDate' value='<?=$_REQUEST['intDate']?>' />
								</div>
		</form>

							</td>
							<td style='width: 500px; text-align: right;'>

<div>

<form method='get' action='' >
					<input type='hidden' name='idStore' value='<?=$_REQUEST['idStore']?>' />
					<input type='hidden' name='intDate' value='<?=$_REQUEST['intDate']?>' />
					<span style='font-size: 10px;'>Dates:</span>
						<select name='intDate' style='wid' onchange='this.form.submit()'>
							<option value=''>- Select Month -</option>
<?	// build list of months to display.
	$months = array();

	// start with the latest thing we'll show them, which is 12 months in the future
	for($monthloop = $current_month+12; $monthloop > $current_month-2; $monthloop--) { 
		$months[$monthloop] = '';
	}

	// now add each of the months that have a status
	$rows = do_mysql_query("SELECT enStatus, ((intYear-2000)*12)+intMonth-1 AS intDate FROM StoreMonths 
		WHERE idStore='" . $_REQUEST['idStore'] . "'
		ORDER BY intYear DESC, intMonth DESC
		", 'get current months');
	while($row = mysql_fetch_assoc($rows)) {
		$months[$row['intDate']] = ' (' . $row['enStatus'] . ')';
	}

	// sort the list
	krsort($months);

	foreach($months as $monthloop => $enStatus) {
		$loopYear = 2000 + floor($monthloop / 12);
		$loopMonth = ($monthloop % 12)+1;
?>
							<option value='<?=$monthloop?>' <?=($_REQUEST['intDate'] == $monthloop?'selected="selected"':'')?>><?=strftime('%B %Y', mktime(0, 0, 0, $loopMonth, 1, $loopYear))?><?=$enStatus?></option>
<?	} ?>
						</select>

					
					
					<span style='font-size: 10px;'>To:</span>
						<select name='intCloneDate' style='wid'>
							<option value=''>- Select Month -</option>
<?	// build list of months to display.
	$months = array();

	// start with the latest thing we'll show them, which is 12 months in the future
	for($monthloop = $current_month+12; $monthloop > $current_month+1; $monthloop--) { 
		$months[$monthloop] = '';
	}

	// now add each of the months that have a status
	$rows = do_mysql_query("SELECT enStatus, ((intYear-2000)*12)+intMonth-1 AS intDate FROM StoreMonths 
		WHERE idStore='" . $_REQUEST['idStore'] . "'
		ORDER BY intYear DESC, intMonth DESC
		", 'get current months');
	while($row = mysql_fetch_assoc($rows)) {
		$months[$row['intDate']] = ' (' . $row['enStatus'] . ')';
	}

	// sort the list
	krsort($months);

	foreach($months as $monthloop => $enStatus) {
		$loopYear = 2000 + floor($monthloop / 12);
		$loopMonth = ($monthloop % 12)+1;
?>
							<option value='<?=$monthloop?>' <?=($_REQUEST['intDate'] == ($monthloop - 1) ? 'selected="selected"':'')?>><?=strftime('%B %Y', mktime(0, 0, 0, $loopMonth, 1, $loopYear))?><?=$enStatus?></option>
<?	} ?>
						</select>
</form>
					</div>



							</td>
						</tr>
					</table>
				</div>
      </div>
	</form>
	


	<table id="calendarmonth" cellpadding='0' cellspacing='0' style='width: 100%;'>
		<tr class="days">
			<th style='width: 14.25%'>Sunday</th>
			<th style='width: 14.25%'>Monday</th>
			<th style='width: 14.25%'>Tuesday</th>
			<th style='width: 14.25%'>Wednesday</th>
			<th style='width: 14.25%'>Thursday</th>
			<th style='width: 14.25%'>Friday</th>
			<th style='width: 14.25%'>Saturday</th>
		</tr>
		<tr>
<?	$weekDayInt = 0;
	 $intMonthDay = 0;
	
	while($first_display_day != 1) { ?>
			<td>&nbsp;</td>
<?			$weekDayInt++;
			$first_display_day += 1;
	} ?>
<?	while($row = mysql_fetch_assoc($events)) { 
		while($tmpDate != $row['dDate']) { 
				
			if($weekDayInt == 7) { $weekDayInt = 0; ?> </tr><tr> <? } 
			$weekDayInt++; 
				
			$intMonthDay++; 
			$tmpDate = $intYear . '-' . ($intMonth < 10 ? '0'.$intMonth : $intMonth) . '-' . ($intMonthDay < 10 ? '0'.$intMonthDay : $intMonthDay);
				
?>
				</td><td style='vertical-align: top;'><div class='top'><?=$intMonthDay?></div>
	<? }
		
		?>
			<div class="<?=$row['chrStyleClass']?> info">			
				<? if($row['bApproved'] == 0 && $row['bApproved'] != '') { ?><div class='dropdownmenu'><ul id='nav'><li><div style='background-color: #98140B; color: white; text-align: center; padding: 3px 0; margin: -2px -1px 0 -2px;'>DISAPPROVED</div><ul><li style='color: black;'><?=$row['txtRejection']?></li></ul></li></ul></div><? } ?>
				<a href="editevent.php?id=<?=$row['ID']?>&idStore=<?=$_REQUEST['idStore']?>&intDate=<?=$_REQUEST['intDate']?>" title="<?=$row['chrDescription']?>" class="<?=$row['chrStyleClass']?>"><?=$row['chrTitle']?></a>
				<p class="start"><?=$row['tBegin']?> to</p>
				<p class='end'><?=$row['tEnd']?></p>
<? 				if($row['intPresenters'] > 0 && $row['bSpecial']==1) { 
						$result = do_mysql_query("SELECT chrName FROM EventPresenters JOIN Presenters ON Presenters.ID=EventPresenters.idPresenter WHERE EventPresenters.idEvent='" . $row['ID'] . "'","Getting the presenters for event");
?>
					<div class='dropdownmenu'>
						<ul id='nav'>
							<li><img src='../images/profile-gray.gif' style='height: 16px; width: 16px;'>
								<ul>
<?						while($row_pres = mysql_fetch_assoc($result)) { ?>
									<li><?=$row_pres['chrName']?></li>
<?						} ?>
								</ul>
							</li>
						</ul>
					</div>
<?				} ?>			
			
			</div>

<?		}

	while($intMonthDay < $days_this_month) { 
	
	 if($weekDayInt == 7) { $weekDayInt = 0; ?> </tr><tr> <? } 
			$weekDayInt++; 
				
			$intMonthDay++; 
			$tmpDate = $intYear . '-' . ($intMonth < 10 ? '0'.$intMonth : $intMonth) . '-' . ($intMonthDay < 10 ? '0'.$intMonthDay : $intMonthDay);
				
?>
				</td><td style='vertical-align: top;'><div class='top'><?=$intMonthDay?></div>
<? }?>
		</td>
		</tr>
	</table>

	<table width="100%" id="calendarlegend" style='margin-top: 0.8em;'>
		<tbody>
			<tr>
				<td rowspan="2">Calendar legend:</td>
				<td class="ws">Workshops</td>
				<td class="pd">Business Day</td>
				<td class="ss">Studio Series</td>
				<td class="mm">Made on a Mac</td>
				<td class="wm">Works on a Mac</td>
				</tr>
			<tr>
				<td class="pw">Pro Workshops</td>
				<td class="sn">School Night</td>
				<td class="se">Special Events</td>
				<td class="gu">Genius Unplugged</td>
				<td class="ug">User Groups</td>
				</tr>
			</tbody>
		</table>

</div>


<?
	include('../docpages/doc_bottom.php');
?>