<?php
	$auth_not_required = 1;
	require("_lib.php");
	
	$url = 'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
	// Set the title, and add the doc_top
	$title = "No Permission";
	require(BASE_FOLDER . 'docpages/doc_meta.php');
	require(BASE_FOLDER . 'docpages/doc_top.php');
?>

	<div style='margin: 10px;'>
	
		<div class="AdminTopicHeader">No Permission</div>
			<div class="AdminDirections" style='width: 870px;'>I'm sorry, but you do not have permission to access the page you were trying to get to.</div>
	
<?	if($_SESSION['chrReferer'] != '') { ?>
		<div>
			To get back to the last page you were at, please click: <a href='<?=$_SESSION['chrReferer']?>'>Here</a>
		</div>
<?	} ?>
	</div>
<?
	include(BASE_FOLDER . 'docpages/doc_bottom.php');
?>