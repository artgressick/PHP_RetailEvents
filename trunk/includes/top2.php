<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?=$title?> - Retail Marketing</title>
<link href="<?=$BF?>includes/css/style.css" rel="stylesheet" type="text/css" />
<script type='text/javascript' src="<?=$BF?>includes/javascript/javascript.js"></script>
	
<?php if(function_exists('insert_into_head')) insert_into_head(); ?>
</head>

<body <?=(isset($bodyParams) ? $bodyParams : '')?>>

  <table width="908" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td colspan="3"><a href='<?=$BF?>index.php' title='link to the main page'><img src="<?=$BF?>images/general-logo.gif" width="309" height="200" /><img src="<?=$BF?>images/general-main1.jpg" width="599" height="200" /></a></td>
    </tr>
    <tr>
      <td width="4" background="<?=$BF?>images/shadow-left.gif"><img src="<?=$BF?>images/shadow-left.gif" width="4" height="5" /></td>
      <td width="900" bgcolor="#ffffff">
<!--This is the log in bar which will be dynamic -->
	  <table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#f1f1f1" style='background: url("<?=$BF?>images/smoothbar.gif") repeat-x; height: 21px; vertical-align: top;'>
          <tr>
            <td valign="middle"><img src="<?=$BF?>images/smoothbar_arrow.gif" style='margin-top: -3px;' /></td>
<? if($_SESSION['idUser'] == '') { ?>
            <td width="100%" valign="middle"><span class="loginbar"><a href="signup.php">Retail Log In</a></span></td>
<?	} else { ?>
            <td width="100%" valign="middle"><span class="loginbar">Welcome <?=$_SESSION['chrFirstName']?> <?=$_SESSION['chrLastName']?> | <a href='<?=$BF?>profile.php'>My Profile</a> | <a href='?auth_destroy=1'>Log Out</a></span></td>
<?	} ?>

            <td align="right" nowrap="nowrap"></td>
          </tr>
        </table>
<!-- this is the end of the login bar -->
<!-- this is the main section of the site and will be used to modify -->
