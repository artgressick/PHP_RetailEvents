<?php
	$BF = '../';
	$title = 'Add Store';
	require($BF. '_lib2.php');
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
		
	// if this is a form submission
	if(isset($_POST['chrName'])) {
	
		$q = "INSERT INTO Stores SET 
			chrName='" . encode($_POST['chrName']) . "',
			idRegion='" . $_POST['idRegion'] . "',
			chrAddress1='" . encode($_POST['chrAddress1']) . "',
			chrAddress2='" . encode($_POST['chrAddress2']) . "',
			chrAddress3='" . encode($_POST['chrAddress3']) . "',
			chrCity='" . encode($_POST['chrCity']) . "',
			chrState='" . encode($_POST['chrState']) . "',
			chrPostalCode='" . encode($_POST['chrPostalCode']) . "',
			chrCountry='" . encode($_POST['chrCountry']) . "',
			chrPhone='" . encode($_POST['chrPhone']) . "',
			chrEmail='" . encode($_POST['chrEmail']) . "',
			chrFax='" . encode($_POST['chrFax']) . "',
			idStoreSize='" . $_POST['idStoreSize'] . "',
			idLocalization='". $_POST['idLocalization'] ."',			
			bDedicatedTheater='" . isset($_POST['bDedicatedTheater']) . "',
			bDedicatedStudio='" . isset($_POST['bDedicatedStudio']) . "',
			bDedicatedClassroom='" . isset($_POST['bDedicatedClassroom']) . "',
			bDedicatediPod='" . isset($_POST['bDedicatediPod']) . "',
			bIgnore='" . isset($_POST['bIgnore']) . "',
			bIgnoreRecaps='" . isset($_POST['bIgnoreRecaps']) . "',				
			intTheaterCap='" . $_POST['intTheaterCap'] . "',
			intTheaterMaxCap='" . $_POST['intTheaterMaxCap'] . "',
			intClassCap='" . $_POST['intClassCap'] . "',
			intiPodCap='" . $_POST['intiPodCap'] . "',
			intStudioCap='" . $_POST['intStudioCap'] . "',
			";

		
		
		/* Setting up the times into values for it to be put into the DB */
		foreach($_POST['chkOpen'] as $val)	{ 
			$bHour = ($_POST['tBeginMeridian'.$val] == 'PM' ? $_POST['tBeginHour'.$val]+12 : $_POST['tBeginHour'.$val]);
			$eHour = ($_POST['tEndMeridian'.$val] == 'PM' ? $_POST['tEndHour'.$val]+12 : $_POST['tEndHour'.$val]); 

			$tbegin = $bHour.':'.$_POST['tBeginMinute'.$val].":00";
			$tend = $eHour .':'. $_POST['tEndMinute'.$val]. ":00";
	
			$q .= "tBegin" . $val . "='" . $tbegin . "',
				   tEnd" . $val . "='" . $tend . "',
				   bOpen" . $val . "=1,
				   ";				
		}

		$q .= "idLocationType='" . $_POST['idLocationType'] . "'";

		database_query($q, "insert store");

		header("Location: index.php");
		die();
	}

	/* Includes to get the countries and states */
	include($BF. 'includes/states.php');
	include($BF. 'includes/countries.php');

	
	$regions = database_query("SELECT ID, chrName FROM Regions WHERE !bDeleted ORDER BY chrName","getting regions");
	$storeSize = database_query("SELECT ID, chrStoreSize FROM StoreSize ORDER BY chrStoreSize","getting store size");
	$locationType = database_query("SELECT ID, chrLocationType FROM LocationType ORDER BY chrLocationType","getting location type");

	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
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


		<div class="AdminTopicHeader">Add Store</div>
		<div class="AdminInstructions2">Add Store information here.</div>

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
										<option value='<?=$row['ID']?>'><?=$row['chrLocalization']?></option>
		<?
			}
		?>
									</select>
								</div> 		

						<div class='form'>
							<div class='formHeader'>Name <span class='Required'>(Required)</span></div>
							<input type='text' size='40' maxlength='80' name='chrName' id='chrName' />
							</div>
						
						
						<div class='form'>
							<div class='formHeader'>Email Address <span class='Required'>(Required)</span></div>
							<input type='text' size='40' maxlength='80' name='chrEmail' id='chrEmail' />
							</div>
						
						<div class='form'>
							<div class='formHeader'>Region <span class='Required'>(Required)</span></div>
							<select name='idRegion' id='idRegion'>
								<option></option>
<?	while($row = mysqli_fetch_assoc($regions)) { ?>
								<option value='<?=$row['ID']?>'><?=$row['chrName']?></option>
<?	} ?>
								</select>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Address <span class='Required'>(Required)</span></div>
							<div><input type='text' size='40' maxlength='80' name='chrAddress1' id='chrAddress1' /></div>
							<div><input type='text' size='40' maxlength='80' name='chrAddress2' id='chrAddress2' /></div>
							<div><input type='text' size='40' maxlength='80' name='chrAddress3' id='chrAddress3' /></div>
							</div>
				
						<div class='form'>
							<div class='formHeader'>City <span class='Required'>(Required)</span></div>
							<input type='text' size='30' maxlength='40' name='chrCity' id='chrCity' />
							</div>
				
						<div class='form'>
							<div class='formHeader'>State/Province <span class='Required'>(US &amp; Canada)</span></div>
							<select name='chrState' id='chrState'>
<?	foreach($states as $st => $name) { ?>
								<option value='<?=@$st?>'><?=$name?></option>
<?	} ?>
								</select>
							</div>
				
						<div class='form'>
							<div class='formHeader'>Postal Code</div>
							<input type='text' size='10' maxlength='10' name='chrPostalCode' id='chrPostalCode' />
							</div>
				
						<div class='form'>
							<div class='formHeader'>Country <span class='Required'>(Required)</span></div>
							<select name='chrCountry' id='chrCountry'>
<?	foreach($countries as $cc => $name) { ?>
								<option value='<?=@$cc?>'><?=$name?></option>
<?	} ?>
								</select>
							</div>
				
						<div class='form'>
							<div class='formHeader'>Phone</div>
							<input type='text' size='30' maxlength='22' name='chrPhone' id='chrPhone' />
							</div>
				
						<div class='form'>
							<div class='formHeader'>Fax</div>
							<input type='text' size='30' maxlength='22' name='chrFax' id='chrFax' />
						</div>
						
						<div class='form'>
							<div class='formHeader'><input type='checkbox' name='bIgnore' id='bIgnore' /> Ignore Monthly Requirements </div>
						</div>
						
						<div class='form'>
							<div class='formHeader'><input type='checkbox' name='bIgnoreRecaps' id='bIgnoreRecaps' /> Ignore Recap Requirements </div>
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
								<option value='<?=$row['ID']?>'><?=$row['chrStoreSize']?></option>
<?	} ?>
								</select>
							</div>
	
						
						<div class='form'>
							<div class='formHeader'>Type</div>
							<select name='idLocationType' id='idLocationType'>
								<option value=''></option>
<?	while($row = mysqli_fetch_assoc($locationType)) { ?>
								<option value='<?=$row['ID']?>'><?=$row['chrLocationType']?></option>
<?	} ?>
								</select>
							</div>
		
						<div class='form'>
							<input type='checkbox' name='bDedicatedStudio' id='bDedicatedStudio' onChange="disableStudio()" /><span class='formHeader'>Dedicated Studio</span>							
								<span id='intStudio' style='margin-left: 30px; display: none;'><input type='text' size='13' name='intStudioCap' id='intStudioCap' /><span class='Required'> (Seat Cap)</span></span>
							</div>
						<script type="text/javascript">//<![CDATA[
							if(document.forms[0].bDedicatedStudio.checked) { document.getElementById('intStudio').style.display = 'inline'; } 
						//]]></script> 
				
				
				
						<div class='form'>
							<input type='checkbox' name='bDedicatediPod' id='bDedicatediPod' onChange="disableiPod()" /><span class='formHeader'>Dedicated iPod Bar</span><span id='intiPod' style='margin-left: 22px; display: none;'><input type='text' size='13' name='intiPodCap' id='intiPodCap' /><span class='Required'> (Seat Cap)</span></span>							
						</div>
						<script type="text/javascript">//<![CDATA[
							if(document.forms[0].bDedicatediPod.checked) { document.getElementById('intiPod').style.display = 'inline'; } 
						//]]></script> 
				
				
					
						<div class='form'>
							<input type='checkbox' name='bDedicatedClassroom' id='bDedicatedClassroom' onChange="disableClass()" /><span class='formHeader'>Dedicated Classroom</span>
								<span id='intClass' style='margin-left: 10px; display: none;'><input type='text' size='13' name='intClassCap' id='intClassCap' /><span class='Required'> (Seat Cap)</span></span>
							</div>
						<script type="text/javascript">//<![CDATA[
							if(document.forms[0].bDedicatedClassroom.checked) { document.getElementById('intClass').style.display = 'inline'; } 
						//]]></script> 
						
						<div class='form'>
							<input type='checkbox' name='bDedicatedTheater' id='bDedicatedTheater' onChange="disableTheater()" /><span class='formHeader'>Dedicated Theater</span>
							
							<div class='' style='padding-left: 2em;'>
								<span id='intThSC' style='margin-left: 10px; display: none;'><input type='text' size='13' name='intTheaterCap' id='intTheaterCap' /><span class='Required'> (Seat Cap)</span></span>
							</div>
							<div class='' style='padding-left: 2em; margin-top: 3px;'>
								<span id='intThMC' style='margin-left: 10px; display: none;'><input type='text' size='13' name='intTheaterMaxCap' id='intTheaterMaxCap' /><span class='Required'> (Max Cap)</span></span>
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
			
<? 
	$twelveHours = "";
	for($hour = 1; $hour<=12; $hour++) { 
		$twelveHours .= "<option value='". $hour. "'>". $hour ."</option>
						";
 	} 

	foreach($weekday_long_names as $key => $val)	{ 
	$short = $weekday_names[$key];
?>
							<div class='form'>
								<div class='formHeader'><?=$val?></div>
								<div>
								<label><input type='checkbox' name='chkOpen[]' value='<?=$short?>' onchange='openchanged(this)' /> Open: </label>
								<select name='tBeginHour<?=$short?>'>
									<option></option>
									<?=$twelveHours?>
								</select>
							:
								<select name='tBeginMinute<?=$short?>'>
									<option>00</option>
									<option>30</option>
								</select>

								<select name='tBeginMeridian<?=$short?>'>
									<option>AM</option>
									<option>PM</option>
								</select>

							</div>
							<div style="padding-left: 15px;">
							<label> Closed: </label>

							<select name='tEndHour<?=$short?>'>
								<option></option>
								<?=$twelveHours?>
							</select>
							:
							<select name='tEndMinute<?=$short?>'>
								<option>00</option>
								<option>30</option>
							</select>
	
							<select name='tEndMeridian<?=$short?>'>
								<option>AM</option>
								<option selected>PM</option>
							</select>

							</div>
						</div>
<? 	}	?>
								
	 				   	</div>
					</div>

				</td>
			</tr>
		</table>



		<div class='FormButtons'>
			<input type='button' name='SubmitAddSection' value='Save New Section' onclick='error_check()' />
		</div>
		
		</form>
		
			  </td>
              <td width="10">&nbsp;</td>
            </tr>
        </table>


<?
	include($BF. 'includes/bottom.php');
?>