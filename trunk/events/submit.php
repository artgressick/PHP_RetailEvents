<?php
	require("../_lib.php");
	
	$store = mysql_fetch_assoc(do_mysql_query("SELECT chrName FROM Stores WHERE ID='" . $_REQUEST['idStore'] . "'","getting store name"));
	
	// get the current month
	$intCurrentMonth = idate('m');
	$intCurrentYear = idate('Y');
	$current_month = (($intCurrentYear-2000)*12)+$intCurrentMonth-1;
	
	$intYear = 2000 + floor($_REQUEST['intDate'] / 12);
	$intMonth = ($_REQUEST['intDate'] % 12)+1;
	
	$date = $intYear .'-'.($intMonth < 10 ? '0'.$intMonth : $intMonth).'-%';
	
	if (@$_REQUEST['Confirmed']) {

		$result = do_mysql_query("SELECT coeLastSubmit FROM Stores WHERE ID='" . $_REQUEST['idStore'] . "'", 'coeLastSubmit date');
		$coeCheck = mysql_fetch_assoc($result);

		// Set all dissaproved by editor things back to 0
		$q = "UPDATE Events SET bDissaproved=0 WHERE idStore='". $_REQUEST['idStore'] ."' AND dDate LIKE '". $date ."'";
		do_mysql_query($q, 'update the stores');

		$attempt=1;
		if($coeCheck['coeLastSubmit'] == '') {
			$coeLastSubmit = '1:'.strftime('%B:%Y', mktime(0, 0, 0, $intMonth, 1, $intYear));
			do_mysql_query("UPDATE Stores SET coeLastSubmit='" . $coeLastSubmit . "' WHERE ID='" . $_REQUEST['idStore'] . "'", 'replace coeLastSubmit');
			echo "update made";
		} else {
			list($attempt,$mnt,$yr) = split(":",$coeCheck['coeLastSubmit']);
			if(strftime('%B %Y', mktime(0, 0, 0, $intMonth, 1, $intYear)) == $mnt . " " . $yr)
			{
				$attempt++;
				$coeLastSubmit = $attempt . ':' . strftime('%B:%Y', mktime(0, 0, 0, $intMonth, 1, $intYear));
				do_mysql_query("UPDATE Stores SET coeLastSubmit='" . $coeLastSubmit . "' WHERE ID='" . $_REQUEST['idStore'] . "'", 'replace coeLastSubmit');
			} else {
				$coeLastSubmit = '1:'.strftime('%B:%Y', mktime(0, 0, 0, $intMonth, 1, $intYear));				
			}
		}
	
		$Headers = "From: " . $_SESSION['chrFirstName'] . " " . $_SESSION['chrLastName'] . " <" . $_SESSION['chrEmail'] . ">\n\n";
		$Subject = $attempt . " - COE ". strftime('%B %Y', mktime(0, 0, 0, $intMonth, 1, $intYear)) . ", " . $store['chrName'] . " to Approve."; 

		$Message = "Requesting review for the ". $store['chrName'] . " store's Calendar of Events.
		
Check this store with the following link:

http://retailmarketing.apple.com/quickin.php?d=". base64_encode("idStore=" . $_REQUEST['idStore'] . "&intDate=" . $_REQUEST['intDate']);
	
		//mail('retailevents@apple.com', $Subject, $Message, $Headers);
				
		$result = do_mysql_query("REPLACE INTO StoreMonths SET idStore='" . $_REQUEST['idStore'] . "', intYear='" . $intYear . "', intMonth='" . $intMonth . "'", 'insert storemonth');

		
		if ($result) {
			header('Location: ./?idStore=' . $_REQUEST['idStore'] . '&intDate=' . $_REQUEST['intDate']);
			exit();

		
		} else {
			$error_messages[] = 'There was a database error.';
		}

	}
	
	
	
	// Set the title, and add the doc_top
	$title = "Submit COE";
	require(BASE_FOLDER . 'docpages/doc_meta_events.php');
	include(BASE_FOLDER . 'docpages/doc_top_events.php');
?>

	<div style='margin: 10px;'>
				<div class="AdminTopicHeader">Submit Calendar</div>
				
		<div class='Question'>
			<p>If you are done making changes to this month's calendar, and you want to send it to the Retail Workshops/Events team for approval, click the button below.</p>
			<p>You will not be able to make further changes to this calendar once it has been submitted for approval.</p>
			</div>
		<div class='FormButtons'>
			<input type='button' onclick='location.href="?idStore=<?=$_REQUEST['idStore']?>&amp;intDate=<?=$_REQUEST['intDate']?>&amp;Confirmed=1";' value='Submit for Approval'/>
			<input type='button' onclick='history.back();' value='Cancel' />
			</div>

	</div>
<?
	include('../docpages/doc_bottom.php');
?>