<?php
	$BF = "../";
	require("../_lib2.php");
	$title = 'Reviews';
	include($BF. 'includes/meta2.php');
	
	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root
		idType of 1 = Super user 
		idType of 2 = Editor
		idType of 3 = Corp Events
		idType of 4 = Store
		
		$pageaccess is array of user types that can access the page
	*/
	
	$pageaccess = array("1","2");
	if(!in_array($_SESSION['idType'],$pageaccess)) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: '. $BF ."nopermission.php"); die(); }
	// End Security Check

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	
	// get the current month information for the coe calendar
	$intMonth = idate('m');
	$intYear = idate('Y');
	$current_month = (($intYear-2000)*12)+$intMonth-1;
	if(!isset($_REQUEST['intDate'])) { $_REQUEST['intDate'] = $current_month+1; }
	
	if (!isset($_REQUEST['bReviewed'])) { $_REQUEST['bReviewed'] = 0; }
		
	$intYear = 2000 + floor($_REQUEST['intDate'] / 12);
	$intMonth = ($_REQUEST['intDate'] % 12)+1;
	$intMonth = ($intMonth < 10 ? '0'.$intMonth : $intMonth);
	$dDate = $intYear."-".$intMonth."-";
	
	// This is for the sorting of the rows and columns.  We must set the default order and name
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrName"; }
	
	//Revised to count only the events that need editor approval
	$q = "SELECT Stores.ID, Stores.chrName, StoreMonths.enStatus,
			(SELECT count(Events.ID) FROM Events JOIN EventTypes ON Events.idEventType = EventTypes.ID 
				WHERE EventTypes.bEditorReview=1 
					AND Events.bReviewed = ".$_REQUEST['bReviewed']."  
					AND Events.dDate LIKE '".$intYear."-".$intMonth."-%' 
					AND Events.bDissaproved=0
					AND idStore=Stores.ID) as intTotal
		FROM Stores 
		JOIN StoreMonths ON Stores.ID = StoreMonths.idStore
		WHERE !bDeleted 
			AND StoreMonths.enStatus = 'Submitted' 
			AND StoreMonths.intMonth = '".$intMonth."' 
			AND StoreMonths.intYear = '".$intYear."'
			AND Stores.idLocalization IN (SELECT GROUP_CONCAT(Localization.ID) FROM Localization JOIN Users ON Users.ID=".$_SESSION['idUser']." WHERE FIND_IN_SET(Localization.ID,chrLoc) GROUP BY Localization.ID)
		ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];

	$result = database_query($q,"getting store needing review");
	
	include($BF. 'includes/top_admin2.php');
?>
					<div class="AdminTopicHeader">COE Editor Review</div>
					<div class="AdminInstructions">These are stores that have events needing approval. Click any store to view the special events which need editing.</div>
					
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
								<select name='bReviewed' id='bReviewed' onchange='this.form.submit();'>
									<option value='0' <?=($_REQUEST['bReviewed'] == 0 ? 'selected="selected"' : "")?>>Not Reviewed</option>
									<option value='1' <?=($_REQUEST['bReviewed'] == 1 ? 'selected="selected"' : "")?>>Reviewed & Approved</option>
								</select>
								
							</td>
							</form>
						</tr>
					</table>

					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
							<? sortList('Name', 'chrName'); ?>
							<? sortList('Special Events', 'intTotal'); ?>
							<? sortList('Current Calendar Status', 'enStatus'); ?>
						</tr>
<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) {
		if($row['intTotal'] > 0) {
		$link = "reviewevents.php?id=" . $row['ID'] . "&month=" . $_REQUEST['intDate'] ."&status=".$_REQUEST['bReviewed'];
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td style='cursor: pointer;' onclick='location.href="<?=$link?>"'><?=$row['chrName']?></td>
							<td style='cursor: pointer;' onclick='location.href="<?=$link?>"'><?=$row['intTotal']?></td>
							<td style='cursor: pointer;' onclick='location.href="<?=$link?>"'><?=$row['enStatus']?></td>
						</tr>
<?
		}
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