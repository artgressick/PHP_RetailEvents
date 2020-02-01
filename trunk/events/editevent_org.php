<?php
	require_once('../_lib.php');

	$error_messages = array();

	if(!isset($_POST['chrTitle'])) { $_POST['chrTitle'] = ''; }
	
	if($_REQUEST['id'] == '') { $_REQUEST['id'] = $_POST['id']; }

	$intYear = 2000 + floor($_REQUEST['intDate'] / 12);
	$intMonth = ($_REQUEST['intDate'] % 12)+1;

	$first_weekday = idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear));
	$first_display_day = 1-$first_weekday;
	$days_this_month = idate('t', mktime(0, 0, 0, $intMonth, 1, $intYear));
	$number_of_weeks = ceil(($days_this_month+idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear))) / 7);

	$result = do_mysql_query("SELECT *, Events.intSeries as intSeriesNum, Events.ID as idEventNum, DAYOFMONTH(dDate) AS intDateDay, Stores.chrName AS chrStoreName, 
		EventTypes.chrName AS chrEventType
		FROM Events
		JOIN Stores ON Stores.ID=idStore
		JOIN EventTypes ON EventTypes.ID=idEventType
		WHERE Events.ID='" . $_REQUEST['id'] . "'", 'get_event_by_id');
	$info = mysql_fetch_assoc($result);
	
	if(!isset($_POST['chrTitle'])) { $_POST['chrTitle'] = $info['chrTitle']; }
	
	$security = mysql_fetch_assoc(do_mysql_query("SELECT enStatus 
		FROM StoreMonths 
		WHERE idStore='". $_REQUEST['idStore'] ."' AND intYear='". $intYear ."' and intMonth='". $intMonth ."'
		","check for calendar status"));
	
	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root */
	if($_SESSION['idType'] != 1 && !in_array($_REQUEST['idStore'],$_SESSION['intStoreList'])) {
		$_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: ' . BASE_FOLDER . "nopermission.php"); die();
	}

	// if this is a form submission
	if(count(@$_POST['Delete'])) {
		header("Location: deleteevent.php?intDate=". $_POST['intDate'] ."&idStore=". $_POST['idStore'] ."&idEvent=". $_POST['idEvent'] ."&intSeries=" . $_POST['intSeries'] . "&eraseall=" . (isset($_POST['chkEditAll']) ? '1' : ''));
		die();
	}

	
	// if this is a form submission
	if(isset($_POST['Update'])) {

				// Check the fields for completeness/formatting
				//   If something is wrong, add an entry to the $error_messages array
				
				if($_SESSION['idType'] != 1 && !in_array($_REQUEST['idStore'],$_SESSION['intStoreList'])) {
					$error_messages[] = "You do not have permission to create an event for this store.";
				}
				if($_POST['idStore'] == '') { $error_messages[] = "You must choose the store in which the event will take place."; }
				if($_POST['chrTitle'] == '') { $error_messages[] = "You must enter the name of the workshop/event."; }
				if($_POST['chrMainEvent'] == '') { $error_messages[] = "You must choose the Main Workshop/Event type."; }
				if($_POST['idEventType'] == '') { $error_messages[] = "You must choose the workshop/event type."; }
			
				if($_POST['chrMainEvent'] == 2) {
					if($_POST['idPresenters'] == '') { $error_messages[] = "You must choose at least one presenter for this workshop/event."; }
					if($_POST['chrDescription'] == '') { $error_messages[] = "You must enter a discription for this event."; }
				}
			
				if($_POST['bGiveaway'] == 1) {
					if($_POST['chrGiveawayProduct'] == '') {
						$error_messages[] = "You must enter the product or service that will be given away.";
					}
					if($_POST['chrGiveawayFrom'] == '') {
						$error_messages[] = "You must select the giveaway source.";
					}
				}
				if($_POST['enEquipmentProvidedBy'] == 'Apple') {
					if($_POST['enEquipmentAppleSource'] == '') {
						$error_messages[] = "You must select where the Apple-provided presentation equipment will be sourced from.";
					}				
				}
		
				$tBegin = ($_POST['tBeginMeridian'] == 'PM' ? $_POST['tBeginHour']+12 : $_POST['tBeginHour']);
				$tEnd = ($_POST['tEndMeridian'] == 'PM' ? $_POST['tEndHour']+12 : $_POST['tEndHour']);
				$tBegin = ($_POST['tBeginHour'] == 12 && $_POST['tBeginMeridian'] == 'PM' ? 12 : $tBegin);
				$tEnd = ($_POST['tEndHour'] == 12 && $_POST['tEndMeridian'] == 'PM' ? 12 : $tEnd);
				$tBegin = ($_POST['tBeginHour'] == 12 && $_POST['tBeginMeridian'] == 'AM' ? 0 : $tBegin);
				$tEnd = ($_POST['tEndHour'] == 12 && $_POST['tEndMeridian'] == 'AM' ? 0 : $tEnd);				
				if($tEnd <= $tBegin) { $error_messages[] = "Your End Time must be at least one hour ahead of the Begin Time."; }
			
				if($_POST['tBeginHour'] == '' || $_POST['tBeginMinute'] == '' || ($_POST['tBeginMeridian'] == '' && $_SESSION['chrTimePreference'] != 24)) {
					$error_messages['tBegin'] = "You must enter the beginning time of the event.";
				}
				if($_POST['tEndHour'] == '' || $_POST['tEndMinute'] == '' || ($_POST['tEndMeridian'] == '' && $_SESSION['chrTimePreference'] != 24)) {
					$error_messages['tEnd'] = "You must enter the ending time of the event.";
				}
		
				$f_tBeginHour = ($_POST['tBeginMeridian'] == 'AM' ? $_POST['tBeginHour']%12 : ($_POST['tBeginHour']%12)+12);
				$f_tEndHour = ($_POST['tEndMeridian'] == 'AM' ? $_POST['tEndHour']%12 : ($_POST['tEndHour']%12)+12);
				$f_tBegin = ($f_tBeginHour < 10 ? '0'.$f_tBeginHour : $f_tBeginHour) . ":" . ($_POST['tBeginMinute'] < 10 ? '0'.$_POST['tBeginMinute'] : $_POST['tBeginMinute']) . ":00";
				$f_tEnd = ($f_tEndHour < 10 ? '0'.$f_tEndHour : $f_tEndHour) . ":" . ($_POST['tEndMinute'] < 10 ? '0'.$_POST['tEndMinute'] : $_POST['tEndMinute']) . ":00";
		
				if($_POST['calDay'] == '') { $error_messages[] = "You must choose at least one day for this workshop/event to occur on."; }

				// if everything is cool, update the record
				if(count($error_messages) === 0) {
			
					if($security['enStatus'] == 'Approved') { 
						do_mysql_query("UPDATE StoreMonths SET enStatus='Rejected' WHERE idStore='". $_POST['idStore'] ."' AND intYear='". $intYear ."' AND intMonth='". $intMonth ."'","rejecting an approved COE");
						do_mysql_query("UPDATE Events SET bApproved=0,txtRejection='Submitted after Approval' WHERE ID='". $_POST['id'] ."'","disaproving event");
					}
					if($security['enStatus'] == 'Rejected' && $info['bApproved'] == 1) { 
						do_mysql_query("UPDATE Events SET bApproved=0 WHERE ID='". $_POST['id'] ."'","disaproving event");
					}
			
					if($_POST['chkEditAll'] == 1) {
				
						$q = "UPDATE Events SET chrTitle='". encode($_POST['chrTitle']) ."',
							chrDescription='". encode($_POST['chrDescription']) ."',
							tBegin='". $f_tBegin ."',
							tEnd='". $f_tEnd ."',
							bReviewed=0,
							idEventType='". $_POST['idEventType'] ."',
							dtModified=now(),
							chrGiveawayProduct='". encode($_POST['chrGiveawayProduct']) ."',
							chrGiveawayFrom='". encode($_POST['chrGiveawayFrom']) ."',
							fFinalBudget='". $_POST['fFinalBudget'] ."',
							chrBudgetQuarter='". encode($_POST['chrBudgetQuarter']) ."',
							enEquipmentProvidedBy='". $_POST['enEquipmentProvidedBy'] ."',
							enEquipmentAppleSource='". $_POST['enEquipmentAppleSource'] ."',
							setMarketingMaterials='". implode(',', $_POST['setMarketingMaterials']) . "'
							WHERE intSeries=". $info['intSeriesNum'];
							
						do_mysql_query($q,"update all in series");
		
						$database_error += !do_mysql_query("DELETE FROM EventProducts WHERE intEventSeries='" . $info['intSeriesNum'] . "'", 'delete product');
						if($_POST['idProducts'] != '') {		
							$ids = $_POST['idProducts']==''?array():explode(',', $_POST['idProducts']);
							$prod = "INSERT INTO EventProducts (idEvent,idProduct,intEventSeries) VALUES ";
							$cntProd=0; 
							foreach($ids as $id) { $prod .= ($cntProd++ == 0 ? '' : ',')."('','". $id ."','". $info['intSeriesNum'] ."')";  }
							do_mysql_query($prod,"inserting products");
						}
		
						$database_error += !do_mysql_query("DELETE FROM EventPresenters WHERE intEventSeries='" . $info['intSeriesNum'] . "'", 'delete presenter');	
						if($_POST['idPresenters'] != '') {
							$ids = $_POST['idPresenters']==''?array():explode(',', $_POST['idPresenters']);
							$pres = "INSERT INTO EventPresenters (idEvent,idPresenter,intEventSeries) VALUES ";
							$cntPres=0; 
							foreach($ids as $id) { $pres .= ($cntPres++ == 0 ? '' : ',')."('','". $id ."','". $info['intSeriesNum'] ."')";  }
							do_mysql_query($pres,"inserting presenters");
						}
						
						header("Location: index.php?idStore=" . $_POST['idStore'] . '&intDate=' . $_POST['intDate']);
						die();
					} 
		
		
					// Array of all the days
					$allDays = $_POST['calDay'];
					foreach($allDays as $current_day) {

						$q = "SELECT ID FROM Events WHERE intSeries='" . $info['intSeriesNum'] . "' AND dDate='" . $intYear . '-' . $intMonth . '-' . $current_day . "'";
						$result = do_mysql_query($q,"check for update");
		
						if(mysql_num_rows($result) > 0) {
					
							$database_error = false;
				
							$database_error += !update_record('Events', 'chrTitle', $_POST['id'], encode($_POST['chrTitle']));
							$database_error += !update_record('Events', 'chrDescription', $_POST['id'], encode($_POST['chrDescription']));
							$database_error += !update_record('Events', 'bReviewed', $_POST['id'], "0");
							$database_error += !update_record('Events', 'idStore', $_POST['id'], $_POST['idStore']);
							$database_error += !update_record('Events', 'idEventType', $_POST['id'], $_POST['idEventType']);
							$database_error += !update_record('Events', 'dtModified', $_POST['id'], date("Y-m-d h:m:s",strtotime("now")));							
							$database_error += !update_record('Events', 'dDate', $_POST['id'], $intYear . '-' . $intMonth . '-' . $current_day);
							$database_error += !update_record('Events', 'tBegin', $_POST['id'], $f_tBegin);
							$database_error += !update_record('Events', 'tEnd', $_POST['id'], $f_tEnd);
							$database_error += !update_record('Events', 'bGiveaway', $_POST['id'], $_POST['bGiveaway']);
							$database_error += !update_record('Events', 'chrGiveawayProduct', $_POST['id'], $_POST['chrGiveawayProduct']);
							$database_error += !update_record('Events', 'chrGiveawayFrom', $_POST['id'], $_POST['chrGiveawayFrom']);
							$database_error += !update_record('Events', 'fFinalBudget', $_POST['id'], $_POST['fFinalBudget']);
							$database_error += !update_record('Events', 'chrBudgetQuarter', $_POST['id'], $_POST['chrBudgetQuarter']);
							$database_error += !update_record('Events', 'enEquipmentProvidedBy', $_POST['id'], $_POST['enEquipmentProvidedBy']);
							$database_error += !update_record('Events', 'enEquipmentAppleSource', $_POST['id'], $_POST['enEquipmentAppleSource']);
							$database_error += !update_record('Events', 'setMarketingMaterials', $_POST['id'], implode(',', $_POST['setMarketingMaterials']));
							$new_id = $_POST['id'];
				
						} else {
			
							$q = "INSERT INTO Events SET 
								chrTitle='" . htmlentities($_POST['chrTitle'], ENT_NOQUOTES, 'UTF-8') . "',
								chrDescription='" . htmlentities($_POST['chrDescription'], ENT_NOQUOTES, 'UTF-8') . "',
								idStore='" . $_POST['idStore'] . "',
								bReviewed=0,
								idEventType='" . $_POST['idEventType'] . "',
								dtModified=now(),
								dDate='" . $intYear . '-' . $intMonth . '-' . $current_day . "',
								tBegin='" . $f_tBegin . "',
								tEnd='" . $f_tEnd . "',
								bGiveaway='" . $_POST['bGiveaway'] . "',
								chrGiveawayProduct='" . $_POST['chrGiveawayProduct'] . "',
								chrGiveawayFrom='" . $_POST['chrGiveawayFrom'] . "',
								fFinalBudget='" . $_POST['fFinalBudget'] . "',
								chrBudgetQuarter='" . $_POST['chrBudgetQuarter'] . "',
								enEquipmentProvidedBy='" . $_POST['enEquipmentProvidedBy'] . "',
								enEquipmentAppleSource='" . $_POST['enEquipmentAppleSource'] . "',
								setMarketingMaterials='" . implode(',', $_POST['setMarketingMaterials']) . "',
								intSeries='" . $info['intSeriesNum'] . "'";
							$result = do_mysql_query($q, 'create new event');
			
						}
					}
	
					
					$database_error += !do_mysql_query("DELETE FROM EventProducts WHERE intEventSeries='" . $info['intSeriesNum'] . "'", 'delete product');
					if($_POST['idProducts'] != '') {		
						$ids = $_POST['idProducts']==''?array():explode(',', $_POST['idProducts']);
						$prod = "INSERT INTO EventProducts (idEvent,idProduct,intEventSeries) VALUES ";
						$cntProd=0; 
						foreach($ids as $id) { $prod .= ($cntProd++ == 0 ? '' : ',')."('','". $id ."','". $info['intSeriesNum'] ."')";  }
						do_mysql_query($prod,"inserting products");
					}
			
					$database_error += !do_mysql_query("DELETE FROM EventPresenters WHERE intEventSeries='" . $info['intSeriesNum'] . "'", 'delete presenter');	
					if($_POST['idPresenters'] != '') {
						$ids = $_POST['idPresenters']==''?array():explode(',', $_POST['idPresenters']);
						$pres = "INSERT INTO EventPresenters (idEvent,idPresenter,intEventSeries) VALUES ";
						$cntPres=0; 
						foreach($ids as $id) { $pres .= ($cntPres++ == 0 ? '' : ',')."('','". $id ."','". $info['intSeriesNum'] ."')";  }
						do_mysql_query($pres,"inserting presenters");
					}
			

				if(is_uploaded_file($_FILES['chrPhoto']['tmp_name'])) {
					do_mysql_query("UPDATE Events SET 
						intImageSize=". $_FILES['chrPhoto']['size'] .",
						chrImageName='". $_POST['id'] ."-". basename($_FILES['chrPhoto']['name']) ."',
						chrImageType='". $_FILES['chrPhoto']['type'] ."'
						WHERE ID=". $_POST['id'] ."
						","insert image");
						
					$uploaddir = BASE_FOLDER . 'eventimages/';
					$uploadfile = $uploaddir . $_POST['id'] .'-'. basename($_FILES['chrPhoto']['name']);
		
					move_uploaded_file($_FILES['chrPhoto']['tmp_name'], $uploadfile);
				}
				
			
					$_SESSION['InfoMessage'][] = 'Changes to the event <span class="Specific">' . $_POST['chrTitle'] . '</span> have been saved.';
					header("Location: index.php?idStore=" . $_POST['idStore'] . '&intDate=' . $_POST['intDate']);
					die();
		
				}			
			} else {

			
			$setMarketingMaterials = array();
			
			list($beginHour, $beginMinute) = split(':', $info['tBegin']);
			$info['tBeginHour'] = number_format($beginHour);
				if($beginHour >= 12) {
					if($beginHour > 12) {
						$info['tBeginHour'] = $beginHour - 12;
					} else {
						$info['tBegin'] = 12;	
					}
					$info['tBeginMeridian'] = 'PM';
				} else {
					if($beginHour == '00') {
						$info['tBeginHour'] = 12;
					} else {
						$info['tBeginHour'] = $beginHour;
					}
					$info['tBeginMeridian'] = 'AM';
				}
				$info['tBeginMinute'] = $beginMinute;
			
			list($endHour, $endMinute) = split(':', $info['tEnd']);
				if($endHour >= 12) {
					if($endHour > 12) {
						$info['tEndHour'] = $endHour - 12;
					} else {
						$info['tEndHour'] = 12;
					}
					$info['tEndMeridian'] = 'PM';
				} else {
					if($endHour == '00') {
						$info['tEndHour'] = 12;
					} else {
						$info['tEndHour'] = $endHour;
					}
					$info['tEndMeridian'] = 'AM';
				}
				$info['tEndMinute'] = $endMinute;
			
			
			$result = do_mysql_query("SELECT Presenters.ID,Presenters.chrName
				FROM EventPresenters 
				JOIN Presenters ON EventPresenters.idPresenter=Presenters.ID 
				WHERE intEventSeries='" . $info['intSeries'] . "'
				", 'get presenters');
	
			$count = 0;
			$info['chrPresenters'] = "";
			$info['idPresenters'] = "";
			while($row = mysql_fetch_assoc($result)) {
				$info['chrPresenters'] .= ($count != 0 ? ','.$row['chrName'] : $row['chrName']);
				$info['idPresenters'] .= ($count++ != 0 ? ','.$row['ID'] : $row['ID']);
			}
	
			$result = do_mysql_query("SELECT Products.ID, Products.chrName 
				FROM EventProducts 
				JOIN Products ON EventProducts.idProduct=Products.ID 
				WHERE intEventSeries='" . $info['intSeries'] . "'
				", 'get products');
	
			$count = 0;		
			$info['chrProducts'] = "";
			$info['idProducts'] = "";
			while($row = mysql_fetch_assoc($result)) {
				$info['chrProducts'] .= ($count != 0 ? ','.$row['chrName'] : $row['chrName']);
				$info['idProducts'] .= ($count++ != 0 ? ','.$row['ID'] : $row['ID']);
			}
		}
	
		
	$eventCategory = do_mysql_query("SELECT ID,chrCategory FROM EventCategory","getting Event Categories");

	$eventnames = do_mysql_query("SELECT idEventType, chrEventTitle, txtEventDescription FROM EventTypeNames ORDER BY idEventType, chrEventTitle", 'get eventtypenames');
	$eventtype_names = array();
	while($row = mysql_fetch_assoc($eventnames)) {
		$eventtype_names[$row['idEventType']][] = addslashes($row['chrEventTitle']);
	}

	$Weeklyeventtype_result = do_mysql_query("SELECT ID, chrName FROM EventTypes WHERE !bDeleted AND idEventCategory=1 ORDER BY chrName", 'get weekly types');
	$Weekly_names = array();
	while($row = mysql_fetch_assoc($Weeklyeventtype_result)) {
		$Weekly_names[$row['ID']][] = addslashes($row['chrName']);
	}
	$Specialeventtype_result = do_mysql_query("SELECT ID, chrName FROM EventTypes WHERE !bDeleted AND idEventCategory=2 ORDER BY ID", 'get special types');
	$Special_names = array();
	while($row = mysql_fetch_assoc($Specialeventtype_result)) {
		$Special_names[$row['ID']][] = addslashes($row['chrName']);
	}
	


function insert_into_head() {
	global $eventtype_names;
	global $Weekly_names;
	global $Special_names;
	global $info;
?>

	<script type="text/javascript">//<![CDATA[
	
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
		var chk = <?=($info['chrEventType'] != '' ? "'" . $info['chrEventType'] . "'" : "''")?>;


		if(choice == '1') {
			for (var i in weekly_names) {
				if(weekly_names[i] != '[object Object]') {
					theform.idEventType.options[theform.idEventType.options.length] = new Option(weekly_names[i], i);
				}
				if(weekly_names[i] == chk) {
					theform.idEventType.options[tmp].selected = true;
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
				if(special_names[i] == chk) {
					theform.idEventType.options[tmp].selected = true;
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
		var m = types_names[typefield.value];
		theform = typefield.form;

		theform.chrTitleList.options.length=0;
		theform.chrTitleList.options[theform.chrTitleList.options.length] = new Option('', '', true, true);

		if (!m) {
			theform.chrTitle.parentNode.style.display='block';
			theform.chrTitle.type='text';
			theform.chrTitleList.parentNode.style.display='none';
			eventname_changed();
			return;
		}

		var tmp = 1;
		var chk = <?=($info['chrTitle'] != '' ? "'" . addslashes($info['chrTitle']) . "'" : "''")?>;

		for (var i in m) {
			theform.chrTitleList.options[theform.chrTitleList.options.length] 
				= new Option(m[i], m[i], (m[i]==theform.chrTitle.value), (m[i]==theform.chrTitle.value));
				
				if(m[i] == chk) {
					theform.chrTitleList.options[tmp].selected = true;
				}
				tmp++;
		}

		theform.chrTitle.parentNode.style.display='none';
		theform.chrTitle.type='hidden';
		theform.chrTitleList.parentNode.style.display='block';
		
		eventname_changed();
	}
	
	var last_div='';
	function eventname_changed()
	{
		var select = document.forms[0].chrTitleList;
		var descfield;
		
		if(select.options.length > 0) {
			str = select.options[select.selectedIndex].value;
			str = str.replace(/[^A-Za-z]+/g,"");

			descfield = document.getElementById(str);
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
	
	function GiveawayChanged()
	{
		var input = document.getElementById("Giveaway");

		if(input.value=="1") {
			document.getElementById("GiveawayProductSection").style.display = "block";
			document.getElementById("GiveawayFromSection").style.display = "block";
		} else {
			document.getElementById("GiveawayProductSection").style.display = "none";
			document.getElementById("GiveawayFromSection").style.display = "none";
		}
	}

	function EquipmentProvidedByChanged()
	{
		var input = document.getElementById("EquipmentProvidedBy");

		if(input.value=="Apple") {
			document.getElementById("EquipmentAppleSourceSection").style.display = "block";
		} else {
			document.getElementById("EquipmentAppleSourceSection").style.display = "none";
		}
	}

	function MyDocLoad()
	{
		mainevent_changed();
		GiveawayChanged();
		EquipmentProvidedByChanged();
		defaultOnLoad();
	}

	//]]></script>
<?
}

function insert_body_params()
{
	?> onload='MyDocLoad();' <?
}

	$eventnames = do_mysql_query("SELECT idEventType, chrEventTitle, txtEventDescription FROM EventTypeNames ORDER BY idEventType, chrEventTitle", 'get eventtypenames');

	// Set the title, and add the doc_top
	$title = "Edit Event";
	require(BASE_FOLDER . 'docpages/doc_meta_events.php');
	include(BASE_FOLDER . 'docpages/doc_top_events.php');
?>
<div style='margin: 10px;'>

		<div class="AdminTopicHeader">Edit Entry</div>
			<div class="AdminDirections" style='width: 870px;'>To Edit a new entry simply fill out the information below and then click the Update Entry button.
		<?=($security['enStatus'] == 'Approved' ? "<br /><span style='color: red'>This workshop/event has been submitted and has been approved.  By making this update, the whole calendar will need to be re-submitted for approval.</span>" : '')?>
				<?=($security['enStatus'] == 'Submitted' ? "<br /><span style='color: red'>This workshop/event has been submitted for approval and cannot be edited.</span>" : '')?>
				<?=(($security['enStatus'] == 'Rejected' && $info['bApproved'] == 1) ? "<br /><span style='color: red'>Changing this event will cause it to be disapproved until resubmittion.</span>" : '')?>
			</div>


	<form id='Form' method='post' action=''  enctype="multipart/form-data">


			<div style='margin: -7px 0 3px 0; background-color: #FFFF99;'><input type='checkbox' name='chkEditAll' value='1' <?=( $info['chkEditAll'] == 1 ? ' checked="checked" ' : '')?> /> Edit All</div>

	
<? if(count($error_messages)) { ?>
	<div class='Messages'>
<?		foreach($error_messages as $error) { ?>
			<p class='ErrorMessage'><?=$error?></p>
<?		} ?>
	</div>
<?	} ?>

		<table cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr>
				<td width="49.5%" style='vertical-align: top;'>
				
			
					<div class='sectionInfo'>
						<div class='sectionHeader'>Entry Information</div>
						<div class='sectionContent'>

							<div class='form'>
								<div class='formHeader'>Category <span class='Required'>(Required)</span></div>
								<select name='chrMainEvent' id='chrMainEvent' onchange='document.getElementById("chrDescription").value=""; document.getElementById("DocLoadFocus").value=""; mainevent_changed();'>
									<option value=''></option>
<?	while($row = mysql_fetch_assoc($eventCategory)) { ?> 									
									<option value='<?=$row['ID']?>' <?=($info['idEventCategory'] == $row['ID'] ? 'selected' : '')?>><?=$row['chrCategory']?></option>
<?	} ?>
								</select>
							</div>


							<div class='form'>
								<div class='formHeader'>Type <span class='Required'>(Required)</span></div>
								<select name='idEventType' id='idEventType' onchange='document.getElementById("chrDescription").value=""; document.getElementById("DocLoadFocus").value=""; eventtype_changed();' value='<?=$info['idEventType']?>'>
								</select>
							</div>
							
							<div class='form'>
								<div class='formHeader'>Title <span class='Required'>(Required)</span></div>
								<div><input type='text' size='40' maxlength='80' id='DocLoadFocus' name='chrTitle' value='<?=$info['chrTitle']?>' /></div>
								<div style='display: none;'><select name='chrTitleList' onChange='document.getElementById("chrDescription").value=""; document.getElementById("DocLoadFocus").value=""; this.form.chrTitle.value=this.value; eventname_changed();'><option value='1'>Option</option></select></div>
							</div>
							
							<div class='form'>
								<div class='formHeader'>Description</div>
								<div><textarea id='chrDescription' name='chrDescription' cols='40' rows='10'><?=$info['chrDescription']?></textarea></div>
<?	while($row = mysql_fetch_assoc($eventnames)) { ?>
<?  $idName = preg_replace('/[^A-Za-z]*/','',$row['chrEventTitle']); ?>
								<div style='display: none' id='<?=$idName?>'><?=$row['txtEventDescription']?></div>
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
							<div class='formHeader'>Select each presenter that will be involved in this event.</div>
							<input type='button' value='Add...' onclick='newwin = window.open("select-presenter.php?d=<?=urlencode(base64_encode('functioncall=presenters_add'))?>","new","width=425,height=400,resizable=1,scrollbars=1"); newwin.focus();'/>

							<input type='hidden' id='idPresenters' name='idPresenters' value='<?=$info['idPresenters']?>' />
							<input type='hidden' id='chrPresenters' name='chrPresenters' value='<?=$info['chrPresenters']?>' />

							<table class='list' id='Presenters' style='width: 100%;'>
								<thead>
									<tr>
										<th class='alignleft'>Presenter</th>
										<th style='width: 1%;'></th>
										</tr>
									</thead>
								<tbody>
<?			if($info['idPresenters'] != '') {
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
<?				} ?>
<?			} ?>
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
							<div class='formHeader'>Select the products on which this event will focus.</div>
							<input type='button' value='Add...' onclick='newwin = window.open("select-product.php?d=<?=urlencode(base64_encode('functioncall=products_add'))?>","new","width=340,height=400,resizable=1,scrollbars=1"); newwin.focus();'/>

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
<?			if($info['idProducts'] != '') {
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
				
				
				
				
<? if($_SESSION['idType'] == 1) { // This checks to see if you are a Corporate Type ?>

					<div class='sectionInfo'>
						<div class='sectionHeader'>Budget</div>
						<div class='sectionContent'>

							<div class='form'>
								<div class='formHeader'>What is the final budget for this project?</div>
								<div>$<input type='text' size='10' maxlength='20' name='fFinalBudget' value='<?=$info['fFinalBudget']?>' /></div>
							</div>

						
							<div class='Field'>
								<div class='L10'>Will all associated purchace orders post to?</div>
									<select name='chrBudgetQuarter'>
										<option></option>
										<option <?=($info['chrBudgetQuarter'] == 'Q1' ? ' selected="selected"' : '')?>>Q1</option>
										<option <?=($info['chrBudgetQuarter'] == 'Q2' ? ' selected="selected"' : '')?>>Q2</option>
										<option <?=($info['chrBudgetQuarter'] == 'Q3' ? ' selected="selected"' : '')?>>Q3</option>
										<option <?=($info['chrBudgetQuarter'] == 'Q4' ? ' selected="selected"' : '')?>>Q4</option>
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
				document.getElementById('tEndHour').options[11].selected = true;
				(document.getElementById('tBeginMeridian').value == 'AM' ? document.getElementById('tEndMeridian').options[1].selected = true : document.getElementById('tEndMeridian').options[0].selected = true);
			} else {
				document.getElementById('tEndHour').options[time].selected = true;
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
<?	if($info['tBeginHour'] == '') { ?>
								<option></option>
<?	} ?>
<?	for($hour = 1; $hour<=12; $hour++) { ?>
								<option value='<?=$hour?>' <?=($hour == $info['tBeginHour'] ? ' selected' : '')?>><?=$hour?></option>
<?	} ?>
							</select>
							:
							<select name='tBeginMinute' id='tBeginMinute'>
								<option<?=('00' == $info['tBeginMinute'] ? ' selected' : '')?>>00</option>
								<option<?=('15' == $info['tBeginMinute'] ? ' selected' : '')?>>15</option>
								<option<?=('30' == $info['tBeginMinute'] ? ' selected' : '')?>>30</option>
								<option<?=('45' == $info['tBeginMinute'] ? ' selected' : '')?>>45</option>
							</select>

							<select name='tBeginMeridian' id='tBeginMeridian'>
								<option value='AM'<?=('AM' == $info['tBeginMeridian'] ? " selected" : "")?>>AM</option>
								<option value='PM'<?=((('PM' == $info['tBeginMeridian']) || !isset($info['tEndMeridian'])) ? " selected" : "")?>>PM</option>
							</select>

							</td><td>&nbsp;</td><td style='vertical-align: top;'>


							<select name='tEndHour' id='tEndHour'>
<?	if($info['tEndHour'] == '') { ?>
								<option></option>
<?	} ?>
<?	for($hour = 1; $hour<=12; $hour++) { ?>
								<option value='<?=$hour?>' <?=($hour == $info['tEndHour'] ? ' selected ' : '')?>><?=$hour?></option>
<?	} ?>
							</select>
							:
							<select name='tEndMinute' id='tEndMinute'>
								<option<?=('00' == $info['tEndMinute'] ? ' selected' : '')?>>00</option>
								<option<?=('15' == $info['tEndMinute'] ? ' selected' : '')?>>15</option>
								<option<?=('30' == $info['tEndMinute'] ? ' selected' : '')?>>30</option>
								<option<?=('45' == $info['tEndMinute'] ? ' selected' : '')?>>45</option>
							</select>

							<select name='tEndMeridian' id='tEndMeridian'>
								<option value='AM'<?=('AM' == $info['tEndMeridian'] ? ' selected' : '')?>>AM</option>
								<option value='PM'<?=((('PM' == $info['tEndMeridian']) || !isset($info['tEndMeridian'])) ? ' selected' : '')?>>PM</option>
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

							<table class='Calendar'>
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
			if($current_day >= 1 && $current_day <= $days_this_month) { ?>
									<td><label><input type='checkbox' name='calDay[]' value='<?=$current_day?>' <?=(($current_day == $info['intDateDay']) ? 'checked="checked" ' : '')?> <?=( in_array($current_day, $inDays) ?'checked="checked" ':'')?> /><?=$current_day?></label></td>
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
									<table><tr><td>
									<div class='formHeader'>
										Upload Photos <span class='Required' style='font-size: 10px;'>(1 file upload, 49 x 49 pixels)</span>
									</div>
									<input name="chrPhoto" type="file" />
									</td><td>
									<span style='padding-left: 10px;'><? if($info['chrImageName'] != '') { ?><img src='<?=BASE_FOLDER?>eventimages/<?=$info['chrImageName']?>' style='width: 49px; height: 49px;' /> <? } ?></span>							</td></tr></table>
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
							<div class='formHeader'>Will a product/service be given away with this workshop/event?</div>
							<select name='bGiveaway' id='Giveaway' onchange='GiveawayChanged();'>
<?	if($info['bGiveaway'] == '') { ?>
								<option></option>
<?	} ?>
								<option value="1" <?=($info['bGiveaway'] == 1 ? ' selected="selected"' : '')?>>Yes</option>
								<option value="0" <?=($info['bGiveaway'] === '0' ? ' selected="selected"' : '')?>>No</option>
							</select>
							</div>
						
						<div class='Field' id='GiveawayProductSection' style='display: none;'>
							<div class='L10'>What product/service will be given away? <span class='Required'>(Required)</span></div>
								<input type='text' size='40' maxlength='80' name='chrGiveawayProduct' value='<?=$info['chrGiveawayProduct']?>' />
						</div>
						
						<div class='Field' id='GiveawayFromSection' style='display: none;'>
							<div class='L10'>Will the item be sent from the store or from corporate? <span class='Required'>(Required)</span></div>
							<select name='chrGiveawayFrom'>
<?	if($info['chrGiveawayFrom'] == '') { ?>
								<option></option>
<?	} ?>
								<option <?=($info['chrGiveawayFrom'] == 'Store' ? ' selected="selected"' : '')?>>Store</option>
								<option <?=($info['chrGiveawayFrom'] == 'Corporate' ? ' selected="selected"' : '')?>>Corporate</option>
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
							<select name='enEquipmentProvidedBy' id='EquipmentProvidedBy' onchange='EquipmentProvidedByChanged();'>
<?	if($info['enEquipmentProvidedBy'] == '') { ?>
								<option></option>
<?	} ?>
								<option <?=($info['enEquipmentProvidedBy'] == 'Apple' ? ' selected="selected"' : '')?>>Apple</option>
								<option <?=($info['enEquipmentProvidedBy'] == 'Presenter' ? ' selected="selected"' : '')?>>Presenter</option>
							</select>
						</div>
						
						<div class='form' id='EquipmentAppleSourceSection' style='display: none;'>
							<div class='formHeader'>Is the equipment in-house, or does it need to come from an outside vendor? <span class='Required'>(Required)</span></div>
							<select name='enEquipmentAppleSource'>
<?	if($info['enEquipmentAppleSource'] == '') { ?>
								<option></option>
<?	} ?>
								<option <?=($info['enEquipmentAppleSource'] == 'In-House' ? ' selected="selected"' : '')?>>In-House</option>
								<option <?=($info['enEquipmentAppleSource'] == 'Outside Vendor' ? ' selected="selected"' : '')?>>Outside Vendor</option>
							</select>
						</div>

<? $mm = split(',',$info['setMarketingMaterials']); ?>

						<div class='form'>
							<div class='formHeader'>Marketing materials needed</div>
							<div><label><input <?=(in_array('Easel', $mm) ? 'checked="checked" ':'')?> type='checkbox' name='setMarketingMaterials[]' value='Easel' />Easel</label></div>
							<div><label><input <?=(in_array('Printed Materials', $mm) ? 'checked="checked" ':'')?> type='checkbox' name='setMarketingMaterials[]' id='MarketingMaterialsPrintedMaterials' value='Printed Materials' onchange='MarketingMaterialsChanged();' />Printed Materials</label></div>
							<div><label><input <?=(in_array('Web Marketing', $mm) ? 'checked="checked" ':'')?> type='checkbox' name='setMarketingMaterials[]' id='MarketingMaterialsWebMarketing' value='Web Marketing' onchange='MarketingMaterialsChanged();' />Web Marketing</label></div>
						</div>

				</div>
			</div>
<?	} ?>
	
	
						
						</div>
					</div>
	
				</td>
			</tr>
		</table>
	
		<?=($security['enStatus'] == 'Submitted' ? "<br /><span style='color: red'>This workshop/event has been submitted for approval and cannot be edited.</span>" : '')?>
		<?=($security['enStatus'] == 'Approved' ? "<br /><span style='color: red'>This workshop/event has been submitted and has been approved.  By making this update, the whole calendar will need to be re-submitted for approval.</span>" : '')?>
		<?=(($security['enStatus'] == 'Rejected' && $info['bApproved'] == 1) ? "<br /><span style='color: red'>Changing this workshop/event will cause it to be disapproved until resubmittion.</span>" : '')?>
		<div class='FormButtons'>
			<input type='hidden' name='intDate' value='<?=$_REQUEST['intDate']?>' />
			<input type='hidden' name='id' value='<?=$_REQUEST['id']?>' />			
			<input type='hidden' name='idStore' value='<?=$_REQUEST['idStore']?>' />
			<input type='hidden' name='intSeries' value='<?=$info['intSeries']?>' />
			<input type='hidden' name='idEvent' value='<?=$info['idEventNum']?>' />
			<input <?=($security['enStatus'] == 'Submitted' ? 'disabled' : '')?> type='submit' name='Update' value='Update Entry' />
			<input <?=($security['enStatus'] == 'Submitted' ? 'disabled' : '')?> type='submit' name='Delete' value='Delete' />
			<input type='button' onclick='history.back();' value='Cancel' />
			</div>
		</div>

	</form>
		
	</div>
<?
	include(BASE_FOLDER . 'docpages/doc_bottom.php');
?>
