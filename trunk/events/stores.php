<?php
	$BF = "../";
	require($BF. "_lib2.php");
	$curPage = "stores";
	$title = 'Stores';
	include($BF. 'includes/meta2.php');
	
	// Getting rid of the notices/warning for the following thing(s)
	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ""; }
	if(!isset($_REQUEST['myList']) || !is_numeric($_REQUEST['myList'])) { 
		if(!isset($_SESSION['myListStores']) || !is_numeric($_SESSION['myListStores'])) { 
			$_REQUEST['myList'] = 1;
			$_SESSION['myListStores'] = 1;
		} else {
			$_REQUEST['myList'] = $_SESSION['myListStores'];
		}
	} else { 
		$_SESSION['myListStores'] = $_REQUEST['myList'];
	}

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrName"; }
	
	if(!isset($_REQUEST['myList']) && $_SESSION['idType'] != 1) { $_REQUEST['myList'] = '1'; }	

	$q = "SELECT Stores.*, Regions.chrName AS chrRegion
			FROM Stores
			JOIN Regions ON Regions.ID=Stores.idRegion ";

	if($_SESSION['myListStores'] == '1') {
		$q .= " JOIN ACL ON Stores.ID=ACL.idItem AND ACL.enType='Stores' AND ACL.idUser='" . $_SESSION['idUser'] . "' ";
	}

	$q .= " WHERE !Stores.bDeleted ";

	if(@$_REQUEST['chrSearch'] != '') {  // if there is a search term
		$q .= "AND ((Stores.chrName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')
			OR (Stores.chrCountry LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')
			OR (Stores.chrCity LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')
			OR (Stores.chrState LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')
			OR (Regions.chrName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')) ";
	}
	$q .= " ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
	$store_result = database_query($q, 'get stores');


	include($BF . 'includes/top_events.php');

	if(@count($_SESSION['InfoMessage'])) { ?>
	<div class='Messages'>
<?		foreach($_SESSION['InfoMessage'] as $msg) { ?>
						<div class='InfoMessage'><?=$msg?></div>
<?		}
		$_SESSION['InfoMessage'] = array(); ?>
	</div>
<? } ?>
		<div class="AdminTopicHeader">Stores</div>
		<div class="AdminInstructions">Click any store to view general store information, specific information about the store's learning environment, and a list of registered users.<br />You will be able to edit any store of which you are a member.</div>


		<table class="AdminUtilityBar">
			<tr>
				<td>
				<form id="search" method="get" action="" style="padding:0;">
					<select name='myList' onChange='this.form.submit();'>
						<option value='1'<?=($_SESSION['myListStores'] == 1 ? ' selected' : '')?>>My Store</option>
						<option value='2'<?=($_SESSION['myListStores'] == 2 ? ' selected' : '')?>>All Stores</option>
					</select>							
				</td>
				
				<td style='width: 100%; white-space: nowrap; text-align: right;'>
					<input type="search" placeholder="Search Stores" autosave="Stores" results='5' name="chrSearch" value='<?=$_REQUEST['chrSearch']?>' />
					&nbsp;<input type="submit" name="submit" value="Go" />
				</form>
				</td>
			</tr>
		</table>

		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>
<?
				$extra = "myList=".$_REQUEST['myList']."&chrSearch=".$_REQUEST['chrSearch'];
				sortList('Name', 'chrName','',$extra);
				sortList('Email', 'chrEmail','',$extra);
				sortList('City', 'chrCity','',$extra);
				sortList('State', 'chrState','',$extra);
				sortList('Country', 'chrCountry','',$extra);
				sortList('Region', 'chrRegion','',$extra);
?>
			</tr>
<?
	$count=0;
	while($row = mysqli_fetch_assoc($store_result)) { 

		if(in_array($row['ID'],$_SESSION['intStoreList']) || $_SESSION['idType'] == 1) {
			$link = 'location.href="editstore.php?id='.$row['ID'].'";';
		} else {
			$link = 'location.href="viewstore.php?id='.$row['ID'].'";';
		}
?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrName']?></td>
				<td style='cursor: pointer;' onclick='location.href="mailto:<?=$row['chrEmail']?>";'><?=$row['chrEmail']?></td>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrCity']?></td>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrState']?></td>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrCountry']?></td>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrRegion']?></td>
			</tr>
<?
	} 
	if($count == 0) { ?>
			<tr>
				<td colspan='6'>No Stores could be found in the database.</td>
			</tr>
<?	} ?>
			</tbody>
		</table>
	
	
	</div>
<?
	include($BF. 'includes/bottom2.php');
?>
