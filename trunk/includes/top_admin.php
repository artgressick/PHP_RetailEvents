</head>
<body <?=(isset($bodyParams) ? 'onload="'. addslashes($bodyParams) .'"' : '')?>>

 <table width="908" border="0" align="center" cellpadding="0" cellspacing="0">
 	<tr>
		<td colspan="3"><a href='<?=$BF?>index.php' title='link to the main page'><img src="<?=$BF?>images/general-logo.gif" width="309" height="200" /><img src="<?=$BF?>images/general-main1.jpg" width="599" height="200" /></a></td>
	</tr>
    <tr>
    	<td width="4" background="<?=$BF?>images/shadow-left.gif"><img src="<?=$BF?>images/shadow-left.gif" width="4" height="5" /></td>
      	<td width="900" bgcolor="#ffffff">
	  
<!--This is the log in bar which will be dynamic -->
	  		<table width="900" border="0" cellpadding="0" cellspacing="0" bgcolor="#f1f1f1" style='background: url("<?=$BF?>images/smoothbar.gif") repeat-x;'>
          		<tr>
					<td><img src="<?=$BF?>images/smoothbar_arrow.gif" /></td>
					<td width="65%"><span class="loginbar" style=''>Welcome <?=$_SESSION['chrFirstName']?> <?=$_SESSION['chrLastName']?> | <a href='<?=$BF?>profile.php'>My Profile</a> | <a href='?logout=1'>Log Out</a><?=(in_array($_SESSION['idType'],array("1","2","3")) ? " | <a href='".$BF."admin/'>Administration Console</a>" : "")?></span></td>
					<td align="right" nowrap="nowrap">
						<div class="navstyle" id="nav">
							<ul>
								<li> | <a href="#" id="id-dropmenu1" rel="dropmenu1">Data Administration</a></li>
								<li> | <a href="#" id="id-dropmenu2" rel="dropmenu2">Content Management</a></li>
							</ul>
						</div>
						<!--1st drop down menu -->                                                   
						<div id="dropmenu1" class="dropmenudiv">
							<ul>
								<li><a href="<?=$BF?>admin/index.php">Apple Retail Stores</a></li>
								<li><a href="<?=$BF?>admin/users.php">Apple Store Employees</a></li>
								<li><a href="<?=$BF?>events/">Stores COE</a></li>
								<li><a href="<?=$BF?>admin/reviews.php">COE Editor Review</a></li>
								<li><a href="<?=$BF?>admin/eventlist.php">COE Calendar Approval</a></li>
								<li><a href="<?=$BF?>admin/recaplist.php">Completed Recaps</a></li>
								<li><a href="<?=$BF?>admin/protour1.php">Pro Tour Special Event</a></li>
								<li><a href="<?=$BF?>admin/products.php">Products</a></li>
								<li><a href="<?=$BF?>admin/eventtypes.php">Event Types</a></li>
								<li><a href="<?=$BF?>admin/eventdescriptions.php">Event Descriptions</a></li>
							</ul>
						</div>

						<!--2st drop down menu -->                                                   
						<div id="dropmenu2" class="dropmenudiv">
							<ul>
								<li><a href="<?=$BF?>admin/static.php?id=1">Home Page</a></li>
								<li><a href="<?=$BF?>admin/static.php?id=2">Contact Us</a></li>
								<li><a href="<?=$BF?>admin/static.php?id=3">Marketing Team</a></li>
								<li><a href="<?=$BF?>admin/sections.php">Sections</a></li>
								<li><a href="<?=$BF?>admin/dynamic.php">Dynamic Page</a></li>
							</ul>
						</div> 
						<script type="text/javascript">dropdown.startnav("nav")</script>
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
              		<td style='padding: 0 0 10px 10px;'>
                