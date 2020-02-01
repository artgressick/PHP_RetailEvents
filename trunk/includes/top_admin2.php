<?php
	/* We need to do a permissions check on the drop down menus, the following is what type equals what
		idType of 1 = Super user 
		idType of 2 = Editor
		idType of 3 = Corp Events
		idType of 4 = Store

		Use the following in a short If statement:  ( in_array($_SESSION['idType'],array("1","2","3")) ? '' : '' )

	*/
?>
</head>
<body <?=(isset($bodyParams) ? 'onload="'. addslashes($bodyParams) .'"' : '')?>>

 <table width="908" border="0" align="center" cellpadding="0" cellspacing="0">
 	<tr>
		<td colspan="3">
		
				<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td><a href="<?=$BF?>index.php"><img src="<?=$BF?>images/index-logo.png" alt="Apple Retail Marketing" width="309" height="300" border="0" /></a></td>
<!--photo switcher-->
							<td>
								<span style="opacity: 1; visibility: visible;" id="crossfade"><span style="z-index: 100; opacity: 0;" class="fader">
<!---->
								<? $rand = mt_rand(1,17); ?>
								<img src="<?=$BF?>images/index-main<?=$rand?>.jpg" alt="" height="300" width="599" border="0" class='imgMargin'></span></span>
<!---->
								<script type="text/javascript">
								var imgs = rand_unique(1,17,4,<?=$rand?>);
								InitCrossFade('crossfade',
									'<span class="fader"><img src="<?=$BF?>images/index-main'+imgs[0]+'.jpg" width="599" height="300" class="imgMargin" alt=""></span>',
									'<span class="fader"><img src="<?=$BF?>images/index-main'+imgs[1]+'.jpg" width="599" height="300" class="imgMargin" alt=""></span>',
									'<span class="fader"><img src="<?=$BF?>images/index-main'+imgs[2]+'.jpg" width="599" height="300" class="imgMargin" alt=""></span>',
									'<span class="fader"><img src="<?=$BF?>images/index-main'+imgs[3]+'.jpg" width="599" height="300" class="imgMargin" alt=""></span>',
									'<span class="fader"><img src="<?=$BF?>images/index-main'+imgs[4]+'.jpg" width="599" height="300" class="imgMargin" alt=""></span>');
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
	  		<table width="900" border="0" cellpadding="0" cellspacing="0" bgcolor="#f1f1f1" style='background: url("<?=$BF?>images/smoothbar.gif") repeat-x;'>
          		<tr>
					<td><img src="<?=$BF?>images/smoothbar_arrow.gif" /></td>
					<td width="65%"><span class="loginbar" style=''>Welcome <?=$_SESSION['chrFirstName']?> <?=$_SESSION['chrLastName']?> | <a href='<?=$BF?>profile.php'>My Profile</a> | <a href='?logout=1'>Log Out</a><?=(in_array($_SESSION['idType'],array("1","2","3")) ? " | <a href='".$BF."admin/'>Administration Console</a>" : "")?></span></td>
					<td align="right" nowrap="nowrap">
						<div class="navstyle" id="nav">
							<ul>
								<?=( in_array($_SESSION['idType'],array("1","2","3")) ? '<li> | <a href="#" id="id-dropmenu1" rel="dropmenu1">Data Administration</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","3")) ? '<li> | <a href="#" id="id-dropmenu2" rel="dropmenu2">Content Management</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","2","3")) ? '<li> | <a href="#" id="id-dropmenu3" rel="dropmenu3">Reports</a></li>' : '' )?>
							</ul>
						</div>
<?
	if (in_array($_SESSION['idType'],array("1","2","3"))) {
?>
						<!--1st drop down menu -->                                               
						<div id="dropmenu1" class="dropmenudiv">
							<ul>
								<?=( in_array($_SESSION['idType'],array("1")) ? '<li><a href="'.$BF.'admin/index.php">Apple Retail Stores</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1")) ? '<li><a href="'.$BF.'admin/users.php">Apple Store Employees</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","2","3")) ? '<li><a href="'.$BF.'events/">Stores COE</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","2")) ? '<li><a href="'.$BF.'admin/reviews.php">COE Editor Review</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1")) ? '<li><a href="'.$BF.'admin/eventlist.php">COE Calendar Approval</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","2","3")) ? '<li><a href="'.$BF.'admin/recaplist.php">Completed Recaps</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1")) ? '<li><a href="'.$BF.'admin/protour1.php">Pro Tour Special Event</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","2")) ? '<li><a href="'.$BF.'admin/products.php">Products</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1")) ? '<li><a href="'.$BF.'admin/eventtypes.php">Workshop/Event Types</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","2")) ? '<li><a href="'.$BF.'admin/eventdescriptions.php">Workshop/Event Descriptions</a></li>' : '' )?>
							</ul>
						</div>
<?
	}
	
	if (in_array($_SESSION['idType'],array("1","3"))) {
?>
						<!--2st drop down menu -->                                                   
						<div id="dropmenu2" class="dropmenudiv">
							<ul>
								<?=( in_array($_SESSION['idType'],array("1","3")) ? '<li><a href="'.$BF.'admin/static.php?id=1">Home Page</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","3")) ? '<li><a href="'.$BF.'admin/static.php?id=2">Contact Us</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","3")) ? '<li><a href="'.$BF.'admin/static.php?id=3">Marketing Team</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","3")) ? '<li><a href="'.$BF.'admin/sections.php">Sections</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","3")) ? '<li><a href="'.$BF.'admin/dynamic.php">Dynamic Page</a></li>' : '' )?>
							</ul>
						</div> 
<?
	}
	
	if (in_array($_SESSION['idType'],array("1","2","3"))) {
?>
						<!--3st drop down menu -->                                                   
						<div id="dropmenu3" class="dropmenudiv">
							<ul>
								<?=( in_array($_SESSION['idType'],array("1","2","3")) ? '<li><a href="'.$BF.'admin/report/wordsearchreport.php">Word Search Report</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","2","3")) ? '<li><a href="'.$BF.'admin/report/wqreport.php">Weekly/Quarterly Report</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","2","3")) ? '<li><a href="'.$BF.'admin/report/superreport.php">Super Report</a></li>' : '' )?>
								<?=( in_array($_SESSION['idType'],array("1","2","3")) ? '<li><a href="'.$BF.'admin/report/myreport.php">My Reports</a></li>' : '' )?>
	
							</ul>
						</div> 
<?
	}
?>					
						<script type="text/javascript">dropdown.startnav("nav")</script>
            		</td>
        		</tr>
			</table>		
<!-- this is the end of the login bar -->
<!-- this is the main section of the site and will be used to modify -->
			<table cellspacing="0" cellpadding="0" border="0" width="100%" style="padding:10px;">
				<tr>
              		<td>