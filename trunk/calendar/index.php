<?php
	$BF = '../';
	$title = 'Calendar Page';
	$curPage = "";
	require($BF. '_lib2.php');

	# Calendar Access Check
	$access = fetch_database_query("SELECT bCalAccess FROM Users WHERE ID=". $_SESSION['idUser'],"getting access");
	if($access['bCalAccess'] == 0) { 
		header('Location: '.$BF.'calendar/noaccess.php'); 
		die(); 
	} else {
		$_SESSION['bCalAccess'] = 1;
	}

	# Setting up initial variables to be used
	if(!isset($_REQUEST['dDate']) || $_REQUEST['dDate'] == '') {
		$intCurDay = date('d'); 	# int value of current day (ex: 30)
		$intCurMonth = date('m');	# int value of current month (ex: 7)
		$intCurYear = date('Y');	# int value of current year (ex: 2007)

		header("Location: ". $BF ."calendar/month.php?dDate=".$intCurYear.$intCurMonth.$intCurDay);
		die();
	} else {
		header("Location: ". $BF ."calendar/month.php?dDate=".  $_REQUEST['dDate']);
		die();
	}
?>