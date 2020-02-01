<?php
	$BF = '../';
	$title = 'Presenters';
	$curPage='presenters';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<?
	// Set the title, and add the doc_top
	include($BF . 'includes/top_events.php');
?>

	<div class="AdminTopicHeader">Alert!!</div>
	<div style="border:1px solid silver; padding:10px;">
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					The workshop that you selected cannot be added to your calendar of events at this time.  This workshop will become available to schedule at a later date.  More details will be made available soon.<br /><br />
					Please note that the new baseline schedule will automatically be populated to your Calendar of Events for November.<br /><br />
					For questions or issues, please contact Tommy Nguyen at <a href="mailto:t.nguyen@apple.com" style="text-decoration:underline; color:#0000FF;">t.nguyen@apple.com</a>
				</td>
			</tr>
		</table>
	</div>				
					
					


					

<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>