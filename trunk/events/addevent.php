<?php
	$BF = '../';
	$title = 'Add Event';
	$curPage = "coe";
	require($BF. '_lib2.php');
	// Checking request variables
	($_SESSION['idType'] == 4 && $_REQUEST['intDate'] > 99 ? ErrorPage() : ""); 
	($_REQUEST['idStore'] == "" || !is_numeric($_REQUEST['idStore']) ? ErrorPage() : "" );
	include($BF. 'includes/meta2.php');

	$intYear = 2000 + floor($_REQUEST['intDate'] / 12);
	$intMonth = ($_REQUEST['intDate'] % 12)+1;

	$first_weekday = idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear));
	$first_display_day = 1-$first_weekday;
	$days_this_month = idate('t', mktime(0, 0, 0, $intMonth, 1, $intYear));
	$number_of_weeks = ceil(($days_this_month+idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear))) / 7);

		$security = fetch_database_query("SELECT enStatus 
			FROM StoreMonths 
			WHERE idStore='". $_REQUEST['idStore'] ."' AND intYear='". $intYear ."' and intMonth='". $intMonth ."'
			","check for calendar status");


	// if this is a form submission
	if(isset($_POST['chrMainEvent'])) {
	
//---- This is a new edition for the Concierge system please see Art for explanation

		if (($_POST['intDate'] > 93) && ($_POST['idEventType'] == 6 || $_POST['idEventType'] == 18 || $_POST['idEventType'] == 7)) {
		
			header("Location: eventblocker.php?idStore=" . $_POST['idStore'] . '&intDate=' . $_POST['intDate']);
			die();
		
		}
//---- End of the concierge section	
	
			$tBeginHour = ($_POST['tBeginMeridian'] == 'AM' ? $_POST['tBeginHour']%12 : ($_POST['tBeginHour']%12)+12);
			$tEndHour = ($_POST['tEndMeridian'] == 'AM' ? $_POST['tEndHour']%12 : ($_POST['tEndHour']%12)+12);
			$tBegin = ($tBeginHour < 10 ? '0'.$tBeginHour : $tBeginHour) . ":" . ($_POST['tBeginMinute'] < 10 ? '0'.$_POST['tBeginMinute'] : $_POST['tBeginMinute']) . ":00";
			$tEnd = ($tEndHour < 10 ? '0'.$tEndHour : $tEndHour) . ":" . ($_POST['tEndMinute'] < 10 ? '0'.$_POST['tEndMinute'] : $_POST['tEndMinute']) . ":00";

	
				if($security['enStatus'] == 'Approved') { 
						database_query("UPDATE StoreMonths SET enStatus='Rejected' WHERE idStore='". $_POST['idStore'] ."' AND intYear='". $intYear ."' AND intMonth='". $intMonth ."'","rejecting an approved COE");
						database_query("UPDATE Events SET bApproved=0,txtRejection='Submitted after Approval' WHERE ID='". $_POST['id'] ."'","disaproving event");
					}
					if($security['enStatus'] == 'Rejected' && $info['bApproved'] == 1) { 
						database_query("UPDATE Events SET bApproved=0 WHERE ID='". $_POST['id'] ."'","disaproving event");
					}
			
			
			$allDays = $_POST['calDay'];
	
			$intSeries = str_pad($_POST['idStore'], 4, "0", STR_PAD_LEFT) . ($intMonth < 10 ? '0'.$intMonth : $intMonth) . $intYear . mt_rand(1000000000,9999999999);
	
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
	
	
			$q = "INSERT INTO Events (". ($_POST['chrTitleList'] != "" ? 'idEventTitle,' : '') ."chrTitle,chrDescription,idStore,idEventType,dtCreated,dDate,tBegin,tEnd,bGiveaway,chrGiveawayProduct,chrGiveawayFrom,
				fFinalBudget,chrBudgetQuarter,enEquipmentProvidedBy,enEquipmentAppleSource,setMarketingMaterials,intSeries";
				if($security['enStatus'] == 'Approved' || $security['enStatus'] == 'Rejected'){ $q .= ",bApproved,txtRejection"; } 
				$q .= ") VALUES ";
			
			$series=0;
			foreach($allDays as $current_day) {
			
			//This is the original way Daniel was entering the bReviewed tag which will alert the team to do the review. As above
			//If the event category was 2 then it was an event and not needed otherwise mark it with a 1 needing approval.

				$q .= ($series++ > 0 ? ',' : '')."(". ($_POST['chrTitleList'] != "" ? "'".encode($_POST['chrTitleList'])."'," : '') . "'" .encode($_POST['chrTitle']) ."','". encode($_POST['chrDescription']) . "','" . $_POST['idStore'] . "','" . $_POST['idEventType'] . "',
					now(),'" . $intYear . '-' . $intMonth . '-' . $current_day . "','" . $tBegin . "','" . $tEnd . "','" . $_POST['bGiveaway'] . "',
					'" . encode($_POST['chrGiveawayProduct']) . "','" . encode($_POST['chrGiveawayFrom']) . "','" . $_POST['fFinalBudget'] . "','" . encode($_POST['chrBudgetQuarter']) . "',
					'" . $_POST['enEquipmentProvidedBy'] . "','" . $_POST['enEquipmentAppleSource'] . "','" . implode(',',$_POST['setMarketingMaterials']) . "','" . $intSeries . "'";
				
				if($security['enStatus'] == 'Approved' || $security['enStatus'] == 'Rejected') { $q .= ",'0','Event added after submittion'"; }
				
				$q .= ")";
				
			}
			database_query($q,"insert event");
			
			global $mysqli_connection;
			$new_id = mysqli_insert_id($mysqli_connection);
			
			print_r($_FILES);
			if(is_uploaded_file($_FILES['chrPhoto']['tmp_name'])) {
				$phName = str_replace(" ","_",basename($_FILES['chrPhoto']['name']));
				
				database_query("UPDATE Events SET 
					intImageSize=". $_FILES['chrPhoto']['size'] .",
					chrImageName='". $new_id ."-". $phName ."',
					chrImageType='". $_FILES['chrPhoto']['type'] ."'
					WHERE ID=". $new_id ."
					","insert image");
					
//				global $mysqli_connection;
//				$new_id2 = mysqli_insert_id($mysqli_connection);

				$uploaddir = $BF . 'eventimages/';
				$uploadfile = $uploaddir . $new_id .'-'. $phName;
	
				move_uploaded_file($_FILES['chrPhoto']['tmp_name'], $uploadfile);
			}
				
			header("Location: index.php?idStore=" . $_POST['idStore'] . '&intDate=' . $_POST['intDate']);
			die();
	}
	
	
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="JavaScript" src="<?=$BF?>includes/listadd.js"></script>
<script language="javascript" type="text/javascript">//<![CDATA[
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;

		<? if($_SESSION['idType'] != 1 && $_SESSION['idType'] != 2 && !in_array($_REQUEST['idStore'],$_SESSION['intStoreList'])) { ?>
					document.getElementById('errors').innerHTML += "<div class='ErrorMessage'>You do not have permission to create an event for this store.</div>"; total += 1;
		<? } ?>

		total += ErrorCheck('idStore', "You must choose the store in which the workshop/event will take place.");
		total += ErrorCheck('chrMainEvent', "You must choose the Main Workshop/Event Type.");
		total += ErrorCheck('idEventType', "You must choose the Workshop/Event Type.");
		total += ErrorCheck('DocLoadFocus', "You must enter the name of the Event.");

		if(document.getElementById('bGiveaway')) {
			if(document.getElementById('bGiveaway').value == 1) {
				total += ErrorCheck('chrGiveawayProduct', "You must enter the Product or Service that will be given away.");
				total += ErrorCheck('chrGiveawayFrom', "You must select the Giveaway Source.");
			}
		}
		if(document.getElementById('enEquipmentProvidedBy')) {
			if(document.getElementById('enEquipmentProvidedBy').value == "Apple") {
				total += ErrorCheck('enEquipmentAppleSource', "You must select where the Apple-provided presentation equipment will be sourced from.");
			}
		}

		if(dtAmount == 0) {
			document.getElementById('errors').innerHTML += "<div class='ErrorMessage'>You must check at least one Date Box.</div>"; total += 1;
		}	

		if(document.getElementById('chrMainEvent')) { 
			if(document.getElementById('chrMainEvent').value == 2) { 
				total += ErrorCheck('chrDescription', "You must enter a Description for this Workshop/Event.");
				if(document.getElementById('presenterBody').innerHTML.length < 50) {
					document.getElementById('errors').innerHTML += "<div class='ErrorMessage'>You must choose at least one Presenter.</div>"; total += 1;
				}
			}
		}
			
		if(document.getElementById("tBeginHour").value == "" || document.getElementById("tEndHour").value == "") { 
			document.getElementById('errors').innerHTML += "<div class='ErrorMessage'>You must choose a Begin Time and End Time.</div>"; total += 1;
		} else { 
		
			/* Compare Time */
			var bTime = new Date();
			var eTime = new Date();
			
			if(document.getElementById("tBeginMeridian").value == 'PM' && document.getElementById("tBeginHour").value != 12) { var bHour = parseInt(document.getElementById("tBeginHour").value) + 12; } 
				else { var bHour = document.getElementById("tBeginHour").value; }
			var bMinute = document.getElementById("tBeginMinute").value;
	
			if(document.getElementById("tEndMeridian").value == 'PM' && document.getElementById("tEndHour").value != 12) { var eHour = parseInt(document.getElementById("tEndHour").value) + 12; } 
				else { var eHour = document.getElementById("tEndHour").value; }
			var eMinute = document.getElementById("tEndMinute").value;
			
			bTime.setHours(bHour);
			bTime.setMinutes(bMinute);
			
			eTime.setHours(eHour);
			eTime.setMinutes(eMinute);

			if((eTime - bTime) < 1) { document.getElementById('errors').innerHTML += "<div class='ErrorMessage'>Your Begin Time must be before your End Time.</div>"; total += 1; }
		}
	
		return (total == 0 ? true : false);
	}
//]]></script>
<script type="text/javascript">

	function GiveawayChanged() {
		var input = document.getElementById("bGiveaway");

		if(input.value=="1") {
			document.getElementById("enGiveawayProductSection").style.display = "block";
			document.getElementById("enGiveawayFromSection").style.display = "block";
		} else {
			document.getElementById("enGiveawayProductSection").style.display = "none";
			document.getElementById("enGiveawayFromSection").style.display = "none";
		}
	}

	function EquipmentProvidedByChanged() {
		var input = document.getElementById("enEquipmentProvidedBy");

		if(input.value=="Apple") {
			document.getElementById("enEquipmentAppleSourceSection").style.display = "block";
		} else {
			document.getElementById("enEquipmentAppleSourceSection").style.display = "none";
		}
	}

	var dtAmount = 0;
	function dateAmount(obj) {
		if(obj.checked) { dtAmount += 1; }
			else { dtAmount -= 1; }
	}

	function resetVals() {
		document.getElementById("chrTitleList").value = "";
		document.getElementById("DocLoadFocus").value = "";
		document.getElementById("chrDescription").value = "";
		
		if(last_div != '') {
			document.getElementById(last_div).style.display='none';
			last_div = '';
		}
	}
	
</script>
<?
	$security = fetch_database_query("SELECT enStatus 
		FROM StoreMonths 
		WHERE idStore='". $_REQUEST['idStore'] ."' AND intYear='". $intYear ."' and intMonth='". $intMonth ."'
		","check for calendar status");

	$eventnames = database_query("SELECT ID,idEventType, chrEventTitle, txtEventDescription FROM EventTypeNames WHERE !bDeleted AND bShow ORDER BY idEventType, chrEventTitle", 'get eventtypenames');	
	$eventCategory = database_query("SELECT ID,chrCategory FROM EventCategory","getting Event Categories");	

	include($BF. 'includes/top_events.php');
?>


		<div class="AdminTopicHeader">Add a new Entry</div>
			<div class="AdminDirections" style='width: 870px;'>To add a new event simply fill out the information below and then click the Add Entry button.
			<?=($security['enStatus'] == 'Approved' ? "<br /><span style='color: red'>This workshop/event has been submitted and has been approved.  By making this update, the whole calendar will need to be re-submitted for approval.</span>" : '')?>
			</div>

	
	<div id='errors'></div>
	<form id='idForm' name='idForm' method='post' action='' enctype="multipart/form-data" onsubmit="return error_check()">

		<table cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr>
				<td width="49.5%" style='vertical-align: top;'>
				
				
					<div class='sectionInfo'>
						<div class='sectionHeader'>Entry Information</div>
						<div class='sectionContent'>

							<div class='form'>
								<div class='formHeader'>Category <span class='Required'>(Required)</span></div>
								<select name='chrMainEvent' id='chrMainEvent' onchange='resetVals(); mainevent_changed();'>
									<option value=''></option>
<?	while($row = mysqli_fetch_assoc($eventCategory)) { ?> 									
									<option value='<?=$row['ID']?>'><?=$row['chrCategory']?></option>
<?	} ?>
								</select>
							</div>


							<div class='form'>
								<div class='formHeader'>Type <span class='Required'>(Required)</span></div>
								<select name='idEventType' id='idEventType' onchange='resetVals(); eventtype_changed();'>
								</select>
							</div>
							
							<div class='form'>
								<div class='formHeader'>Title <span class='Required'>(Required)</span></div>
								<div><input type='text' size='40' maxlength='80' id='DocLoadFocus' name='chrTitle' /></div>
								<div style='display: none;'><select id='chrTitleList' name='chrTitleList' onChange='eventname_changed(this.value);'><option value='1'>Option</option></select></div>
							</div>
							
							<div class='form'>
								<div class='formHeader'>Description</div>
								<div><textarea id='chrDescription' name='chrDescription' cols='40' rows='10'></textarea></div>
<?	while($row = mysqli_fetch_assoc($eventnames)) { ?>
								<div style='display: none' id='<?=$row["ID"]?>'><?=$row['txtEventDescription']?></div>
<?	} ?>	
							</div>
				
				<!-- End of the section -->
						
					</div>
				</div>
									
				<!-- End of the Event Information Section -->
				
				
				<!-- This hides the whole section by default unless an Event is chosen -->
				<div id='idPresProd' style='display: none;'>
					<div class='sectionInfo'>
						<div class='sectionHeader'>Presenters</div>
						<div class='sectionContent'>
			
						<div class='form'>
							<div class='formHeader'>Select each presenter that will be involved in this workshop/event.</div>
							<input type='button' value='Add...' onclick='newwin = window.open("select-presenter.php?idStore=<?=$_REQUEST['idStore']?>&d=<?=urlencode(base64_encode('functioncall=presenters_add'))?>","new","width=435,height=400,resizable=1,scrollbars=1"); newwin.focus();'/>

							<input type='hidden' id='idPresenters' name='idPresenters' />
							<input type='hidden' id='chrPresenters' name='chrPresenters' />

							<table class='list' id='Presenters' style='width: 100%;'>
								<thead>
									<tr>
										<th class='alignleft'>Presenter</th>
										<th style='width: 1%;'></th>
										</tr>
									</thead>
									<tbody id='presenterBody' name='presenterBody'>
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


					<div class='sectionInfo'>
						<div class='sectionHeader'>Products</div>
						<div class='sectionContent'>
					
						<div class='form'>
							<div class='formHeader'>Select the products on which this workshop/event will focus.</div>
							<input type='button' value='Add...' onclick='newwin = window.open("select-product.php?d=<?=urlencode(base64_encode('functioncall=products_add'))?>","new","width=600,height=400,resizable=1,scrollbars=1"); newwin.focus();'/>

							<input type='hidden' id='idProducts' name='idProducts' />
							<input type='hidden' id='chrProducts' name='chrProducts' />

							<table class='list' id='Products' style='width: 100%;'>
								<thead>
									<tr>
										<th class='alignleft'>Product</th>
										<th style='width: 1%;'></th>
										</tr>
									</thead>
									<tbody>
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
				
				
				
				
<? if($_SESSION['idType'] == 1) { // This checks to see if you are a Corporate Type ?>

					<div class='sectionInfo'>
						<div class='sectionHeader'>Budget</div>
						<div class='sectionContent'>

							<div class='form'>
								<div class='formHeader'>What is the final budget for this project?</div>
								<div>$<input type='text' size='10' maxlength='20' name='fFinalBudget' /></div>
							</div>

						
							<div class='Field'>
								<div class='L10'>Will all associated purchace orders post to?</div>
									<select name='chrBudgetQuarter'>
										<option></option>
										<option>Q1</option>
										<option>Q2</option>
										<option>Q3</option>
										<option>Q4</option>
									</select>
								</div>
							</div>
					
					<!-- End of the section -->
						</div>
					</div>
<?	} 	?>
				<!-- End of the Budget Section -->
		
				</td>
				

				<!-- This is the gutter in the middle of the table chunks -->
				<td width="1%"></td>
	
	
	
	<script type="text/javascript">//<![CDATA[
		function timechange() {
			var time = document.getElementById('tBeginHour').value;
			time = parseInt(time);
			if(time == 11) {
				document.getElementById('tEndHour').options[12].selected = true;
				(document.getElementById('tBeginMeridian').value == 'AM' ? document.getElementById('tEndMeridian').options[1].selected = true : document.getElementById('tEndMeridian').options[0].selected = true);
			} else if(time == 12) {
				document.getElementById('tEndHour').options[1].selected = true;
			} else {
				document.getElementById('tEndHour').options[time+1].selected = true;
			}

		}	
	</script>
	
				
				
				<td width="49.5%" style='vertical-align: top;'>
					<div class='sectionInfo'>
						<div class='sectionHeader'>Time</div>
						<div class='sectionContent'>
	
						<div class='form'>
						<table>
							<tr>
								<td><div class='formHeader'>Start <span class='Required'>(Required)</span></div></td>
								<td style='width: 25px;'></td>
								<td><div class='formHeader'>End <span class='Required'>(Required)</span></div></td>
							</tr>
							<tr>
							  <td style='vertical-align: top;'>

							<select name='tBeginHour' id='tBeginHour' onchange='timechange()'>
								<option value=''></option>
<?	for($hour = 1; $hour<=12; $hour++) { ?>
								<option value='<?=$hour?>'><?=$hour?></option>
<?	} ?>
							</select>
							:
							<select name='tBeginMinute' id='tBeginMinute'>
								<option>00</option>
								<option>15</option>
								<option>30</option>
								<option>45</option>
							</select>

							<select name='tBeginMeridian' id='tBeginMeridian'>
								<option value='AM'>AM</option>
								<option selected value='PM'>PM</option>
							</select>

							</td><td>&nbsp;</td><td style='vertical-align: top;'>


							<select name='tEndHour' id='tEndHour'>
								<option value=''></option>
<?	for($hour = 1; $hour<=12; $hour++) { ?>
								<option value='<?=$hour?>'><?=$hour?></option>
<?	} ?>
							</select>
							:
							<select name='tEndMinute' id='tEndMinute'>
								<option>00</option>
								<option>15</option>
								<option>30</option>
								<option>45</option>
							</select>

							<select name='tEndMeridian' id='tEndMeridian'>
								<option value='AM'>AM</option>
								<option selected value='PM'>PM</option>
							</select>
								</td>
							</tr>
						</table>
						
						
						</div>
					</div>
				</div>
		
				
		
				<div class='sectionInfo'>
						<div class='sectionHeader'>Occurrences</div>
						<div class='sectionContent'>
	
						
							<div class='form'>
								<div class='formHeader aligncenter'><?=strftime('%B %Y', mktime(0, 0, 0, $intMonth, 1, $intYear))?></div>

							<table class='Calendar' cellpadding="0" cellspacing="0" border="0">
								<tr>
									<th style='width: 1in;text-align: center;'><label>Sun</label></th>
									<th style='width: 1in;text-align: center;'><label>Mon</label></th>
									<th style='width: 1in;text-align: center;'><label>Tue</label></th>
									<th style='width: 1in;text-align: center;'><label>Wed</label></th>
									<th style='width: 1in;text-align: center;'><label>Thu</label></th>
									<th style='width: 1in;text-align: center;'><label>Fri</label></th>
									<th style='width: 1in;text-align: center;'><label>Sat</label></th>
									</tr>
<?
	if(isset($_POST['calDay'])) { $inDays = $_POST['calDay'];  } else { $inDays = array(); }
	for($current_week = 0; $current_week < $number_of_weeks; $current_week++) { ?>
								<tr>
<?		for($current_day = ($current_week*7)+$first_display_day; $current_day < (($current_week*7)+$first_display_day+7); $current_day++) {
			if($current_day >= 1 && $current_day <= $days_this_month) { 
?>
									<td><label><input type='checkbox' name='calDay[]' id='calDay[]' value='<?=$current_day?>' onclick='dateAmount(this)' /><?=$current_day?></label></td>
<?			} else { ?>
									<td>&nbsp;</td>
<?			}
		} ?>
									</tr>	
<?	}
?>
								</table>
							</div>
						</div>
		
		
			<!-- This hides the whole section by default unless an Event is chosen -->
			<div id='idPresPhoto' style='display: none;'>	
		
				<div class='sectionInfo' style='margin-top: 10px;'>
					<div class='sectionHeader'>Photos</div>
						<div class='sectionContent'>
	
						
							<div class='form'>
								<div class='formHeader aligncenter'></div>

								<div class='form'>
									<div class='formHeader'>
										Upload Photos <span class='Required' style='font-size: 10px;'>(1 file upload, 49 x 49 pixels)</span>
										<span style='padding-left: 10px;'></span>
									</div>
									<input name="chrPhoto" type="file" />
								</div>
							</div>
						</div>
		
				</div>
			</div>	
	
	
<? if($_SESSION['idType'] == 1) { ?>

				<div class='sectionInfo' style='margin-top: 10px;'>
					<div class='sectionHeader'>Giveaways</div>
					<div class='sectionContent'>
						
						<div class='form'>
							<div class='formHeader'>Will a product/service be given away with this event?</div>
							<select name='bGiveaway' id='bGiveaway' onchange='GiveawayChanged();'>
								<option value=""></option>
								<option value="1">Yes</option>
								<option value="0">No</option>
							</select>
							</div>
						
						<div class='Field' id='enGiveawayProductSection' style='display: none;'>
							<div class='L10'>What product/service will be given away? <span class='Required'>(Required)</span></div>
								<input type='text' size='40' maxlength='80' name='chrGiveawayProduct' id='chrGiveawayProduct' />
						</div>
						
						<div class='Field' id='enGiveawayFromSection' style='display: none;'>
							<div class='L10'>Will the item be sent from the store or from corporate? <span class='Required'>(Required)</span></div>
							<select id='chrGiveawayFrom' name='chrGiveawayFrom'>
								<option value=''></option>
								<option>Store</option>
								<option>Corporate</option>
							</select>
						</div>
						
					</div>
				</div>

				<div class='sectionInfo'>
					<div class='sectionHeader'>Retail Marketing Support</div>
					<div class='sectionContent'>
						
						<div class='forms'>
							<div style='color: red;'>
								You must receive written prior approval from <a href='mailto:retailevents@apple.com'>retailevents@apple.com</a> before submitting a request to rent equipment or obtain outside services  as well as submit a request for printed materials or web marketing.
							</div>
						</div>
							
						<div class='form'>
							<div class='formHeader'>Equipment provided by</div>
							<select name='enEquipmentProvidedBy' id='enEquipmentProvidedBy' onchange='EquipmentProvidedByChanged();'>
								<option value=''></option>
								<option>Apple</option>
								<option>Presenter</option>
							</select>
						</div>
						
						<div class='form' id='enEquipmentAppleSourceSection' style='display: none;'>
							<div class='formHeader'>Is the equipment in-house, or does it need to come from an outside vendor? <span class='Required'>(Required)</span></div>
							<select id='enEquipmentAppleSource' name='enEquipmentAppleSource'>
								<option value=''></option>
								<option>In-House</option>
								<option>Outside Vendor</option>
							</select>
						</div>

						<div class='form'>
							<div class='formHeader'>Marketing materials needed</div>
							<div><label><input type='checkbox' name='setMarketingMaterials[]' value='Easel' />Easel</label></div>
							<div><label><input type='checkbox' name='setMarketingMaterials[]' id='MarketingMaterialsPrintedMaterials' value='Printed Materials' onchange='MarketingMaterialsChanged();' />Printed Materials</label></div>
							<div><label><input type='checkbox' name='setMarketingMaterials[]' id='MarketingMaterialsWebMarketing' value='Web Marketing' onchange='MarketingMaterialsChanged();' />Web Marketing</label></div>
						</div>

				</div>
			</div>
<?	} ?>
	
	
						
						</div>
					</div>
	
				</td>
			</tr>
		</table>

		<?=($security['enStatus'] == 'Approved' ? "<br /><span style='color: red'>This workshop/event has been submitted and has been approved.  By making this update, the whole calendar will need to be re-submitted for approval.</span>" : '')?>


		<div class='FormButtons'>
			<input type='hidden' id='intDate' name='intDate' value='<?=$_REQUEST['intDate']?>' />
			<input type='hidden' id='idStore' name='idStore' value='<?=$_REQUEST['idStore']?>' />			
			<input type='submit' value='Save Entry' />
			<input type='button' onclick='history.back();' value='Cancel' />
			</div>
		</div>


	</form>

<?
	$eventnames = database_query("SELECT ID,idEventType, chrEventTitle FROM EventTypeNames WHERE !bDeleted AND bShow ORDER BY idEventType, chrEventTitle", 'get eventtypenames');
	$eventtype_names = array();
	//while($row = mysqli_fetch_assoc($eventnames)) {
	//	$eventtype_names[$row['idEventType']][] = addslashes($row['chrEventTitle']);
	//}
	
	$storeinfo = fetch_database_query("SELECT Localization.ID AS idLocalization FROM Stores JOIN Localization ON Stores.idLocalization=Localization.ID WHERE Stores.ID=".$_REQUEST['idStore'], "Get Store Localization");

	
	$Weeklyeventtype_result = database_query("SELECT ID, chrName FROM EventTypes WHERE !bDeleted AND bShow AND idEventCategory='1' AND idLocalization=".$storeinfo['idLocalization']." ORDER BY chrName", 'get weekly types');
	$Weekly_names = array();
	while($row = mysqli_fetch_assoc($Weeklyeventtype_result)) {
		$Weekly_names[$row['ID']][] = addslashes(decode($row['chrName']));
	}
	
	$Specialeventtype_result = database_query("SELECT ID, chrName FROM EventTypes WHERE !bDeleted AND bShow AND idEventCategory='2' AND idLocalization=".$storeinfo['idLocalization']." ORDER BY ID", 'get special types');
	$Special_names = array();
	while($row = mysqli_fetch_assoc($Specialeventtype_result)) {
		$Special_names[$row['ID']][] = addslashes(decode($row['chrName']));
	}
?>

<script type='text/javascript'>

var filter_list = new Array();	
<?	$count=0;
	while($filter = mysqli_fetch_assoc($eventnames)) {
?>
filter_list[<?=$count?>] = new Array(3);
filter_list[<?=$count?>][0]=<?=$filter['idEventType']?>; filter_list[<?=$count?>][1]=<?=$filter['ID']?>; filter_list[<?=$count?>][2]='<?=addslashes(decode($filter['chrEventTitle']))?>';
<?	$count++;
} ?>
var totallist=<?=$count?>;


	var types_names = {
<?	foreach($eventtype_names as $id => $names) {
		?><?=$id?> : ['<?=implode("','", $names)?>'],
<?	} ?>
	9999999 : {}
	};
	
	var weekly_names = {
<?	foreach($Weekly_names as $id => $names) {
		?><?=$id?> : ['<?=implode("','", $names)?>'],
<?	} ?>
	9999999 : {}
	};
	var special_names = {
<?	foreach($Special_names as $id => $names) {
		?><?=$id?> : ['<?=implode("','", $names)?>'],
<?	} ?>
	9999999 : {}
	};
	
	function mainevent_changed()
	{
		var tf = document.getElementById('chrMainEvent');
		choice = tf.value;
		theform = tf.form;
		
		theform.idEventType.options.length=0;
		theform.idEventType.options[theform.idEventType.options.length] = new Option('', '', true, true);

		var tmp = 1;

		if(choice == '1') {
			for (var i in weekly_names) {
				if(weekly_names[i] != '[object Object]') {
					theform.idEventType.options[theform.idEventType.options.length] = new Option(weekly_names[i], i);
				}
				tmp++;
			}
			
			document.getElementById("idPresProd").style.display="none";
			document.getElementById("idPresPhoto").style.display="none";
		}
		else if(choice == '2') {
			for (var i in special_names) {
				if(special_names[i] != '[object Object]') {
					theform.idEventType.options[theform.idEventType.options.length] = new Option(special_names[i], i);
				}
				tmp++;
			}
			document.getElementById("idPresProd").style.display="block";
			document.getElementById("idPresPhoto").style.display="block";
			
		}
		eventtype_changed();
	}

	function eventtype_changed()
	{
		var typefield = document.getElementById('idEventType');
		//var m = types_names[typefield.value];
		theform = typefield.form;
		
		var inHere = 0;
		if(filter_list[typefield.value] == null) {
			inHere = 1;
		} else {
			inHere = 0;
			for(var i=0; i<totallist; i++) {
				if(filter_list[i][0] == typefield.value) { inHere++; }
			}
		}
		if(inHere == 0) {
			theform.chrTitle.parentNode.style.display='block';
			theform.chrTitle.type='text';
			theform.chrTitleList.parentNode.style.display='none';
			if(document.getElementById('idEventType').value != "") {
				eventname_changed();
			}
			return;
		}

		theform.chrTitle.parentNode.style.display='none';
		theform.chrTitle.type='hidden';
		theform.chrTitleList.parentNode.style.display='block';
		theform.chrTitleList.options.length=0;
		theform.chrTitleList.options[theform.chrTitleList.options.length] = new Option('', '', true, true);
		
		var i;
		for(i=0; i<filter_list.length; i++) {
			if(filter_list[i][0] == typefield.value) {
				theform.chrTitleList.options[theform.chrTitleList.options.length] 
					= new Option(filter_list[i][2], filter_list[i][1], (filter_list[i][2]==theform.chrTitle.value), (filter_list[i][2]==theform.chrTitle.value));
			}
		}

		theform.chrTitle.parentNode.style.display='none';
		theform.chrTitle.type='hidden';
		theform.chrTitleList.parentNode.style.display='block';

		if(document.getElementById('idEventType').value != "") {
			eventname_changed();
		}
	}


	var last_div='';
	function eventname_changed(val)
	{
		var select = document.forms[0].chrTitleList;
		var descfield;
		
		if(typeof(val) != 'undefined') {
			var i;
			for(var i=0; i<totallist; i++) {
				if(filter_list[i][1] == val) { document.getElementById('DocLoadFocus').value = filter_list[i][2]; }
			}
		}
		
		if(select.options.length > 0) {
			str = select.options[select.selectedIndex].value;
			descfield = document.getElementById(document.getElementById('chrTitleList').value);
		}
		
		if(last_div != '') {
			document.getElementById(last_div).style.display='none';
			last_div = '';
		}
		
		if(!descfield) {
			document.getElementById('chrDescription').style.display='block';
		} else {
			document.getElementById('chrDescription').style.display='none';
			last_div = str;
			descfield.style.display="block";
		}
	}
</script>
<?
	include($BF. 'includes/bottom2.php');
?>