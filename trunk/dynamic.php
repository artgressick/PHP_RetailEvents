<?php
	$auth_not_required = 1;
	require("_lib.php");

	//Check to see if they are linking to the Events Section
	if($_REQUEST['id'] == "coe") {
		header('Location: events/');
		die();
	}
	
	if(@!$_REQUEST['id']) {
		header('Location: ./');
		die();
	}

	$page = mysql_fetch_assoc(do_mysql_query("
		SELECT Content_Dynamic.bSection, Content_Dynamic.chrPageTitle, 
			Content_Dynamic.txtSourceCode,
			Content_Dynamic.idSection,
			IF(Content_Dynamic.bSection, Content_Dynamic.chrSectionName, Sec.chrSectionName) AS chrSectionName
		FROM Content_Dynamic
		LEFT JOIN Content_Dynamic AS Sec ON Sec.ID=Content_Dynamic.idSection AND !Content_Dynamic.bSection
		WHERE Content_Dynamic.ID='" . $_REQUEST['id'] . "' AND !Content_Dynamic.bDeleted AND Content_Dynamic.idStatus=1
		","get page"));

	if($page['bSection']) {
		$idSection = $_REQUEST['id'];
	} else {
		$idSection = $page['idSection'];
	}

	// This is the section for the Navigation once I build the pages :-)
	
	//Daniel we need to fix this too much information
	
	
	$pages_result = do_mysql_query("
		select chrSectionName, chrPageTitle, bSection, idSection, ID
		from Content_Dynamic
		where !bDeleted and idStatus=1
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
	<form id="form1" name="form1" method="post" action="">
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
			foreach($pages_by_section[$section['ID']] as $page2) {
?>
									<option value="<?=$page2['ID']?>">&nbsp;&nbsp;&nbsp;<?=decode($page2['chrPageTitle'])?></a></option>
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
							<td width="900" colspan="5" height="10"></td>
						</tr>
						<tr>
							<td width="20">&nbsp;</td>
							<td width="640" valign="top">								
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td align="left" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<img src="<?=BASE_FOLDER?>images/wire-header-left.gif">
										</td>
										<td width="100%" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
<?php
	if ($_REQUEST['id'] == 25 ) {
?>
											<strong><span style="color:#282828;">Baseline Requirements</span></strong>
<?php
	} else {
?>
											<strong><span style="color:#282828;">Scripts Information</span></strong>
<?php
	}
?>

										</td>
										<td align="right" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<img src="<?=BASE_FOLDER?>images/wire-header-right.gif">
										</td>
									</tr>
									<tr>
										<td colspan="3" style="border:solid 1px #c0c1c2;">
											<div style="padding:10px;">
												<?=decode($page['txtSourceCode'])?>
											</div>
										</td>
									</tr>
								</table>
							</td>
							<td width="20">&nbsp;</td>
<!-- This is the right side of the page -->
							<td width="200" valign="top">
								<table width="100%" border="0" cellpadding="0" cellspacing="0" class="Navigation" style="padding-bottom:10px;">
									<tr>
										<td align="left" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<img src="<?=BASE_FOLDER?>images/wire-header-left.gif">
										</td>
										<td width="100%" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<strong><span style="color:#282828;">Scripts</span></strong>
										</td>
										<td align="right" background="<?=BASE_FOLDER?>images/wire-header-bg.gif">
											<img src="<?=BASE_FOLDER?>images/wire-header-right.gif">
										</td>
									</tr>
									<tr>
										<td colspan="3" style="border:solid 1px #c0c1c2;">
											<div style="padding:10px;">
												<ul style='margin: 0 -25px 0 -10px; list-style: none;'>
													<li><strong><a href="index.php">Home Page</a></strong></li>
													<li><strong><a href="?id=<?=$idSection?>"><?=decode($page['chrSectionName'])?></strong></a>
<?
				if(isset($pages_by_section[$idSection])) {
?>
													<ul style='margin: 0; padding: 7px; list-style: none; font-size: 10px;'>
<?
					foreach($pages_by_section[$idSection] as $row) {
?>
														<li class="<?=($row['ID'] == $_REQUEST['id'] ? 'rightNavActive' : 'rightNavInactive')?>">- <a href="?id=<?=$row['ID']?>" style='text-decoration: underline'><?=decode($row['chrPageTitle'])?></a></li>
<?
					}
?>
													</ul>
<?
				}
?>
													</li>
												</ul>
											</div>
										</td>
									</tr>
								</table>
							</td>
						<td width="20">&nbsp;</td>
					</tr>
				</table>
<?php
	include('docpages/doc_bottom.php');
?>
