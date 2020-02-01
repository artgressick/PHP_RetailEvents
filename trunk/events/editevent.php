 <?php
	$BF = '../';
	$title = 'Edit Event';
	$curPage = "coe";
	require($BF. '_lib2.php');
	// Checking request variables
	($_SESSION['idType'] == 4 && $_REQUEST['intDate'] > 99 ? ErrorPage() : ""); 
	($_REQUEST['id'] == "" || !is_numeric($_REQUEST['id']) ? ErrorPage() : "" );
	($_REQUEST['idStore'] == "" || !is_numeric($_REQUEST['idStore']) ? ErrorPage() : "" );
	include($BF. 'includes/meta2.php');

	if(!isset($_SESSION['idType'])) { $_SESSION['idType'] = 0; }

	$intYear = 2000 + floor($_REQUEST['intDate'] / 12);
	$intMonth = ($_REQUEST['intDate'] % 12)+1;

	$first_weekday = idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear));
	$first_display_day = 1-$first_weekday;
	$days_this_month = idate('t', mktime(0, 0, 0, $intMonth, 1, $intYear));
	$number_of_weeks = ceil(($days_this_month+idate('w', mktime(0, 0, 0, $intMonth, 1, $intYear))) / 7);

	$info = fetch_database_query("SELECT *, Events.intSeries as intSeriesNum, Events.ID as idEventNum, DAYOFMONTH(dDate) AS intDateDay, Stores.chrName AS chrStoreName, 
		EventTypes.chrName AS chrEventType, EventTypeNames.chrEventTitle as chrTitle2, EventTypeNames.txtEventDescription as chrDescription2, Events.idEventType
		FROM Events
		JOIN Stores ON Stores.ID=idStore
		JOIN EventTypes ON EventTypes.ID=idEventType
		LEFT JOIN EventTypeNames ON EventTypeNames.ID=Events.idEventTitle
		WHERE Events.ID='" . $_REQUEST['id'] . "'", 'get_event_by_id');

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
				$tBegin = ($tBeginHour < 10 ? '0'.$tBeginHour : $tBeginHour) . ":" . $_POST['tBeginMinute'] . ":00";
				$tEnd = ($tEndHour < 10 ? '0'.$tEndHour : $tEndHour) . ":" . $_POST['tEndMinute'] . ":00";
	
				//Grab Store Information 
				$q = "SELECT ID, chrName, chrEmail
						FROM Stores
						WHERE ID=". $_REQUEST['idStore'];
				$tmpstoreinfo = fetch_database_query($q, "Getting Store Information for E-mail");
	
				
	
				// Big If statement to see what all changed. We Only need to do this if the calender is approved
				$changeflag = 0;
				$dtflag = 0;
				$adminmail = 0;
				$thirdmail = 0;
				
				if ($_POST['fFinalBudget'] == "") { $_POST['fFinalBudget'] = 0; }
				if (	$security['enStatus'] == 'Approved' && (						
						$info['chrTitle'] != encode($_POST['chrTitle']) ||
						$info['chrDescription'] != encode($_POST['chrDescription']) ||
						$info['idEventType'] != $_POST['idEventType'])) {
						
						$changeflag = 1;
					}
					
										
					$bEmailSent = false;
					if($security['enStatus'] == 'Approved' && $changeflag == 1) { 
						database_query("UPDATE StoreMonths SET enStatus='Rejected' WHERE idStore='". $_POST['idStore'] ."' AND intYear='". $intYear ."' AND intMonth='". $intMonth ."'","rejecting an approved COE");
						database_query("UPDATE Events SET bApproved=0,bReviewed=0,txtRejection='Submitted after Approval' WHERE ID='". $_POST['id'] ."'","disaproving event");
						$security['enStatus'] = 'Rejected';
												
						// The To: Emails.
						$today = date('Y-m-d',strtotime("today"));
							
						$to      	= $tmpstoreinfo['chrEmail']; //Store E-mail Address
						
						$subject    = 'COE Calendar Rejected for '. date('F Y', strtotime('01-'.$intMonth.'-'.$intYear)) .'!';  //Subject
				
						$headers .= 'From: retailevents@apple.com' . "\r\n";
						$headers .= 'Bcc: programmers@techitsolutions.com' . "\r\n";
			
						$Message    = "Please be advised that your submitted Calendar of Events for ". date('F Y', strtotime('01-'.$intMonth.'-'.$intYear)) ." has been rejected.". "\r\n" . "\r\n" .
									  "Please login and correct any items if neeeded and resubmit the Calendar"  . "\r\n" . "\r\n" .
									  "http://retailmarketing.apple.com/" . "\r\n" . "\r\n" .
									  "Retail Marketing ".$today; //Message to store
						
						mail($to, $subject, $Message, $headers);
						$bEmailSent = true;
						
					}
					if($security['enStatus'] == 'Rejected' && $info['bApproved'] == 1 && $changeflag == 1) { 
						database_query("UPDATE Events SET bApproved=0,bReviewed=0 WHERE ID='". $_POST['id'] ."'","disaproving event");
					}					
			
					if($_POST['chkEditAll'] == 1 && ($info['intSeriesNum'] > 0 || $info['intSeriesNum'] != "") ) {
						
						$q = "SELECT * FROM Events WHERE intSeries='". $info['intSeriesNum']."' AND idStore='".$_POST['idStore']."'";
						$allEvents = database_query($q, "Getting All Events so we can check for changes");
				
						while($row = mysqli_fetch_assoc($allEvents)) {
						
							if (($security['enStatus'] == 'Approved' || $row['bApproved'] == 1) && ($row['chrTitle'] != encode($_POST['chrTitle']) ||
								$row['chrDescription'] != encode($_POST['chrDescription']) ||
								$row['idEventType'] != $_POST['idEventType'])) {


								
								database_query("UPDATE StoreMonths SET enStatus='Rejected' WHERE idStore='". $_POST['idStore'] ."' AND intYear='". $intYear ."' AND intMonth='". $intMonth ."'","rejecting an approved COE");
								database_query("UPDATE Events SET bApproved=0,bReviewed=0,txtRejection='Submitted after Approval' WHERE ID='". $row['ID'] ."'","disaproving event");
								$security['enStatus'] = 'Rejected';
								$changeflag = 1;
										
								if(!bEmailSent) {
									// The To: Emails.
									$today = date('Y-m-d',strtotime("today"));
									
									$to      	= $tmpstoreinfo['chrEmail']; //Store E-mail Address
								
									$subject    = 'COE Calendar Rejected for '. date('F Y', strtotime('01-'.$intMonth.'-'.$intYear)) .'!';  //Subject
						
									$headers .= 'From: retailevents@apple.com' . "\r\n";
									$headers .= 'Bcc: programmers@techitsolutions.com' . "\r\n";
					
									$Message    = "Please be advised that your submitted Calendar of Events for ". date('F Y', strtotime('01-'.$intMonth.'-'.$intYear)) ." has been rejected.". "\r\n" . "\r\n" .
												  "Please login and correct any items if neeeded and resubmit the Calendar"  . "\r\n" . "\r\n" .
												  "http://retailmarketing.apple.com/" . "\r\n" . "\r\n" .
												  "Retail Marketing ".$today; //Message to store
								
									mail($to, $subject, $Message, $headers);

									$bEmailSent = true;
								}

							}
							
	
							$q = "UPDATE Events SET chrTitle='". encode($_POST['chrTitle']) ."',
								chrDescription='". encode($_POST['chrDescription']) ."',
								idEventTitle='". ($_POST['chrTitleList'] != "" ? $_POST['idEventTitle'] : 0) ."',
								tBegin='". $tBegin ."',
								tEnd='". $tEnd ."',
								idEventType='". $_POST['idEventType'] ."',
								dtModified=now(),
								chrGiveawayProduct='". encode($_POST['chrGiveawayProduct']) ."',
								chrGiveawayFrom='". encode($_POST['chrGiveawayFrom']) ."',
								fFinalBudget='". $_POST['fFinalBudget'] ."',
								chrBudgetQuarter='". encode($_POST['chrBudgetQuarter']) ."',
								enEquipmentProvidedBy='". $_POST['enEquipmentProvidedBy'] ."',
								enEquipmentAppleSource='". $_POST['enEquipmentAppleSource'] ."',
								setMarketingMaterials='". implode(',', $_POST['setMarketingMaterials']) . "'
								WHERE ID=". $row['ID'];
								
							database_query($q,"update event in series");
						
						}
					
						database_query("DELETE FROM EventProducts WHERE intEventSeries='" . $info['intSeriesNum'] . "'", 'delete product');	
						if($_POST['idProducts'] != '') {		
							$ids = $_POST['idProducts']==''?array():explode(',', $_POST['idProducts']);
							$prod = "INSERT INTO EventProducts (idEvent,idProduct,intEventSeries) VALUES ";
							$cntProd=0; 
							foreach($ids as $id) { $prod .= ($cntProd++ == 0 ? '' : ',')."('','". $id ."','". $info['intSeriesNum'] ."')";  }
							database_query($prod,"inserting products");
						}
		
						database_query("DELETE FROM EventPresenters WHERE intEventSeries='" . $info['intSeriesNum'] . "'", 'delete presenter');	
						if($_POST['idPresenters'] != '') {
							$ids = $_POST['idPresenters']==''?array():explode(',', $_POST['idPresenters']);
							$pres = "INSERT INTO EventPresenters (idEvent,idPresenter,intEventSeries) VALUES ";
							$cntPres=0; 
							foreach($ids as $id) { $pres .= ($cntPres++ == 0 ? '' : ',')."('','". $id ."','". $info['intSeriesNum'] ."')";  }
							database_query($pres,"inserting presenters");
						}										
						
						// For Push to official Site Do the following
						if($security['enStatus'] == 'Approved' && $changeflag == 0) { 
							if($connection = @mysqli_connect('weblab11.apple.com', 'techit', 'dollap')) {
								if(@mysqli_select_db($connection, 'retail')) {
								
									mysqli_data_seek($allEvents,0);
				
									while($row = mysqli_fetch_assoc($allEvents)) {
										$q = "UPDATE RetailEvents SET 
										tBegin='". $tBegin ."', 
										tEnd='". $tEnd ."', 
										chrGiveawayProduct='". addslashes(decode($_POST['chrGiveawayProduct'])) ."', 
										chrGiveawayFrom='". addslashes(decode($_POST['chrGiveawayFrom'])) ."', 
										fFinalBudget='". $_POST['fFinalBudget'] ."', 
										chrBudgetQuarter='". addslashes(decode($_POST['chrBudgetQuarter'])) ."', 
										enEquipmentProvidedBy='". $_POST['enEquipmentProvidedBy'] ."', 
										enEquipmentAppleSource='". $_POST['enEquipmentAppleSource'] ."', 
										setMarketingMaterials='". implode(',', $_POST['setMarketingMaterials']) ."'
										WHERE ID=".$row['ID'];
										mysqli_query($connection, $q);
										
										$future = strtotime("+3 days") ." ";
										$present = strtotime("now");
										$eventdate = strtotime($row['dDate']);
										
										if (($eventdate > $present && $eventdate < $future) && $adminmail == 0) { 
											//Send to Tommy if needed
												
											$subject = 'URGENT! Store '. $tmpstoreinfo['chrName'] .' has edited an event due to begin in less than 72 hours.';
											$headers = 'From: retailevents@apple.com' . "\r\n";
											$to = 't.nguyen@apple.com';
											mail($to, $subject, "Apple Store ".$tmpstoreinfo['chrName']." has edited an event due to begin in less than 72 hours.", $headers);
											$adminmail = 1;
										
										}
										
										if ($thirdmail == 0) { 
											//Send a notification
												
											$subject = 'ALERT! - '. $tmpstoreinfo['chrName'] .' has edited an event series and was pushed.';
											$headers = 'From: retailevents@apple.com' . "\r\n";
											$to = 'programmers@techitsolutions.com';
											mail($to, $subject, "Apple Store ".$tmpstoreinfo['chrName']." has edited an event series and was pushed./r/nEvent Series=".$info['intSeriesNum']."/r/nQuery='".$q."'", $headers);
											$thirdmail = 1;
										
										}
										
													
									}
									
									mysqli_query($connection, "DELETE FROM EventProducts WHERE intEventSeries='" . $info['intSeriesNum'] . "'");	
									if($_POST['idProducts'] != '') {		
										$ids = $_POST['idProducts']==''?array():explode(',', $_POST['idProducts']);
										$prod = "INSERT INTO EventProducts (idEvent,idProduct,intEventSeries) VALUES ";
										$cntProd=0; 
										foreach($ids as $id) { $prod .= ($cntProd++ == 0 ? '' : ',')."('','". $id ."','". $info['intSeriesNum'] ."')";  }
										mysqli_query($connection, $prod);
									}
					
									mysqli_query($connection, "DELETE FROM EventPresenters WHERE intEventSeries='" . $info['intSeriesNum'] . "'");	
									if($_POST['idPresenters'] != '') {
										$ids = $_POST['idPresenters']==''?array():explode(',', $_POST['idPresenters']);
										$pres = "INSERT INTO EventPresenters (idEvent,idPresenter,intEventSeries) VALUES ";
										$cntPres=0; 
										foreach($ids as $id) { $pres .= ($cntPres++ == 0 ? '' : ',')."('','". $id ."','". $info['intSeriesNum'] ."')";  }
										mysqli_query($connection, $pres);
									}
									
									mysqli_query($connection, "UPDATE stores SET bUploaded=1 WHERE idAltStore=". $_POST['idStore']);
								}
							}	

						}
						
						if ($_POST['refer'] != "") {
							parse_str(base64_decode($_POST['refer']),$refer);
							header("Location: ".$refer['refer']);
							die();
						}		
						
						if ($adminmail == 1) {
							header("Location: threedaynotice.php?idStore=". $_POST['idStore'] . '&intDate=' . $_POST['intDate']);
							die();
						} else {						
							header("Location: index.php?idStore=" . $_POST['idStore'] . '&intDate=' . $_POST['intDate']);
							die();
						}
					} 
		
		
					// Array of all the days
					$allDays = $_POST['calDay'];
					foreach($allDays as $current_day) {

						$q = "SELECT ID FROM Events WHERE intSeries='" . $info['intSeriesNum'] . "' AND dDate='" . $intYear . '-' . $intMonth . '-' . $current_day . "'";
						$result = database_query($q,"check for update");
		
						if(mysqli_num_rows($result) > 0) {
					
							$table = 'Events';
							$mysqlStr = '';
							$audit = '';

			
							// "List" is a way for php to split up an array that is coming back.  
							// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
							//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
							//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
							//    ...  This also will ONLY add changes to the audit table if the values are different.
							list($mysqlStr,$audit) = set_strs($mysqlStr,'chrTitle',$info['chrTitle'],$audit,$table,$_POST['id']);
							list($mysqlStr,$audit) = set_strs($mysqlStr,'chrDescription',$info['chrDescription'],$audit,$table,$_POST['id']);
							list($mysqlStr,$audit) = set_strs($mysqlStr,'idEventType',$info['idEventType'],$audit,$table,$_POST['id']);
							$_POST['idEventTitle'] = $_POST['chrTitleList'];
							list($mysqlStr,$audit) = set_strs($mysqlStr,'idEventTitle',$info['idEventTitle'],$audit,$table,$_POST['id']);
							list($mysqlStr,$audit) = set_strs($mysqlStr,'idEventType',$info['idEventType'],$audit,$table,$_POST['id']);
								$_POST['dtModified'] = date("Y-m-d h:m:s",strtotime("now"));
							list($mysqlStr,$audit) = set_strs_date($mysqlStr,'dtModified',$info['dtModified'],$audit,$table,$_POST['id']);
								$_POST['dDate'] = $intYear . '-' . $intMonth . '-' . $current_day;
							list($mysqlStr,$audit) = set_strs_date($mysqlStr,'dDate',$info['dDate'],$audit,$table,$_POST['id']);
								$_POST['tBegin'] = $tBegin;
							list($mysqlStr,$audit) = set_strs($mysqlStr,'tBegin',$info['tBegin'],$audit,$table,$_POST['id']);
								$_POST['tEnd'] = $tEnd;
							list($mysqlStr,$audit) = set_strs($mysqlStr,'tEnd',$info['tEnd'],$audit,$table,$_POST['id']);
							list($mysqlStr,$audit) = set_strs($mysqlStr,'bGiveaway',$info['bGiveaway'],$audit,$table,$_POST['id']);
							list($mysqlStr,$audit) = set_strs($mysqlStr,'chrGiveawayProduct',$info['chrGiveawayProduct'],$audit,$table,$_POST['id']);
							list($mysqlStr,$audit) = set_strs($mysqlStr,'chrGiveawayFrom',$info['chrGiveawayFrom'],$audit,$table,$_POST['id']);
							list($mysqlStr,$audit) = set_strs($mysqlStr,'fFinalBudget',$info['fFinalBudget'],$audit,$table,$_POST['id']);
							list($mysqlStr,$audit) = set_strs($mysqlStr,'chrBudgetQuarter',$info['chrBudgetQuarter'],$audit,$table,$_POST['id']);
							list($mysqlStr,$audit) = set_strs($mysqlStr,'enEquipmentProvidedBy',$info['enEquipmentProvidedBy'],$audit,$table,$_POST['id']);
							list($mysqlStr,$audit) = set_strs($mysqlStr,'enEquipmentAppleSource',$info['enEquipmentAppleSource'],$audit,$table,$_POST['id']);
								$_POST['setMarketingMaterials'] = implode(',',$_POST['setMarketingMaterials']);
							list($mysqlStr,$audit) = set_strs($mysqlStr,'setMarketingMaterials',$info['setMarketingMaterials'],$audit,$table,$_POST['id']);
		
							// if nothing has changed, don't do anything.  Otherwise update / audit.
							if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }

							//echo $mysqlStr;
							//die();
							
							
							// For Push to official Site Do the following
							if($security['enStatus'] == 'Approved' && $changeflag == 0) { 
													
								if($connection = @mysqli_connect('weblab11.apple.com', 'techit', 'dollap')) {
									if(@mysqli_select_db($connection, 'retail')) {
									
										$q = "UPDATE RetailEvents SET 
										tBegin='". $tBegin ."', 
										tEnd='". $tEnd ."', 
										dDate='".$_POST['dDate']."',
										chrGiveawayProduct='". addslashes(decode($_POST['chrGiveawayProduct'])) ."', 
										chrGiveawayFrom='". addslashes(decode($_POST['chrGiveawayFrom'])) ."', 
										fFinalBudget='". $_POST['fFinalBudget'] ."', 
										chrBudgetQuarter='". addslashes(decode($_POST['chrBudgetQuarter'])) ."', 
										enEquipmentProvidedBy='". $_POST['enEquipmentProvidedBy'] ."', 
										enEquipmentAppleSource='". $_POST['enEquipmentAppleSource'] ."', 
										setMarketingMaterials='". implode(',', $_POST['setMarketingMaterials']) ."' 
										WHERE ID=".$_POST['id'];
										mysqli_query($connection, $q);
										
										mysqli_query($connection, "DELETE FROM EventProducts WHERE intEventSeries='" . $info['intSeriesNum'] . "'");	
										if($_POST['idProducts'] != '') {		
											$ids = $_POST['idProducts']==''?array():explode(',', $_POST['idProducts']);
											$prod = "INSERT INTO EventProducts (idEvent,idProduct,intEventSeries) VALUES ";
											$cntProd=0; 
											foreach($ids as $id) { $prod .= ($cntProd++ == 0 ? '' : ',')."('','". $id ."','". $info['intSeriesNum'] ."')";  }
											mysqli_query($connection, $prod);
										}
						
										mysqli_query($connection, "DELETE FROM EventPresenters WHERE intEventSeries='" . $info['intSeriesNum'] . "'");	
										if($_POST['idPresenters'] != '') {
											$ids = $_POST['idPresenters']==''?array():explode(',', $_POST['idPresenters']);
											$pres = "INSERT INTO EventPresenters (idEvent,idPresenter,intEventSeries) VALUES ";
											$cntPres=0; 
											foreach($ids as $id) { $pres .= ($cntPres++ == 0 ? '' : ',')."('','". $id ."','". $info['intSeriesNum'] ."')";  }
											mysqli_query($connection, $pres);
										}
										
										
										
										mysqli_query($connection, "UPDATE stores SET bUploaded=1 WHERE idAltStore=". $split[0]);
										$future = strtotime("+3 days") ." ";
										$present = strtotime("now");
										$eventdate = strtotime($_POST['dDate']);
										$oldeventdate = strtotime($info['dDate']);
										
										if (($eventdate > $present && $eventdate < $future) || ($oldeventdate > $present && $oldeventdate < $future) && $adminmail == 0) { 
											//Send to Tommy if needed
											$subject = 'URGENT! Store '. $tmpstoreinfo['chrName'] .' has edited an event due to begin in less than 72 hours.';
											$headers = 'From: retailevents@apple.com' . "\r\n";
											$to = 't.nguyen@apple.com';
											mail($to, $subject, "Apple Store ".$tmpstoreinfo['chrName']." has edited an event due to begin in less than 72 hours.", $headers);
											$adminmail = 1;
										
										}
										
										if ($thirdmail == 0) { 
											//Send a notification
												
											$subject = 'ALERT! - '. $tmpstoreinfo['chrName'] .' has edited an event and was pushed.';
											$headers = 'From: retailevents@apple.com' . "\r\n";
											$to = 'programmers@techitsolutions.com';
											mail($to, $subject, "Apple Store ".$tmpstoreinfo['chrName']." has edited an event and was pushed./r/nEvent ID=".$_POST['id']."/r/nQuery='".$q."'", $headers);
											$thirdmail = 1;
										
										}

									}			
		
								}
			
							}
							
							
							
						} else {


			
							$q = "INSERT INTO Events SET 
								chrTitle='" . encode($_POST['chrTitle']) . "',
								chrDescription='" . encode($_POST['chrDescription']) . "',
								idEventTitle='". ($_POST['chrTitleList'] != "" ? 'idEventTitle,' : 0) ."',
								idStore='" . $_POST['idStore'] . "',
								bReviewed=0,
								idEventType='" . $_POST['idEventType'] . "',
								dtModified=now(),
								dDate='" . $intYear . '-' . $intMonth . '-' . $current_day . "',
								tBegin='" . $tBegin . "',
								tEnd='" . $tEnd . "',
								bGiveaway='" . $_POST['bGiveaway'] . "',
								chrGiveawayProduct='" . encode($_POST['chrGiveawayProduct']) . "',
								chrGiveawayFrom='" . encode($_POST['chrGiveawayFrom']) . "',
								fFinalBudget='" . $_POST['fFinalBudget'] . "',
								chrBudgetQuarter='" . encode($_POST['chrBudgetQuarter']) . "',
								enEquipmentProvidedBy='" . $_POST['enEquipmentProvidedBy'] . "',
								enEquipmentAppleSource='" . $_POST['enEquipmentAppleSource'] . "',
								setMarketingMaterials='" . implode(',', $_POST['setMarketingMaterials']) . "',
								intSeries='" . $info['intSeriesNum'] . "'";
							$result = database_query($q, 'create new event');
			
						}
					}
	
					
					database_query("DELETE FROM EventProducts WHERE intEventSeries='" . $info['intSeriesNum'] . "'", 'delete product');
					if($_POST['idProducts'] != '') {		
						$ids = $_POST['idProducts']==''?array():explode(',', $_POST['idProducts']);
						$prod = "INSERT INTO EventProducts (idEvent,idProduct,intEventSeries) VALUES ";
						$cntProd=0; 
						foreach($ids as $id) { $prod .= ($cntProd++ == 0 ? '' : ',')."('','". $id ."','". $info['intSeriesNum'] ."')";  }
						database_query($prod,"inserting products");
					}
			
					database_query("DELETE FROM EventPresenters WHERE intEventSeries='" . $info['intSeriesNum'] . "'", 'delete presenter');	
					if($_POST['idPresenters'] != '') {
						$ids = $_POST['idPresenters']==''?array():explode(',', $_POST['idPresenters']);
						$pres = "INSERT INTO EventPresenters (idEvent,idPresenter,intEventSeries) VALUES ";
						$cntPres=0; 
						foreach($ids as $id) { $pres .= ($cntPres++ == 0 ? '' : ',')."('','". $id ."','". $info['intSeriesNum'] ."')";  }
						database_query($pres,"inserting presenters");
					}
			
				if(is_uploaded_file($_FILES['chrPhoto']['tmp_name'])) {
					$phName = str_replace(" ","_",basename($_FILES['chrPhoto']['name']));
					
					database_query("UPDATE Events SET 
						intImageSize=". $_FILES['chrPhoto']['size'] .",
						chrImageName='". $_POST['id'] ."-". $phName ."',
						chrImageType='". $_FILES['chrPhoto']['type'] ."'
						WHERE ID=". $_POST['id'] ."
						","insert image");
						
					$uploaddir = $BF . 'eventimages/';
					$uploadfile = $uploaddir . $_POST['id'] .'-'. $phName;
		
					move_uploaded_file($_FILES['chrPhoto']['tmp_name'], $uploadfile);
				}		
			
				if ($adminmail == 1) {
					header("Location: threedaynotice.php?idStore=". $_POST['idStore'] . '&intDate=' . $_POST['intDate']);
					die();
				} else {						
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
			
			
			$result = database_query("SELECT Presenters.ID,Presenters.chrName
				FROM EventPresenters 
				JOIN Presenters ON EventPresenters.idPresenter=Presenters.ID 
				WHERE intEventSeries='" . $info['intSeries'] . "'
				", 'get presenters');
	
			$count = 0;
			$info['chrPresenters'] = "";
			$info['idPresenters'] = "";
			while($row = mysqli_fetch_assoc($result)) {
				$info['chrPresenters'] .= ($count != 0 ? ','.$row['chrName'] : $row['chrName']);
				$info['idPresenters'] .= ($count++ != 0 ? ','.$row['ID'] : $row['ID']);
			}
	
			$result = database_query("SELECT Products.ID, Products.chrName 
				FROM EventProducts 
				JOIN Products ON EventProducts.idProduct=Products.ID 
				WHERE intEventSeries='" . $info['intSeries'] . "'
				", 'get products');
	
			$count = 0;		
			$info['chrProducts'] = "";
			$info['idProducts'] = "";
			while($row = mysqli_fetch_assoc($result)) {
				$info['chrProducts'] .= ($count != 0 ? ','.$row['chrName'] : $row['chrName']);
				$info['idProducts'] .= ($count++ != 0 ? ','.$row['ID'] : $row['ID']);
			}
	}

	if($info['chrDescription'] == "" && $info['chrTitle'] == "") { $info['chrTitle'] = $info['chrTitle2']; $info['chrDescription'] = $info['chrDescription2']; }
	
	
	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript" type="text/javascript">//<![CDATA[
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;

		<? if($_SESSION['idType'] != 1 && $_SESSION['idType'] != 2 && !in_array($_REQUEST['idStore'],$_SESSION['intStoreList'])) { ?>
					document.getElementById('errors').innerHTML += "<div class='ErrorMessage'>You do not have permission to create an workshop/event for this store.</div>"; total += 1;
		<? } ?>

		total += ErrorCheck('idStore', "You must choose the store in which the event will take place.");
		total += ErrorCheck('chrMainEvent', "You must choose the Main Workshop/Event Type.");
		total += ErrorCheck('idEventType', "You must choose the Workshop/Event Type.");
		total += ErrorCheck('DocLoadFocus', "You must enter the name of the Event.");	
		
		if(document.getElementById('chrMainEvent').value == 2) {
			total += ErrorCheck('idPresenters', "You must choose at least one Presenter for this Workshop/Event.");
			total += ErrorCheck('chrDescription', "You must enter a Description for this Workshop/Event.");
		}

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

		if(input) {
			if(input.value=="1") {
				document.getElementById("enGiveawayProductSection").style.display = "block";
				document.getElementById("enGiveawayFromSection").style.display = "block";
			} else {
				document.getElementById("enGiveawayProductSection").style.display = "none";
				document.getElementById("enGiveawayFromSection").style.display = "none";
			}
		}
	}

	function EquipmentProvidedByChanged() {
		var input = document.getElementById("enEquipmentProvidedBy");

		if(input) {
			if(input.value=="Apple") {
				document.getElementById("enEquipmentAppleSourceSection").style.display = "block";
			} else {
				document.getElementById("enEquipmentAppleSourceSection").style.display = "none";
			}
		}
	}

	var dtAmount = 1;
	function dateAmount(obj) {
		if(obj.checked) { dtAmount += 1; }
			else { dtAmount -= 1; }
	}

	function resetVals() {
		document.getElementById("chrTitleList").value = "";
		document.getElementById("DocLoadFocus").value = "";
		document.getElementById("chrDescription").value = "";
		
		eventname_changed();
	}
	
</script>
<?
	$security = fetch_database_query("SELECT enStatus 
		FROM StoreMonths 
		WHERE idStore='". $_REQUEST['idStore'] ."' AND intYear='". $intYear ."' and intMonth='". $intMonth ."'
		","check for calendar status");


	$eventnames = database_query("SELECT ID, idEventType, chrEventTitle, txtEventDescription FROM EventTypeNames WHERE !bDeleted AND (bShow OR ID='".$info['idEventTitle']."') ORDER BY idEventType, chrEventTitle", 'get eventtypenames');	
	$eventCategory = database_query("SELECT ID,chrCategory FROM EventCategory","getting Event Categories");	

	$bodyParams = "MyDocLoad();";

	include($BF. 'includes/top_events.php');
?>

		<div class="AdminTopicHeader">Edit Entry</div>
			<div class="AdminDirections" style='width: 870px;'>To Edit a new entry simply fill out the information below and then click the Update Entry button.
			<?=($security['enStatus'] == 'Approved' ? "<br /><span style='color: red'>This event has been submitted and has been approved.  By making this update, the whole calendar may need to be re-submitted for approval.</span>" : '')?>
			</div>

	
	<div id='errors'></div>

	<form id='idForm' name='idForm' method='post' action='' enctype="multipart/form-data" onsubmit="return error_check()">

	<div style='margin: -7px 0 3px 0; background-color: #FFFF99;'><input type='checkbox' name='chkEditAll' id='chkEditAll' value='1' <?=( isset($info['chkEditAll']) && $info['chkEditAll'] == 1 ? ' checked="checked" ' : '')?> /> Edit All</div>

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
									<option<?=($info['idEventCategory'] == $row['ID'] ? ' selected="selected"' : '')?> value='<?=$row['ID']?>'><?=$row['chrCategory']?></option>
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
								<div><input type='text' size='40' maxlength='80' id='DocLoadFocus' name='chrTitle' value="<?=$info['chrTitle']?>" /></div>
								<div id='titleListDiv' style='display: none;'><select id='chrTitleList' name='chrTitleList' onChange='eventname_changed(this.value);'><option value='0'>Option</option></select></div>
								<input type='hidden' name='chrTitle2' id='chrTitle2' value="<?=$info['chrTitle']?>" />
							</div>
							
							<div class='form'>
								<div class='formHeader'>Description</div>
								<div><textarea id='chrDescription' name='chrDescription' cols='40' rows='10'><?=$info['chrDescription']?></textarea></div>
								<div style='display: none;'><textarea id='chrDescription2' name='chrDescription2'><?=$info['chrDescription']?></textarea></div>
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
							<div class='formHeader'>Select each presenter that will be involved in this event.</div>
							<input type='button' value='Add...' onclick='newwin = window.open("select-presenter.php?idStore=<?=$_REQUEST['idStore']?>&d=<?=urlencode(base64_encode('functioncall=presenters_add'))?>","new","width=435,height=400,resizable=1,scrollbars=1"); newwin.focus();'/>

							<input type='hidden' id='idPresenters' name='idPresenters' value='<?=$info['idPresenters']?>' />
							<input type='hidden' id='chrPresenters' name='chrPresenters' value='<?=$info['chrPresenters']?>' />
							<input type='hidden' id='idOldPresenters' name='idOldPresenters' value='<?=$info['idPresenters']?>' />

							<table class='list' id='Presenters' style='width: 100%;'>
								<thead>
									<tr>
										<th class='alignleft'>Presenter</th>
										<th style='width: 1%;'></th>
										</tr>
									</thead>
								<tbody id='presenterBody' name='presenterBody'>
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
							<div class='formHeader'>Select the products on which this workshop/event will focus.</div>
							<input type='button' value='Add...' onclick='newwin = window.open("select-product.php?d=<?=urlencode(base64_encode('functioncall=products_add'))?>","new","width=340,height=400,resizable=1,scrollbars=1"); newwin.focus();'/>

							<input type='hidden' id='idProducts' name='idProducts' value='<?=$info['idProducts']?>' />
							<input type='hidden' id='chrProducts' name='chrProducts' value='<?=$info['chrProducts']?>' />
							<input type='hidden' id='idOldProducts' name='idOldProducts' value='<?=$info['idProducts']?>' />
							
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
								<div>$<input type='text' size='10' maxlength='20' name='fFinalBudget' /></div>
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
								<option value='<?=$hour?>' <?=($hour == $info['tBeginHour'] ? ' selected="selected"' : '')?>><?=$hour?></option>
<?	} ?>
							</select>
							:
							<select name='tBeginMinute' id='tBeginMinute'>
								<option>00</option>
								<option<?=('15' == $info['tBeginMinute'] ? ' selected="selected"' : '')?>>15</option>
								<option<?=('30' == $info['tBeginMinute'] ? ' selected="selected"' : '')?>>30</option>
								<option<?=('45' == $info['tBeginMinute'] ? ' selected="selected"' : '')?>>45</option>
							</select>

							<select name='tBeginMeridian' id='tBeginMeridian'>
								<option value='AM'>AM</option>
								<option value='PM'<?=('PM' == $info['tBeginMeridian'] ? " selected" : "")?>>PM</option>

							</select>

							</td><td>&nbsp;</td><td style='vertical-align: top;'>


							<select name='tEndHour' id='tEndHour'>
								<option value=''></option>
<?	for($hour = 1; $hour<=12; $hour++) { ?>
								<option value='<?=$hour?>' <?=($hour == $info['tEndHour'] ? ' selected="selected"' : '')?>><?=$hour?></option>
<?	} ?>
							</select>
							:
							<select name='tEndMinute' id='tEndMinute'>
								<option>00</option>
								<option<?=('15' == $info['tEndMinute'] ? ' selected' : '')?>>15</option>
								<option<?=('30' == $info['tEndMinute'] ? ' selected' : '')?>>30</option>
								<option<?=('45' == $info['tEndMinute'] ? ' selected' : '')?>>45</option>
							</select>

							<select name='tEndMeridian' id='tEndMeridian'>
								<option value='AM'>AM</option>
								<option value='PM'<?=('PM' == $info['tEndMeridian'] ? " selected" : "")?>>PM</option>
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
									<td><label><input<?=(($current_day == $info['intDateDay']) ? ' checked="checked"' : '')?> type='checkbox' name='calDay[]' id='calDay[]' value='<?=$current_day?>' onclick='dateAmount(this)' /><?=$current_day?></label></td>
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
									<span style='padding-left: 10px;'><? if($info['chrImageName'] != '') { ?><img src='<?=$BF?>eventimages/<?=$info['chrImageName']?>' style='width: 49px; height: 49px;' /> <? } ?></span>							</td></tr></table>
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
							<select name='bGiveaway' id='bGiveaway' onchange='GiveawayChanged();'>
								<option value=""></option>
								<option value="1" <?=($info['bGiveaway'] == 1 ? ' selected="selected"' : '')?>>Yes</option>
								<option value="0" <?=($info['bGiveaway'] === '0' ? ' selected="selected"' : '')?>>No</option>
							</select>
							</div>
						
						<div class='Field' id='enGiveawayProductSection' style='display: none;'>
							<div class='L10'>What product/service will be given away? <span class='Required'>(Required)</span></div>
								<input type='text' size='40' maxlength='80' name='chrGiveawayProduct' id='chrGiveawayProduct' value='<?=$info['chrGiveawayProduct']?>' />
						</div>
						
						<div class='Field' id='enGiveawayFromSection' style='display: none;'>
							<div class='L10'>Will the item be sent from the store or from corporate? <span class='Required'>(Required)</span></div>
							<select id='chrGiveawayFrom' name='chrGiveawayFrom'>
								<option value=''></option>
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
							<select name='enEquipmentProvidedBy' id='enEquipmentProvidedBy' onchange='EquipmentProvidedByChanged();'>
								<option value=''></option>
								<option <?=($info['enEquipmentProvidedBy'] == 'Apple' ? ' selected="selected"' : '')?>>Apple</option>
								<option <?=($info['enEquipmentProvidedBy'] == 'Presenter' ? ' selected="selected"' : '')?>>Presenter</option>
							</select>
						</div>
						
						<div class='form' id='enEquipmentAppleSourceSection' style='display: none;'>
							<div class='formHeader'>Is the equipment in-house, or does it need to come from an outside vendor? <span class='Required'>(Required)</span></div>
							<select id='enEquipmentAppleSource' name='enEquipmentAppleSource'>
								<option value=''></option>
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

		<?=($security['enStatus'] == 'Approved' ? "<br /><span style='color: red'>This event has been submitted and has been approved.  By making this update, the whole calendar may need to be re-submitted for approval.</span>" : '')?>
		<div class='FormButtons'>
			<input type='hidden' id='intDate' name='intDate' value='<?=$_REQUEST['intDate']?>' />
			<input type='hidden' id='idStore' name='idStore' value='<?=$_REQUEST['idStore']?>' />			
			<input type='hidden' id='id' name='id' value='<?=$_REQUEST['id']?>' />
			<input type='hidden' id='refer' name='refer' value='<?=(isset($_REQUEST['d']) ? $_REQUEST['d'] : "")?>' />			
			<input type='submit' value='Update Event' />
			<input type='button' value='Delete' onclick='window.location="deleteevent.php?idEvent=<?=$_REQUEST['id']?>&idStore=<?=$_REQUEST['idStore']?>&intDate=<?=$_REQUEST['intDate']?>&intSeries=<?=$info['intSeries']?>&eraseall="+(document.getElementById("chkEditAll").checked ? 1 : "");' />
			<input type='button' onclick='history.back();' value='Cancel' />
			</div>
		</div>

	</form>

<?
	$eventnames = database_query("SELECT ID, idEventType, chrEventTitle FROM EventTypeNames WHERE !bDeleted AND (bShow OR ID='".$info['idEventTitle']."') ORDER BY idEventType, chrEventTitle", 'get eventtypenames');
	$eventtype_names = array();
	//while($row = mysqli_fetch_assoc($eventnames)) {
	//	$eventtype_names[$row['idEventType']][] = addslashes($row['chrEventTitle']);
	//}

	$storeinfo = fetch_database_query("SELECT Localization.ID AS idLocalization FROM Stores JOIN Localization ON Stores.idLocalization=Localization.ID WHERE Stores.ID=".$_REQUEST['idStore'], "Get Store Localization");
		
	$Weeklyeventtype_result = database_query("SELECT ID, chrName FROM EventTypes WHERE !bDeleted AND (bShow OR ID='".$info['idEventType']."') AND idEventCategory='1' AND idLocalization=".$storeinfo['idLocalization']." ORDER BY chrName", 'get weekly types');
	$Weekly_names = array();
	while($row = mysqli_fetch_assoc($Weeklyeventtype_result)) {
		$Weekly_names[$row['ID']][] = addslashes(decode($row['chrName']));
	}
	
	$Specialeventtype_result = database_query("SELECT ID, chrName FROM EventTypes WHERE !bDeleted AND (bShow OR ID='".$info['idEventType']."') AND idEventCategory='2' AND idLocalization=".$storeinfo['idLocalization']." ORDER BY ID", 'get special types');
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
		

		var tmp = 1;
		var chk = <?=($info['idEventTitle'] != '' ? "'" . $info['idEventTitle'] . "'" : "''")?>;

		var i;
		for(i=0; i<filter_list.length; i++) {
			if(filter_list[i][0] == typefield.value) {
				theform.chrTitleList.options[theform.chrTitleList.options.length] 
					= new Option(filter_list[i][2], filter_list[i][1], (filter_list[i][2]==theform.chrTitle.value), (filter_list[i][2]==theform.chrTitle.value));
				if(filter_list[i][1] == chk) {
					theform.chrTitleList.options[tmp].selected = true;
				}
				tmp++;
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
	function finalLoad() {
		document.getElementById('chrDescription').style.display='block';
		document.getElementById(last_div).style.display='none';
		last_div = '';
	}
	function MyDocLoad()
	{
		mainevent_changed();
		GiveawayChanged();
		EquipmentProvidedByChanged();
		defaultOnLoad();
		if(document.getElementById('titleListDiv').style.display != 'block') {
			finalLoad();
			document.getElementById('DocLoadFocus').value = document.getElementById('chrTitle2').value;
			document.getElementById('chrDescription').value = document.getElementById('chrDescription2').value;
		} else {
			document.getElementById('DocLoadFocus').value = document.getElementById('chrTitle2').value;
		}
	}

	
</script>
<?
	include($BF. 'includes/bottom2.php');
?>