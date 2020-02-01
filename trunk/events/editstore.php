<?php
	$BF = "../";
	require($BF. "_lib2.php");
	$curPage = "stores";
	$title = 'Edit Store';
	include($BF. 'includes/meta2.php');
			
	/* Includes to get the countries and states */
	include($BF . 'includes/states.php');
	include($BF . 'includes/countries.php');
	/* Weeknames for the date checks */
	include($BF . 'includes/week_names.php');
	
	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root */
	if($_SESSION['idType'] != 1 && !in_array($_REQUEST['id'],$_SESSION['intStoreList'])) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: ' . $BF . "nopermission.php"); die(); }
	
	$error_messages = array();

	if(!in_array($_REQUEST['id'],$_SESSION['intStoreList']) && $_SESSION['idType'] != 1) {
		die("You do not have permission to view this store.");
	}

	$info = fetch_database_query("SELECT * FROM Stores WHERE ID='" . $_REQUEST['id'] . "'","getting store info");
	$SM = fetch_database_query("SELECT ID, chrFirstName, chrLastName FROM Users WHERE ID='" . $info['idStoreManager'] . "'","getting store manager");
	$TC = fetch_database_query("SELECT ID, chrFirstName, chrLastName FROM Users WHERE ID='" . $info['idTheaterCoordinator'] . "'","getting theater coordinator");
	$info['chrStoreManager'] = $SM['chrFirstName'] . " " . $SM['chrLastName'];
	$info['chrTheaterCoordinator'] = $TC['chrFirstName'] . " " . $TC['chrLastName'];

	$acls_result = database_query("SELECT ACL.*, Users.chrEmail, Users.chrFirstName, Users.chrLastName, Users.ID
		FROM ACL
		JOIN Users ON ACL.idUser=Users.ID AND ACL.enType='Stores'
		WHERE idItem='" . $_REQUEST['id'] . "'
		ORDER BY chrLastName, chrFirstName
		", 'get acl');
	while($row = mysqli_fetch_assoc($acls_result)) {
		$t_ids[] = $row['ID'];
		$t_chrs[] = str_replace(',', '&#44;', $row['chrFirstName'] . ' ' . $row['chrLastName']);
		$info['enPermission' . $row['ID']] = $row['enPermission'];
		$info['Users'][$row['ID']] = $row;
	}
	$info['chrUsers'] = implode(',', $t_chrs);
	$info['idUsers'] = implode(',', $t_ids);

	// if this is a form submission
	if(count($_POST)) {
		// Check the fields for completeness/formatting
		//   If something is wrong, add an entry to the $error_messages array

		if ($_POST['chrName'] == '') { $error_messages[] = "You must enter a Store Name."; }
		if ($_POST['chrEmail'] == '') { $error_messages[] = "You must enter an Email Address."; }
		if ($_POST['idRegion'] == '') {	$error_messages[] = "You must choose the store's region."; }
		if($_POST['chrAddress1'] == '') { $error_messages[] = "You must enter the address."; }
		if($_POST['chrCity'] == '') { $error_messages[] = "You must enter the city/locality."; }
		if($_POST['chrCountry'] == '') { 
			$error_messages[] = "You must choose the country.";
		} else if($_POST['chrCountry'] == 'US') {
			if($_POST['chrState'] == '') { $error_messages[] = "You must choose the state."; }
			if($_POST['chrPostalCode'] == '') { $error_messages[] = "You must enter the ZIP Code."; }
		} else if($_POST['chrCountry'] == 'CA') {
			if($_POST['chrState'] == '') { $error_messages[] = "You must choose the province."; }
			if($_POST['chrPostalCode'] == '') { $error_messages[] = "You must enter the Postal Code."; }
		}
		
		// if everything is cool, create the new record
		// if everything is cool, create the new record
		if(count($error_messages) == 0) {
		
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
			list($mysqlStr,$audit) = set_strs($mysqlStr,'chrEmail',$info['chrEmail'],$audit,$table,$_POST['id']);
			
			list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCity',$info['chrCity'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'chrState',$info['chrState'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'chrPostalCode',$info['chrPostalCode'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'chrCountry',$info['chrCountry'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'chrPhone',$info['chrPhone'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'chrFax',$info['chrFax'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'idStoreSize',$info['idStoreSize'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'idStoreSize',$info['idStoreSize'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs_checkbox($mysqlStr,'bDedicatedTheater',$info['bDedicatedTheater'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs_checkbox($mysqlStr,'bDedicatedStudio',$info['bDedicatedStudio'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs_checkbox($mysqlStr,'bDedicatedClassroom',$info['bDedicatedClassroom'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs_checkbox($mysqlStr,'bDedicatedStudio',$info['bDedicatedStudio'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs_checkbox($mysqlStr,'bDedicatediPod',$info['bDedicatediPod'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'intTheaterCap',$info['intTheaterCap'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'intTheaterMaxCap',$info['intTheaterMaxCap'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'intClassCap',$info['intClassCap'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'intStudioCap',$info['intStudioCap'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'intiPodCap',$info['intiPodCap'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'idLocationType',$info['idLocationType'],$audit,$table,$_POST['id']);
			list($mysqlStr,$audit) = set_strs($mysqlStr,'idTheaterCoordinator',$info['idTheaterCoordinator'],$audit,$table,$_POST['id']);
		
		
			/* Setting up the times into values for it to be put into the DB */
			foreach($weekday_names as $key => $val)	{ 
				if($_POST['bOpen'.$val] == 'on') {		

					$bHour = ($_POST['tBeginMeridian'.$val] == 'PM' ? $_POST['tBeginHour'.$val]+12 : $_POST['tBeginHour'.$val]);
					$eHour = ($_POST['tEndMeridian'.$val] == 'PM' ? $_POST['tEndHour'.$val]+12 : $_POST['tEndHour'.$val]); 
	
					$tbegin = $bHour.':'.$_POST['tBeginMinute'.$val].":00";
					$tend = $eHour .':'. $_POST['tEndMinute'.$val]. ":00";
					
					$_POST['tBegin'.$val] = $tbegin;
					$_POST['tEnd'.$val] = $tend;
					$_POST['bOpen'.$val] = 1;
		
					list($mysqlStr,$audit) = set_strs($mysqlStr,'tBegin'.$val,$info['tBegin'.$val],$audit,$table,$_POST['id']);
					list($mysqlStr,$audit) = set_strs($mysqlStr,'tEnd'.$val,$info['tEnd'.$val],$audit,$table,$_POST['id']);
					list($mysqlStr,$audit) = set_strs($mysqlStr,'bOpen'.$val,$info['bOpen'.$val],$audit,$table,$_POST['id']);
		
				} else { 

					/* If the check box isn't checks, update the DB to turn off those days. */
					$_POST['tBegin'.$val] = 0;
					$_POST['tEnd'.$val] = 0;
					$_POST['bOpen'.$val] = 0;

					list($mysqlStr,$audit) = set_strs($mysqlStr,'tBegin'.$val,$info['tBegin'.$val],$audit,$table,$_POST['id']);
					list($mysqlStr,$audit) = set_strs($mysqlStr,'tEnd'.$val,$info['tEnd'.$val],$audit,$table,$_POST['id']);
					list($mysqlStr,$audit) = set_strs($mysqlStr,'bOpen'.$val,$info['bOpen'.$val],$audit,$table,$_POST['id']);

				}
			}
			
			// if nothing has changed, don't do anything.  Otherwise update / audit.
			if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }

	
			if($_POST['idUsers'] != '') {
				$ids = explode(',', $_POST['idUsers']);

				// look for additions
				foreach($ids as $user_id) {
					if(!isset($info['Users'][$user_id])) {
						// if this is a new item, add it

						database_query("INSERT INTO ACL SET 
							enType='Stores',
							idItem='" . $_POST['id'] . "', idUser='" . $user_id . "',
							enPermission='" . $_POST['enPermission' . $user_id] . "'
							", 'insert user');

					} else {
						// if this item existed previously, update it

						database_query("UPDATE ACL SET enPermission='" . $_POST['enPermission' . $user_id] . "'
							WHERE enType='Stores' AND idItem='" . $_POST['id'] . "' AND idUser='" . $user_id . "'
								AND (enPermission!='" . $_POST['enPermission' . $user_id] . "')
							", 'update user item');

					}
				}
			} else {
				$ids = array();
			}

			// look for removals
			foreach($info['Users'] as $user_id => $user) {
				if(!in_array($user_id, $ids)) {
					database_query("DELETE FROM ACL WHERE enType='Stores' AND idItem='" . $_POST['id'] . "' AND idUser='" . $user_id . "'
						", 'delete user');
				}
			}

			/* Everything is done uploading ... set the message and leave */
			$_SESSION['InfoMessage'][] = 'The store <span class="Specific">' . $_POST['chrName'] . '</span> has been updated.';

			header("Location: stores.php");
			die();
	
		}

		/* If an error has occured, place all the info into this to be used to re-fill the items */
		$info = $_POST;
	}
	
	if(!isset($info)) { $info = 0; }
	
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


	include($BF . 'includes/top_events.php');
	
?>


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



<div style='margin: 10px;'>


		<div class="AdminTopicHeader">Edit Stores</div>
		<div class="AdminInstructions2">Edit Store information here.</div>
		
	<form id='Form' method='post' action=''>

<div class="Messages">
<? if(count($error_messages)) {
		foreach($error_messages as $error) { ?>
			<p class='ErrorMessage'><?=$error?></p>
<?		}
	} ?>
</div>

		<table class='TwoColumns' style='margin-bottom: 20px;'>
			<tr>
				<td class="Left">

					<div class='sectionInfo'>
						<div class='sectionHeader'>Store</div>
						<div class='sectionContent'>

						<div class='form'>
							<div class='formHeader'>Name <span class='Required'>(Required)</span></div>
							<input type='text' size='40' maxlength='80' name='chrName' value='<?=@$info['chrName']?>' />
						</div>
						
						
						<div class='form'>
							<div class='formHeader'>Email Address <span class='Required'>(Required)</span></div>
							<input type='text' size='40' maxlength='80' name='chrEmail' value='<?=@$info['chrEmail']?>' />
						</div>
						
						<div class='form'>
							<div class='formHeader'>Region <span class='Required'>(Required)</span></div>
							<select name='idRegion'>
								<option></option>
<?	while($row = mysqli_fetch_assoc($regions)) { ?>
								<option value='<?=$row['ID']?>' <?=($row['ID']==$info['idRegion'] ? 'selected="selected"' : '')?>><?=$row['chrName']?></option>
<?	} ?>
								</select>
						</div>
						
						<div class='form'>
							<div class='formHeader'>Address <span class='Required'>(Required)</span></div>
							<div><input type='text' size='40' maxlength='80' name='chrAddress1' value='<?=@$info['chrAddress1']?>' /></div>
							<div><input type='text' size='40' maxlength='80' name='chrAddress2' value='<?=@$info['chrAddress2']?>' /></div>
							<div><input type='text' size='40' maxlength='80' name='chrAddress3' value='<?=@$info['chrAddress3']?>' /></div>
						</div>
				
						<div class='form'>
							<div class='formHeader'>City <span class='Required'>(Required)</span></div>
							<input type='text' size='30' maxlength='40' name='chrCity' value='<?=@$info['chrCity']?>' />
						</div>
				
						<div class='form'>
							<div class='formHeader'>State/Province <span class='Required'>(US &amp; Canada)</span></div>
							<select name='chrState'>
<?	if(@$info['chrState'] == '') { ?>
								<option></option>
<?	} ?>
<?	foreach($states as $st => $name) { ?>
								<option value='<?=@$st?>'<?=(@$info['chrState']==$st?' selected="selected"':'')?>><?=$name?></option>
<?	} ?>
								</select>
						</div>
				
						<div class='form'>
							<div class='formHeader'>Postal Code</div>
							<input type='text' size='10' maxlength='10' name='chrPostalCode' value='<?=@$info['chrPostalCode']?>' />
						</div>
				
						<div class='form'>
							<div class='formHeader'>Country <span class='Required'>(Required)</span></div>
							<select name='chrCountry'>
<?	if(@$info['chrCountry'] == '') { ?>
								<option></option>
<?	} ?>
<?	foreach($countries as $cc => $name) { ?>
								<option value='<?=@$cc?>'<?=(@$info['chrCountry']==$cc?' selected="selected"':'')?>><?=$name?></option>
<?	} ?>
								</select>
						</div>
				
						<div class='form'>
							<div class='formHeader'>Phone</div>
							<input type='text' size='14' maxlength='22' name='chrPhone' value='<?=@$info['chrPhone']?>' />
						</div>
				
						<div class='form'>
							<div class='formHeader'>Fax</div>
							<input type='text' size='14' maxlength='22' name='chrFax' value='<?=@$info['chrFax']?>' />
						</div>
	
					</div>
				</div>
			
			
			
				<div class='sectionInfo'>
					<div class='sectionHeader'>Location</div>
					<div class='sectionContent'>

	
						<div class='form'>
							<div class='formHeader'>Store Size</div>
							<select name='idStoreSize'>
								<option value=''></option>
<?	while($row = mysqli_fetch_assoc($storeSize)) { ?>
								<option value='<?=$row['ID']?>' <?=($row['ID']==$info['idStoreSize'] ? 'selected="selected"' : '')?>><?=$row['chrStoreSize']?></option>
<?	} ?>
								</select>
						</div>
	
						
						<div class='form'>
							<div class='formHeader'>Type</div>
							<select name='idLocationType'>
								<option value=''></option>
<?	while($row = mysqli_fetch_assoc($locationType)) { ?>
								<option value='<?=$row['ID']?>' <?=($row['ID']==$info['idLocationType'] ? 'selected="selected"' : '')?>><?=$row['chrLocationType']?></option>
<?	} ?>
								</select>
						</div>
		
						<div class='form'>
							<input type='checkbox' name='bDedicatedStudio' onChange="disableStudio()" <?=(@$info['bDedicatedStudio']?'checked="checked"':'')?> /><span class='formHeader'>Dedicated Studio</span>							
								<span id='intStudio' style='margin-left: 30px; display: none;'><input type='text' size='13' name='intStudioCap' value='<?=@$info['intStudioCap']?>' /><span class='Required'> (Seat Cap)</span></span>
						</div>
						<script type="text/javascript">//<![CDATA[
							if(document.forms[0].bDedicatedStudio.checked) { document.getElementById('intStudio').style.display = 'inline'; } 
						//]]></script> 
				
				
				
						<div class='form'>
							<input type='checkbox' name='bDedicatediPod' onChange="disableiPod()" <?=(@$info['bDedicatediPod'] ? 'checked="checked"' : '')?> />	<span class='formHeader'>Dedicated iPod Bar</span><span id='intiPod' style='margin-left: 22px; display: none;'><input type='text' size='13' name='intiPodCap' value='<?=@$info['intiPodCap']?>' /><span class='Required'> (Seat Cap)</span></span>							
						</div>
						<script type="text/javascript">//<![CDATA[
							if(document.forms[0].bDedicatediPod.checked) { document.getElementById('intiPod').style.display = 'inline'; } 
						//]]></script> 
				
				
					
						<div class='form'>
							<input type='checkbox' name='bDedicatedClassroom' onChange="disableClass()" <?=(@$info['bDedicatedClassroom'] ? 'checked="checked"' : '')?> /><span class='formHeader'>Dedicated Classroom</span>
									<span id='intClass' style='margin-left: 10px; display: none;'><input type='text' size='13' name='intClassCap' value='<?=@$info['intClassCap']?>' /><span class='Required'> (Seat Cap)</span></span>
							</div>
						<script type="text/javascript">//<![CDATA[
							if(document.forms[0].bDedicatedClassroom.checked) { document.getElementById('intClass').style.display = 'inline'; } 
						//]]></script> 
						
						<div class='form'>
							<input type='checkbox' name='bDedicatedTheater' onChange="disableTheater()" <?=(@$info['bDedicatedTheater'] ? 'checked="checked"' : '')?> /><span class='formHeader'>Dedicated Theater</span>
							
							<div class='' style='padding-left: 2em;'>
								<span id='intThSC' style='margin-left: 10px; display: none;'><input type='text' size='13' name='intTheaterCap' value='<?=@$info['intTheaterCap']?>' /><span class='Required'> (Seat Cap)</span></span>
							</div>
							<div class='' style='padding-left: 2em; margin-top: 3px;'>
								<span id='intThMC' style='margin-left: 10px; display: none;'><input type='text' size='13' name='intTheaterMaxCap' value='<?=@$info['intTheaterMaxCap']?>' /><span class='Required'> (Mac Cap)</span></span>
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
 
 	



			
		<script type='text/javascript'>//<![CDATA[
			function openchanged(checkbox)
			{
				var div = checkbox;

				do {				
					div = div.parentNode;
					if(!div) {
						alert("Field div not found");
						return;
					}
				} while(div.className != 'form' || div.nodeName != 'DIV');
				
				var item = div.firstChild;
				do {
					if(item.nodeName == "SELECT")
					{
						item.disabled = !checkbox.checked;
					}
					item = item.nextSibling;
				} while(item);
			}
			
			
		//]]></script>
			
					<div class='sectionInfo'>
						<div class='sectionHeader'>Store Hours</div>
						<div class='sectionContent'>
			
<? 
	foreach($weekday_long_names as $key => $val)	{ 
	$short = $weekday_names[$key];

	// if it's not set, it's probably a newly loaded page.  Need to break up the tBegin / tEnd dates
	if(!isset($info['tBeginMinute'.$short])) { 
	
		$time = split(':',$info['tBegin'.$short]);
		$info['tBeginHour'.$short] = ($time[0] > 12 ? $time[0]-12 : $time[0]);
		$info['tBeginMinute'.$short] = $time[1];
		$info['tBeginMeridian'.$short] = ($time[0] > 12 ? 'PM' : 'AM');
		$time = '';
		$time = split(':',$info['tEnd'.$short]);
		$info['tEndHour'.$short] = ($time[0] > 12 ? $time[0]-12 : $time[0]);
		$info['tEndMinute'.$short] = $time[1];
		$info['tEndMeridian'.$short] = ($time[0] > 12 ? 'PM' : 'AM');
	}

?>
							<div class='form'>
								<div class='formHeader'><?=$val?></div>
								<div>
								<label><input type='checkbox' id='bOpen<?=$short?>' name='bOpen<?=$short?>' onchange='openchanged(this)' <?=(($info['bOpen'.$short] == 'on') || ($info['bOpen'.$short] == 1) ? 'checked="checked"' : '')?> /> Open: </label>
								<select name='tBeginHour<?=$short?>'>
<?	if(@$info['tBeginHour'.$short] == '') { ?>
									<option></option>
<?	} ?>
<?	for($hour = 1; $hour<=12; $hour++) { ?>
									<option value='<?=$hour?>' <?=($hour == $info['tBeginHour'.$short]?' selected="selected"':'')?>><?=$hour?></option>
<?	} ?>
								</select>
							:
								<select name='tBeginMinute<?=$short?>'>
									<option<?=('00' == $info['tBeginMinute'.$short]?' selected="selected"':'')?>>00</option>
									<option<?=('30' == $info['tBeginMinute'.$short]?' selected="selected"':'')?>>30</option>
								</select>

								<select name='tBeginMeridian<?=$short?>'>
<?		if(@$info['tBeginMeridian'.$short] == '') { ?>
									<option></option>
<?		} ?>
									<option<?=(('AM' == $info['tBeginMeridian'.$short]) || ($info == 0) ? ' selected="selected"' : '')?>>AM</option>
									<option<?=('PM' == $info['tBeginMeridian'.$short] ? ' selected="selected"' : '')?>>PM</option>
								</select>

							</div>
							<div style="padding-left: 15px;">
							<label> Closed: </label>

							<select name='tEndHour<?=$short?>'>
<?	if(@$info['tEndHour'.$short] == '') { ?>
								<option></option>
<?	} ?>
<?	for($hour = 1; $hour<=12; $hour++) { ?>
								<option value='<?=$hour?>' <?=($hour == $info['tEndHour'.$short]?' selected="selected"':'')?>><?=$hour?></option>
<?	} ?>
							</select>
							:
							<select name='tEndMinute<?=$short?>'>
								<option<?=('00' == $info['tEndMinute'.$short] ? ' selected="selected"':'')?>>00</option>
								<option<?=('30' == $info['tEndMinute'.$short] ? ' selected="selected"':'')?>>30</option>
							</select>
	
							<select name='tEndMeridian<?=$short?>'>
<?		if(@$info['tEndMeridian'.$short] == '') { ?>
								<option></option>
<?		} ?>
								<option<?=('AM' == $info['tEndHour'.$short] ? ' selected="selected"':'')?>>AM</option>
								<option<?=(('PM' == $info['tEndMeridian'.$short]) || ($info == 0) ? ' selected="selected"':'')?>>PM</option>
							</select>

							</div>
						</div>
		<script type='text/javascript'>//<![CDATA[
			openchanged(document.getElementById('bOpen<?=$short?>'));
		//]]></script>

<? 	}	?>
								
	 				   	</div>
					</div>
			


					<div class='sectionInfo'>
						<div class='sectionHeader'>Users</div>
						<div class='sectionContent'>

				<input type='hidden' id='idUsers' name='idUsers' value='<?=$info['idUsers']?>' />
				<input type='hidden' id='chrUsers' name='chrUsers' value='<?=$info['chrUsers']?>' />
				<div class='L10'>Users <input type='button' value='Add...' onclick='newwin = window.open("select-user.php?d=<?=urlencode(base64_encode('functioncall=user_add'))?>","new","width=435,height=400,resizable=1,scrollbars=1"); newwin.focus();'/></div>
				<table class='list' id='ListUsers' style='width: 100%;'>
					<tbody>
<?			if($info['idUsers'] != '') { ?>
<?
				$ids = explode_or_empty(',', $info['idUsers']);
				$chrs = explode_or_empty(',', $info['chrUsers']);
				$count = 0;
				foreach($ids as $k => $user_id) { 
					$chr = $chrs[$k];
?>
						<tr class='<?=(++$count%2?'odd':'even')?>'>
							<td style='width: 99%;'><?=$chr?></td>
							<td>
<?
								$tmpLevels = str_replace("enPermission","enPermission".$user_id,$levels);
								$tmpLevels = str_replace("value='".$info['enPermission' . $user_id]."'","value='".$info['enPermission' . $user_id]."' selected='selected'",$tmpLevels);
?>
								<?=$tmpLevels?>
							</td>
							<td style='width: 1%;'><input type='button' value='Remove' onclick="user_remove(<?=$user_id?>, this); " /></td>
							</tr>
<?				} ?>
<?			} ?>
						</tbody>
					</table>
				<div id='NoUsers' class='NoRecords' style='<?=(@$info['idUsers'] == ''?'':'display: none;')?>'>(No users have been added.)</div>

<script type="text/javascript">//<![CDATA[
function user_add(id, chr, level) 
{ 
	document.getElementById('NoUsers').style.display='none';

	var row = list_add('ListUsers', 'idUsers', 'chrUsers', id, chr, level);
	if(!row) {
	} else {
		var td = document.createElement("TD");
		td.className='alignleft nowrap';
		td.innerHTML= level;
		row.insertBefore(td, row.lastChild);

		row.lastChild.innerHTML= "<input type='button' value='Remove' onclick=\"user_remove(" + id + ", this);\" />";
	}
}
function user_remove(id, button)
{
	list_remove('ListUsers', 'idUsers', 'chrUsers', id, button);
	var table = document.getElementById('ListUsers');
	var tbody = table.getElementsByTagName("TBODY")[0];
	var rows = tbody.getElementsByTagName("TR");
	if(!rows.length) {
		document.getElementById('NoUsers').style.display='block';
	}
}
// ]]></script>

	 				   	</div>
					</div>
	
	
	
					</td>
				</tr>
			</table>
			

		
		<div class='FormButtons'>
			<input type='hidden' name='id' value='<?=$_REQUEST['id']?>' />
			<input type='submit' name='SubmitAddStore' value='Update Information' />
			</div>
		
		</form>
	
<?
	include($BF. 'includes/bottom2.php');

function explode_or_empty($delimiter, $string) {
	if($string == '') {
		return(array());
	}
	return(explode($delimiter, $string));
}

?>
