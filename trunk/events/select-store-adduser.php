<?php
	$BF = "../";
	require($BF. "_lib2.php");
	$title = 'Add Person Poppup';
	include($BF. 'includes/meta2.php');
	$error_messages = array();

	// if this is a form submission
	if(count($_POST)) {
		// Check the fields for completeness/formatting
		//   If something is wrong, add an entry to the $error_messages array
		
		if($_POST['chrFirstName'] == '') { $error_messages[] = "You must enter the first name."; }
		if($_POST['chrLastName'] == '') { $error_messages[] = "You must enter the last name."; }
		if($_POST['chrEmail'] == '') { $error_messages[] = "You must enter the email.";	}
		
		$result = database_query("SELECT ID FROM Users WHERE chrEmail='" . $_POST['chrEmail'] . "' AND !bDeleted", 'look for dups');
		if(mysqli_num_rows($result)) {
			$error_messages[] = "There is already a user with that email address.";
		}

		// if everything is cool, create the new record
		if(count($error_messages) == 0) {
			database_query("INSERT INTO Users SET
				chrFirstName='" . encode($_POST['chrFirstName']) . "',
				chrLastName='" . encode($_POST['chrLastName']) . "',
				chrEmail='" . encode($_POST['chrEmail']) . "',
				chrPassword=MD5('" . $_POST['chrPassword'] . "')
				", 'create new user');

			global $mysqli_connection;
			$newID = mysqli_insert_id($mysqli_connection);
			
			header("Location: select-user.php?d=".$_POST['d']."&idSelected=". $newID ."&chrSelected=".str_replace("&",'&amp;',stripslashes(encode($_POST['chrFirstName']." ".$_POST['chrLastName']))));
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

	include($BF . 'includes/top_popup.php');
?>

	<table class='Tabs' style='margin-bottom: -3px;'>
			<tr>
				<td class=''><a href='select-user.php?d=<?=@$_REQUEST['d']?>'>Users</a></td>
				<td class='Current'><a href='#'>Create New User</a></td>
				<td class='TheRest'></td>
			</tr>
		</table>
			
	<div class='sectionInfo'>
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
					<input type='text' size='20' maxlength='30' name='chrFirstName' value='<?=@$f_chrFirstName?>' />
					</div>
	
				<div class='form'>
					<div class='formHeader'>Last Name <span class='Required'>(Required)</span></div>
					<input type='text' size='20' maxlength='40' name='chrLastName' value='<?=@$f_chrLastName?>' />
					</div>
	
				<div class='form'>
					<div class='formHeader'>Email <span class='Required'>(Required)</span></div>
					<input type='text' size='20' maxlength='80' name='chrEmail' value='<?=@$f_chrEmail?>' />
					</div>
	
				<div class='form'>
					<div class='formHeader'>Password</div>
					<input type='password' size='20' name='chrPassword' value='<?=@$f_chrPassword?>' />
					</div>
	
				<div class='FormButtons'>
					<input type='submit' name='SubmitAddUser' value='Save New User' />
					<input type='hidden' name='d' value='<?=$_REQUEST['d']?>' />
					</div>
					
				</form>
			</div>

		</div>
	</div>

<?
	include($BF. 'includes/bottom_popup.php');
?>
