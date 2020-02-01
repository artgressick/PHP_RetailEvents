<?php
	$BF = '../';
	$title = 'Pro Tour Special Events';
	require($BF. '_lib2.php');

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


	include($BF. 'includes/meta2.php');

	$intYear = date('Y', strtotime('now'));
	$intMonth = date('m', strtotime('now'));
	
	if(count($_POST)) {
	
		foreach($_SESSION['protourstores'] as $k) {
			if($_POST['id'.$k] == 'on') {
				$intSeries = str_pad($k, 4, "0", STR_PAD_LEFT) . ($intMonth < 10 ? '0'.$intMonth : $intMonth) . $intYear . mt_rand(1000000000,9999999999);
				if($_POST['idProducts'] != '') {		
					$ids = $_POST['idProducts']==''?array():explode(',', $_POST['idProducts']);
					$prod = "INSERT INTO EventProducts (idEvent,idProduct,intEventSeries) VALUES ";
					$cntProd=0; 
					foreach($ids as $id) { $prod .= ($cntProd++ == 0 ? '' : ',')."('','". $id ."','". $intSeries ."')";  }
					database_query($prod,"inserting products");
				}
	
				if($_POST['idPresenters'] != '') {
					$ids = $_POST['idPresenters']==''?array():explode(',', $_POST['idPresenters']);
					$pres = "INSERT INTO EventPresenters (idEvent,idPresenter,intEventSeries) VALUES ";
					$cntPres=0; 
					foreach($ids as $id) { $pres .= ($cntPres++ == 0 ? '' : ',')."('','". $id ."','". $intSeries ."')";  }
					database_query($pres,"inserting presenters");
				}
			
				database_query("INSERT INTO Events SET 
					chrTitle='". encode($_POST['chrTitle']) ."',
					chrDescription='". encode($_POST['chrDescription']) ."',
					idStore='". $k ."',
					intSeries='". $intSeries ."',					
					idEventType='". $_POST['idEventType'] ."',
					dDate='". date('Y-m-d',strtotime($_POST['dDate'.$k])) ."',
					tBegin='". date('H:i:s',strtotime($_POST['tBegin'.$k])) ."',
					tEnd='". date('H:i:s',strtotime($_POST['tEnd'.$k])) ."'","update info");
			}
		}
		header("Location: ../events/");
		die();
	} else {
		$info = null;
	}
		
	$eventTypes = database_query("SELECT ID, chrName FROM EventTypes WHERE idEventCategory=2 AND !bDeleted","getting types");
	$stores = database_query("SELECT ID, chrName from Stores WHERE ID IN (". implode(',',$_SESSION['protourstores']) .") ORDER BY chrName","getting stores");
?>

<style type="text/css">
.infoTable { border: 1px solid #333; background: #ccc; }
.infoTable td { vertical-align: top; }

.subbar { border-top: 1px solid #333; width: 75%; margin: 10px 0; }

.storeBox { padding: 3px; }
</style>

<?
	include($BF. 'includes/top_admin2.php');
?>

				<form method='post' action=''>
	
					<div class="AdminTopicHeader">Special Events Pro Tour Setup</div>
					<div class="AdminInstructions2">Choose the dates and times for the stores you wish to add events to.</div>

					<div class='form'>

					<div class='form'>
						<div class='formHeader'>Category <span class='Required'>(Required)</span></div>
							<select name='idEventType' id='idEventType'>
								<option value=''></option>
<?
	while($row = mysqli_fetch_assoc($eventTypes)) {
?>
								<option value='<?=$row['ID']?>'><?=$row['chrName']?></option>
<?
	}
?>
							</select>
						</div>

						<div class='formHeader'>Title <span class='Required'>(Required)</span></div>
							<div><input type='text' size='40' maxlength='80' id='DocLoadFocus' name='chrTitle' value='<?=$info['chrTitle']?>' /></div>
						</div>
							
						<div class='form'>
							<div class='formHeader'>Description</div>
							<div><textarea id='chrDescription' name='chrDescription' cols='80' rows='5'><?=$info['chrDescription']?></textarea></div>
						</div>
						
					<table>
						<tr>
							<td style='vertical-align: top;'>
							<!-- This hides the whole section by default unless an Event is chosen -->
							
							<div class='sectionInfo'>
							<div class='sectionHeader'>Presenters</div>
							<div class='sectionContent'>
							
							<div class='form'>
								<div class='formHeader'>Select each presenter that will be involved in this event.</div>
								<input type='button' value='Add...' onclick='newwin = window.open("../events/select-presenter.php?d=<?=urlencode(base64_encode('functioncall=presenters_add'))?>","new","width=600,height=400,resizable=1,scrollbars=1"); newwin.focus();'/>

								<input type='hidden' id='idPresenters' name='idPresenters' value='<?=$info['idPresenters']?>' />
								<input type='hidden' id='chrPresenters' name='chrPresenters' value='<?=$info['chrPresenters']?>' />

								<table class='list' id='Presenters' style='width: 100%; background-image:url(../images/list_head.gif);'>
									<thead>
										<tr>
											<th class='alignleft'>Presenter</th>
											<th style='width: 1%;'></th>
										</tr>
									</thead>
									<tbody>
<?
	if($info['idPresenters'] != '') {
		$ids = explode(',', $info['idPresenters']);
		$chrs = explode(',', $info['chrPresenters']);
		$count = 0;
		foreach($ids as $item_id) {
			list($key, $chr) = each($chrs);
?>
										<tr class='<?=(++$count%2?'odd':'even')?>'>
											<td><?=$chr?></td>
											<td class='alignright'><input type='button' value='Remove' onclick="list_remove('Presenters', 'idPresenters', 'chrPresenters', <?=$item_id?>, this); " /></td>
										</tr>
<?
		}
	}
?>
									</tbody>
								</table>

<script type="text/javascript">//<![CDATA[
function presenters_add(id, chr) 
	{ 
		list_add('Presenters', 'idPresenters', 'chrPresenters', id, chr); 
	}
// ]]></script>

				<!-- End of the section -->
							</div>
							</div>
							</div>
							</td>
							<!-- Right side popup -->
							<td style='vertical-align: top;'>
							
							<div class='sectionInfo'>
							<div class='sectionHeader'>Products</div>
							<div class='sectionContent'>
					
							<div class='form'>
								<div class='formHeader'>Select the products on which this event will focus.</div>
								<input type='button' value='Add...' onclick='newwin = window.open("../events/select-product.php?d=<?=urlencode(base64_encode('functioncall=products_add'))?>","new","width=600,height=400,resizable=1,scrollbars=1"); newwin.focus();'/>

								<input type='hidden' id='idProducts' name='idProducts' value='<?=$info['idProducts']?>' />
								<input type='hidden' id='chrProducts' name='chrProducts' value='<?=$info['chrProducts']?>' />

								<table class='list' id='Products' style='width: 100%;'>
									<thead>
										<tr>
											<th class='alignleft'>Product</th>
											<th style='width: 1%;'></th>
										</tr>
									</thead>
									<tbody>
<?
	if($info['idProducts'] != '') {
		$ids = explode(',', $info['idProducts']);
		$chrs = explode(',', $info['chrProducts']);
		$count = 0;
		foreach($ids as $item_id) { 
			list($key, $chr) = each($chrs);
?>
										<tr class='<?=(++$count%2?'odd':'even')?>'>
											<td><?=$chr?></td>
											<td class='alignright'><input type='button' value='Remove' onclick="list_remove('Products', 'idProducts', 'chrProducts', <?=$item_id?>, this); " /></td>
										</tr>
<?				} ?>
<?			} ?>
									</tbody>
								</table>

<script type="text/javascript">//<![CDATA[
function products_add(id, chr) 
{ 
	list_add('Products', 'idProducts', 'chrProducts', id, chr); 
}
// ]]></script>
		
			<!-- End of the section -->
							</div>
							</div>
							</div>
				
							<!-- End of the Presenters and Products! -->
							</td>
						</tr>
					</table>

<?
	$cnt = 0;
	while($row = mysqli_fetch_assoc($stores)) {
?>
					<?=($cnt++ != 0 ? '<div class="subbar"></div>' : '')?>
					<div style='storeBox'>
					<table>
						<tr>
							<td colspan='2'><input checked type='checkbox' name='id<?=$row['ID']?>' /> Add Information for this store</td>
						</tr>
						<tr>
							<td><strong>Store</strong>:</td><td><?=$row['chrName']?></td>
						</tr>
						<tr>
							<td><strong>Date</strong>:</td><td><input type='input' size="24" name='dDate<?=$row['ID']?>' /> (example: 1/10/2007 or 12/24/2006)</td>
						</tr>
						<tr>
							<td><strong>Time</strong>:</td><td>From: <input type='input' size='7' name='tBegin<?=$row['ID']?>' /> To: <input type='input' size='7' name='tEnd<?=$row['ID']?>' /> (example: 1:00am or 5:30pm)</td>
						</tr>
					</table>
					</div>
<?
	}
?>

	<input type='submit' value='Submit Values' style='margin-top: 10px;' />

	
	</form>	
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>