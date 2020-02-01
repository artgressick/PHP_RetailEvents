<?php
	$BF = '';
	$title = 'Error!';
	require($BF. '_lib2.php');

	// Set the title, and add the doc_top
	include($BF. 'includes/meta2.php');
	include($BF. 'includes/top_events.php');


if (count($_POST)) {
		if($_SESSION['idType'] == 4) { //if store user redirect to events else redirect to admin
			header('Location: ' . $BF . 'events/index.php');
			die();
		} else if($_SESSION['idType'] == 1) {
			header('Location: ' . $BF . 'admin/index.php');
			die();
		} else if($_SESSION['idType'] == 2) {
			header('Location: ' . $BF . 'admin/reviews.php');
			die();
		} else if($_SESSION['idType'] == 3) {
			header('Location: ' . $BF . 'admin/recaplist.php');
			die();
		}
}
?>				
		<div style='padding:5px;'>

		<div class="AdminTopicHeader"><span style="color:red;">Error!</span></div>

		<form id='idForm' name='idForm' method='post' action=''>

		<table>
			<tr><td>
			<div><strong>An Error as occured! This is usually due to missing or incomplete information.  Please try again.</strong></div>
			
			<div><input type="button" id="back" name="back" value="Back" onclick="javascript: history.go(-1)" />&nbsp;&nbsp;&nbsp;<input type="submit" id="submit" name="submit" value="Home" /></div>
			</td></tr>
		</table>

		</form>
		</div>
<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom.php');
?>