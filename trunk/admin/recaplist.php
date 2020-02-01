<?php
	$BF = '../';
	$title = 'Store Recaps';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');

	// Getting rid of the notices/warning for the following thing(s)
	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ""; }

	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root
		idType of 1 = Super user 
		idType of 2 = Editor
		idType of 3 = Corp Events
		idType of 4 = Store
		
		$pageaccess is array of user types that can access the page
	*/
	
	$pageaccess = array("1","2","3");
	if(!in_array($_SESSION['idType'],$pageaccess)) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: '. $BF ."nopermission.php"); die(); }
	// End Security Check
		
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "rSuccess DESC, chrTitle ASC, dDateFormat ASC"; $_REQUEST['ordCol'] = ""; }
	
	// get the current month
	$intCurrentDay = idate('d');
	$intCurrentMonth = idate('m');
	$intCurrentYear = idate('Y');
	$current_month = (($intCurrentYear-2000)*12)+$intCurrentMonth-1;
	
	if($_REQUEST['intDate'] == '' && $_REQUEST['idStore'] == '') {
		header("Location: recaplist.php?intDate=" . ($current_month));
		die();
	}
	
	$intYear = 2000 + floor($_REQUEST['intDate'] / 12);
	$intMonth = ($_REQUEST['intDate'] % 12)+1;
	
	$q = "SELECT Events.ID, Events.chrTitle, Stores.chrName as chrStore, Recaps.rSuccess as rSuccess, Recaps.chrAttendance as chrAttendance, DATE_FORMAT(dDate, '%D %M, %Y') as dDateFormat,
		(SELECT count(ID) FROM RecapImages WHERE idEvent=Events.ID) as intPhotos
		FROM Recaps
		JOIN Events ON Events.ID=Recaps.idEvent
		JOIN Stores ON Stores.ID=Events.idStore
		WHERE chrStatus='Complete' AND dDate like '".$intYear."-". ($intMonth < 10 ? '0'.$intMonth : $intMonth) ."-%' 
		". ($_REQUEST['intStore'] != "" ? "AND Events.idStore = '". $_REQUEST['intStore']."'" : "")."
		ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	

	$result = database_query($q,"getting stores");
	
	$q = "SELECT ID, chrName
		FROM Stores
		ORDER BY chrName";
	
	$StoreList = database_query($q,"Getting Store List")

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top_admin2.php');
?>
					<div class="AdminTopicHeader">Completed Recaps</div>
					<div class="AdminInstructions">This is a list of completed recaps for the stores after the event have been completed.</div>
					
					<!-- Tool Bar with the Add Store and Search button -->
					<table class="AdminUtilityBar">
						<tr>
						<form method='get' action='' id='Form'>
							<td valign="center">
								<select name='intDate' onchange='this.form.submit();'>
<?

	// build list of months to display.
	$months = array();

	// start with the latest thing we'll show them, which is 12 months in the future
	for($monthloop = $current_month+12; $monthloop > $current_month; $monthloop--) { 
		$months[$monthloop] = '';
	}

	// now add each of the months that have any activity in them
	$rows = database_query("SELECT DISTINCT intMonth, intYear, ((intYear-2000)*12)+intMonth-1 AS intDate
		FROM StoreMonths 
		ORDER BY intYear DESC, intMonth DESC
		", 'get current months');
	while($row = mysqli_fetch_assoc($rows)) {
		$months[$row['intDate']] = '';
	}

	// sort the list
	krsort($months);

	foreach($months as $monthloop => $enStatus) {
		$loopYear = 2000 + floor($monthloop / 12);
		$loopMonth = ($monthloop % 12)+1;
?>
									<option value='<?=$monthloop?>' <?=($_REQUEST['intDate'] == $monthloop?'selected="selected"':'')?>><?=strftime('%B %Y', mktime(0, 0, 0, $loopMonth, 1, $loopYear))?><?=$enStatus?></option>
<?
	}
?>
								</select>	
								<select name='intStore' onchange='this.form.submit();'>
								<option value=''>Show All Stores</option>
<?
	while($row = mysqli_fetch_assoc($StoreList)) {
		?> <option value='<?=$row['ID']?>' <?=($_REQUEST['intStore'] == $row['ID']?'selected="selected"':'')?>><?=$row['chrName']?></option>
<?		
	}

?>								
								</select>
							</td>						
						</form>
						</tr>
					</table>
					
					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
							<? sortList('Workshop/Event Title', 'chrTitle', '', 'intDate='.$_REQUEST['intDate'].'&intStore='.$_REQUEST['intStore']); ?>
							<? sortList('Store Name', 'chrStore', '', 'intDate='.$_REQUEST['intDate'].'&intStore='.$_REQUEST['intStore']); ?>
							<? sortList('Event Date', 'dDateFormat', '', 'intDate='.$_REQUEST['intDate'].'&intStore='.$_REQUEST['intStore']); ?>
							<? sortList('Photos', 'intPhotos', '', 'intDate='.$_REQUEST['intDate'].'&intStore='.$_REQUEST['intStore']); ?>
							<? sortList('Attendees', 'chrAttendance', '', 'intDate='.$_REQUEST['intDate'].'&intStore='.$_REQUEST['intStore']); ?>
							<? sortList('Success', 'rSuccess', '', 'intDate='.$_REQUEST['intDate'].'&intStore='.$_REQUEST['intStore']); ?>							
								
						</tr>
<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) { 
		$link = 'location.href="showrecap.php?id='. $row["ID"] .'";';
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrTitle']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrStore']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['dDateFormat']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['intPhotos']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrAttendance']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['rSuccess']?></td>							
						</tr>
<?	} ?>
					</table>
<?
	if($count == 0) { 
?>
					<div style='padding: 3px; border: 1px solid gray; border-top: none; text-align:center'>No records to display, Please be sure to select a Store</div>
<?
	}
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>