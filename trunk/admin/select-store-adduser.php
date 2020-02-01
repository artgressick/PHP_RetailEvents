<?php
	require_once('../_lib.php');

	$error_messages = array();

	// if this is a form submission
	if(count($_POST)) {
		// Check the fields for completeness/formatting
		//   If something is wrong, add an entry to the $error_messages array
		
		if($_POST['chrFirstName'] == '') { $error_messages[] = "You must enter the first name."; }
		if($_POST['chrLastName'] == '') { $error_messages[] = "You must enter the last name."; }
		if($_POST['chrEmail'] == '') { $error_messages[] = "You must enter the email.";	}
		
		$result = do_mysql_query("SELECT ID FROM Users WHERE chrEmail='" . $_POST['chrEmail'] . "' AND !bDeleted", 'look for dups');
		if(mysql_num_rows($result)) {
			$error_messages[] = "There is already a user with that email address.";
		}

		// if everything is cool, create the new record
		if(count($error_messages) == 0) {
			do_mysql_query("INSERT INTO Users SET
				chrFirstName='" . $_POST['chrFirstName'] . "',
				chrLastName='" . $_POST['chrLastName'] . "',
				chrEmail='" . $_POST['chrEmail'] . "',
				chrPassword=MD5('" . $_POST['chrPassword'] . "')
				", 'create new user');
			$new_id = mysql_insert_id();
			
			audit_new_record('Users', $new_id);
			
			header("Location: select-store-user.php?idSelected=" . $new_id);
			die();
		}

		// if there is an error, copy all of the submitted form data so that the form can fill it in.
		//  this is so that the form will have the (invalid/incomplete) form data that they already filled in
		foreach($_REQUEST as $k => $v) {
			$t = 'f_' . $k;
			$$t = $v;
		}
	} else {

		foreach($_REQUEST as $k => $v) {
			$t = 'f_' . $k;
			$$t = $v;
		}
	}

	$title = "Add Person Poppup";
	require(BASE_FOLDER . 'docpages/doc_meta.php');
	include(BASE_FOLDER . 'docpages/doc_top_poppup.php');	
?>

	<table class='Tabs' style='margin-bottom: -3px;'>
			<tr>
				<td class=''><a href='select-users.php?d=<?=@$_REQUEST['d']?>'>Users</a></td>
				<td class='Current'><a href='#'>Create New User</a></td>
				<td class='TheRest'></td>
			</tr>
		</table>
			
	<div class='sectionInfo' style='width: 500px;'>
		<div class='sectionHeader'>Add Store Users</div>
		<div class='sectionContent'>

		<div class='WithinTabs'>
			<form id='Form' method='post' action=''>

<? if(count($error_messages)) {
		foreach($error_messages as $error) { ?>
				<p class='ErrorMessage'><?=$error?></p>
<?		}
	} ?>

				<div class='form'>
					<div class='formHeader'>First Name <span class='Required'>(Required)</span></div>
					<input type='text' size='30' maxlength='30' name='chrFirstName' value='<?=@$f_chrFirstName?>' />
					</div>
	
				<div class='form'>
					<div class='formHeader'>Last Name <span class='Required'>(Required)</span></div>
					<input type='text' size='40' maxlength='40' name='chrLastName' value='<?=@$f_chrLastName?>' />
					</div>
	
				<div class='form'>
					<div class='formHeader'>Email <span class='Required'>(Required)</span></div>
					<input type='text' size='50' maxlength='80' name='chrEmail' value='<?=@$f_chrEmail?>' />
					</div>
	
				<div class='form'>
					<div class='formHeader'>Password</div>
					<input type='password' size='40' name='chrPassword' value='<?=@$f_chrPassword?>' />
					</div>
	
				<div class='FormButtons'>
					<input type='submit' name='SubmitAddUser' value='Save New User' />
					</div>
					
				</form>
			</div>

		</div>
	</div>

<?
	doc_bottom_popup();
?>
