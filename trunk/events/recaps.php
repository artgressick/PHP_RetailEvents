<?php
	$BF = "../";
	require($BF. "_lib2.php");
	$curPage = "recaps";
	$title = 'Recaps';
	include($BF. 'includes/meta2.php');
	
	// get the current month
	$intCurrentDay = idate('d');
	$intCurrentMonth = idate('m');
	$intCurrentYear = idate('Y');
	$current_month = (($intCurrentYear-2000)*12)+$intCurrentMonth-1;
	
	if($_REQUEST['intDate'] == '' && $_REQUEST['idStore'] == '') {
		if($_SESSION['idType'] == 1) {
			header("Location: recaps.php?idStore=56&intDate=" . ($current_month));
			die();
		} else { 
			header("Location: recaps.php?idStore=" . $_SESSION['intStoreList'][0] . "&intDate=" . ($current_month));
			die();
		}
	}

	// Checking request variables
	($_REQUEST['idStore'] == "" || !is_numeric($_REQUEST['idStore']) ? ErrorPage() : "" );
	($_REQUEST['intDate'] == "" || !is_numeric($_REQUEST['intDate']) ? ErrorPage() : "" );
	
	$intYear = 2000 + floor($_REQUEST['intDate'] / 12);
	$intMonth = ($_REQUEST['intDate'] % 12)+1;

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "dDate"; }
	
	
	$q = "SELECT Events.ID, Events.chrTitle, DATE_FORMAT(dDate, '%D %M, %Y') as dDateFormat, dDate, DAY(dDate) as dDay,Recaps.rSuccess as rSuccess, Recaps.chrAttendance as chrAttendance,Recaps.chrStatus,

		(SELECT count(ID) FROM RecapImages WHERE idEvent=Events.ID) as intPhotos
		FROM Events
		LEFT JOIN Recaps ON Recaps.idEvent=Events.ID
		JOIN EventTypes ON EventTypes.ID=Events.idEventType
		JOIN EventCategory ON EventCategory.ID=EventTypes.idEventCategory
		WHERE idStore=". $_REQUEST['idStore'] ." AND dDate like '". $intYear ."-". ($intMonth < 10 ? '0'.$intMonth : $intMonth) ."-%' AND EventTypes.bEditorReview = '1'	
		ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
			
	$results = database_query($q,"Getting recaps for store");
	
	// Getting my specific stores from the list.
	$q = "SELECT Stores.ID, Stores.chrName
		FROM Stores
		WHERE !bDeleted AND ID IN (SELECT idItem FROM ACL WHERE idUser='" . $_SESSION['idUser'] . "')
		ORDER BY chrName";
	$myStoreList = database_query($q,"getting my stores");
	
	// Getting ALL other stores BUT mine. Checks to see if there was any stores associated to you first.
	$q = "SELECT Stores.ID, Stores.chrName
		FROM Stores
		WHERE !bDeleted " . (mysqli_num_rows($myStoreList) ? "AND ID NOT IN (SELECT idItem FROM ACL WHERE idUser='" . $_SESSION['idUser'] . "')" : '') . "
		ORDER BY chrName";
	$storeList = database_query($q,"getting all other stores");

	
	// Set the title, and add the doc_top
	include($BF . 'includes/top_events.php');

	if(@count($_SESSION['InfoMessage'])) { ?>
	<div class='Messages'>
<?		foreach($_SESSION['InfoMessage'] as $msg) { ?>
						<div class='InfoMessage'><?=$msg?></div>
<?		}
		$_SESSION['InfoMessage'] = array(); ?>
	</div>
<? } ?>
				<div class="AdminTopicHeader">Store Recaps</div>
				<div class="AdminInstructions">Click on any of the events listed to write up a recap of that event.</div>
				<!-- Tool Bar with the Add Store and Search button -->
				<table class="AdminUtilityBar">
					<tr>
						<td style='padding-left: 5px; width: 250px;'>
							<form id="search" method="get" action="">
								<!-- Search Area with it's own FORM to GET information -->
								<span style=''>
 <span style='font-size: 10px'>Stores: </span><select name='idStore' onchange='this.form.submit();'>
<?
	// If there ARE stores associated to your name, display those on top with optgroups!  otherwise just spit out all the stores.
	if(mysqli_num_rows($myStoreList) > 0) {
?>
							<optgroup label="My Stores">
<?
		$count=0;	
		while($row = mysqli_fetch_assoc($myStoreList)) { 
			if($count==0 && $_REQUEST['idStore'] == '') { $_REQUEST['idStore'] = $row['ID']; }
?>
								<option value='<?=$row['ID']?>'<?=($row['ID']==$_REQUEST['idStore'] ? ' selected' : '')?>><?=$row['chrName']?></option>
<?
			$count++;
		}
?>
							</optgroup>
							<optgroup label="Other Stores">
<?
		while($row = mysqli_fetch_assoc($storeList)) {
?>
							<option value='<?=$row['ID']?>'<?=($row['ID']==$_REQUEST['idStore'] ? ' selected' : '')?>><?=$row['chrName']?></option>
<?
		}
?>
							</optgroup>
<?
	} else { 
		$count = 0;
		while($row = mysqli_fetch_assoc($storeList)) { 
			if($count==0 && $_REQUEST['idStore'] == '') { $_REQUEST['idStore'] = $row['ID']; }
?>
							<option value='<?=$row['ID']?>'<?=($row['ID']==$_REQUEST['idStore'] ? ' selected' : '')?>><?=$row['chrName']?></option>
<?
		$count++;
		}
	}
?>
						</select>
						
						</td>
						<td style='width: 100%; white-space: nowrap; text-align: right;'>
					<span style='font-size: 10px;'>Dates:</span>
						<select name='intDate' onchange='this.form.submit();' style='wid'>
<?
						// build list of months to display.
						$months = array();

						// start with the latest thing we'll show them, which is 12 months in the future
						for($monthloop = $current_month+1; $monthloop >= 79; $monthloop--) { 
							$months[$monthloop] = '';
						}

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
								
								</form>
							</td>
						</tr>
					</table>
				</div>
           
	
	
		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>
<?
				$extra = "idStore=".$_REQUEST['idStore']."&intDate=".$_REQUEST['intDate'];
				sortList('Workshop/Event Name', 'chrTitle','',$extra);
				sortList('Date', 'dDate','',$extra);
?>
				<th>Recap Status</th>
<?
				sortList('Photos Added', 'intPhotos','',$extra);
				sortList('Attendees', 'chrAttendance','',$extra);
				sortList('Success', 'rSuccess','',$extra);
?>
			</tr>
<?	$count=0;
	while($row = mysqli_fetch_assoc($results)) { 
		$bf = strtotime($row['dDate']);
		$af = strtotime($intCurrentYear.'-'.$intCurrentMonth.'-'.$intCurrentDay);
		$bDisabled = ($bf <= $af ? 0 : 1);
		
		if ($bDisabled == 0) {
			$link = 'location.href="'.((in_array($_REQUEST['idStore'],$_SESSION['intStoreList']) || $_SESSION['idType'] == 1) ? 'editrecap.php' : 'viewrecap.php').'?id='.$row['ID'].'&idStore='.$_REQUEST['idStore'].'&intDate='.$_REQUEST['intDate'].'";';
			$color = "inherit";
			if($row['chrStatus'] == "") {
				$status = "Incomplete";
			} else {
				$status = $row['chrStatus'];
			}
		} else {
			$link = "#";
			$color = "#999";
			$status = "This event has not yet occured";
		}
		
	?>
	
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;color:<?=$color?>;' onclick='<?=$link?>'><?=$row['chrTitle']?></td>
				<td style='cursor: pointer;color:<?=$color?>;' onclick='<?=$link?>'><?=$row['dDateFormat']?></td>
				<td style='cursor: pointer;color:<?=$color?>;' onclick='<?=$link?>'><?=$status?></td>
				<td style='cursor: pointer;color:<?=$color?>;' onclick='<?=$link?>'><?=$row['intPhotos']?></td>
				<td style='cursor: pointer;color:<?=$color?>;' onclick='<?=$link?>'><?=$row['chrAttendance']?></td>
				<td style='cursor: pointer;color:<?=$color?>;' onclick='<?=$link?>'><?=$row['rSuccess']?></td>
			</tr>
	
<?
	} 
	if($count == 0) { ?>
			<tr>
				<td align="center" colspan='6'>No Recaps could be found in the database.</td>
			</tr>
<?	} ?>
		</table>

<?
	include($BF. 'includes/bottom2.php');
?>
