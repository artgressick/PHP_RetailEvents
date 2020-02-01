<?php
	$BF = '../../';
	$title = 'Super Report';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');

	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root
		idType of 1 = Super user 
		idType of 2 = Editor
		idType of 3 = Corp Events
		idType of 4 = Store
		
		$pageaccess is array of user types that can access the page
	*/
	
	$pageaccess = array("1","2","3");
	if(!in_array($_SESSION['idType'],$pageaccess)) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: '. $BF ."nopermission.php"); die(); }
	// End Security Check
	
	if(isset($_POST['chrName']) && $_POST['chrName'] != "" && $_POST['submit'] == "Save") {
	   database_query("INSERT INTO CustomReports SET
		   idUser='". $_SESSION['idUser'] ."',
		   chrName='". $_POST['chrName'] ."',
		   txtWebColumns='". implode(',',$_SESSION['webcols']) ."',
		   txtExcelColumns='". implode(',',$_SESSION['xlscols']) ."',
		   txtQuery='". encode($_SESSION['SREPORT']) ."',
		   dDateStart='". $_SESSION['dStart'] ."',
		   dDateEnd='". $_SESSION['dEnd'] ."',
		   tTimeStart='". $_SESSION['tStart'] ."',
		   tTimeEnd='".$_SESSION['tEnd'] ."',
		   chrSortCol='". $_REQUEST['sortCol'] ."',
		   chrOrdCol='". $_REQUEST['ordCol'] ."',
		   intLimit='". $_SESSION['intLimit'] ."'","Saving Super Report");
	}
	
	if(isset($_REQUEST['id'])) {
		$info = fetch_database_query("SELECT * FROM CustomReports WHERE ID=". $_REQUEST['id'],"getting session info");
		$_SESSION['SREPORT'] = decode($info['txtQuery']);
		$_SESSION['webcols'] = explode(',',$info['txtWebColumns']);
		$_SESSION['xlscols'] = explode(',',$info['txtExcelColumns']);
		$_SESSION['dStart'] = $info['dDateStart'];
		$_SESSION['dEnd'] = $info['dDateEnd'];
		$_SESSION['tStart'] = $info['tTimeStart'];
		$_SESSION['tEnd'] = $info['tTimeEnd'];
		$_REQUEST['sortCol'] = $info['chrSortCol'];
		$_REQUEST['ordCol'] = $info['chrOrdCol'];		
		$_SESSION['intLimit'] = $info['intLimit'];
	}
	
	if (isset($_POST['intLimit'])) {
		$_SESSION['dStart'] = $_POST['dStart'];
		$_SESSION['dEnd'] = $_POST['dEnd'];
		$_SESSION['tStart'] = $_POST['tStart'];
		$_SESSION['tEnd'] = $_POST['tEnd'];
		$_SESSION['intLimit'] = $_POST['intLimit'];
	}
	$S_Report = $_SESSION['SREPORT'];				
	
	
	if($_SESSION['dStart'] != "") { $Begin_Date = date('Y-m-d',strtotime($_SESSION['dStart'])); } else if ( $_SESSION['dStart'] == "NOW" ) { $Begin_Date = date('Y-m-d',time()); }
	if($_SESSION['dEnd'] != "")   { $End_Date =   date('Y-m-d',strtotime($_SESSION['dEnd'])); } else if ( $_SESSION['dEnd'] == "NOW" ) { $End_Date = date('Y-m-d',time()); }
	if($_SESSION['tStart'] != "") { $Begin_Time = date('H:i:s',strtotime($_SESSION['tStart'])); } else if ( $_SESSION['tStart'] == "NOW" ) { $Begin_Time = date('H:i:s',time()); }
	if($_SESSION['tEnd'] != "") { $End_Time = date('H:i:s',strtotime($_SESSION['tEnd'])); } else if ( $_SESSION['tEnd'] == "NOW" ) { $End_Time = date('H:i:s',time()); }
	$datewhere = "";
	if($_SESSION['dStart'] != "") {
		$datewhere .= " AND Events.dDate >= '". $Begin_Date ."'";
	}
	 
	if($_SESSION['dEnd'] != "") {
		$datewhere .= " AND Events.dDate <= '". $End_Date ."'";
	}
	 
	if($_SESSION['tStart'] != "") { 
		$datewhere .= " AND Events.tBegin >= '". $Begin_Time ."'";
	} 
	 
	if($_SESSION['tEnd'] != "") { 
		$datewhere .= " AND Events.tBegin <= '". $End_Time ."'";	
	} 

		
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol']) || $_REQUEST['sortCol']=="") { $_REQUEST['sortCol'] = "chrStoreName, dtEvent"; $_REQUEST['ordCol'] = "DESC"; }
	
	$S_Report .= $datewhere ." ". $_SESSION['where'];
	$S_Report .= " ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	

	$report = database_query($S_Report,"Generating Report");	
	$total_results = mysqli_num_rows($report);
	$_SESSION['EXCEL'] = $S_Report;

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
include($BF. 'includes/top_admin2.php');
?>
		<div class="AdminTopicHeader">Super Report List</div>
		<div class="AdminInstructions">Please download the excel report to see ALL the fields for this report.</div>
		
		<!-- Tool Bar with the Add Store and Search button -->
	<form method='post' action='' enctype="multipart/form-data">		
		<table class="AdminUtilityBar">
			<tr>
	
				<td valign="center">
<?						if(isset($_REQUEST['id'])) { 
							echo $info['chrName'];
						} else { ?>
					Save Custom Report: <input type="text" name="chrName" id="chrName" size="40" value="<?=(isset($_POST['chrName']) ? $_POST['chrName'] : "")?>" /> <input type="submit" id="submit" name="submit" value="Save" />
<?						} ?>
				</td>
				<td align="right" valign="center"><a href="_superreport.php" />Export to Excel</a></td>				
			</tr>
		</table>
		<table class="AdminUtilityBar">
			<tr>
				<td valign="center">
					Date Between <input type="text" name="dStart" id="dStart" maxlength="20" size="10" value="<?=$_SESSION['dStart']?>" /> and 
					<input type="text" name="dEnd" id="dEnd" maxlength="20" size="10" value="<?=$_SESSION['dEnd']?>" /> 
					and Start Times between: <input type="text" name="tStart" id="tStart" maxlength="10" size="10" value="<?=$_SESSION['tStart']?>" /> and 
					<input type="text" name="tEnd" id="tEnd" maxlength="10" size="10" value="<?=$_SESSION['tEnd']?>" /> 	
				</td>			
			</tr>
		</table>
		<table class="AdminUtilityBar2">
			<tr>
				<td valign="center">
					Total Results Possible: <?=$total_results?>.  Limit Results to: <input type="text" name="intLimit" id="intLimit" maxlength="5" size="5" value="<?=$_SESSION['intLimit']?>" /> <i>Max 99,999</i> <input type="submit" id="submit" name="submit" value="Update" /> 
					<input type="button" value="Reset All" onclick="location.href='superreport.php'" />
				</td>			
			</tr>
		</table>
	</form>
					
		
					<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
						<tr>
						<? 	
							$chrStoreName = 0;
							$EventsName = 0;
							$chrETName = 0;
							$chrEventTitle = 0;
							$chrEventType = 0;
							$dtEvent = 0;
							$chrRecapAttendance = 0;
							$chrRecapSales = 0;
							$rRecapSuccess = 0;
							$rRecapAddStaff = 0;
							$rRecapPresended = 0;
										
							if (in_array("chrStoreName", $_SESSION['webcols'])) {
									$chrStoreName = 1;
									sortList('Store Name', 'chrStoreName');
							}
							if (in_array("EventsName", $_SESSION['webcols'])) {
									$EventsName = 1;
									?><th>Event Name</th><?		
							}
							if (in_array("chrETName", $_SESSION['webcols'])) {
									$chrETName = 1;
									sortList('Event Type', 'chrETName');		
							}
							if (in_array("dtEvent", $_SESSION['webcols'])) {
									$dtEvent = 1;
									sortList('Event Date/Time', 'dtEvent');
							}
							if (in_array("chrRecapAttendance", $_SESSION['webcols'])) {
									$chrRecapAttendance = 1;
									sortList('Attendance', 'chrRecapAttendance');
							}
							if (in_array("chrRecapSales", $_SESSION['webcols'])) {
									$chrRecapSales = 1;
									sortList('Additional Sales', 'chrRecapSales');
							}
							if (in_array("rRecapSuccess", $_SESSION['webcols'])) {
									$rRecapSuccess = 1;
									sortList('Success Rating', 'rRecapSuccess');
							}
							if (in_array("rRecapAddStaff", $_SESSION['webcols'])) {
									$rRecapAddStaff = 1;
									sortList('Staff Needed', 'rRecapAddStaff');
							}
							if (in_array("rRecapPresended", $_SESSION['webcols'])) {
									$rRecapPresended = 1;
									sortList('How Presented', 'rRecapPresended');
							}

						 ?>

						</tr>
<?

	$count=0;	
	while ($count < $_SESSION['intLimit'] && $row = mysqli_fetch_assoc($report)) { 
?> 
				<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 	onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>	<?
					if ($chrStoreName == 1) {
?>						<td style=''><?=$row['chrStoreName']?></td>		<?
					}
					if ($EventsName == 1) {
?>						<td style=''><?=($row['chrEventName'] != "" ? $row['chrEventName'] : ($row['chrTitle'] != "" ? $row['chrTitle'] : $row['chrEventTitle'] ))?></td>		<?
					}
					if ($chrETName == 1) {
?>						<td style=''><?=$row['chrETName']?> (<?=$row['chrLocalization']?>)</td>		<?
					}
					if ($dtEvent == 1) {
?>						<td style=''><?=$row['dtEvent']?></td>		<?
					}
					if ($chrRecapAttendance == 1) {
?>						<td style=''><?=$row['chrRecapAttendance']?></td>		<?
					}
					if ($chrRecapSales == 1) {
?>						<td style=''><?=$row['chrRecapSales']?></td>		<?
					}					
					if ($rRecapSuccess == 1) {
?>						<td style=''><?=$row['rRecapSuccess']?></td>		<?
					}
					if ($rRecapAddStaff == 1) {
?>						<td style=''><?=$row['rRecapAddStaff']?></td>		<?
					}
					if ($rRecapPresended == 1) {
?>						<td style=''><?=$row['rRecapPresended']?></td>		<?
					}											
?>						
				</tr>
<?	} ?>

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