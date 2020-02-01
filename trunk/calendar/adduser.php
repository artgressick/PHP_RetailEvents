<?php
	$BF = '../';
	$title = 'Add User';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');
	
	# Calendar Access Check
	if(!$_SESSION['bCalAccess']) { header("Location: ". $BF ."calendar/index.php"); die(); }
	
	// if this is a form submission
	if(isset($_POST['idUser'])) {
	
		$q = "UPDATE Users SET bCalAccess=1 WHERE ID=". $_POST['idUser'];
		database_query($q, "insert User");

		$info = fetch_database_query("SELECT chrEmail FROM Users WHERE ID=". $_POST['idUser'],"getting info");
		$upload_dir = $BF. "userfiles/".$info['chrEmail'];
		if (!is_dir($upload_dir)) {
			#chmod($BF ."userfiles/", 0777);
			mkdir($upload_dir, 0777);
		}
		//check if the directory is writable.
        if (!is_writeable("$upload_dir")) { chmod($upload_dir, 0777); }

		header("Location: users.php");
		die();
	}

	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('idUser', "You must choose a User.");
		
		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>
<?
	include($BF. 'components/list/sortList.php'); 
	include($BF. 'calendar/includes/top.php');
?>
<div style='padding: 10px;'>
		<div class="AdminTopicHeader">Add User</div>
		<div class="AdminInstructions2">Your are about to add a new user to the database. Once added you will need to send them their username and password.</div>
		
		<form id='idForm' name='idForm' method='post' action=''>

		<div id='errors'></div>

		<div class='form'>
			<div class='formHeader'>Add User <span class='Required'>(Required)</span></div>
			<select id='idUser' name='idUser'>
				<option value=''>-Add User-</option>
<?	$q = "SELECT ID,chrLastName,chrFirstName
		FROM Users
		WHERE !bDeleted AND !bCalAccess
		  AND ID NOT IN (SELECT ID FROM Users as U WHERE U.bCalAccess AND !bDeleted)
		ORDER BY chrLastName,chrFirstName";
	$results = database_query($q,"getting results");
	while($row = mysqli_fetch_assoc($results)) { ?>
				<option value='<?=$row['ID']?>'><?=$row['chrLastName'].", ".$row['chrFirstName']?>

<?	} ?>
			</select>
		</div>

		<div class='FormButtons'>
			<input type='button' name='SubmitAddUser' value='Save Users' onclick='error_check()' />
		</div>

		</form>
</div>
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom.php');
?>