<?php
	$auth_not_required = 1;
	require("_lib.php");
	
	if(!isset($_REQUEST['id'])) {
		$_REQUEST['id'] = 1;
	}
	
	$info = mysql_fetch_assoc(do_mysql_query("SELECT txtSourceCode FROM Content_Static WHERE ID='" . $_REQUEST['id'] . "'","getting homepage"));

	// This is the section for the Navigation once I build the pages :-)
	
	//Daniel we need to fix this, too much information is coming in here
	
	
	$pages_result = do_mysql_query("
		select chrSectionName, chrPageTitle, bSection, idSection, ID
		from Content_Dynamic
		where bSection = TRUE
		order by idOrder, chrSectionName, chrPageTitle
		","get sections");
	$sections = array();
	$pages_by_section = array();
	while($row = mysql_fetch_assoc($pages_result)) {
		if($row['bSection']) {
			$sections[$row['ID']] = $row;
		} else {
			$pages_by_section[$row['idSection']][] = $row;
		}
	}
	
	// Set the title, and add the doc_top
	$title = "Welcome";
	require(BASE_FOLDER . 'docpages/doc_meta_home.php');
?>
<body>
	<form method="get" action="dynamic.php">
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
				<td width="900" bgcolor="#ffffff" style='background-color: white;'>
<!--This is the log in bar which will be dynamic -->
					<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f1f1f1" style='background: url("images/smoothbar.gif") repeat-x; height: 21px; vertical-align: top;'>
						<tr>
							<td valign="middle"></td>
<? if(isset($_SESSION['idUser']) && $_SESSION['idUser'] != '') { ?>
							<td width="100%" style="padding-left: 10px;"><span class="loginbar">Welcome <?=$_SESSION['chrFirstName']?> <?=$_SESSION['chrLastName']?> | <a href='?auth_destroy=1'>Log Out</a>
							<?=($_SESSION['idType'] != 4 ? " | <a href='".BASE_FOLDER."admin/'>Administration Console</a>" : "")?>
							</span></td>
<?	} else { ?>
							<td width="100%"></td>
<?	} ?>
							<td align="right" nowrap="nowrap" style='padding-right: 5px;'>
								<select name="id">
									<option value="" selected='selected'>Home</option>
<?
	if(isset($sections_result)) {
		while($row = mysql_fetch_assoc($sections_result)) { ?>
									<option value="<?=$row['ID']?>"><?=decode($row['chrSectionName'])?></a></option>
<?
	}
		mysql_data_seek($sections_result, 0);
	}
?>
									<option value="coe" >My Store</option>
<?
	foreach($sections as $section) { 
?>
									<option value="<?=$section['ID']?>"><?=decode($section['chrSectionName'])?></a></option>
<?
		if(isset($pages_by_section[$section['ID']])) {
			foreach($pages_by_section[$section['ID']] as $page) {
?>
									<option value="<?=$page['ID']?>">&nbsp;&nbsp;&nbsp;<?=decode($page['chrPageTitle'])?></a></option>
<?
			}
		}
	}
?>
            					</select>
								<input type="submit" value="Go" />
							</td>
						</tr>
					</table>
<!-- this is the end of the login bar -->
<!-- this is the main section of the site and will be used to modify -->
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td width="640px" style="padding:15px;" valign="top">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td align="left" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<img src="<?=BASE_FOLDER?>images/wire-header-left.gif">
										</td>
										<td width="100%" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<strong><span style="color:#282828;">Announcements</span></strong>
										</td>
										<td align="right" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<img src="<?=BASE_FOLDER?>images/wire-header-right.gif">
										</td>
									</tr>
									<tr>
										<td colspan="3" style="border:solid 1px #c0c1c2;">
											<div style="padding:10px;">
												<?=decode($info['txtSourceCode'])?>
											</div>
										</td>
									</tr>
								</table>
							</td>
							<td valign="top" width="200px" style="padding:15px;">
								<table width="100%" border="0" cellpadding="0" cellspacing="0" class="Navigation">
									<tr>
										<td align="left" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<img src="<?=BASE_FOLDER?>images/wire-header-left.gif">
										</td>
										<td width="100%" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<strong><span style="padding-left:5px; color:#282828;">Log In Now</span></strong>
										</td>
										<td align="right" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<img src="<?=BASE_FOLDER?>images/wire-header-right.gif">
										</td>
									</tr>
									<tr>
										<td colspan="3" style="border:solid 1px #c0c1c2;">
											<div style="padding-left:10px; padding-right:10px; padding-bottom:10px;">
<?
	if(isset($_SESSION['ErrorMessage']) && $_SESSION['ErrorMessage'] != "" ) {
?>
												<div class='Messages'>
													<div class='InfoMessage' style='margin-left: 3px;'><?=$_SESSION['ErrorMessage']?></div>
												</div>
<?
	}
	if (!isset($_SESSION['idUser'])) {
?>
<p>
												<div>
													<span style="font-weight:bold; font-size:10px;">Login</span><br />
													<input type='text' name='chrLoginName' width="25" />
												</div>
												<div style="padding-top:5px;">
													<span style="font-weight:bold; font-size:10px;">Password</span><br />
													<input type='password' name='chrLoginPassword' style='width: 130px;' />
												</div>
												<div style="padding-top:5px;">
													<a href='resetpassword.php' style='color: blue; font-size: 11px; '>Forgot Password?</a>
												</div>
												<div style="padding-top:5px;">
													<input type='submit' value='Login' name='btnLogin' />
												</div>
<?
	} else {
?>
												<div style="padding-top:10px;">
													Welcome <?=$_SESSION['chrFirstName']?> <?=$_SESSION['chrLastName']?>
													<div style='margin-top: 10px;'><a href='index.php?auth_destroy=1' style='color: blue; font-size: 11px; '>Logout</a></div>
												</div>
<?
	}
	$_SESSION['ErrorMessage'] = "";
?>
										</td>
									</tr>
								</table>
								<table width="100%" border="0" cellpadding="0" cellspacing="0" class="Navigation" style="padding-top:10px;">
									<tr>
										<td align="left" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<img src="<?=BASE_FOLDER?>images/wire-header-left.gif">
										</td>
										<td width="100%" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<strong><span style="padding-left:5px; color:#282828;">Getting Started</span></strong>
										</td>
										<td align="right" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<img src="<?=BASE_FOLDER?>images/wire-header-right.gif">
										</td>
									</tr>
									<tr>
										<td colspan="3" style="border:solid 1px #c0c1c2; padding:10px;">
											To get started using the Retail Database, download the "How to" Guide:<br/>
											<a href='guide.pdf' style='color: blue; font-size: 11px; '>"How To" Guide</a>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
<?php
	include('docpages/doc_bottom.php');
?>
</form>