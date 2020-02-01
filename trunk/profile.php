<?php
	require_once('_lib.php');

	$error_messages = array();

	// if this is a form submission
	if(count($_POST)) {
		// Check the fields for completeness/formatting
		//   If something is wrong, add an entry to the $error_messages array

		if($_POST['chrEmail'] == '') { $error_messages["email"] = "You must enter an email address.";}
		
		if($_POST['chrFirstName'] == '') { $error_messages["firstname"] = "You must enter a First Name."; }
		if($_POST['chrLastName'] == '') { $error_messages["lastname"] = "You must enter a Last Name."; }
		if($_POST['chrPassword1'] != "" && ($_POST['chrPassword1'] != $_POST['chrPassword2'])) { $error_messages["password"] = "The passwords do not match.";	}

		// if everything is cool, create the new record
		if(count($error_messages) == 0) {
			update_record('Users', 'chrFirstName', $_POST['id'], encode($_POST['chrFirstName']));
			update_record('Users', 'chrLastName', $_POST['id'], encode($_POST['chrLastName']));
			update_record('Users', 'chrEmail', $_POST['id'], encode($_POST['chrEmail']));
			if($_POST['chrPassword1'] != "") { do_mysql_query("UPDATE Users SET chrPassword=MD5('" . $_POST['chrPassword1'] . "') WHERE ID=" . $_POST['id'],"update password"); }
		
			if($_POST['id'] == $_SESSION['idUser']) {
				$_SESSION['chrEmail'] = $_POST['chrEmail'];
				$_SESSION['chrFirstName'] = $_POST['chrFirstName'];
				$_SESSION['chrLastName'] = $_POST['chrLastName'];
				$_SESSION['chrTimeFormat'] = $_POST['chrTimeFormat'];
			}
	
			//$_SESSION['InfoMessage'][] = 'Changes to your profile have been saved.';
			header("Location: " . $_POST['referer']);
			exit();
		}
			
		// if there is an error, copy all of the submitted form data so that the form can fill it in.
		//  this is so that the form will have the (invalid/incomplete) form data that they already filled in
			$info = $_POST;
	} else {

		// if there is an error, copy all of the submitted form data so that the form can fill it in.
		$info = mysql_fetch_assoc(do_mysql_query("SELECT Users.chrFirstName, Users.chrLastName, Users.chrEmail, UserTypes.chrType
			FROM Users 
			LEFT JOIN UserTypes ON UserTypes.ID=Users.idType 
			WHERE Users.ID='" . $_SESSION['idUser'] . "'","getting user info"));
	}
	if(!isset($info)) { $info = 0; } 
	

// Set the title, and add the doc_top
	$title = "My Profile";
	require(BASE_FOLDER . 'docpages/doc_meta_home.php');
	require(BASE_FOLDER . 'docpages/doc_top.php');
?>

	<div style='margin: 10px;'>

	<div class="AdminTopicHeader">My Profile</div>
		<div class="AdminDirections" style='width: 870px;'>After editing your personal information, click on the "Update Information" button at the bottom of the page.</div>

		<form id='Form' method='post' action=''>
	
<? if(count($error_messages)) {
		foreach($error_messages as $error) { ?>
			<p class='ErrorMessage'><?=$error?></p>
<?		}
	} ?>

<?	if(@count($_SESSION['InfoMessage'])) { ?>
	<div class='Messages'>
<?		foreach($_SESSION['InfoMessage'] as $msg) { ?>
						<div class='InfoMessage'><?=$msg?></div>
<?		}
		$_SESSION['InfoMessage'] = array(); ?>
	</div>
<? } ?>

		<div class='sectionInfo'>
			<div class='noHeader'>

				<div class='form'>
					<div class='formHeader'>Email <span class='Required'>(Required)</span></div>
					<input type='text' size='40' maxlength='80' name='chrEmail' value='<?=@$info['chrEmail']?>' />
					</div>
	
				<div class='form'>
					<div class='formHeader'>First Name <span class='Required'>(Required)</span></div>
					<input type='text' size='20' maxlength='30' name='chrFirstName' value='<?=@$info['chrFirstName']?>' />
					</div>
				
				<div class='form'>
					<div class='formHeader'>Last Name <span class='Required'>(Required)</span></div>
					<input type='text' size='20' maxlength='40' name='chrLastName' value='<?=@$info['chrLastName']?>' />
					</div>
	
				<div class='form'>
					<div class='formHeader'>Security Level</div>
					<?=$info['chrType']?>
				</div>
	

				<div class='form'>
					<div class='formHeader'>Change Password</div>
					<input type='password' size='30' maxlength='40' name='chrPassword1' /><br />
					<input type='password' size='30' maxlength='40' name='chrPassword2' /> (confirm)
					</div>
	
				<div class='FormButtons'>
					<input type='hidden' name='id' value='<?=$_SESSION['idUser']?>' />
					<input type='hidden' name='referer' value="<?=$_SERVER['HTTP_REFERER']?>" />
					<input type='submit' name='SubmitEditUser' value='Update Information' />
					</div>
				</fieldset>
			</form>

</div>

<? 	require(BASE_FOLDER . 'docpages/doc_bottom.php'); ?>
