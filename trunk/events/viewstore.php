<?php
	$BF = "../";
	require($BF. "_lib2.php");
	$curPage = "stores";
	$title = 'View Store';
	include($BF. 'includes/meta2.php');
			
	/* Includes to get the countries and states */
	include($BF . 'includes/states.php');
	include($BF . 'includes/countries.php');
	/* Weeknames for the date checks */
	include($BF . 'includes/week_names.php');
	
	$q = "SELECT Stores.*, Regions.chrName as chrRegion, chrLocationType, chrStoreSize
		FROM Stores 
		LEFT JOIN Regions ON Regions.ID=Stores.idRegion
		LEFT JOIN StoreSize ON StoreSize.ID=Stores.idStoreSize
		LEFT JOIN LocationType ON LocationType.ID=Stores.idLocationType
		WHERE Stores.ID='" . $_REQUEST['id'] . "'"
	;
	
	$info = fetch_database_query($q,"getting store info");
	
	if(!isset($info)) { $info = 0; }
	
	$regions = database_query("SELECT ID, chrName FROM Regions WHERE !bDeleted ORDER BY chrName","getting regions");
	$storeSize = database_query("SELECT ID, chrStoreSize FROM StoreSize ORDER BY chrStoreSize","getting store size");
	$locationType = database_query("SELECT ID, chrLocationType FROM LocationType ORDER BY chrLocationType","getting location type");
	
	
	

	include($BF . 'includes/top_events.php');
	
?>

<div style='margin: 10px;'>


	<div class="AdminTopicHeader">View Store</div>

		<table class='TwoColumns'>
			<tr>
				<td class="Left">

					<div class='sectionInfo'>
						<div class='sectionHeader'>Store</div>
						<div class='sectionContent'>

						<div class='form'>
							<div class='formHeader'>Name</div>
								<div class='formDisplay'><?=@$info['chrName']?></div>
							</div>
						
						
						<div class='form'>
							<div class='formHeader'>Email Address</div>
								<div class='formDisplay'><?=@$info['chrEmail']?></div>
							</div>
						
						<div class='form'>
							<div class='formHeader'>Region</div>
							<div class='formDisplay'><?=$info['chrRegion']?></div>
						</div>
						
						<div class='form'>
							<div class='formHeader'>Address</div>
								<div class='formDisplay'><?=@$info['chrAddress1']?>
								<?=($info['chrAddress2'] != '' ? "<br />".$info['chrAddress2'] : "")?>
								<?=($info['chrAddress3'] != '' ? "<br />".$info['chrAddress3'] : "")?></div>
							</div>
				
						<div class='form'>
							<div class='formHeader'>City</div>
								<div class='formDisplay'><?=@$info['chrCity']?></div>
							</div>
				
						<div class='form'>
							<div class='formHeader'>State/Province</div>
								<div class='formDisplay'><?=@$info['chrCountry']?></div>
							</div>
				
						<div class='form'>
							<div class='formHeader'>Postal Code</div>
								<div class='formDisplay'><?=@$info['chrPostalCode']?></div>
							</div>
				
						<div class='form'>
							<div class='formHeader'>Country</div>
							<div class='formDisplay'></div>
						</div>
				
						<div class='form'>
							<div class='formHeader'>Phone</div>
								<div class='formDisplay'><?=@$info['chrPhone']?></div>
							</div>
				
						<div class='form'>
							<div class='formHeader'>Fax</div>
								<div class='formDisplay'><?=@$info['chrFax']?></div>
						</div>
	
					</div>
				</div>
			
			
			
				<div class='sectionInfo'>
					<div class='sectionHeader'>Location</div>
					<div class='sectionContent'>

	
						<div class='form'>
							<div class='formHeader'>Store Size</div>
								<div class='formDisplay'><?=@$info['chrStoreSize']?></div>
							</div>
	
						
						<div class='form'>
							<div class='formHeader'>Type</div>
								<div class='formDisplay'><?=@$info['chrLocationType']?></div>
							</div>
		
						<div class='form'>
							<span class='formHeader'>Dedicated Studio</span>							
								<div class='formDisplay'>Maximum Capacity: <?=@$info['intStudioCap']?></div>
							</div>
			
				
						<div class='form'>
							<span class='formHeader'>Dedicated iPod Bar</span>
								<div class='formDisplay'>Maximum Capacity: <?=@$info['intiPodCap']?></div>
						</div>
			
				
						<div class='form'>
							<span class='formDisplay'>Dedicated Classroom</span>
									<div class='formDisplay'>Maximum Capacity: <?=@$info['intClassCap']?></div>
							</div>
					
						<div class='form'>
							<span class='formDisplay'>Dedicated Theater</span>
								<div class='formDisplay'>Theater Capacity: <?=@$info['intTheaterCap']?></div>
							</div>
							<div>
								<div class='formDisplay'>Maximum Capacity: <?=@$info['intTheaterMaxCap']?></div>
							</div>
						</div>
			
						</div>
					</div>
			
					</td>
				<td class='Gutter'></td>
				<td class="Right">				

			
					<div class='sectionInfo'>
						<div class='sectionHeader'>Store Hours</div>
						<div class='sectionContent'>
			
<? 
	foreach($weekday_long_names as $key => $val)	{ 
		$short = $weekday_names[$key];
		$tbs = split(":",$info['tBegin'.$short]);
		$tes = split(":",$info['tEnd'.$short]);
?>
		<div><strong><?=$val?><strong></div>
		<div style='padding-left: 5px; margin-bottom: 10px;'>
			<?=($info['bOpen'.$short] == '1' ? "Opened from ". date("g:i a",mktime($tbs[0],$tbs[1],0,0,0,0))." to ".date("g:i a",mktime($tes[0],$tes[1],0,0,0,0))  : 'Closed')?>
		</div>

<?	}	?>
			
				   	</div>
				</div>
					</td>
				</tr>
			</table>
			

<?
	include($BF. 'includes/bottom2.php');
?>