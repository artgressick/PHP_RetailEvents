<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?=$title?> - Retail Marketing</title>
<link href="<?=BASE_FOLDER?>includes/css/style.css" rel="stylesheet" type="text/css" />
<script type='text/javascript' src="<?=BASE_FOLDER?>includes/javascript/javascript.js"></script>
	
<?php if(function_exists('insert_into_head')) insert_into_head(); ?>
</head>

<body <? if(function_exists('insert_body_params')) insert_body_params(); ?>>

	<table width="908" border="0" align="center" cellpadding="0" cellspacing="0">
		<tr>
			<td colspan="3">
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td><a href="index.php"><img src="images/general-logo.png" alt="Apple Retail Marketing" width="309" height="200" border="0" /></a></td>
						<td><img src="<?=BASE_FOLDER?>images/general-main1.jpg" width="599" height="200" />	</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td width="4"></td>
      		<td width="900" bgcolor="#ffffff">
<!--This is the log in bar which will be dynamic -->
	  			<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#f1f1f1" style='background: url("images/smoothbar.gif") repeat-x; height: 21px; vertical-align: top;'>
          			<tr>
            			<td valign="middle"><img src="<?=BASE_FOLDER?>images/smoothbar_arrow.gif" style='margin-top: -3px;' /></td>
<? if($_SESSION['idUser'] == '') { ?>
            			<td width="100%" valign="middle"><span class="loginbar"><a href="signup.php">Retail Log In</a></span></td>
<?	} else { ?>
            			<td width="100%" valign="middle"><span class="loginbar">Welcome <?=$_SESSION['chrFirstName']?> <?=$_SESSION['chrLastName']?> | <a href='<?=BASE_FOLDER?>profile.php'>My Profile</a><?=($_SESSION['idLevel'] == "1" ? " | <a href='".BASE_FOLDER."admin/'>Administration Console</a>" : "")?> | <a href='?auth_destroy=1'>Log Out</a></span></td>
<?	} ?>

            			<td align="right" nowrap="nowrap"></td>
          			</tr>
        		</table>
<!-- this is the end of the login bar -->
<!-- this is the main section of the site and will be used to modify -->
