<?php
	require_once("../_lib.php");
	
	// get the current month
	$intCurrentDay = idate('d');
	$intCurrentMonth = idate('m');
	$intCurrentYear = idate('Y');
	$current_month = (($intCurrentYear-2000)*12)+$intCurrentMonth-1;
	($_SESSION['idType'] == 4 && $_REQUEST['intDate'] > 99 ? $_REQUEST['intDate'] = 99 : ""); 
	if($_REQUEST['intDate'] == '' && $_REQUEST['idStore'] == '') {
		if($_SESSION['idType'] == 1) {
			header("Location: index.php?idStore=56&intDate=" . ($current_month + 1));
			die();
		} else { 
			header("Location: index.php?idStore=" . $_SESSION['intStoreList'][0] . "&intDate=" . ($current_month + 1));
			die();
		}
	}
	
	$intYear = 2000 + floor($_REQUEST['intDate'] / 12);
	$intMonth = ($_REQUEST['intDate'] % 12)+1;
				
	$editChk = mysql_fetch_assoc(do_mysql_query("SELECT enStatus FROM StoreMonths WHERE idStore='" . $_REQUEST['idStore'] . "' AND intMonth='" . $intMonth . "' AND intYear='" . $intYear . "'","edit check"));

	if($editChk['enStatus'] == 'Submitted') { 	
		$edit_mode = false;
	} else {
		$edit_mode = true;
	}
	
	$approval_mode = false;
	$this_month_status = '';
	$is_retailevents_user = "";//$_SESSION['idType'];
	if(@$_SESSION['idType'] != 1) { 
		$is_my_store = in_array($_REQUEST['idStore'],$_SESSION['intStoreList']);
	} else {
		$is_my_store = true;
	}

	
		
	$first_weekday = idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear)) . "<br />";
	$first_display_day = 1-$first_weekday . "<br />";
	$days_this_month = idate('t', mktime(0, 0, 0, $intMonth, 1, $intYear)) . "<br />";
	$number_of_weeks = ceil(($days_this_month+idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear))) / 7) . "<br />";

	// Getting my specific stores from the list.
	$q = "SELECT Stores.ID, Stores.chrName
		FROM Stores
		WHERE !bDeleted AND ID IN (SELECT idItem FROM ACL WHERE idUser='" . $_SESSION['idUser'] . "')
		ORDER BY chrName";
	$myStoreList = do_mysql_query($q,"getting my stores");
	
	// Getting ALL other stores BUT mine. Checks to see if there was any stores associated to you first.
	$q = "SELECT Stores.ID, Stores.chrName
		FROM Stores
		WHERE !bDeleted " . (mysql_num_rows($myStoreList) ? "AND ID NOT IN (SELECT idItem FROM ACL WHERE idUser='" . $_SESSION['idUser'] . "')" : '') . "
		ORDER BY chrName";
	$storeList = do_mysql_query($q,"getting all other stores");

		// Getting Events
	$query = "SELECT Events.*, DATE_FORMAT(dDate, '%e') AS intMonthDay, TIME_FORMAT(tBegin, '%H') AS intStartHour, bApproved, Events.intSeries, EventTypes.idEventCategory,
		TIME_FORMAT(tBegin, '%M0') AS intStartMinute, EventTypes.chrName AS chrEventTypeName, EventTypes.chrStyleClass, EventTypeNames.chrEventTitle,
		DATE_FORMAT(tBegin,'%l:%i %p') as tBegin, DATE_FORMAT(tEnd,'%l:%i %p') as tEnd,
		(SELECT count(idEvent) FROM EventPresenters WHERE EventPresenters.intEventSeries=Events.intSeries) as intPresenters
		FROM Events
		JOIN EventTypes ON EventTypes.ID=idEventType
		LEFT JOIN EventTypeNames ON EventTypeNames.ID=Events.idEventTitle
		WHERE idStore='" . $_REQUEST['idStore'] . "' AND MONTH(dDate)='" . $intMonth . "' AND YEAR(dDate)='" . $intYear . "'
		ORDER BY dDate,tBegin,tEnd,chrTitle ASC";
	$events = do_mysql_query($query, 'get events');
	
	// for the clone calendar check
	$chkClone = mysql_num_rows($events);
	
	
	$monthChk = mysql_fetch_assoc(do_mysql_query("SELECT enStatus FROM StoreMonths WHERE intMonth='" . $intMonth . "' AND intYear='". $intYear ."' AND idStore='" . $_REQUEST['idStore'] . "'","get enStatus"));
	
	
	 function insert_into_head() { 
?>

	<script language="JavaScript" type='text/javascript' src="../includes/overlays.js"></script>
		
	<style type="text/css">
/* This is the drop down menu */
DIV.dropdownmenu ul { padding: 0; margin: 0; list-style: none; width: 105px; }
DIV.dropdownmenu li { float: left; position: relative; width: 15em; background-color: inherit; }
DIV.dropdownmenu li ul { display: none; position: absolute; top: 1em; margin-left: 2em; margin-top: -3em; left: 0; background-color: white; border: 1px solid black; padding: 5px; width: 150px; }
DIV.dropdownmenu li ul li { padding: 3px 0; border-bottom: 1px solid #999; }
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


//	window.onload=startList();

	//--><!]]></script>

<?
	}	

	// Set the title, and add the doc_top
	$title = "COE";
	require(BASE_FOLDER . 'docpages/doc_meta_events.php');
	include(BASE_FOLDER . 'docpages/doc_top_events.php');
?>

	<form action='' method="get">
		
		<!-- this is the overlay DIV that grays out the page. You must declare the page table that is going to be deleted..-->
		<div id='overlaypage' class='overlaypage'>
			<div id='gray' class='gray'></div>
			<div id='message' class='message'>
				<div class='warning' id='warning'>
					<div class='red'>WARNING!!</div>
					<div class='body'>
						<div>You are about to the entire calendar of events for <?=date('F Y', strtotime($intMonth.'/01/'.$intYear))?>
							<input type='hidden' value='' id='idDel' name='idDel' />
							<div style='display: none;' id='delName' name='delName'></div>
						</div>
						<div style='margin-top: 20px; '><strong>Are you sure you want to do this? It cannot be undone!</strong><br />
							<input type='button' value='Yes' onclick="javascript:delCal('../ajax_delete.php?postType=deleteCalendar&idStore=<?=$_REQUEST['idStore']?>&dDate=<?=$intYear."-".($intMonth < 10 ? '0'.$intMonth : $intMonth)?>');" /> &nbsp;&nbsp; <input type='button' value='No' onclick="javascript:revert();" />
						</div>
					</div>
				</div>
			</div>
		</div>
		

	<div style='margin: 10px;'>
				<div class="AdminTopicHeader">My Calendar</div>
				
				
		<div style=''>
				<div class="AdminDirections" style='width: 870px;'>Click any store to view general store information, specific information about the store's learning environment, and a list of registered users. You will be able to edit any store of which you are a member.</div>


<?	if(isset($_SESSION['InfoMessage'])) { ?>
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
							<td style='width: 435px;'>
							
							
<!-- This is the ADD EVENTS Button -->
<?	$disableAdd = 0;
	if($_REQUEST['idStore'] == '' || !in_array($_REQUEST['idStore'],$_SESSION['intStoreList']) || (($intMonth < $intCurrentMonth) && ($intYear < $intCurrentYear))) { $disableAdd = 1; }
	if($monthChk['enStatus'] != 'Submitted') { ?>
								<div><input <?=((($disableAdd == 1) && $_SESSION['idType'] != 1) ? 'disabled' : '')?> type='button' value='Add Entry' onclick='location.href="addevent.php?idStore=<?=$_REQUEST['idStore']?>&intDate=<?=$_REQUEST['intDate']?>"'  /></div>
<?	} ?>

<!-- This is the Clone Events Button -->
							

							</td>
							<td style='width: 630px; text-align: right;'>

<div>

 <span style='font-size: 10px'>Stores:</span>
						<select name='idStore' onchange='this.form.submit();'>
<?	// If there ARE stores associated to your name, display those on top with optgroups!  otherwise just spit out all the stores.
	if(mysql_num_rows($myStoreList) > 0) { ?>
							<option value=''>- Select Store -</option>
							<optgroup label="My Stores">
<?	$count=0;	
	while($row = mysql_fetch_assoc($myStoreList)) { 
		if($count==0 && $_REQUEST['idStore'] == '') { $_REQUEST['idStore'] = $row['ID']; } ?>
							<option value='<?=$row['ID']?>'<?=($row['ID']==$_REQUEST['idStore'] ? ' selected' : '')?>><?=$row['chrName']?></option>
<?		$count++;
	} ?>
							</optgroup>
							<optgroup label="Other Stores">
<?		while($row = mysql_fetch_assoc($storeList)) { ?>
							<option value='<?=$row['ID']?>'<?=($row['ID']==$_REQUEST['idStore'] ? ' selected' : '')?>><?=$row['chrName']?></option>
<?		} ?>
							</optgroup>
<?	} else { 
		$count = 0;
		while($row = mysql_fetch_assoc($storeList)) { 
			if($count==0 && $_REQUEST['idStore'] == '') { $_REQUEST['idStore'] = $row['ID']; } ?>
							<option value='<?=$row['ID']?>'<?=($row['ID']==$_REQUEST['idStore'] ? ' selected' : '')?>><?=$row['chrName']?></option>
<?		$count++;
		}
	} ?>
						</select>
					
					
					
					
					
					<span style='font-size: 10px;'>Dates:</span>
						<select name='intDate' onchange='this.form.submit();' style='wid'>
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
		if($_SESSION['idType']!=4 || $monthloop <= 99) { 
?>
							<option value='<?=$monthloop?>' <?=($_REQUEST['intDate'] == $monthloop?'selected="selected"':'')?>><?=strftime('%B %Y', mktime(0, 0, 0, $loopMonth, 1, $loopYear))?><?=$enStatus?></option>
<?
		}
	} ?>
						</select>


					</div>





							</td>
						</tr>
					</table>
				</div>
      </div>
	</form>
	


	<table id="calendarmonth" cellpadding='0' cellspacing='0' style='width: 100%;'>
		<tr class="days">
			<th style='width: 125px;'>Sunday</th>
			<th style='width: 125px'>Monday</th>
			<th style='width: 125px'>Tuesday</th>
			<th style='width: 125px'>Wednesday</th>
			<th style='width: 125px'>Thursday</th>
			<th style='width: 125px'>Friday</th>
			<th style='width: 125px'>Saturday</th>
		</tr>
		<tr>
<?	$weekDayInt = 0;
	 $intMonthDay = 0;
	
	while($first_display_day != 1) { ?>
			<td>&nbsp;</td>
<?			$weekDayInt++;
			$first_display_day += 1;
	} ?>
<?	$tmpDate = "";
	while($row = mysql_fetch_assoc($events)) { 
		while($tmpDate != $row['dDate']) { 
				
			if($weekDayInt == 7) { $weekDayInt = 0; ?> </tr><tr> <? } 
			$weekDayInt++; 
				
			$intMonthDay++; 
			$tmpDate = $intYear . '-' . ($intMonth < 10 ? '0'.$intMonth : $intMonth) . '-' . ($intMonthDay < 10 ? '0'.$intMonthDay : $intMonthDay);
				
?>
				</td><td style='vertical-align: top; width: 125px; white-space: normal;'><div class='top'><?=$intMonthDay?></div>
	<? }
		
		?>
			<div class="<?=$row['chrStyleClass']?> info">			
				<? if($row['bApproved'] == 0 && $row['txtRejection'] != '') { ?><div class='dropdownmenu'><ul id='nav'><li><div style='width: 125px; background-color: #98140B; color: white; text-align: center; padding: 3px 0; margin: -2px -1px 0 -2px;'>DISAPPROVED</div><ul><li style='color: black;'><?=$row['txtRejection']?></li></ul></li></ul></div><? } ?>
				<a href="editevent.php?id=<?=$row['ID']?>&idStore=<?=$_REQUEST['idStore']?>&intDate=<?=$_REQUEST['intDate']?>" title="<?=str_replace('"',"&quot;",stripslashes($row['chrDescription']))?>" class="<?=$row['chrStyleClass']?>"><?=stripslashes(($row['idEventTitle'] == "" || $row['idEventTitle'] == 0 ? $row['chrTitle'] : $row['chrEventTitle']))?></a>
				<p class="start"><?=$row['tBegin']?> to</p>
				<p class='end'><?=$row['tEnd']?></p>
<? 				if($row['intPresenters'] > 0 && $row['idEventCategory'] == 2) { 
						$result = do_mysql_query("SELECT chrName, chrCompanyLabel FROM EventPresenters JOIN Presenters ON Presenters.ID=EventPresenters.idPresenter WHERE EventPresenters.intEventSeries='" . $row['intSeries'] . "'","Getting the presenters for event");
?>
					<div class='dropdownmenu'>
						<ul id='nav'>
							<li><img src='../images/profile-gray.gif' style='height: 16px; width: 16px;'>
								<ul>
<?						while($row_pres = mysql_fetch_assoc($result)) { ?>
									<li><?=$row_pres['chrName']?> (<?=$row_pres['chrCompanyLabel']?>)</li>
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
				</td><td style='vertical-align: top; width: 125px; white-space: normal;'><div class='top'><?=$intMonthDay?></div>
<? }?>
		</td>
		</tr>
	</table>


<div style='background-color: #C3C4C5;'>
<?	

$localization = mysql_fetch_assoc(do_mysql_query("SELECT idLocalization FROM Stores WHERE ID='".$_REQUEST['idStore']."'", 'Getting Store Localization'));


$submitbutton = "SELECT chrEventTitle, count(Events.ID) as intCount
	FROM EventTypeNames
	JOIN EventTypes ON EventTypeNames.idEventType=EventTypes.ID
	LEFT JOIN Events ON Events.idEventTitle=EventTypeNames.ID 
		AND Events.idStore='" . $_REQUEST['idStore'] . "' 
		AND Events.dDate LIKE '" . $intYear . "-" . ($intMonth<10 ? '0'.$intMonth : $intMonth) . "-%'
	WHERE EventTypeNames.bWeeklyRequired=1 
		AND !EventTypeNames.bDeleted 
		AND EventTypeNames.bShow 
		AND EventTypes.idLocalization='".$localization['idLocalization']."'
	GROUP BY EventTypeNames.chrEventTitle
	having intCount=0
	";
$submitbutton_results = do_mysql_query($submitbutton, 'get Submit button info');

$storeInfo = mysql_fetch_assoc(do_mysql_query("SELECT count(Events.ID) as eventCount, Stores.enStoreSize, StoreSize.chrStoreSize
	FROM Events
	JOIN Stores ON Stores.ID=Events.idStore
	JOIN StoreSize ON StoreSize.ID=Stores.idStoreSize 
	WHERE Events.idStore='" . $_REQUEST['idStore'] . "' AND Events.dDate LIKE '" . $intYear . "-" . ($intMonth<10 ? '0'.$intMonth : $intMonth) . "-%'
	GROUP BY Stores.enStoreSize", 'get storetype and events count'));

$showButton = 'Disallow';

if(($storeInfo['enStoreSize'] == 'Mini' || $storeInfo['chrStoreSize'] == 'Mini') && $storeInfo['eventCount'] >= 0) { $showButton = 'Approved'; }
if(($storeInfo['enStoreSize'] == '30 foot' || $storeInfo['chrStoreSize'] == '30 Foot') && $storeInfo['eventCount'] >= 12) { $showButton = 'Approved'; }
if(($storeInfo['enStoreSize'] == '45 foot' || $storeInfo['chrStoreSize'] == '45 Foot') && $storeInfo['eventCount'] >= 15) { $showButton = 'Approved'; }
if(($storeInfo['enStoreSize'] == 'High Profile' || $storeInfo['chrStoreSize'] == 'High Profile') && $storeInfo['eventCount'] >= 25) { $showButton = 'Approved'; }


			// Creating Variables to check Recaps 2 months prior to selected Month/Year
			$intRCheck = $_REQUEST['intDate'] - 2;
			$intRYear = 2000 + floor($intRCheck / 12);
			$intRMonth = ($intRCheck % 12)+1;	
			$intIncompleteRecaps = 0;
			$chrIncRecap = "";
			
			$rows = do_mysql_query("SELECT Events.ID, Recaps.chrStatus
				FROM Events
				LEFT JOIN Recaps on Events.ID = Recaps.idEvent
				JOIN EventTypes on Events.idEventType = EventTypes.ID
				WHERE Events.dDate LIKE '". $intRYear ."-". ($intRMonth < 10 ? '0'.$intRMonth : $intRMonth) ."-%' AND Events.idStore = '". $_REQUEST['idStore'] ."' AND EventTypes.bEditorReview = '1'", 'Getting Recaps List for Store');/////
			$RecapCount = 0;
	
	 // Re-Enable this after March 25, 2007
			$RecapsDate = date('F', strtotime(($intMonth-2).'/01/'.$intYear ));
			while($row = mysql_fetch_assoc($rows)) {
				if ($row['chrStatus'] != "Complete") {
				$RecapCount++;
				$chrIncRecap = "You must complete all Recaps from ".$RecapsDate." in order to submit this Calendar. Total Recaps Not Complete=".$RecapCount.".  Click <a href='recaps.php?idStore=".$_REQUEST['idStore']."&intDate=".($_REQUEST['intDate']-2)."'><span style='color:#FF0000;'>HERE</span></a> to goto your Recaps Page.";
				}
			}
	
$ignoreChk = mysql_fetch_assoc(do_mysql_query("SELECT bIgnore FROM Stores WHERE ID=".$_REQUEST['idStore'],"getting ignore status"));
if($ignoreChk['bIgnore'] == 1) {
	if($chrIncRecap == "") {
	?>
		<div style='padding: 5px;'><input type='button' class='' value='Submit Calendar' onclick='location.href="submit.php?idStore=<?=$_REQUEST['idStore']?>&amp;intDate=<?=$_REQUEST['intDate']?>";' /></div>
	<?  } else { ?>
		<div style='padding: 5px;'><input disabled type='button' class='' value='Submit Calendar' onclick='location.href="submit.php?idStore=<?=$_REQUEST['idStore']?>&amp;intDate=<?=$_REQUEST['intDate']?>";' /> <span><?=$chrIncRecap?></span></div>
	<?  } 


} else { 
	if($monthChk['enStatus'] != 'Submitted' && $monthChk['enStatus'] != 'Approved') { ?>
		<table style='width: 100%; '>
			<tr>
				<td style='width: 0.1cm'>
	<? if($storeInfo['enStoreSize'] == 'Mini' || $storeInfo['chrStoreSize'] == 'Mini') {
			$ok = 1;
			$size = 'Mini';
		} else {
			$ok = mysql_num_rows($submitbutton_results) == 0 ? 1 : 0;
			$size = '';
		} 
		if($edit_mode && $is_my_store && ($_REQUEST['intDate'] == ($current_month) || $_REQUEST['intDate'] == ($current_month + 1) || $_REQUEST['intDate'] == ($current_month + 2)) && $showButton=="Approved" && $ok == 1) { 

			
			if($chrIncRecap == "") {
			?>
				<input type='button' class='' value="<?=($monthChk['enStatus'] == 'Rejected' ? 'Re-Submit Calendar' : 'Submit Calendar')?>" onclick='location.href="submit.php?idStore=<?=$_REQUEST['idStore']?>&amp;intDate=<?=$_REQUEST['intDate']?>";' />
		<?  } else { ?>
				<input disabled type='button' style='color: #999;' value='Submit Calendar' onclick='location.href="submit.php?idStore=<?=$_REQUEST['idStore']?>&amp;intDate=<?=$_REQUEST['intDate']?>";' />
			
		<?  } 
		
		
			} else { ?>
					<input disabled type='button' style='color: #999;' value='Submit Calendar' onclick='location.href="submit.php?idStore=<?=$_REQUEST['idStore']?>&amp;intDate=<?=$_REQUEST['intDate']?>";' />
	<?		} ?>
				</td>
				<td style='padding-left: 10px;'>
	
			<?=($chrIncRecap != "" ? $chrIncRecap : '')?>
	<?		if($is_my_store) { 
				if($edit_mode) { 
					if($showButton!='Approved') { 
						if($storeInfo['enStoreSize'] == '' && $storeInfo['chrStoreSize'] == '') { ?> 
							<p>You must supply this store with a Store Size.</p> 
	<? 					} else if($storeInfo['eventCount'] > 0) { ?>
						<p>The minimum number of workshops needed for your store size has not been met.</p>	
	<?					} ?>
	<?				} else if(mysql_num_rows($submitbutton_results) != 0 && $size != 'Mini') 	{ ?>				
						<p>At least one of each of the following Workshops must be created before you may submit this calendar<br /> --
	<? 	$count=0;
		while($eventName = mysql_fetch_assoc($submitbutton_results)) { ?>
			<?=($count>0 ? ", " : "")?>"<?=$eventName['chrEventTitle']?>"
	<?		$count++;	
		}
	?>					</p>
	<?                } else if($_REQUEST['intDate'] != ($current_month) && $_REQUEST['intDate'] != ($current_month + 1) && $_REQUEST['intDate'] != ($current_month + 2)) { ?>				
						<p>You may only submit a calendar when it is within the next one or two months of the current date.</p>
	<?				   } else { 
	?>
							<p>When you are done making changes to your workshops/events calendar, click the "<?=($monthChk['enStatus'] == 'Rejected' ? 'Re-Submit Calendar' : 'Submit Calendar')?>" button to send it to the Retail Workshops/Events team.</p>
	<?							
					}
	
	
				} else if($this_month_status == 'Submitted') { ?>
					<p>Your month calendar has been submitted to the Retail Workshops/Events team for approval.  The calendar cannot be changed.  You will need to contact <a href="mailto:retailevents@apple.com">retailevents@apple.com</a> with your change request.</p>
	<?			} ?>
	<?		} else { // if this is not the user's store, and they are not a corp user ?>
	<?			if($editChk['enStatus'] == 'Submitted') { ?>
					<p>This workshop/event calendar has been submitted and is awaiting approval.</p>
	<?			} else if($editChk['enStatus'] == 'Approved') { ?>
					<p>This workshop/event calendar has been approved by Retail Workshops/Events.</p>
	<?			} ?>
	<?		} ?>
	
	<?		if($approval_mode) { ?>
					<p>You may make any changes, then click the "Approve Calendar" button above to finalize this store's calendar.</p>
	<?		} else if($is_retailevents_user && $edit_mode && $this_month_status != 'Approved') { ?>
					<p>This calendar has not yet been submitted, you can still make changes to it.</p>
	<?		} else if($is_retailevents_user) {?>
					<p>Though it has been approved, you may make changes to it.</p>
	<?		} ?>	
				</td>
			</tr>
		</table>
<?	}
}  ?>
</div>
<?	if($monthChk['enStatus'] == '' && ($_SESSION['idType'] == 1 || in_array($_REQUEST['idStore'],$_SESSION['intStoreList']))) { ?>


<?				if(false) { ?>
		<div style='background-color: #C3C4C5; padding: 3px; margin-top: 5px;'><input type='button' onclick='javascript:warning("1","1");' value='Clear All Workshops' /> This will delete all events for this month only.  <span style='color: red; background: black;'>&nbsp;! USE WITH CAUTION !&nbsp;</span></div>


<?				} ?>


<?	} ?>
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
