<?php
	$BF = "../";
	require($BF."_lib2.php");
	// Checking request variables
	($_REQUEST['id'] == "" || !is_numeric($_REQUEST['id']) ? ErrorPage() : "" );
	($_REQUEST['status'] == "" || !is_numeric($_REQUEST['status']) ? ErrorPage() : "" );

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

	$title = 'Reviews';
	include($BF. 'includes/meta2.php');

	if(!isset($_REQUEST['month'])) { $_REQUEST['month'] = $current_month+1; }
	
	$intYear = 2000 + floor($_REQUEST['month'] / 12);
	$intMonth = ($_REQUEST['month'] % 12)+1;
	$intMonth = ($intMonth < 10 ? '0'.$intMonth : $intMonth);
	$dDate = $intYear."-".$intMonth."-";
	$rejblank = 0;

	//Revised to show only the Events that need to be reviewed. Anything that has been approved will not show up on this page.
	$q = "SELECT Events.ID, Stores.chrName, chrAddress1, chrCity, chrState, chrPostalCode, chrCountry, chrPhone, chrFax, chrEmail, Events.chrTitle, chrDescription, dDate, 
			EventTypes.chrName as chrType, Events.bApproved, Events.dtReviewed, Events.dtRejected, Events.txtRejection,
		DATE_FORMAT(dDate,'%M %D, %Y') as dDate2, DATE_FORMAT(tBegin,'%l:%i %p') as tBegin, DATE_FORMAT(tEnd,'%l:%i %p') as tEnd 
		FROM Stores 
		JOIN Events ON Events.idStore=Stores.ID 
		JOIN EventTypes ON EventTypes.ID=Events.idEventType	
		WHERE EventTypes.bEditorReview=1 
			AND bReviewed = ".$_REQUEST['status']."
			AND dDate LIKE '".$intYear."-".$intMonth."-%' 
			AND Events.bDissaproved=0
			AND Stores.ID=". $_REQUEST['id'] ." 
		ORDER BY dDate";
	//$result = do_mysql_query($q,"getting info");
	$result = database_query($q,"getting info");
	//$row = mysql_fetch_assoc($result);
	$row = mysqli_fetch_assoc($result);

	if(count($_POST)) {
	if ($_POST['chkDisapproved'] != "") {
		//Find the check boxes that have been approved. Change the bReviewed to 1 so it is approved.
		foreach($_POST['chkApproved'] as $id) {
			database_query("UPDATE Events SET chrTitle='". encode($_POST['chrTitle'.$id]) ."', idEditor=". $_SESSION['idUser'] .",dtReviewed=now(), chrDescription='". encode($_POST['chrDescription'.$id]) ."', txtRejection='', bReviewed=1 WHERE ID=". $id,"update approved info");
		}
		
		//Find the check boxes that have not been approved. Change the bReveiwed to 0 so it is unapproved and bApproved to 0, denied.
		foreach($_POST['chkDisapproved'] as $id) {
			if ($_POST['txtRejection'.$id] == "") { 
				$rejblank = 1;
			}
			database_query("UPDATE Events SET bApproved=0,bDissaproved=1,bReviewed=0,dtRejected=now(), idEditor=". $_SESSION['idUser'] .",txtRejection='". encode($_POST['txtRejection'.$id]) ."' WHERE ID=". $id,"update disapproved info");
		}
	}
		
		if ($rejblank == 0) {
	
			//Find the check boxes that have been approved. Change the bReviewed to 1 so it is approved.
			foreach($_POST['chkApproved'] as $id) {
				database_query("UPDATE Events SET chrTitle='". $_POST['chrTitle'.$id] ."', dtReviewed=now(), chrDescription='". encode($_POST['chrDescription'.$id]) ."', txtRejection='', bReviewed=1 WHERE ID=". $id,"update approved info");
			}
			
			//Find the check boxes that have not been approved. Change the bReveiwed to 0 so it is unapproved and bApproved to 0, denied.
	
		
			foreach($_POST['chkDisapproved'] as $id) {
				database_query("UPDATE Events SET bApproved=0,bReviewed=0,dtRejected=now(),txtRejection='". encode($_POST['txtRejection'.$id]) ."' WHERE ID=". $id,"update disapproved info");
			}
			
			//Revised to show only the Events that need Editor Review and have been approved.
			$q = "SELECT count(Events.ID) as intRejected FROM Events 
						JOIN EventTypes ON Events.idEventType = EventTypes.ID
						WHERE bDissaproved=1
						AND dDate LIKE '". $dDate ."%' 
						AND idStore=". $_REQUEST['id'];
			$info = mysqli_fetch_assoc(database_query($q,"check for any events."));
			
			if($info['intRejected'] != 0) {
				database_query("UPDATE StoreMonths SET enStatus='Rejected' WHERE idStore=". $_REQUEST['id'] ." AND intMonth=". $intMonth ." AND intYear=". $intYear,"update status");
				//Send e-mail to store if calander has been rejected
			
				//Grab Store Information 
				$q = "SELECT ID, chrName, chrEmail
						FROM Stores
						WHERE ID=". $_REQUEST['id'];
				$tmpstoreinfo = fetch_database_query($q, "Getting Store Information for E-mail");
			
				// The To: Emails.
				$today = date('Y-m-d',strtotime("today"));
				
				$to      	= $tmpstoreinfo['chrEmail']; //Store E-mail Address
			
				$subject    = 'COE Calendar Rejected for '. date('F Y', strtotime('01-'.$intMonth.'-'.$intYear)) .'!';  //Subject

				$headers = 'To: '.$tmpstoreinfo['chrEmail'] . "\r\n";
				$headers .= 'From: retailevents@apple.com' . "\r\n";
				$headers .= 'Bcc: programmers@techitsolutions.com' . "\r\n";
		
				$Message    = "Please be advised that your submitted Calendar of Events for ". date('F Y', strtotime('01-'.$intMonth.'-'.$intYear)) ." has been rejected.". "\r\n" . "\r\n" .
							  "Please login and correct any items and resubmit the Calendar"  . "\r\n" . "\r\n" .
							  "http://retailmarketing.apple.com/" . "\r\n" . "\r\n" .
							  "Retail Marketing ".$today; //Message to store
			
				mail($to, $subject, $Message, $headers);
			}
			
			header("Location: reviews.php");
			die();
		}
	}
?>
<style type="text/css">
.infoTable { border: 1px solid #333; background: #ccc; width:100%; }
.infoTable td { vertical-align: top; }
</style>

<script type="text/javascript">
function dissaprove(val) {
	if(document.getElementById('chkDis'+val).checked) {
		document.getElementById('titleArea'+val).style.display = "none";
		document.getElementById('descArea'+val).style.display = "none";
		document.getElementById('disapproveArea'+val).style.display = "";
		document.getElementById('chkApp'+val).disabled = true;
	} else {
		document.getElementById('titleArea'+val).style.display = "";
		document.getElementById('descArea'+val).style.display = "";
		document.getElementById('disapproveArea'+val).style.display = "none";
		document.getElementById('chkApp'+val).disabled = false;
	}
}
function approve(val) {
	if(document.getElementById('chkApp'+val).checked) {
		document.getElementById('chkDis'+val).disabled = true;
	} else {
		document.getElementById('chkDis'+val).disabled = false;
	}
}


var selectedTable = "";
var selectedObj = "";

function saveInfo(obj,tbl) {
	if (document.selection) { // for IE
		//alert(document.selection.createRange().text);
		selectedTable = tbl;
	} else if (typeof obj.selectionStart != 'undefined') { // for FF, Opera etc...
		//alert(obj.value.substring(obj.selectionStart, obj.selectionEnd) +" -- start: "+ obj.selectionStart +" -- end: "+ obj.selectionEnd);
		selectedTable = tbl;
		selectedObj = obj;
	} else {
		alert('Could not find selection');
	}
};

function formatText(tag) {
	if (document.selection) { // for IE
       	if (selectedText != "") {
			var selectedText = document.selection.createRange().text;
            var newText = "<" + tag + ">" + selectedText + "</" + tag + ">";
            document.selection.createRange().text = newText;
		}
    } else if (typeof selectedObj.selectionStart != 'undefined') {
		el = document.getElementById(selectedTable);

 		el.value = el.value.substring(0,el.selectionStart) +'<'+tag+'>'+ el.value.substring(el.selectionStart,el.selectionEnd) +'</'+tag+'>'+ el.value.substring(el.selectionEnd,el.value.length);
  	} else {
		alert('Could not find selection');
	}
}

</script>
<?	
	include($BF. 'includes/top_admin2.php');
?>
<form method='post' action=''>
					<div class="AdminTopicHeader">Special Workshops/Events Review</div>
					<div class="AdminInstructions2">Edit the following Special Workshops/Events, and check the "Approved" box next to it if it is ready to be Submitted.</div>
					<?=($rejblank==1 ? '<div style="background-color:#FF6666; font-weight:bold; padding:4px; border:1px solid #000;">You Must enter a rejection message into each box.</div><br />' : "")?>
					
					<table class='infoTable'>
						<tr>
							<td style='text-align:left; white-space:nowrap; font-weight:BOLD;'>Store:</td>
							<td style='width: 50%; text-align:left;'><?=$row['chrName']?></td>
							<td style='text-align:left; white-space:nowrap; font-weight:BOLD;'>Phone Number:</td>
							<td style='width: 50%; text-align:left;'><?=$row['chrPhone']?></td>
						</tr>
						<tr>
							<td style='font-weight:BOLD;'>Address: </td>
							<td><?=$row['chrAddress1']?></td>
							<td style='font-weight:BOLD;'>Fax Number:</td>
							<td><?=$row['chrFax']?></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><?=$row['chrCity']?>, <?=$row['chrState']?> <?=$row['chrPostalCode']?></td>
							<td style='font-weight:BOLD;'>Email Address:</td>
							<td><?=$row['chrEmail']?></td>
						</tr>
					</table>

					<table cellpadding="0" cellspacing="0" style='width: 100%;'>
<?	$count=0;
	do {
		if($count++ != 0) {
?>
						<tr>
							<td colspan='3' align='center'><div style="margin: 10px 0;"></div></td>
						</tr>
<?
		} else {
?>
							<div style='margin-top: 10px;'></div>
<?
		}
?>
							
						<tr>
							<td style="background-color:#CCCCCC; font-size:11px;"><input id='chkApp<?=$row['ID']?>' type='checkbox' name='chkApproved[]' 
							<?=(isset($_POST['chkApproved']) ? (in_array($row['ID'], $_POST['chkApproved']) ? "checked='checked'" : "") : "")?>
							value='<?=$row['ID']?>' onchange='approve(<?=$row['ID']?>);' <?=($_REQUEST['status'] == 1 ? ($row['bApproved'] == 1 ? "checked='checked'" : "") : "")?> /> <strong>Approve</strong></td>
							<td style='border-top: 1px solid #CCC; border-left: 1px solid #CCC; padding:5px; font-size:11px; font-weight:bold;'>Date and Time:</td>
							<td style='border-top: 1px solid #CCC; border-right: 1px solid #CCC; padding:5px; font-size:11px;'><?=$row['dDate2']?> -- <?=$row['tBegin']?> to <?=$row['tEnd']?></td>
						</tr>
						<tr>
							<td style="background-color:#CCCCCC; font-size:11px;"><input id='chkDis<?=$row['ID']?>' type='checkbox' name='chkDisapproved[]'
							<?=(isset($_POST['chkDisapproved']) ? (in_array($row['ID'], $_POST['chkDisapproved']) ? "checked='checked'" : "") : "")?>
							value='<?=$row['ID']?>' onchange='dissaprove(<?=$row['ID']?>);' /> <strong>Disapprove</strong></td>
							<td style='border-left: 1px solid #CCC; padding:5px; font-size:11px; font-weight:bold;'>Workshop/Event Type</td>
							<td style='border-right: 1px solid #CCC; padding:5px; font-size:11px;'><?=$row['chrType']?> </td>
						</tr>
						<tr id='titleArea<?=$row['ID']?>' <?=(isset($_POST['chkDisapproved']) ? (in_array($row['ID'], $_POST['chkDisapproved']) ? "style='display: none;'" : "") : "")?>>
							<td></td>
							<td style='border-left: 1px solid #CCC; padding:5px; font-size:11px; font-weight:bold;'>Title</td>
							<td style='border-right: 1px solid #CCC; padding:5px;'><input type='text' name='chrTitle<?=$row['ID']?>' value='<?=$row['chrTitle']?>' style='width: 400px;' /> 
							<input type="button" name="edit" value="Edit" onclick='location.href="<?=$BF?>events/editevent.php?id=<?=$row['ID']?>&idStore=<?=$_REQUEST['id']?>&intDate=<?=$_REQUEST['month']?>&d=<?=base64_encode("refer=" . $_SERVER['REQUEST_URI'])?>"' /></td>
						</tr>
						<tr id='descArea<?=$row['ID']?>' <?=(isset($_POST['chkDisapproved']) ? (in_array($row['ID'], $_POST['chkDisapproved']) ? "style='display: none;'" : "") : "")?>>
							<td></td>
							<td style='border-left: 1px solid #CCC; border-bottom: 1px solid #CCC; padding:5px; vertical-align:top; font-size:11px; font-weight:bold;'>Description</td>
							<td style='border-right: 1px solid #CCC; padding:5px; border-bottom: 1px solid #CCC; padding:5px;'><textarea name='chrDescription<?=$row['ID']?>' id='chrDescription<?=$row['ID']?>' cols='75' rows='5' style='float: left;'  onmouseup='saveInfo(this,"chrDescription<?=$row['ID']?>")'><?=$row['chrDescription']?></textarea><div style='margin-top: 1px;'><input type="button" value="Bold" onclick="formatText('strong');" /><br />
						    <input type="button" value="Italics" onclick="formatText('em');" /><br />
						    <input type="button" value="Underline" onclick="formatText('u');" /></div></td>
						</tr><?
						if ($_REQUEST['status'] == 1) { ?>
						<tr id='descArea<?=$row['ID']?>'>
							<td></td>
							<td colspan='2'>Approved On: <?=($row['dtReviewed'] != "" ? date('m/d/y - g:i a',strtotime($row['dtReviewed'])) : "UNKNOWN")?>
							<?=($row['dtRejected'] != "" ? "and Last Disapproved On: ".date('m/d/y - g:i a',strtotime($row['dtRejected'])) : "" )?>
							</td>
						</tr>
						 <?
						} else if ($row['dtRejected'] != "") { ?>
						<tr id='descArea<?=$row['ID']?>'>
							<td></td>
							<td colspan='2'>Last Disapproved On: <?=date('m/d/y - g:i a',strtotime($row['dtRejected']))?>
							</td>
						</tr>
						 <?
						} ?>
						
						<tr id='disapproveArea<?=$row['ID']?>' <?=(isset($_POST['chkDisapproved']) ? (in_array($row['ID'], $_POST['chkDisapproved']) ? "" : "style='display: none;'") : "style='display: none;'")?>>
							<td></td>
							<td style='border-left: 1px solid #CCC; border-bottom: 1px solid #CCC; color: red; font-size:11px; font-weight:bold; text-align:left; vertical-align:middle; padding:5px;'>Dissaprove<br />Message<br />(Required)</td>
							<td style='border-right: 1px solid #CCC; padding:5px; border-bottom: 1px solid #CCC; padding:5px;'><textarea name='txtRejection<?=$row['ID']?>' cols='75' rows='5'><?=(isset($_POST['txtRejection'.$row['ID']]) ? "".$_POST['txtRejection'.$row['ID']]."" : "")?></textarea></td>
						</tr>
						
				
<?
	} while($row = mysqli_fetch_assoc($result));
?>
					</table>
				
					<div style='margin: 10px 0'>	
						<?
						if ($_REQUEST['status'] == 0) { ?>
						<input type='submit' value='Submit Reasons' />
						<input type='hidden' value='<?=$_REQUEST['id']?>' name='id' />
						<input type='hidden' value='<?=$_REQUEST['month']?>' name='month' />
					<?  }  ?>
					</div>
	
	</form>	
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>