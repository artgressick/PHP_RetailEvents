<?php
	$BF = '../../';
	$title = 'Weekly/Quarterly Report';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');

	if (isset($_REQUEST)) { $info = $_REQUEST; } else { $info = 0; }

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
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "dYear DESC,quarter DESC,dWeek DESC"; $_REQUEST['ordCol'] = ""; }
	
	if(isset($info['dStart'])) { $Begin_Date = date('Y-m-d',strtotime($info['dStart'])); }
	if(isset($info['dEnd'])) { $End_Date = date('Y-m-d',strtotime($info['dEnd'])); }
	
		$q = "SELECT Events.ID, chrCountry, year(dDate) as dYear, week(dDate) as dWeek, IF((quarter(dDate) + 1) = '5','1',(quarter(dDate) + 1)) as quarter, count(Events.ID) as intCount, chrCategory
			FROM Events
			JOIN Stores ON Stores.ID=Events.idStore
			JOIN EventTypes ON EventTypes.ID=Events.idEventType
			JOIN EventCategory ON EventTypes.idEventCategory = EventCategory.ID
			WHERE !Stores.bDeleted AND dDate >= '".@$Begin_Date."' AND dDate <= '".@$End_Date."'
			GROUP BY chrCountry, year(dDate), quarter(dDate), week(dDate), chrCategory
			ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	

	$_SESSION['REReport'] = $q;

	$result = database_query($q,"Getting Report Information");

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top_admin2.php');
?>
					<div class="AdminTopicHeader">Weekly/Quarterly Report</div>
					<div class="AdminInstructions">This is the weekly/quarterly report, please choose a begin date and a end date, and Click the Submit Button<br />Enter Times in the following format: Month/Day/Year (i.e. 1/15/2006) <i>NOTE: If date is entered in a different format results will vary</i></div>
					
					<!-- Tool Bar with the Add Store and Search button -->
					<table class="AdminUtilityBar">
						<tr>
						<form method='post' action='' enctype="multipart/form-data">
							<td valign="center">
								Begin Date: <input type="text" name="dStart" id="dStart" maxlength="20" size="10" value="<?=$info['dStart']?>" /> End Date: <input type="text" name="dEnd" id="dEnd" maxlength="20" size="10" value="<?=$info['dEnd']?>" /> <input type="submit" id="submit" name="submit" value="Submit" /> <input type="button" value="Reset" onclick="location.href='wqreport.php'" />
							</td>
							<td align="right" valign="center"><a href="_wqreport.php" />Export to Excel</a></td>				
						</form>
						</tr>
					</table>
					
					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
							<? sortList('Country', 'chrCountry', '', 'dStart='.$info['dStart'].'&dEnd='.$info['dEnd']); ?>
							<? sortList('Year', 'dYear', '', 'dStart='.$info['dStart'].'&dEnd='.$info['dEnd']); ?>
							<? sortList('Quarter', 'quarter', '', 'dStart='.$info['dStart'].'&dEnd='.$info['dEnd']); ?>
							<? sortList('Week', 'dWeek', '', 'dStart='.$info['dStart'].'&dEnd='.$info['dEnd']); ?>
							<? sortList('Count', 'intCount', '', 'dStart='.$info['dStart'].'&dEnd='.$info['dEnd']); ?>
							<? sortList('Category', 'chrCategory', '', 'dStart='.$info['dStart'].'&dEnd='.$info['dEnd']); ?>							
								
						</tr>
<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) { 
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td style=''><?=$row['chrCountry']?></td>
							<td style=''><?=$row['dYear']?></td>
							<td style=''><?=$row['quarter']?></td>
							<td style=''><?=$row['dWeek']?></td>
							<td style=''><?=$row['intCount']?></td>
							<td style=''><?=$row['chrCategory']?></td>							
						</tr>
<?	} ?>
					</table>
<?
	if($count == 0) { 
?>
					<div style='padding: 3px; border: 1px solid gray; border-top: none; text-align:center'>No records to display, Please be sure to enter a Begin Date and End Date.</div>
<?
	}
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>