<?php
	$BF = '../';
	$title = 'Presenters';
	$curPage='presenters';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');
	

	// Getting rid of the notices/warning for the following thing(s)
	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ""; }
	if(!isset($_REQUEST['myList']) || !is_numeric($_REQUEST['myList'])) { 
		if(!isset($_SESSION['myListPresenters']) || !is_numeric($_SESSION['myListPresenters'])) { 
			$_REQUEST['myList'] = 1;
			$_SESSION['myListPresenters'] = 1;
		} else {
			$_REQUEST['myList'] = $_SESSION['myListPresenters'];
		}
	} else { 
		$_SESSION['myListPresenters'] = $_REQUEST['myList'];
	}
	
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrName"; }
	
	$q = "SELECT Presenters.ID, Presenters.chrName, Presenters.chrCity, Presenters.chrEmail, Presenters.chrOfficePhone, Presenters.idStore,Presenters.chrCompanyLabel
			FROM Presenters
			WHERE !Presenters.bDeleted
		". ($_REQUEST['chrSearch'] != '' ? " AND Presenters.chrName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%'" : '') ." 
		". (count($_SESSION['intStoreList']) && $_REQUEST['myList'] == 1 ? " AND Presenters.idStore IN (". implode(',',$_SESSION['intStoreList']) .") " : '' ) ."
		ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	

	$result = database_query($q,"getting presenters");

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	// Set the title, and add the doc_top
	include($BF . 'includes/top_events.php');

	//You need the next 2 lines if you are going to be using the DELETE function. This will bring in the overlay and also send the TABLE to remove from
	$DeleteTable = "Presenters"; //This is the table that the overlay will use.
	include($BF. 'includes/overlay.php');
?>

<?	if(@count($_SESSION['InfoMessage'])) { ?>
	<div class='Messages'>
<?		foreach($_SESSION['InfoMessage'] as $msg) { ?>
						<div class='InfoMessage'><?=$msg?></div>
<?		}
		$_SESSION['InfoMessage'] = array(); ?>
	</div>
<? } ?>

					<div class="AdminTopicHeader">Presenters</div>
					<div class="AdminInstructions">Click on a Guest Presenter to learn more about them or press the "Add" button to add a guest presenter.</div>
					
					<table class="AdminUtilityBar">
						<tr>
							<td><input type='button' value='Add Presenter' onclick="location.href='addpresenter.php'" /></td>
							<form id="search" method="get" action="">
							<td style='width: 100%; white-space: nowrap; text-align: right;'>
								<select name='myList' onChange='this.form.submit();'>
									<option value='1'<?=($_SESSION['myListPresenters'] == 1 ? ' selected' : '')?>>My Presenters</option>
									<option value='2'<?=($_SESSION['myListPresenters'] == 2 ? ' selected' : '')?>>All Presenters</option>
								</select>

								<input type="search" placeholder="Search Presenters" autosave="Stores" results='5' name="chrSearch" value='<?=$_REQUEST['chrSearch']?>' /></td>
							</form>
						</tr>
					</table>


					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
						<?  sortList('Name/Artist', 'chrName');
							sortList('Company/Label', 'chrEmail');
							sortList('City', 'chrCity');
							sortList('Main Phone', 'chrOfficePhone');
							sortList('Email', 'chrEmail');
	if($_SESSION['myListPresenters'] == '1' || $_SESSION['idType'] == 1) { ?> 
							<th class='options'><img src="<?=$BF?>images/options.gif"></th>
<?	} ?>
						</tr>
<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($result)) { 
		$link = 'location.href="editpresenter.php?id='. $row["ID"] .'";';
?>
						<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrName']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrCompanyLabel']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrCity']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrOfficePhone']?></td>
							<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrEmail']?></td>
							<td class='options'><?=deleteButton($row['ID'],$row['chrName'])?></td>			
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