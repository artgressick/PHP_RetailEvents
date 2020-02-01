<?php
	require("../_lib.php");
	
	default_sortby('presenters', 'chrName', 'ASC');
		
	if(($_REQUEST['myList'] == '') && ($_SESSION['myListPresenters'] == '') && $_SESSION['idType'] != 1) { $_SESSION['myListPresenters'] = '1'; }	
	if($_REQUEST['myList'] == '2') { $_SESSION['myListPresenters'] = '2'; }	
	if($_REQUEST['myList'] == '1') { $_SESSION['myListPresenters'] = '1'; }	

	$q = "SELECT DISTINCT Presenters.ID, Presenters.chrName, Presenters.chrCity, Presenters.chrEmail, Presenters.chrOfficePhone, Presenters.idStore,
			Presenters.chrCompanyLabel
			FROM Presenters
			WHERE !Presenters.bDeleted
		";
	if($_REQUEST['chrSearch'] != '') { 
		$q .= " AND (chrName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%' 
				OR chrEmail LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')"; 	
	}
	if($_SESSION['myListPresenters'] == '1') {
		$q .= " AND Presenters.idStore IN (SELECT idItem FROM ACL WHERE idUser='" . $_SESSION['idUser'] . "')";
	}
	$q .= " ORDER BY " . sortby_to_sql(get_sortby('presenters')); 
	
	$presenters = do_mysql_query($q, 'get store users');
	
	// Set the title, and add the doc_top
	$title = "Guest Presenters";
	require(BASE_FOLDER . 'docpages/doc_meta_events.php');
	include(BASE_FOLDER . 'docpages/doc_top_events.php');
?>
	<div style='margin: 10px;'>

			<div class="AdminTopicHeader">Guest Presenters</div>
				<div class="AdminDirections" style='width: 870px;'>Click on a Guest Presenter to learn more about them or press the "Add" button to add a guest presenter.</div>

<?	if(@count($_SESSION['InfoMessage'])) { ?>
	<div class='Messages'>
<?		foreach($_SESSION['InfoMessage'] as $msg) { ?>
						<div class='InfoMessage'><?=$msg?></div>
<?		}
		$_SESSION['InfoMessage'] = array(); ?>
	</div>
<? } ?>

				<!-- Tool Bar with the Add Store and Search button -->
				<div class="AdminToolBar" style='width: 880px;'>
					<table style='margin: 0; padding: 3px 0; width: 880px;'>
						<tr>
							<td style='width: 650px;'>
								<input type='button' value='Add Presenter' onclick="location.href='addpresenter.php'" />
							</td>
							<td style='text-align: right;'>
							<form id="search" method="get" action="">
								<!-- Search Area with it's own FORM to GET information -->
								<span style='margin: 0;'>
								<select name='myList' onChange='this.form.submit();'>
									<option value='1'<?=($_SESSION['myListPresenters'] == 1 ? ' selected' : '')?>>My Presenters</option>
									<option value='2'<?=($_SESSION['myListPresenters'] == 2 ? ' selected' : '')?>>All Presenters</option>
								</select>
								</td><td id='search' style='text-align: right; padding-right: 5px;'>Search:<input type="search" name="chrSearch" class="sbox" id="q" value='<?=$_REQUEST['chrSearch']?>' /></span>
								</form>
							</td>
						</tr>
					</table>
				</div>
           
	
		<table class='list' style='width: 880px; margin-bottom: 10px;'>
		<thead>
			<tr>
				<? list_th('presenters', 'Name/Artist', 'chrName', 'width: 2in;'); ?>
				<? list_th('presenters', 'Company/Label', 'chrCompanyLabel', 'width: 2in;'); ?>
				<? list_th('presenters', 'City', 'chrCity', 'width: 2in;'); ?>
				<? list_th('presenters', 'Main Phone', 'chrOfficePhone', 'width: 2in;'); ?>
				<? list_th('presenters', 'Email', 'chrEmail', 'width: 3in;'); ?>
				<? if($_SESSION['myListPresenters'] == '1' || $_SESSION['idType'] == 1) { ?> <th>Delete</th> <? } ?>			
				</tr>
			</thead>
		<tbody>
<?	$count = 0;
	while($presenter = mysql_fetch_assoc($presenters)) { 
		if(!in_array($presenter['idStore'],$_SESSION['intStoreList']) && $_SESSION['idType'] != 1) { ?>
			<tr class='<?=($count++%2?'odd':'even')?>'
					onmouseover='RowHighlight(this);'
					onmouseout='RowUnHighlight(this);'
					style='cursor: pointer;'>
				<td
					onclick='location.href="viewpresenter.php?id=<?=$presenter['ID']?>";'
					><a class='listlink' href='viewpresenter.php?id=<?=$presenter['ID']?>'><?=$presenter['chrName']?></a></td>
				<td
					onclick='location.href="viewpresenter.php?id=<?=$presenter['ID']?>";'
					><a class='listlink' href='viewpresenter.php?id=<?=$presenter['ID']?>'><?=$presenter['chrCompanyLabel']?></a></td>
				<td
					onclick='location.href="viewpresenter.php?id=<?=$presenter['ID']?>";'
					><a class='listlink' href='viewpresenter.php?id=<?=$presenter['ID']?>'><?=$presenter['chrCity']?></a></td>
				<td
					onclick='location.href="viewpresenter.php?id=<?=$presenter['ID']?>";'
					><a class='listlink' href='viewpresenter.php?id=<?=$presenter['ID']?>'><?=$presenter['chrOfficePhone']?></a></td>
				<td
					onclick='location.href="viewpresenter.php?id=<?=$presenter['ID']?>";'
					><a class='listlink' href='viewpresenter.php?id=<?=$presenter['ID']?>'><?=$presenter['chrEmail']?></a></td>
				<? if($_SESSION['myListPresenters'] == '1' || $_SESSION['idType'] == 1) { ?> <td><input type='button' value=' - ' onclick="javascript:location.href='deletepresenter.php?id=<?=$presenter['ID']?>'"></td> <? } ?>
			</tr>
<?		} else { ?>
			<tr class='<?=($count++%2?'odd':'even')?>'
					onmouseover='RowHighlight(this);'
					onmouseout='RowUnHighlight(this);'
					style='cursor: pointer;'>
				<td
					onclick='location.href="editpresenter.php?id=<?=$presenter['ID']?>";'
					><a class='listlink' href='editpresenter.php?id=<?=$presenter['ID']?>'><?=$presenter['chrName']?></a></td>
				<td
					onclick='location.href="viewpresenter.php?id=<?=$presenter['ID']?>";'
					><a class='listlink' href='viewpresenter.php?id=<?=$presenter['ID']?>'><?=$presenter['chrCompanyLabel']?></a></td>
				<td
					onclick='location.href="editpresenter.php?id=<?=$presenter['ID']?>";'
					><a class='listlink' href='editpresenter.php?id=<?=$presenter['ID']?>'><?=$presenter['chrCity']?></a></td>
				<td
					onclick='location.href="editpresenter.php?id=<?=$presenter['ID']?>";'
					><a class='listlink' href='editpresenter.php?id=<?=$presenter['ID']?>'><?=$presenter['chrOfficePhone']?></a></td>
				<td
					onclick='location.href="editpresenter.php?id=<?=$presenter['ID']?>";'
					><a class='listlink' href='editpresenter.php?id=<?=$presenter['ID']?>'><?=$presenter['chrEmail']?></a></td>
				<? if($_SESSION['myListPresenters'] == '1' || $_SESSION['idType'] == 1) { ?> <td><input type='button' value=' - ' onclick="javascript:location.href='deletepresenter.php?id=<?=$presenter['ID']?>'"></td> <? } ?>
			</tr>
<?		}
	} 
	if($count == 0) { ?>
			<tr>
				<td colspan='4'>No presenters were found in the database.</td>
			</tr>
<?	} ?>
			</tbody>
		</table>
	
	</div>
<?
	include(BASE_FOLDER . 'docpages/doc_bottom.php');
?>
