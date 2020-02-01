<?php
	$BF = '../';
	$title = 'COE Approval';
	require("../_lib2.php");
	include($BF. 'includes/meta2.php');
	
	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root
		idType of 1 = Super user 
		idType of 2 = Editor
		idType of 3 = Corp Events
		idType of 4 = Store
		
		$pageaccess is array of user types that can access the page
	*/
	
	$pageaccess = array("1");
	if(!in_array($_SESSION['idType'],$pageaccess)) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: '. $BF ."nopermission.php"); die(); }
	// End Security Check
	
	// get the current month information for the coe calendar
	$intMonth = idate('m');
	$intYear = idate('Y');
	$current_month = (($intYear-2000)*12)+$intMonth-1;

	if(!isset($_REQUEST['intDate'])) { $_REQUEST['intDate'] = $current_month+1; }
	
	$intYear = 2000 + floor($_REQUEST['intDate'] / 12);
	$intMonth = ($_REQUEST['intDate'] % 12)+1;

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "enStatus DESC, bReview DESC, Stores.chrName"; }
	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ''; }
	
	$q = "SELECT Stores.ID, Stores.chrName as chrStore, Regions.chrName AS chrRegionName, 
			(SELECT enStatus FROM StoreMonths WHERE idStore=Stores.ID AND intMonth='". $intMonth ."' AND intYear='". $intYear ."') AS enStatus,
			(SELECT ID FROM Events WHERE Events.dDate LIKE '". $intYear ."-". $intMonth ."%' AND Events.idStore=Stores.ID LIMIT 1) AS bHasEvents,
			(SELECT count(Events.ID) 
				FROM Events
				JOIN EventTypes ON Events.idEventType = EventTypes.ID
				WHERE bReviewed=0 
				AND EventTypes.bEditorReview=1 
				AND Events.dDate LIKE '". $intYear ."-". ($intMonth < 10 ? '0'.$intMonth : $intMonth) ."%' 
				AND Events.idStore=Stores.ID) as bReview
			FROM Stores 
			JOIN Regions ON Regions.ID=Stores.idRegion 
			WHERE !Stores.bDeleted AND Stores.idLocalization IN (SELECT GROUP_CONCAT(Localization.ID) FROM Localization JOIN Users ON Users.ID=".$_SESSION['idUser']." WHERE FIND_IN_SET(Localization.ID,chrLoc) GROUP BY Localization.ID) ";
	if($_REQUEST['chrSearch'] != '') {  // if there is a search term
		$q .= "AND ((Stores.chrName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')
			OR (Stores.chrCountry LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')
			OR (Regions.chrName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')) ";
	}
	
	$q .= " 
		ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
	$result = database_query($q,"getting event stores");

	include($BF. 'includes/top_admin2.php');

?>
					<div class="AdminTopicHeader">COE Calendar Approval</div>
					<div class="AdminInstructions">These are the calendars that need approving. STATUS refers to the SUBMISSION of the calendar by the store. REVIEW refers to the status of the Content Editor. Only stores that have both SUBMITTED and READY should be push to Apple.com website.</div>
					
					<table class="AdminUtilityBar">
						<tr>
							<form method='get' action='' id='Form'>
							<td>
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
							</td>
							<td style="text-align:right;">
								<input type="search" name="chrSearch" placeholder="Search Store" autosave="Stores" results='5' value='<?=$_REQUEST['chrSearch']?>'>
							</td>
							</form>
						</tr>
					</table>

					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
							<? sortList('Name', 'Stores.chrName', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&intDate=' . $_REQUEST['intDate']); ?>
							<? sortList('Region', 'chrRegionName', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&intDate=' . $_REQUEST['intDate']); ?>
							<? sortList('Store Calendar Status', 'enStatus', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&intDate=' . $_REQUEST['intDate']); ?>
							<? sortList('Review Status', 'bReview', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&intDate=' . $_REQUEST['intDate']); ?>
						</tr>
<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) { 
		$link = "massapprove.php?idStore= " . $row['ID'] . "-" . $_REQUEST['intDate'];
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td class='nowrap'
							onclick='location.href="<?=$link?>";'><a href='<?=$link?>' class='listlink'><?=$row['chrStore']?></a></td>
							<td class='nowrap'
								onclick='location.href="<?=$link?>";'><a href='<?=$link?>' class='listlink'><?=$row['chrRegionName']?></a></td>
							<td class='nowrap'
								onclick='location.href=<?=$link?>";'><a href='<?=$link?>' class='listlink'><?=($row['enStatus'] == '' ? ($row['bHasEvents'] ? 'Activity' : '(empty)') : $row['enStatus'])?></a></td>
							<td class='nowrap' 
								onclick='location.href="<?=$link?>";'><a href='<?=$link?>' class='listlink'><?=(($row['bReview'] == 0 ? 'Ready' : 'Needs Review ('.$row['bReview'].' left to approve)') )?></a></td>			
						</tr>
<?
	}
?>
					</table>
<?
	if($count == 0) { 
?>
					<div style='padding: 3px; border: 1px solid gray; border-top: none; text-align:center'>No records to display</div>
<?
	}
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>