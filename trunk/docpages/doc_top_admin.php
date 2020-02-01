<body <? if(function_exists('insert_body_params')) insert_body_params(); ?>>

  <table width="908" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td colspan="3"><a href='<?=BASE_FOLDER . 'index.php'?>' title='link to the main page'><img src="<?=BASE_FOLDER?>images/general-logo.gif" width="309" height="200" /><img src="<?=BASE_FOLDER?>images/general-main1.jpg" width="599" height="200" /></a></td>
    </tr>
    <tr>
      <td width="4" background="<?=BASE_FOLDER?>images/shadow-left.gif"><img src="<?=BASE_FOLDER?>images/shadow-left.gif" width="4" height="5" /></td>
      <td width="900" bgcolor="#ffffff">
	  
	  
<!--This is the log in bar which will be dynamic -->
	  <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f1f1f1" style='background: url("<?=BASE_FOLDER?>images/smoothbar.gif") repeat-x;'>
          <tr>
			<td>
				<img src="<?=BASE_FOLDER?>images/smoothbar_arrow.gif" />
			</td>
            <td style='' width="65%">

				<span class="loginbar" style=''>Welcome <?=$_SESSION['chrFirstName']?> <?=$_SESSION['chrLastName']?> | <a href='<?=BASE_FOLDER?>profile.php'>My Profile</a> | <a href='?auth_destroy=1'>Log Out</a></span>
			</td>
            <td align="right" nowrap="nowrap" width="32%">&nbsp;</td>
        </table>
		
		
<!-- this is the end of the login bar -->
<!-- this is the main section of the site and will be used to modify -->
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td width="900" colspan="5" height="10"></td>
            </tr>
			<tr>
              <td width="10">&nbsp;</td>
                