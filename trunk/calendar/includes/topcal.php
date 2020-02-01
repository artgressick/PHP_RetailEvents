	<script type='text/javascript' src="<?=$BF?>calendar/includes/calendar.js"></script>
</head>
<body <?=(isset($bodyParams) ? 'onload="'. addslashes($bodyParams) .'"' : '')?>>

<?
	# This is needed for the info on the left side.  Doesn't seem to work correctly without a page redirect?
	if(!isset($_COOKIE['caltypebox'])) {
		setcookie('caltypebox','show');
		setcookie('subscribebox','hide');
		setcookie('userbox','hide');
		header("Location: ". $_SERVER['REQUEST_URI']);
		die();
	}
?>

 <table width="908" border="0" align="center" cellpadding="0" cellspacing="0">
 	<tr>
		<td colspan="3"><a href='<?=$BF?>index.php' title='link to the main page'><img src="<?=$BF?>images/calendar-logo.gif" width="309" height="150" /><img src="<?=$BF?>images/calendar-main.jpg" width="599" height="150" /></a></td>
	</tr>
    <tr>
    	<td width="4" background="<?=$BF?>images/shadow-left.gif"><img src="<?=$BF?>images/shadow-left.gif" width="4" height="5" /></td>
      	<td width="900" bgcolor="#ffffff">
	  
<!--This is the log in bar which will be dynamic -->
	  		<table width="900" border="0" cellpadding="0" cellspacing="0" bgcolor="#f1f1f1" style='background: url("<?=$BF?>images/smoothbar.gif") repeat-x;'>
          		<tr>
					<td><img src="<?=$BF?>images/smoothbar_arrow.gif" /></td>
					<td width="65%"><span class="loginbar" style=''>Welcome <?=$_SESSION['chrFirstName']?> <?=$_SESSION['chrLastName']?> | <a href='<?=$BF?>profile.php'>My Profile</a> | <a href='<?=$BF?>calendar/<?=$_SESSION['calSection']?>.php?dBegin=<?=(!isset($_REQUEST['dBegin']) ? $_SESSION['calDate'] : $_REQUEST['dBegin'])?>'>Calendar View</a> | <a href='?logout=1'>Log Out</a></span></td>
					<td align="right" nowrap="nowrap">
						<div class="navstyle" id="nav">
							<ul>
								<li><a href="#" id="id-dropmenu1" rel="dropmenu1">Data Administration</a></li>
							</ul>
						</div>
						<!--1st drop down menu -->                                               
						<div id="dropmenu1" class="dropmenudiv">
							<ul>
								<li><a href="<?=$BF?>calendar/month.php">Calendars</a></li>
								<li><a href="<?=$BF?>calendar/users.php">Calendar Users</a></li>
								<li><a href="<?=$BF?>calendar/caltypes.php">Calendar Types</a></li>
							</ul>
						</div>
	
						<script type="text/javascript">dropdown.startnav("nav")</script>
            		</td>
        		</tr>
			</table>		


	<div style='margin: 10px;'>		
	

	

	<table cellpadding='0' cellspacing='0' class='calframe'>
		<tr>
			<td id='calmenu' class='calmenu'>
				<form method="post" action="">

	
				<div class='clickTitle' onclick='miniMenuDisplay("caltype")'>Calendar Types</div>
				<div id='caltypebox' class='clickBox' style='display: <?=($_COOKIE['caltypebox'] == 'show' ? '' : 'none')?>;'>
<?	
	$results = database_query("SELECT ID,chrCalendarType,chrColorText,chrColorBG 
		FROM CalendarTypes 
		WHERE !bDeleted
		ORDER BY chrCalendarType
	","cal types"); 
	while($row = mysqli_fetch_assoc($results)) { 
?>
					<div style='background: <?=$row['chrColorBG']?>; color: <?=$row['chrColorText']?>;'><input<?=($_SESSION['idCalTypes'] == "" ? ' checked="checked"' : (preg_match('/(^|,)'.$row['ID'].'(,|$)/',$_SESSION['idCalTypes']) ? ' checked="checked"' : ''))?> type='checkbox' name='idCalTypes[]' value='<?=$row['ID']?>' /> <?=$row['chrCalendarType']?></div>
<?	} ?>
					<input type='submit' value='Show Calendars' style='margin-top: 10px;'>
				</div>


				<div class='clickTitle' onclick='miniMenuDisplay("user")'>Users</div>
				<div id='userbox' class='clickBox' style='display: <?=($_COOKIE['userbox'] == 'show' ? '' : 'none')?>;'>
<?	
	$results = database_query("SELECT ID,chrFirstName,chrLastName FROM Users WHERE !bDeleted AND bCalAccess ORDER BY chrLastName,chrFirstName","get cal users"); 
	while($row = mysqli_fetch_assoc($results)) { 
?>
					<div><input<?=($_SESSION['idCalUsers'] == "" ? ' checked="checked"' : (preg_match('/(^|,)'.$row['ID'].'(,|$)/',$_SESSION['idCalUsers']) ? ' checked="checked"' : ''))?> type='checkbox' name='idCalUsers[]' value='<?=$row['ID']?>' /> <?=$row['chrLastName'].", ".$row['chrFirstName']?></div>
<?	} ?>
					<input type='submit' value='Show Users' style='margin-top: 10px;'>
				</div>


				<div class='clickTitleBottom' onclick='miniMenuDisplay("subscribe")'>Subscribe</div>
				<div id='subscribebox' class='clickBoxBottom' style='display: <?=($_COOKIE['subscribebox'] == 'show' ? '' : 'none')?>;'>
					<a href='<?=$BF?>calendar/makeical.php'><img src="<?=$BF?>calendar/images/ical_subscription.png"></a>
					To subscribe to your calendar please click on the image above.
				</div>

			
		


				</form>
			</td>
			<td class='calcontent'>	