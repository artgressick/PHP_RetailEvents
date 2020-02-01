<?php
	$BF = '../../';
	$title = 'Super Report';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	
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
	
	if (count($_POST)) {
	
		$_SESSION['dStart'] = $_POST['dStart'];
		$_SESSION['dEnd'] = $_POST['dEnd'];
		$_SESSION['tStart'] = $_POST['tStart'];
		$_SESSION['tEnd'] = $_POST['tEnd'];		
		$columns = "";
		$join = "";
		$where = "";
		$_SESSION['webcols'] = array();	
		$_SESSION['xlscols'] = array();		
		$columns = "Stores.chrName AS chrStoreName, Stores.chrCity AS chrStoreCity, Stores.chrState AS chrStoreState, Stores.chrCountry AS chrStoreCountry";
		$_SESSION['webcols'][] = "chrStoreName";
		$_SESSION['xlscols'][] = "chrStoreName";
		$_SESSION['xlscols'][] = "chrStoreCity";
		$_SESSION['xlscols'][] = "chrStoreState";
		$_SESSION['xlscols'][] = "chrStoreCountry";
		$_SESSION['xlscols'][] = "EventsName";
		$_SESSION['xlscols'][] = "chrETName";
		$_SESSION['xlscols'][] = "EventDescription";
		
	
			
		if ($_POST['region'] != "") {
			$where .= " AND Stores.idRegion IN (". implode(",", $_POST['region']).") ";
		}
		
		if ($_POST['store'] != "") {
			$where .= " AND Stores.ID IN (". implode(",", $_POST['store']).") ";
		}	
		
		if ($_POST['storesize'] != "") {
			$columns .= ", StoreSize.chrStoreSize";
			$_SESSION['xlscols'][] = "chrStoreSize";
			$join .= " LEFT JOIN StoreSize on Stores.idStoreSize = StoreSize.ID ";
			$where .= " AND Stores.idStoreSize IN (". implode(",", $_POST['storesize']).") ";
		}	
		
		$catselect = "";
		
		if ($_POST['workshops'] == 1) {
			if ($_POST['eventtype'] != "") {
				if($catselect == "") {
					$catselect .= " AND ((EventTypes.idEventCategory=1 AND EventTypes.bEditorReview!=1 AND EventTypeNames.ID IN (". implode(",", $_POST['eventtype']).")) ";
				} else {
					$catselect .= " OR (EventTypes.idEventCategory=1 AND EventTypes.bEditorReview!=1 AND EventTypeNames.ID IN (". implode(",", $_POST['eventtype']).")) ";
				}
			} else {
				if($catselect == "") {
					$catselect .= " AND ((EventTypes.idEventCategory=1 AND EventTypes.bEditorReview!=1) ";
				} else {
					$catselect .= " OR (EventTypes.idEventCategory=1 AND EventTypes.bEditorReview!=1) ";
				}
			}
		}

				($_POST['EventsName'] == 1 ? $_SESSION['webcols'][] = "EventsName" : "" );
				($_POST['EventsType'] == 1 ? $_SESSION['webcols'][] = "chrETName" : "" );

		if ($_POST['events'] == 1) {
			if($catselect == "") {
				$catselect .= " AND ((EventTypes.idEventCategory=2 AND EventTypes.bEditorReview=1) ";
			} else {
				$catselect .= " OR (EventTypes.idEventCategory=2 AND EventTypes.bEditorReview=1) ";
			}
			
		}

		if ($_POST['customworkshops'] == 1) {

				$_SESSION['xlscols'][] = "chrEventTitle";
				$_SESSION['xlscols'][] = "chrDescription";
				$_SESSION['xlscols'][] = "chrEventType";
			if($catselect == "") {
				$catselect .= " AND ((EventTypes.idEventCategory=1 AND EventTypes.bEditorReview=1) ";
			} else {
				$catselect .= " OR (EventTypes.idEventCategory=1 AND EventTypes.bEditorReview=1) ";
			}
		}
		
		if($catselect != "") { $where .= $catselect.") "; }
		
		if ($_POST['eventtypea'] != "") {
				$where .= " AND EventTypes.ID IN (". implode(",", $_POST['eventtypea']).") ";
		}
		
		if ($_POST['localization'] != "") {
				$where .= " AND (Localization.ID IN (". implode(",", $_POST['localization']).") OR Stores.idLocalization IN (". implode(",", $_POST['localization']).")) ";
		}
	
		if ($_POST['presenters'] != "") {
				$columns .= ", Presenters.chrName AS chrPresentersName";
				$_SESSION['xlscols'][] = "chrPresentersName";
				$join .= " LEFT JOIN EventPresenters AS EPresenters on Events.intSeries = EPresenters.intEventSeries LEFT JOIN Presenters on EPresenters.idPresenter = Presenters.ID ";
				$where .= " ". ($_POST['reqpresenters'] == 1 ? "AND" : "OR" ) ." Presenters.ID IN (". implode(",", $_POST['presenters']).") ";
		}
		
		if ($_POST['products'] != "") {
				$columns .= ", Products.chrName AS chrProductsName";
				$_SESSION['xlscols'][] = "chrProductsName";
				$join .= " LEFT JOIN EventProducts AS EProducts on Events.intSeries = EProducts.intEventSeries LEFT JOIN Products on EProducts.idProduct = Products.ID ";
				$where .= " ". ($_POST['reqproducts'] == 1 ? "AND" : "OR" ) ." Products.ID IN (". implode(",", $_POST['products']).") ";
		}
		
		if ($_POST['EventDT'] == 1 ) {
				$_SESSION['webcols'][] = "dtEvent";
				$_SESSION['xlscols'][] = "dtEvent";
		}
		
		if ($_POST['RecapsAttendance'] == 1 ) {
				$columns .= ", Recaps.chrAttendance as chrRecapAttendance";
				$_SESSION['webcols'][] = "chrRecapAttendance";
				$_SESSION['xlscols'][] = "chrRecapAttendance";
		}	
		
		if ($_POST['RecapsSales'] == 1 ) {
				$columns .= ", Recaps.chrSales as chrRecapSales";
				$_SESSION['webcols'][] = "chrRecapSales";
				$_SESSION['xlscols'][] = "chrRecapSales";
		}	
		
		if ($_POST['RecapsSuccess'] == 1 ) {
				$columns .= ", Recaps.rSuccess as rRecapSuccess";
				$_SESSION['webcols'][] = "rRecapSuccess";
				$_SESSION['xlscols'][] = "rRecapSuccess";
		}	
	
		if ($_POST['RecapsAddstaff'] == 1 ) {
				$columns .= ", Recaps.chrAddstaff as rRecapAddStaff";
				$_SESSION['webcols'][] = "rRecapAddStaff";
				$_SESSION['xlscols'][] = "rRecapAddStaff";
		}						
		
		if ($_POST['RecapsApple'] == 1 ) {
				$columns .= ", Recaps.chrApple as rRecapPresended";
				$_SESSION['webcols'][] = "rRecapPresended";
				$_SESSION['xlscols'][] = "rRecapPresended";
		}			
			
		$_SESSION['where'] = $where;
	
		$_SESSION['intLimit'] = $_POST['limitresults'];

		if ( $_POST['bCount'] == 1 ) { $_SESSION['intLimit'] = 1; }		
		
		$_SESSION['SREPORT'] = "SELECT Events.ID as ID,". $columns .", Events.chrEventName, EventTypeNames.chrEventTitle, EventTypes.chrName AS chrETName, chrLocalization, Events.chrTitle, Events.chrDescription,  EventTypeNames.txtEventDescription, DATE_FORMAT(concat(Events.dDate,' ',Events.tBegin),'%M %D, %Y %l:%i %p') as dtEvent
			  FROM Events
			  LEFT JOIN Stores on Stores.ID = Events.idStore
			  LEFT JOIN Recaps on Recaps.idEvent = Events.ID
			  LEFT JOIN EventTypes on Events.idEventType = EventTypes.ID
			  LEFT JOIN Localization ON EventTypes.idLocalization=Localization.ID
			  LEFT JOIN EventTypeNames on Events.idEventTitle = EventTypeNames.ID			  

			  ". $join ." 
			  WHERE !Stores.bDeleted ";
			
			header("Location: superreport2.php");
			die();
	}
	

	$Begin_Date = date('m/d/Y',strtotime('-1 month'));
	$End_Date = date('m/d/Y',strtotime('+1 month'));
	$Begin_Time = "7:00 am";
	$End_Time = "9:00 pm";
	
		$q = "SELECT ID, chrName FROM Regions where !bDeleted ORDER BY ID";
		
		$regions = database_query($q,"Getting Regions");				
		
		$q = "SELECT ID, chrName, idStoreSize FROM Stores where !bDeleted AND ID NOT IN (140,166,167,168) ORDER BY chrName";
		
		$stores = database_query($q,"Getting Stores");				
	
		$q = "SELECT * FROM StoreSize";
		
		$storesizes = database_query($q,"Getting Store Sizes");	
		
		$q = "SELECT ID, chrEventTitle FROM EventTypeNames WHERE !bDeleted ORDER BY chrEventTitle";
		
		$eventtypenames = database_query($q,"Getting Event Title Names");
		
		$q = "SELECT ID, chrLocalization FROM Localization WHERE !bDeleted ORDER BY ID";
		
		$localization = database_query($q,"Getting Localizations");

		$q = "SELECT EventTypes.ID, chrName, chrLocalization FROM EventTypes JOIN Localization ON EventTypes.idLocalization=Localization.ID WHERE !EventTypes.bDeleted ORDER BY chrName";
		
		$eventtypes = database_query($q,"Getting Event Types");
		
		$q = "SELECT ID, chrName FROM Presenters WHERE !bDeleted ORDER BY chrName";
		
		$presenters = database_query($q,"Getting Presenters");
		
		$q = "SELECT ID, chrName FROM Products WHERE !bDeleted ORDER BY chrName";
		
		$products = database_query($q,"Getting Products");
	
	

	$result = database_query($q,"Getting Report Information");

?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	include($BF. 'includes/top_admin2.php');
?>
		<form  id='idForm' name='idForm' method='post' action='' enctype="multipart/form-data">
					<div class="AdminTopicHeader">Super Report</div>
					<div class="AdminInstructions2">The biggest report you will ever see.</i></div>
					
					<!-- Tool Bar with the Add Store and Search button -->
					<table class="AdminUtilityBar2">
						<tr>
							<td valign="center">
								Date Between <input type="text" name="dStart" id="dStart" maxlength="20" size="10" value="<?=$Begin_Date?>" /> and <input type="text" name="dEnd" id="dEnd" maxlength="20" size="10" value="<?=$End_Date?>" /> and Start Times between: <input type="text" name="tStart" id="tStart" maxlength="10" size="10" value="<?=$Begin_Time?>" /> and <input type="text" name="tEnd" id="tEnd" maxlength="10" size="10" value="<?=$End_Time?>" /> <input type="submit" id="submit" name="submit" value="Run Report" /> <input type="button" value="Reset All" onclick="location.href='superreport.php'" />	
							</td>			
						</tr>
					</table>
		<?	$num = mysqli_num_rows($regions);	?>					
		<table class='showcolumns' width='100%' cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td style="text-align:left">
				<div onclick='quickHideG("Show Regions", "Hide Regions", "1a", "<?=$BF?>");' id='qhTitle1a' ><img src='<?=$BF?>images/arrow_right.png' alt='closed squig' style='padding-top: 1px;'/> Show Regions</div>
				</td>
				<td style="text-align:right">
				(<?=$num?> Regions)
				</td>
			</tr>
		</table>
		<div id='qhBody1a' class='showcolumnsinner' style='display: none; border-top: none;'>
	
			<table cellspacing="0" cellpadding="0" style="width:100%;" >
				<tr>
					<td>
						<table class="selection">
							<tr>
								<td style="vertical-align:top">
							
							<?
							$count=0;	
							$perRow = ceil($num / 5);
							while ($row = mysqli_fetch_assoc($regions)) { 
								if ( $count++ == $perRow ) {
									$count=1;
								?>
							</td>
							<td style="vertical-align:top">
								<? } ?>
								<div id='regiondiv<?=$row['ID']?>' onmouseover='RowHighlight("regiondiv<?=$row['ID']?>");' onmouseout='UnRowHighlight("regiondiv<?=$row['ID']?>");' style='cursor: pointer;'><input type="checkbox" id="region<?=$row['ID']?>" name="region[]" value="<?=$row['ID']?>" /> <span onclick='FieldClick("region",<?=$row['ID']?>)'><?=$row['chrName']?></span></div>
						<?	} ?>
							  </td>
							</tr>
						</table>						
					</td>
				</tr>
				<tr>
					<td>
						<input type='button' value='Select All' onclick='SelectALL("region")' /> <input type='button' value='UnSelect All' onclick='UnSelectALL("region")' />
					</td>
				</tr>
			</table>
		</div>
		<br />
	<?	$num = mysqli_num_rows($stores);	?>
		<table class='showcolumns' width='100%' cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td style="text-align:left">	
		<div onclick='quickHideG("Show Stores", "Hide Stores", "2a", "<?=$BF?>");' id='qhTitle2a' ><img src='<?=$BF?>images/arrow_right.png' alt='closed squig' style='padding-top: 1px;'/> Show Stores</div>
				</td>
				<td style="text-align:right">
				(<?=$num?> Stores)
				</td>
			</tr>
		</table>
		<div id='qhBody2a' class='showcolumnsinner' style='display: none; border-top: none;'>
			<table cellspacing="0" cellpadding="0" style="width:100%;" >
				<tr>
					<td>
						<table class="selection">
							<tr>
								<td style="vertical-align:top">
							
							<?
							$count=0;	
							$perRow = ceil($num / 5);
							while ($row = mysqli_fetch_assoc($stores)) { 
								if ( $count++ == $perRow ) {
									$count=1;
								?>
							</td>
							<td style="vertical-align:top">
								<? } ?>
								<div id='storediv<?=$row['ID']?>' onmouseover='RowHighlight("storediv<?=$row['ID']?>");' onmouseout='UnRowHighlight("storediv<?=$row['ID']?>");' style='cursor: pointer;'><input type="checkbox" id="store<?=$row['ID']?>" name="store[]" value="<?=$row['ID']?>" /> <span onclick='FieldClick("store",<?=$row['ID']?>)'><?=$row['chrName']?></span></div>
						<?	} ?>
							  </td>
							</tr>
						</table>		
					</td>
				</tr>
				<tr>
					<td>
						<input type='button' value='Select All' onclick='SelectALL("store")' /> <input type='button' value='UnSelect All' onclick='UnSelectALL("store")' />
					</td>
				</tr>
			</table>
		</div>
		<br />
		<?	$num = mysqli_num_rows($storesizes);	?>
		<table class='showcolumns' width='100%' cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td style="text-align:left">				
		<div onclick='quickHideG("Show Store Sizes", "Hide Store Sizes", "3a", "<?=$BF?>");' id='qhTitle3a' ><img src='<?=$BF?>images/arrow_right.png' alt='closed squig' style='padding-top: 1px;'/> Show Store Sizes</div>
				</td>
				<td style="text-align:right">
				(<?=$num?> Store Sizes)
				</td>
			</tr>
		</table>
		<div id='qhBody3a' class='showcolumnsinner' style='display: none; border-top: none;'>
			<table cellspacing="0" cellpadding="0" style="width:100%;" >
				<tr>
					<td>
						<table class="selection">
							<tr>
								<td style="vertical-align:top">
								<?
								$count=0;	
								$perRow = ceil($num / 4);
								while ($row = mysqli_fetch_assoc($storesizes)) { 
									if ( $count++ == $perRow ) {
										$count=1;
									?>
								</td>
								<td style="vertical-align:top">
									<? } ?>								
									<div id='storesizediv<?=$row['ID']?>' onmouseover='RowHighlight("storesizediv<?=$row['ID']?>");' onmouseout='UnRowHighlight("storesizediv<?=$row['ID']?>");'
									style='cursor: pointer;'><input type="checkbox" id="storesize<?=$row['ID']?>" name="storesize[]" value="<?=$row['ID']?>" /> <span onclick='FieldClick("storesize",<?=$row['ID']?>)'><?=$row['chrStoreSize']?>
									</span>
									</div>
								</td>
								<?	} ?>
							</tr>
						</table>		
					</td>
				</tr>
				<tr>
					<td>
						<input type='button' value='Select All' onclick='SelectALL("storesize")' /> <input type='button' value='UnSelect All' onclick='UnSelectALL("storesize")' />
					</td>
				</tr>
			</table>
		</div>
		
		
		<br />
		<div id="surround11a">
		<?	$num = mysqli_num_rows($localization);	?>	
		<table class='showcolumns' width='100%' cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td style="text-align:left">
					<div onclick='quickHideG("Show Localizations", "Hide Localizations", "11a", "<?=$BF?>");' id='qhTitle11a'><img src='<?=$BF?>images/arrow_right.png' alt='closed squig' style='padding-top: 1px;' /> Show Localizations</div>
				</td>
				<td style="text-align:right">
				(<?=$num?> Localizations)
				</td>
			</tr>
		</table>		
		<div id='qhBody11a' class='showcolumnsinner' style='display: none; border-top: none;'>
	
			<table cellspacing="0" cellpadding="0" style="width:100%;" >
				<tr>
					<td>
						<table class="selection">
							<tr>
								<td style="vertical-align:top">
							
							<?
							$count=0;
							$num = mysqli_num_rows($localization);	
							$perRow = ceil($num / 2);
							while ($row = mysqli_fetch_assoc($localization)) { 
								if ( $count++ == $perRow ) {
									$count=1;
								?>
							</td>
							<td style="vertical-align:top">
								<? } ?>
								<div id='localizationdiv<?=$row['ID']?>' onmouseover='RowHighlight("localizationdiv<?=$row['ID']?>");' onmouseout='UnRowHighlight("localizationdiv<?=$row['ID']?>");'
								 style='cursor: pointer;'><input type="checkbox" id="localization<?=$row['ID']?>" name="localization[]" value="<?=$row['ID']?>" /> <span onclick='FieldClick("localization",<?=$row['ID']?>)'><?=$row['chrLocalization']?>
								 </span>
							  </div>
						<?	} ?>
							  </td>
							</tr>
						</table>		
					</td>
				</tr>
				<tr>
					<td>
						<input type='button' value='Select All' onclick='SelectALL("localization")' /> <input type='button' value='UnSelect All' onclick='UnSelectALL("localization")' />
					</td>
				</tr>
			</table>
		</div>
		<br />	
		
				
		<div id="surround10a">
		<?	$num = mysqli_num_rows($eventtypes);	?>	
		<table class='showcolumns' width='100%' cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td style="text-align:left">
					<div onclick='quickHideG("Show Workshop/Event Types", "Hide Workshop/Event Types", "10a", "<?=$BF?>");' id='qhTitle10a'><img src='<?=$BF?>images/arrow_right.png' alt='closed squig' style='padding-top: 1px;' /> Show Workshop/Event Types</div>
				</td>
				<td style="text-align:right">
				(<?=$num?> Workshop Types)
				</td>
			</tr>
		</table>		
		<div id='qhBody10a' class='showcolumnsinner' style='display: none; border-top: none;'>
	
			<table cellspacing="0" cellpadding="0" style="width:100%;" >
				<tr>
					<td>
						<table class="selection">
							<tr>
								<td style="vertical-align:top">
							
							<?
							$count=0;
							$num = mysqli_num_rows($eventtypes);	
							$perRow = ceil($num / 3);
							while ($row = mysqli_fetch_assoc($eventtypes)) { 
								if ( $count++ == $perRow ) {
									$count=1;
								?>
							</td>
							<td style="vertical-align:top">
								<? } ?>
								<div id='eventtypesadiv<?=$row['ID']?>' onmouseover='RowHighlight("eventtypesadiv<?=$row['ID']?>");' onmouseout='UnRowHighlight("eventtypesadiv<?=$row['ID']?>");'
								 style='cursor: pointer;'><input type="checkbox" id="eventtypea<?=$row['ID']?>" name="eventtypea[]" value="<?=$row['ID']?>" /> <span onclick='FieldClick("eventtypesa",<?=$row['ID']?>)'><?=$row['chrName']?> (<?=$row['chrLocalization']?>)
								 </span>
							  </div>
						<?	} ?>
							  </td>
							</tr>
						</table>		
					</td>
				</tr>
				<tr>
					<td>
						<input type='button' value='Select All' onclick='SelectALL("eventtypea")' /> <input type='button' value='UnSelect All' onclick='UnSelectALL("eventtypea")' />
					</td>
				</tr>
			</table>
		</div>
		<br />			
		<div class='showcolumns' onclick='quickHideG("Show Category", "Hide Category", "4a", "<?=$BF?>");' id='qhTitle4a' ><img src='<?=$BF?>images/arrow_right.png' alt='closed squig' style='padding-top: 1px;'/> Show Category</div>
		<div id='qhBody4a' class='showcolumnsinner' style='display: none; border-top: none;'>
	
			<table cellspacing="0" cellpadding="0" style="width:100%;" >
				<tr>
					<td>
						<td style="width:33%;"><div id='workshopsdiv' onmouseover='RowHighlight("workshopsdiv");' onmouseout='UnRowHighlight("workshopsdiv");' style='cursor: pointer;'>
											  <input type="checkbox" id="workshops" name="workshops" value="1"  onclick='display("surround5a")'/> <span onclick='FieldClick("workshop","s"); display("surround5a")'>Workshops</span>
											  </div></td>
						
						<td style="width:34%;"><div id='customworkshopsdiv' onmouseover='RowHighlight("customworkshopsdiv");' onmouseout='UnRowHighlight("customworkshopsdiv");' style='cursor: pointer;'>
											  <input type="checkbox" id="customworkshops" name="customworkshops" value="1" /> <span onclick='FieldClick("customworkshop","s")'>Custom Workshops</span>
											  </div></td>	
						<td style="width:33%;"><div id='eventsdiv' onmouseover='RowHighlight("eventsdiv");' onmouseout='UnRowHighlight("eventsdiv");' style='cursor: pointer;'>
											  <input type="checkbox" id="events" name="events" value="1" /> <span onclick='FieldClick("event","s")'>Events</span>
											  </div></td>
					</td>
				</tr>
			</table>
		</div>
		<br />
		<div id="surround5a" style='display: none'>
		<?	$num = mysqli_num_rows($eventtypenames);	?>	
		<table class='showcolumns' width='100%' cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td style="text-align:left">
					<div onclick='quickHideG("Show Workshop Titles", "Hide Workshop Titles", "5a", "<?=$BF?>");' id='qhTitle5a'><img src='<?=$BF?>images/arrow_right.png' alt='closed squig' style='padding-top: 1px;' /> Show Workshop Titles</div>
				</td>
				<td style="text-align:right">
				(<?=$num?> Workshop Titles)
				</td>
			</tr>
		</table>		
		<div id='qhBody5a' class='showcolumnsinner' style='display: none; border-top: none;'>
	
			<table cellspacing="0" cellpadding="0" style="width:100%;" >
				<tr>
					<td>
						<table class="selection">
							<tr>
								<td style="vertical-align:top">
							
							<?
							$count=0;
							$num = mysqli_num_rows($eventtypenames);	
							$perRow = ceil($num / 3);
							while ($row = mysqli_fetch_assoc($eventtypenames)) { 
								if ( $count++ == $perRow ) {
									$count=1;
								?>
							</td>
							<td style="vertical-align:top">
								<? } ?>
								<div id='eventtypesdiv<?=$row['ID']?>' onmouseover='RowHighlight("eventtypesdiv<?=$row['ID']?>");' onmouseout='UnRowHighlight("eventtypesdiv<?=$row['ID']?>");'
								 style='cursor: pointer;'><input type="checkbox" id="eventtype<?=$row['ID']?>" name="eventtype[]" value="<?=$row['ID']?>" /> <span onclick='FieldClick("eventtypes",<?=$row['ID']?>)'><?=$row['chrEventTitle']?>
								 </span>
							  </div>
						<?	} ?>
							  </td>
							</tr>
						</table>		
					</td>
				</tr>
				<tr>
					<td>
						<input type='button' value='Select All' onclick='SelectALL("eventtype")' /> <input type='button' value='UnSelect All' onclick='UnSelectALL("eventtype")' />
					</td>
				</tr>
			</table>
		</div>
		<br />
		</div>
	<?	$num = mysqli_num_rows($presenters);	?>
		<table class='showcolumns' width='100%' cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td style="text-align:left">		
					<div onclick='quickHideG("Show Presenters", "Hide Presenters", "6a", "<?=$BF?>");' id='qhTitle6a' ><img src='<?=$BF?>images/arrow_right.png' alt='closed squig' style='padding-top: 1px;'/> Show Presenters </div>
				</td>
				<td style="text-align:right">
				(<?=$num?> Presenters)
				</td>
			</tr>
		</table>			
		<div id='qhBody6a' class='showcolumnsinner' style='display: none; border-top: none;'>
	
			<table cellspacing="0" cellpadding="0" style="width:100%;" >
				<tr>
					<td>
						<div><input type="checkbox" name="reqpresenters" value="1" /> Require Presenters Selected <i>(NOTE: If not checked, report will show options selected <strong>OR</strong> stores with selected presenters)</i></div>
						<table class="selection">
							<tr>
								<td style="vertical-align:top">
							
							<?
							$count=0;
							$num = mysqli_num_rows($presenters);	
							$perRow = ceil($num / 4);
							while ($row = mysqli_fetch_assoc($presenters)) { 
								if ( $count++ == $perRow ) {
									$count=1;
								?>
							</td>
							<td style="vertical-align:top">
								<? } ?>
								<div id='presentersdiv<?=$row['ID']?>' onmouseover='RowHighlight("presentersdiv<?=$row['ID']?>");' onmouseout='UnRowHighlight("presentersdiv<?=$row['ID']?>");'
								style='cursor: pointer;'><input type="checkbox" id="presenters<?=$row['ID']?>" name="presenters[]" value="<?=$row['ID']?>" /> <span onclick='FieldClick("presenters",<?=$row['ID']?>)'><?=$row['chrName']?> 
								</span>
								</div>
						<?	} ?>
							  </td>
							</tr>
						</table>		
					</td>
				</tr>
			</table>
		</div>
		<br />
	<?	$num = mysqli_num_rows($products);	?>	
		<table class='showcolumns' width='100%' cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td style="text-align:left">		
		<div onclick='quickHideG("Show Products", "Hide Products", "7a", "<?=$BF?>");' id='qhTitle7a' ><img src='<?=$BF?>images/arrow_right.png' alt='closed squig' style='padding-top: 1px;'/> Show Products</div>
				</td>
				<td style="text-align:right">
				(<?=$num?> Products)
				</td>
			</tr>
		</table>			
		<div id='qhBody7a' class='showcolumnsinner' style='display: none; border-top: none;'>
	 		<table cellspacing="0" cellpadding="0" style="width:100%;" >
				<tr>
					<td>
						<div><input type="checkbox" name="reqproducts" value="1" /> Require Products Selected <i>(NOTE: If not checked, report will show options selected <strong>OR</strong> stores with selected products)</i></div>
						<table class="selection">
							<tr>
								<td style="vertical-align:top">
							
							<?
							$count=0;
							$num = mysqli_num_rows($products);	
							$perRow = ceil($num / 5);
							while ($row = mysqli_fetch_assoc($products)) { 
								if ( $count++ == $perRow ) {
									$count=1;
								?>
							</td>
							<td style="vertical-align:top">
								<? } ?>
								<div id='productsdiv<?=$row['ID']?>' onmouseover='RowHighlight("productsdiv<?=$row['ID']?>");' onmouseout='UnRowHighlight("productsdiv<?=$row['ID']?>");'
								style='cursor: pointer;'><input type="checkbox" id="products<?=$row['ID']?>" name="products[]" value="<?=$row['ID']?>" /> <span onclick='FieldClick("products",<?=$row['ID']?>)'><?=$row['chrName']?></span></div>
						<?	} ?>
							  </td>
							</tr>
						</table>		
					</td>
				</tr>
			</table>
		</div>
		<br />	
		<div class='showcolumns' onclick='quickHideG("Show Column Options", "Hide Column Options", "8a", "<?=$BF?>");' id='qhTitle8a' ><img src='<?=$BF?>images/arrow_right.png' alt='closed squig' style='padding-top: 1px;'/> Show Column Options</div>
		<div id='qhBody8a' class='showcolumnsinner' style='display: none; border-top: none;'>
			<table cellspacing="0" cellpadding="0" style="width:100%;" >
				<tr>
					<th style="text-align:left; width:50%;"><strong>Events</strong></th>
					<th style="text-align:left; width:50%;"><strong>Recaps</strong></th>
				</tr>
				<tr>
					<td style="vertical-align:top;">
						<div id='EventsNamediv' onmouseover='RowHighlight("EventsNamediv");' onmouseout='UnRowHighlight("EventsNamediv");' style='cursor: pointer;'>
				 	 	<input type="checkbox" id="EventsName" name="EventsName" value="1" /> <span onclick='FieldClick("EventsNam","e")'>Event Name</span>
						</div>
						<div id='EventDTdiv' onmouseover='RowHighlight("EventDTdiv");' onmouseout='UnRowHighlight("EventDTdiv");' style='cursor: pointer;'>
				 	 	<input type="checkbox" id="EventDT" name="EventDT" value="1" /> <span onclick='FieldClick("EventDT","")'>Event Date/Time</span>
						</div>
						<div id='EventsTypediv' onmouseover='RowHighlight("EventsTypediv");' onmouseout='UnRowHighlight("EventsTypediv");' style='cursor: pointer;'>
				 	 	<input type="checkbox" id="EventsType" name="EventsType" value="1" /> <span onclick='FieldClick("EventsType","")'>Event Type</span>
						</div>
					<td style="vertical-align:top;">
						<div id='RecapsAttendancediv' onmouseover='RowHighlight("RecapsAttendancediv");' onmouseout='UnRowHighlight("RecapsAttendancediv");' style='cursor: pointer;'>
				 	 	<input type="checkbox" id="RecapsAttendance" name="RecapsAttendance" value="1" /> <span onclick='FieldClick("RecapsAttendance","")'>Recaps Attendance</span>
						</div>
						<div id='RecapsSalesdiv' onmouseover='RowHighlight("RecapsSalesdiv");' onmouseout='UnRowHighlight("RecapsSalesdiv");' style='cursor: pointer;'>
				 	 	<input type="checkbox" id="RecapsSales" name="RecapsSales" value="1" /> <span onclick='FieldClick("RecapsSales","")'>Recaps Daily Sales</span>
						</div>
						<div id='RecapsSuccessdiv' onmouseover='RowHighlight("RecapsSuccessdiv");' onmouseout='UnRowHighlight("RecapsSuccessdiv");' style='cursor: pointer;'>
				 	 	<input type="checkbox" id="RecapsSuccess" name="RecapsSuccess" value="1" /> <span onclick='FieldClick("RecapsSuccess","")'>Recaps Success</span>
						</div>
						<div id='RecapsAddstaffdiv' onmouseover='RowHighlight("RecapsAddstaffdiv");' onmouseout='UnRowHighlight("RecapsAddstaffdiv");' style='cursor: pointer;'>
				 	 	<input type="checkbox" id="RecapsAddstaff" name="RecapsAddstaff" value="1" /> <span onclick='FieldClick("RecapsAddstaff","")'>Recaps Additional Staff Needed</span>
						</div>
						<div id='RecapsApplediv' onmouseover='RowHighlight("RecapsApplediv");' onmouseout='UnRowHighlight("RecapsApplediv");' style='cursor: pointer;'>
				 	 	<input type="checkbox" id="RecapsApple" name="RecapsApple" value="1" /> <span onclick='FieldClick("RecapsApple","")'>Recaps How Presented</span>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<br />
		<div class='showcolumns' onclick='quickHideG("Show Report Options", "Hide Report Options", "9a", "<?=$BF?>");' id='qhTitle9a' ><img src='<?=$BF?>images/arrow_down.png' alt='open squig' style='padding-top: 1px;'/> Hide Report Options</div>
		<div id='qhBody9a' class='showcolumnsinner' style='display: block; border-top: none;'>
			<table cellspacing="0" cellpadding="0" style="width:100%;" >
				<tr>
					<th style="text-align:left; width:50%;"><strong>Results</strong></th>
					<th style="text-align:left; width:50%;"><strong></strong></th>
				</tr>
				<tr>
					<td style="vertical-align:top;">
						<div id='count1div' onmouseover='RowHighlight("count1div");' onmouseout='UnRowHighlight("count1div");' style='cursor: pointer;'>
				 	 	<input type="radio" id="bCount" name="bCount" value="1" /> <span onclick='FieldClick("count","1")'>Give Result Count Only</span>
						</div>
						<div id='count2div' onmouseover='RowHighlight("count2div");' onmouseout='UnRowHighlight("count2div");' style='cursor: pointer;'>
				 	 	<input type="radio" id="bCount" name="bCount" value="0" checked="checked" /> <span onclick='FieldClick("count","2")'>Show Count with Results</span>
						</div>
						 Limit Results to: <input type="text" name="limitresults" id="limitresults" maxlength="5" size="5" value="1000" /> <i>Max 99,999</i></td>
					<td style="vertical-align:top;">
					</td>
				</tr>
			</table>
		</div>
		<br />		
		<input type="submit" id="submit" name="submit" value="Run Report" /> <input type="button" value="Reset All" onclick="location.href='superreport.php'" />			
					
	</form>					
					
<?

	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>