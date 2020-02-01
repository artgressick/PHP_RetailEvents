<body <? if(function_exists('insert_body_params')) insert_body_params(); ?>>
	<table width="908" border="0" align="center" cellpadding="0" cellspacing="0">
		<tr>
			<td colspan="3">
			
							<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td><a href="<?=BASE_FOLDER?>index.php"><img src="<?=BASE_FOLDER?>images/index-logo.png" alt="Apple Retail Marketing" width="309" height="300" border="0" /></a></td>
<!--photo switcher-->
							<td>
								<span style="opacity: 1; visibility: visible;" id="crossfade"><span style="z-index: 100; opacity: 0;" class="fader">
<!---->
								<? $rand = mt_rand(1,17); ?>
								<img src="<?=BASE_FOLDER?>images/index-main<?=$rand?>.jpg" alt="" height="300" width="599" border="0" class='imgMargin'></span></span>
<!---->
								<script type="text/javascript">
								var imgs = rand_unique(1,17,4,<?=$rand?>);
								InitCrossFade('crossfade',
									'<span class="fader"><img src="<?=BASE_FOLDER?>images/index-main'+imgs[0]+'.jpg" width="599" height="300" class="imgMargin" alt=""></span>',
									'<span class="fader"><img src="<?=BASE_FOLDER?>images/index-main'+imgs[1]+'.jpg" width="599" height="300" class="imgMargin" alt=""></span>',
									'<span class="fader"><img src="<?=BASE_FOLDER?>images/index-main'+imgs[2]+'.jpg" width="599" height="300" class="imgMargin" alt=""></span>',
									'<span class="fader"><img src="<?=BASE_FOLDER?>images/index-main'+imgs[3]+'.jpg" width="599" height="300" class="imgMargin" alt=""></span>',
									'<span class="fader"><img src="<?=BASE_FOLDER?>images/index-main'+imgs[4]+'.jpg" width="599" height="300" class="imgMargin" alt=""></span>');
								</script>
<!--end of photo switcher-->
	
							</td>
						</tr>
					</table>
			
			</td>
		</tr>
		<tr>
			<td width="4"></td>
			<td width="900" bgcolor="#ffffff">
<!--This is the log in bar which will be dynamic -->
				<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f1f1f1" style='background: url("<?=BASE_FOLDER?>images/smoothbar.gif") repeat-x;'>
					<tr>
						<td><img src="<?=BASE_FOLDER?>images/smoothbar_arrow.gif" /></td>
						<td style='' width="65%">
							<span class="loginbar" style=''>Welcome <?=$_SESSION['chrFirstName']?> <?=$_SESSION['chrLastName']?> | <a href='<?=BASE_FOLDER?>profile.php'>My Profile</a> | <a href='?auth_destroy=1'>Log Out</a><?=(in_array($_SESSION['idType'],array("1","2","3")) ? " | <a href='".BASE_FOLDER."admin/'>Administration Console</a>" : "")?></span>
						</td>
						<td style='text-align: center; background: <?=(CURRENT_PAGE=='index' ? 'white' : 'inherit')?>' nowrap="nowrap"><a href="index.php" title="COE">COE</a></td>
						<td><img src='<?=BASE_FOLDER?>images/smoothbar_divider.gif'></td>
						<td style='text-align: center; background: <?=(CURRENT_PAGE=='presenters' ? 'white' : 'inherit')?>' nowrap="nowrap"><a href="presenters.php" title="Guest Presenters">Guest Presenters</a></td>
						<td><img src='<?=BASE_FOLDER?>images/smoothbar_divider.gif'></td>
						<td style='text-align: center; background: <?=(CURRENT_PAGE=='recaps' ? 'white' : 'inherit')?>' nowrap="nowrap"><a href="recaps.php" title="Guest Presenters">Recaps</a></td>
						<td><img src='<?=BASE_FOLDER?>images/smoothbar_divider.gif'></td>			
						<td style='text-align: center; background: <?=(CURRENT_PAGE=='stores' ? 'white' : 'inherit')?>' nowrap="nowrap"><a href="stores.php" title="Store Info">Store info</a></td>
					</tr>
				</table>
<!-- this is the end of the login bar -->
<!-- this is the main section of the site and will be used to modify -->
