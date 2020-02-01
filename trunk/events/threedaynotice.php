<?php
	$BF = '../';
	$title = '3 Day Notice';
	$curPage = "coe";
	require($BF. '_lib2.php');
	include($BF. 'includes/meta.php');


	if(!isset($_SESSION['idType'])) { $_SESSION['idType'] = 0; }

	include($BF. 'includes/top_events.php');
?>

	<div style='margin: 10px;'>

		<div class="AdminTopicHeader">3 Day Notice!</div>
			<div class="AdminDirections" style='width: 870px;'>Event due to begin within the next 3 days.</div>

	
	<div id='errors'></div>

	<form id='idForm' name='idForm' method='post' action='' enctype="multipart/form-data">

	<div style='margin: -7px 0 3px 0; background-color: #FFFF99; padding:15px;'>One or more of the Events changed will be occuring within the next 3 days. Usually the update on apple.com/retail will be processed within 24-48 hours. An E-mail has been sent to the retail web team to alert them of this change. If your change is urgent, or if you need the change processed over the weekend, we suggest you call Tommy Nguyen at 408.623.1896.</div>

		<div class='FormButtons'>


			<input type='button' value='OK' onclick="javascript: location.href='index.php?idStore=<?=$_REQUEST['idStore']?>&intDate=<?=$_REQUEST['intDate']?>';" />

			</div>
		</div>

	</form>
		
	</div>


<?
	include($BF. 'includes/bottom.php');
?>