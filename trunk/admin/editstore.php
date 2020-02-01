<?php
	$BF = '../';
	$title = 'Edit Store';
	require($BF. '_lib2.php');
	// Checking request variables
	($_REQUEST['id'] == "" || !is_numeric($_REQUEST['id']) ? ErrorPage() : "" );
	include($BF. 'includes/meta2.php');

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

	/* Weeknames for the date checks */
	include($BF. 'includes/week_names.php');
		
	$info = fetch_database_query("SELECT * FROM Stores WHERE ID=". $_REQUEST['id'],"Getting Store Info");
		
	// if this is a form submission
	if(count($_POST)) {

				$table = 'Stores';
				$mysqlStr = '';
				$audit = '';

				// "List" is a way for php to split up an array that is coming back.  
				// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
				//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
				//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
				//    ...  This also will ONLY add changes to the audit table if the values are different.
				list($mysqlStr,$audit) = set_strs($mysqlStr,'chrName',$info['chrName'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'idRegion',$info['idRegion'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress1',$info['chrAddress1'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress2',$info['chrAddress2'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'chrAddress3',$info['chrAddress3'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCity',$info['chrCity'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'chrPostalCode',$info['chrPostalCode'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'chrState',$info['chrState'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCountry',$info['chrCountry'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'chrEmail',$info['chrEmail'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'chrPhone',$info['chrPhone'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'chrFax',$info['chrFax'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'idStoreSize',$info['idStoreSize'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'idLocalization',$info['idLocalization'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs_checkbox($mysqlStr,'bDedicatedTheater',$info['bDedicatedTheater'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs_checkbox($mysqlStr,'bDedicatedStudio',$info['bDedicatedStudio'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs_checkbox($mysqlStr,'bDedicatedClassroom',$info['bDedicatedClassroom'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs_checkbox($mysqlStr,'bDedicatediPod',$info['bDedicatediPod'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs_checkbox($mysqlStr,'bIgnore',$info['bIgnore'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs_checkbox($mysqlStr,'bIgnoreRecaps',$info['bIgnore'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'intTheaterCap',$info['intTheaterCap'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'intTheaterMaxCap',$info['intTheaterMaxCap'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'intClassCap',$info['intClassCap'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'intiPodCap',$info['intiPodCap'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'intStudioCap',$info['intStudioCap'],$audit,$table,$_POST['id']);
				list($mysqlStr,$audit) = set_strs($mysqlStr,'idLocationType',$info['idLocationType'],$audit,$table,$_POST['id']);

				// Setting up the times into values for it to be put into the DB
				foreach($_POST['chkOpen'] as $val)	{ 
					$bHour = ($_POST['tBeginMeridian'.$val] == 'PM' ? $_POST['tBeginHour'.$val]+12 : $_POST['tBeginHour'.$val]);
					$eHour = ($_POST['tEndMeridian'.$val] == 'PM' ? $_POST['tEndHour'.$val]+12 : $_POST['tEndHour'.$val]); 

					$_POST['tBegin'. $val] = $bHour.':'.$_POST['tBeginMinute'.$val].":00";
					$_POST['tEnd'. $val] = $eHour .':'. $_POST['tEndMinute'.$val]. ":00";
					if (!isset($_POST['bOpen'. $val]) || $_POST['bOpen'. $val] == "") { $_POST['bOpen'. $val] = 0; }
					
					list($mysqlStr,$audit) = set_strs($mysqlStr,'tBegin'. $val,$info['tBegin'. $val],$audit,$table,$_POST['id']);
					list($mysqlStr,$audit) = set_strs($mysqlStr,'tEnd'. $val,$info['tEnd'. $val],$audit,$table,$_POST['id']);
					list($mysqlStr,$audit) = set_strs($mysqlStr,'bOpen'. $val,$info['bOpen'. $val],$audit,$table,$_POST['id']);

				}

				// if nothing has changed, don't do anything.  Otherwise update / audit.
				if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }

				// When the page is done updating, move them back to whatever the list page is for the section you are in.
				header("Location: index.php");
				die();
	}

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrName"; }


	/* Includes to get the countries and states */
	include($BF. 'includes/states.php');
	include($BF. 'includes/countries.php');

	
	$regions = database_query("SELECT ID, chrName FROM Regions WHERE !bDeleted ORDER BY chrName","getting regions");
	$storeSize = database_query("SELECT ID, chrStoreSize FROM StoreSize ORDER BY chrStoreSize","getting store size");
	$locationType = database_query("SELECT ID, chrLocationType FROM LocationType ORDER BY chrLocationType","getting location type");

	$lvls = database_query("SELECT * FROM Levels WHERE ID>2","getting levels");
	$levels = "<select name='enPermission' id='enPermission'><option value=''>-Select Level-</option>";
	while($row = mysqli_fetch_assoc($lvls)) {
		if($row['chrName'] == 'Regional Director' && !$_SESSION['idType']) {
//			continue;
		}
		$levels .= "<option value='". $row['chrName'] ."'>". $row['chrName'] ."</option>";
	}
	$levels .= "</select>";



	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;
		total += ErrorCheck('idLocalization', "You must select an Localization.");
		total += ErrorCheck('chrName', "You must enter a Store Name.");
		total += ErrorCheck('chrEmail', "You must enter a Email Address.","email");
		total += ErrorCheck('idRegion', "You must choose a Region");
		total += ErrorCheck('chrAddress1', "You must enter an Address.");
		total += ErrorCheck('chrCity', "You must enter a City.");
		total += ErrorCheck('chrCountry', "You must choose a Country.");
		if (document.getElementById('chrCountry').value == 'US' || document.getElementById('chrCountry').value == 'CA') {
			total += ErrorCheck('chrState', "You must choose a State.");
		}		

		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>
<script type="text/javascript">//<![CDATA[
	function disableClass()
	{
		if(document.forms[0].bDedicatedClassroom.checked) { 
			document.forms[0].intClassCap.disabled = false; 
			document.getElementById('intClass').style.display = 'inline';
		}
		else{ 
			document.forms[0].intClassCap.disabled = true;
			document.getElementById('intClass').style.display = 'none';
		}
	}

	function disableTheater()	{
		if(document.forms[0].bDedicatedTheater.checked) {
			document.forms[0].intTheaterCap.disabled = false;
			document.forms[0].intTheaterMaxCap.disabled = false;
			document.getElementById('intThMC').style.display = 'inline';
			document.getElementById('intThSC').style.display = 'inline';
		}
		else {
			document.forms[0].intTheaterCap.disabled = true;
			document.forms[0].intTheaterMaxCap.disabled = true;
			document.getElementById('intThMC').style.display = 'none';
			document.getElementById('intThSC').style.display = 'none';
		}
	}

	function disableStudio() {
		if(document.forms[0].bDedicatedStudio.checked) {
			document.forms[0].intStudioCap.disabled = false;
			document.getElementById('intStudio').style.display = 'inline';
		}
		else {
			document.forms[0].intStudioCap.disabled = true;
			document.getElementById('intStudio').style.display = 'none';
		}
	}

	function disableiPod() 	{
		if(document.forms[0].bDedicatediPod.checked) {
			document.forms[0].intiPodCap.disabled = false;
			document.getElementById('intiPod').style.display = 'inline';
		}
		else {
			document.forms[0].intiPodCap.disabled = true;
			document.getElementById('intiPod').style.display = 'none';
		}
	}
//]]></script>
<?

	include($BF. 'includes/top_admin2.php');
?>


		<div class="AdminTopicHeader">Edit Stores</div>
		<div class="AdminInstructions2">Edit Store information here.</div>

		<form id='idForm' name='idForm' method='post' action=''>

		<div id='errors'></div>

		<table class='TwoColumns' style='margin-bottom: 20px;'>
			<tr>
				<td class="Left">

					<div class='sectionInfo'>
						<div class='sectionHeader'>Store</div>
						<div class='sectionContent'>

							<div class='form'>										
								<div class='formHeader'>Localization <span class='Required'>(Required)</span></div>
									<select name='idLocalization' id='idLocalization'>
										<option value=''>Please choose an Localization</option>
		<?	
			$localization = database_query("SELECT ID, chrLocalization FROM Localization WHERE !bDeleted AND ID IN (".$_SESSION['chrLoc'].") ORDER BY ID","getting Event Categories");
			while($row = mysqli_fetch_assoc($localization)) { 
		?>								
										<option <?=($row['ID'] == $info['idLocalization'] ? ' selected' : '')?> value='<?=$row['ID']?>'><?=$row['chrLocalization']?></option>
		<?
			}
		?>
									</select>
							</div> 

						<div class='form'>
							<div class='formHeader'>Name <span class='Required'>(Required)</span></div>
							<input type='text' size='40' maxlength='80' name='chrName' id='chrName' value='<?=$info['chrName']?>' />
							</div>
						
						
						<div class='form'>
							<div class='formHeader'>Email Address <span class='Required'>(Required)</span></div>
							<input type='text' size='40' maxlength='80' name='chrEmail' id='chrEmail' value='<?=$info['chrEmail']?>' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>Region <span class='Required'>(Required)</span></div>
							<select name='idRegion' id='idRegion'>
								<option></option>
<?	while($row = mysqli_fetch_assoc($regions)) { ?>
								<option<?=($row['ID'] == $info['idRegion'] ? ' selected' : '')?> value='<?=$row['ID']?>'><?=$row['chrName']?></option>
<?	} ?>
								</select>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Address <span class='Required'>(Required)</span></div>
							<div><input type='text' size='40' maxlength='80' name='chrAddress1' id='chrAddress1' value='<?=$info['chrAddress1']?>' /></div>
							<div><input type='text' size='40' maxlength='80' name='chrAddress2' id='chrAddress2' value='<?=$info['chrAddress2']?>' /></div>
							<div><input type='text' size='40' maxlength='80' name='chrAddress3' id='chrAddress3' value='<?=$info['chrAddress3']?>' /></div>
							</div>
				
						<div class='form'>
							<div class='formHeader'>City <span class='Required'>(Required)</span></div>
							<input type='text' size='30' maxlength='40' name='chrCity' id='chrCity' value='<?=$info['chrCity']?>' />
							</div>
				
						<div class='form'>
							<div class='formHeader'>State/Province <span class='Required'>(US &amp; Canada)</span></div>
							<select name='chrState' id='chrState'>
<?	foreach($states as $st => $name) { ?>
								<option<?=($st == $info['chrState'] ? ' selected' : '')?> value='<?=@$st?>'><?=$name?></option>
<?	} ?>
								</select>
							</div>
				
						<div class='form'>
							<div class='formHeader'>Postal Code</div>
							<input type='text' size='10' maxlength='10' name='chrPostalCode' id='chrPostalCode' value='<?=$info['chrPostalCode']?>' />
							</div>
				
						<div class='form'>
							<div class='formHeader'>Country <span class='Required'>(Required)</span></div>
							<select name='chrCountry' id='chrCountry'>
<?	foreach($countries as $cc => $name) { ?>
								<option<?=($cc == $info['chrCountry'] ? ' selected' : '')?> value='<?=@$cc?>'><?=$name?></option>
<?	} ?>
								</select>
							</div>
				
						<div class='form'>
							<div class='formHeader'>Phone</div>
							<input type='text' size='30' maxlength='22' name='chrPhone' id='chrPhone' value='<?=$info['chrPhone']?>' />
							</div>
				
						<div class='form'>
							<div class='formHeader'>Fax</div>
							<input type='text' size='30' maxlength='22' name='chrFax' id='chrFax' value='<?=$info['chrFax']?>' />
						</div>
						
						<div class='form'>
							<div class='formHeader'><input<?=($info['bIgnore'] == 1 ? ' checked' : '')?> type='checkbox' name='bIgnore' id='bIgnore' /> Ignore Monthly Requirements </div>
						</div>
						
						<div class='form'>
							<div class='formHeader'><input<?=($info['bIgnoreRecaps'] == 1 ? ' checked' : '')?> type='checkbox' name='bIgnoreRecaps' id='bIgnoreRecaps' /> Ignore Recap Requirements </div>
						</div>
	
					</div>
				</div>
			
			
			
				<div class='sectionInfo'>
					<div class='sectionHeader'>Location</div>
					<div class='sectionContent'>

	
						<div class='form'>
							<div class='formHeader'>Store Size</div>
							<select name='idStoreSize' id='idStoreSize'>
								<option value=''></option>
<?	while($row = mysqli_fetch_assoc($storeSize)) { ?>
								<option<?=($info['idStoreSize'] == $row['ID'] ? ' selected' : '')?> value='<?=$row['ID']?>'><?=$row['chrStoreSize']?></option>
<?	} ?>
								</select>
							</div>
	
						
						<div class='form'>
							<div class='formHeader'>Type</div>
							<select name='idLocationType' id='idLocationType'>
								<option value=''></option>
<?	while($row = mysqli_fetch_assoc($locationType)) { ?>
								<option<?=($info['idLocationType'] == $row['ID'] ? ' selected' : '')?>  value='<?=$row['ID']?>'><?=$row['chrLocationType']?></option>
<?	} ?>
								</select>
							</div>
		
						<div class='form'>
							<input<?=($info['bDedicatedStudio'] == 1 ? ' checked' : '')?> type='checkbox' name='bDedicatedStudio' id='bDedicatedStudio' onChange="disableStudio()" /><span class='formHeader'>Dedicated Studio</span>							
								<span id='intStudio' style='margin-left: 30px; display: none;'><input type='text' size='13' name='intStudioCap' id='intStudioCap' value='<?=$info['intStudioCap']?>' /><span class='Required'> (Seat Cap)</span></span>
							</div>
						<script type="text/javascript">//<![CDATA[
							if(document.forms[0].bDedicatedStudio.checked) { document.getElementById('intStudio').style.display = 'inline'; } 
						//]]></script> 
				
				
				
						<div class='form'>
							<input<?=($info['bDedicatediPod'] == 1 ? ' checked' : '')?> type='checkbox' name='bDedicatediPod' id='bDedicatediPod' onChange="disableiPod()" /> <span class='formHeader'>Dedicated iPod Bar</span><span id='intiPod' style='margin-left: 22px; display: none;'><input type='text' size='13' name='intiPodCap' id='intiPodCap' value='<?=$info['intiPodCap']?>' /><span class='Required'> (Seat Cap)</span></span>							
						</div>
						<script type="text/javascript">//<![CDATA[
							if(document.forms[0].bDedicatediPod.checked) { document.getElementById('intiPod').style.display = 'inline'; } 
						//]]></script> 
				
				
					
						<div class='form'>
							<input<?=($info['bDedicatedClassroom'] == 1 ? ' checked' : '')?> type='checkbox' name='bDedicatedClassroom' id='bDedicatedClassroom' onChange="disableClass()" /><span class='formHeader'>Dedicated Classroom</span>
								<span id='intClass' style='margin-left: 10px; display: none;'><input type='text' size='13' name='intClassCap' id='intClassCap' value='<?=$info['intClassCap']?>' /><span class='Required'> (Seat Cap)</span></span>
							</div>
						<script type="text/javascript">//<![CDATA[
							if(document.forms[0].bDedicatedClassroom.checked) { document.getElementById('intClass').style.display = 'inline'; } 
						//]]></script> 
						
						<div class='form'>
							<input<?=($info['bDedicatedTheater'] == 1 ? ' checked' : '')?> type='checkbox' name='bDedicatedTheater' id='bDedicatedTheater' onChange="disableTheater()" /><span class='formHeader'>Dedicated Theater</span>
							
							<div class='' style='padding-left: 2em;'>
								<span id='intThSC' style='margin-left: 10px; display: none;'><input type='text' size='13' name='intTheaterCap' id='intTheaterCap' value='<?=$info['intTheaterCap']?>' /><span class='Required'> (Seat Cap)</span></span>
							</div>
							<div class='' style='padding-left: 2em; margin-top: 3px;'>
								<span id='intThMC' style='margin-left: 10px; display: none;'><input type='text' size='13' name='intTheaterMaxCap' id='intTheaterMaxCap' value='<?=$info['intTheaterMaxCap']?>' /><span class='Required'> (Max Cap)</span></span>
							</div>
						</div>
						<script type="text/javascript">//<![CDATA[
							if(document.forms[0].bDedicatedTheater.checked) 
							{ 
								document.getElementById('intThMC').style.display = 'inline';
								document.getElementById('intThSC').style.display = 'inline';
							} 
							//]]></script>
			
			
						</div>
					</div>
			
					</td>
				<td class='Gutter'></td>
				<td class="Right">				
			
					<div class='sectionInfo'>
						<div class='sectionHeader'>Store Hours</div>
						<div class='sectionContent'>
			
<?	foreach($weekday_long_names as $key => $val) {
	$short = $weekday_names[$key];
	$tBeginHour = $tBeginMin = $tBeginMer = $tEndHour = $tEndMin = $tEndMer = 0;
	if($info['tBegin'.$short] != "") { 
		list($tBeginHour,$tBeginMin) = split(':',$info['tBegin'. $short]);
		if($tBeginHour > 12) { 
			$tBeginHour -= 12;
			$tBeginMer = "PM";
		}
	}
	if($info['tEnd'.$short] != "") { 
		list($tEndHour,$tEndMin) = split(':',$info['tEnd'. $short]);
		if($tEndHour > 12) { 
			$tEndHour -= 12;
			$tEndMer = "PM";
		}
	}
?>
							<div class='form'>
								<div class='formHeader'><?=$val?></div>
								<div>
								<label><input<?=($info['bOpen'.$short] == 1 ? ' checked="checked"' : '')?> type='checkbox' name='bOpen<?=$short?>' value='1' onchange='openchanged(this)' /> Open: </label>
								<input type="hidden" name="chkOpen[]" value="<?=$short?>" />
								<select name='tBeginHour<?=$short?>'>
									<option></option>
<?		for($hour = 1; $hour<=12; $hour++) { ?>
									<option<?=($hour == $tBeginHour ? ' selected="selected"' : '')?>><?=$hour?></option>
<?		} ?>
								</select>
							:
								<select name='tBeginMinute<?=$short?>'>
									<option>00</option>
									<option<?=($tBeginMin == '30' ? ' selected="selected"' : '')?>>30</option>
								</select>

								<select name='tBeginMeridian<?=$short?>'>
									<option>AM</option>
									<option<?=($tBeginMer == 'PM' ? ' selected="selected"' : '')?>>PM</option>
								</select>

							</div>
							<div style="padding-left: 15px;">
							<label> Closed: </label>

							<select name='tEndHour<?=$short?>'>
								<option></option>
<?		for($hour = 1; $hour<=12; $hour++) { ?>
									<option<?=($hour == $tEndHour ? ' selected="selected"' : '')?>><?=$hour?></option>
<?		} ?>
							</select>
							:
							<select name='tEndMinute<?=$short?>'>
								<option>00</option>
								<option<?=($tEndMin == '30' ? ' selected' : '')?>>30</option>
							</select>
	
							<select name='tEndMeridian<?=$short?>'>
								<option>AM</option>
								<option<?=($tEndMer == 'PM' ? ' selected' : '')?>>PM</option>
							</select>

							</div>
						</div>
<? 	}	?>
						

				 				   	</div>
								</div>

						
						
						<div class='sectionInfo'>
							<div class='sectionHeader'>Users</div>
							<div class='sectionContent'>

<?	$results = database_query("SELECT ACL.ID,Users.chrFirstName,Users.chrLastName,ACL.enPermission FROM ACL JOIN Users ON Users.ID=ACL.idUser WHERE ACL.idItem=". $_REQUEST['id'],"Getting User Infomration"); ?>
									Add Person <input type='button' value='+' onclick='javascript:newwin = window.open("popup_adduser.php?tbl=people_assoc&idItem=<?=$_REQUEST['id']?>","new","width=500,height=400,resizable=1,scrollbars=1"); newwin.focus();' >

									<table id='people_assoc' class='List' cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<th>User Name</th>
											<th>Role</th>
											<th><img src="<?=$BF?>images/options.gif"></th>
										</tr>
<?
$count=0;
	while($row = mysqli_fetch_assoc($results)) { ?>	
										<tr id='people_assoctr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
											onmouseover='RowHighlight("people_assoctr<?=$row['ID']?>");' onmouseout='UnRowHighlight("people_assoctr<?=$row['ID']?>");'>
											<td><?=$row['chrFirstName']?> <?=$row['chrLastName']?></td>
											<td>
											
											<select name='enPermission' id='enPermission' onchange="javascipt:quickassoc('ajax_adduser.php?postType=quickInsert&ID=<?=$row['ID']?>&enPermission='+this.value)"><option value='0'>-Select Role-</option><option<?=($row['enPermission'] == 'Store Manager' ? ' selected' : '')?> value="Store Manager">Store Manager</option><option<?=($row['enPermission'] == 'Theater Coordinator' ? ' selected="selected"' : '')?> value="Theater Coordinator">Theater Coordinator</option></select>

											</td>
											<td class='options'><div class='deleteImage' onmouseover='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete_on.png"' onmouseout='document.getElementById("deleteButton<?=$row['ID']?>").src="<?=$BF?>images/button_delete.png"'>
											<a href="javascript:quickdel('<?=$BF?>ajax_delete.php?postType=permDelete&tbl=ACL&idUser=<?=$_SESSION['idUser']?>&id=<?=$row['ID']?>', <?=$row['ID']?>,'people_assoc');"><img id='deleteButton<?=$row['ID']?>' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a></td>
										</tr>
<?	} ?>
									</table>

							</div>
						</div>
						
						

				</td>
			</tr>
		</table>



		<div class='FormButtons' style='padding-top: 10px;'>
			<input type='button' name='SubmitAddStore' value='Update Store' onclick='error_check()' />
			<input type='hidden' name='id' value='<?=$_REQUEST["id"]?>' />
		</div>
		
		</form>

<?
	include($BF. 'includes/bottom2.php');
?>