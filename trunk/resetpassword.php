<?php
	$auth_not_required = true;
	require_once('_lib.php');

	$error_messages = array();

	if(count($_POST)) {
		if(isset($_POST['d'])) { // if this was a submission of the final form
			parse_str(base64_decode($_POST['d']), $data);

			$query = "SELECT * FROM Users WHERE chrEmail='" . $data['chrEmail'] . "' AND chrLostPasswordSpecial='" . $data['special'] . "'";
			$result = do_mysql_query($query, 'find account');
			$row = mysql_fetch_assoc($result);


			if($row === false) {
				// if they gave the wrong special, clean the account of any special that exists.
				// this is a safeguard from crackers
				$query = "UPDATE Users SET chrLostPasswordSpecial=NULL WHERE chrEmail='" . $data['chrEmail'] . "'";
				$result = do_mysql_query($query, 'clear account');

				$_SESSION['ErrorMessage'][] = 'The Lost Password Request you clicked was invalid.  Please fill out this form to send a new Request.'; 
				header("Location: resetpassword.php?chrEmail=" . $data['chrEmail']);
				exit();

			} else {
				// make sure it isn't over 25 hours old (give them extra time to fill in the form)
				if(strtotime($row['dtLostPasswordRequest']) < strtotime('NOW-25 HOURS')) {
					$_SESSION['ErrorMessage'][] = 'The Lost Password Request you clicked has expired (it must be used within 24 hours).  Please fill out this form to send a new Request.'; 
					header("Location: resetpassword.php?chrEmail=" . $data['chrEmail']);
					exit();
				}
			}

			if($_POST['chrPassword1'] != $_POST['chrPassword2']) {
				$error_messages[] = "The passwords you entered do not match."; 
			} else {
	
				$query = "UPDATE Users SET chrPassword=MD5('" . $_POST['chrPassword1'] . "'), chrLostPasswordSpecial=NULL WHERE chrEmail='" . $data['chrEmail'] . "'";
				$result = do_mysql_query($query, 'change password');
		
				$_SESSION['InfoMessage'][] = 'Your password has been changed.'; 
				header("Location: " . BASE_FOLDER . "./?chrEmail=" . urlencode($data['chrEmail']));
				exit();
			}

		} else { // if this is a submission of the first form

			$query = "SELECT * FROM Users WHERE chrEmail='" . $_POST['chrEmail'] . "'";
			$result = do_mysql_query($query, 'get account');
			$row = mysql_fetch_assoc($result);
	
			if($row) {
				// create a password change request
				$special = mt_rand(100000000, 9999999999);
				$query = "UPDATE Users SET chrLostPasswordSpecial='" . $special . "', dtLostPasswordRequest=NOW() WHERE ID='" . $row['ID'] . "'";
				$result = do_mysql_query($query, 'create special');
	
				//send the user their password
				$Headers = "From: Retail Events <retailevents@apple.com>\r\n";
	
				$Subject = "Forgot Your Password?";
	
				$Message = "Someone (hopefully you!) notified us that you have forgotten your password to the Retail Events Website.\n\n";
				$Message .= "To change your password, click the following link (or copy and paste it into your browser's address bar):\n\n";
				$Message .= "    http://retailmarketing.apple.com/resetpassword.php?d=" . base64_encode("special=" . $special . "&chrEmail=" . urlencode($_REQUEST['chrEmail'])) . "\n\n";
				$Message .= "Please note, you must use this link in the next 24 hours or it will be disabled, and you will have to place another Lost Password Request.\n";
	
				mail($_POST['chrEmail'], $Subject, $Message, $Headers);
	
				$_SESSION['InfoMessage'][] = 'An email has been sent to you with instructions to change your account password.';
				header("Location: ./");
				exit();				
			} else {
				$error_messages[] = "There is no account with that email address.";
			}
		}
	}

	// Set the title, and add the doc_top
	$title = "Forgot Password";
	require(BASE_FOLDER . 'docpages/doc_meta_home.php');
	require(BASE_FOLDER . 'docpages/doc_top.php');	
?>

<div style='padding: 10px;'>

<div class="Messages">
<? if(count($error_messages)) {
		foreach($error_messages as $error) { ?>
			<div class='ErrorMessage'><?=$error?></div>
<?		}
	} ?>
</div>


	<h1>Forgot My Password</h1>

<?	if(isset($_REQUEST['d'])) { // provide the form to actually change the password
?>
	<div class='Instructions'>
		To change your password, enter your new password twice in the form below.
		</div>

	<form action='' method='post'>
		<fieldset>
			<div class='Field'><div class="L10">Your New Password</div>
				<input name="chrPassword1" type="password" size="30" />
				</div>
		
			<div class='Field'><div class="L10">(Confirm)</div>
				<input name="chrPassword2" type="password" size="30" />
				</div>
		
			<div class='FormButtons'>
				<input type='hidden' name='d' value='<?=$_REQUEST['d']?>' />
				<input type="submit" value="Change My Password" />
				<input type='button' value='Cancel' onclick='location.href="./";'>
				</div>
			</fieldset>
		</form>


<?	} else { // provide the form to submit request
?>
	<div class='Instructions'>
		To request a new password, enter your email address in the form below.  An email will be sent to your email address with instructions to change your password.
		</div>

	<form action='' method='post'>
		<fieldset>
			<div class='Field'><div class="L10">Your Email Address <span class="Required">(Required)</span></div>
				<input name="chrEmail" type="text" size="40" maxlength="50"<?=(isset($_REQUEST['chrEmail'])?' value="' . $_REQUEST['chrEmail'] . '"':'')?> />
				</div>
		
			<div class='FormButtons'>
				<input type="submit" value="Submit Request" />
				<input type='button' value='Cancel' onclick='location.href="./";'>
				</div>
			</fieldset>
		</form>

	</div>
<?	} ?>

<?	include('docpages/doc_bottom.php'); ?>
