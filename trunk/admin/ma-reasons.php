<?php
	require("../_lib.php");

	$q = "SELECT Events.ID, chrTitle, chrName as chrType, chrStyleClass,
			DATE_FORMAT(dDate,'%M %D, %Y') as dDate, DATE_FORMAT(tBegin,'%l:%i %p') as tBegin, DATE_FORMAT(tEnd,'%l:%i %p') as tEnd 
			FROM Events 
			JOIN EventTypes ON EventTypes.ID=Events.idEventType  AND (idEventCategory=1 OR idEventCategory=2)
			WHERE idStore='" . $_REQUEST['idStore'] . "' AND MONTH(dDate)='" . $_REQUEST['intMonth'] . "' AND YEAR(dDate)='" . $_REQUEST['intYear'] . "' AND bApproved=0
			ORDER BY dDate ASC";
	$result = do_mysql_query($q,"getting info");

	if(count($_POST)) {
		while($row = mysql_fetch_assoc($result)) {
			if($_POST['txt'.$row['ID']] != '') {
				update_record('Events', 'txtRejection', $row['ID'], encode($_POST['txt'.$row['ID']]));
			}
		}
		
		header('Location: eventlist.php');
		die();
	}

	// Set the title, and add the doc_top
	$title = "COE Calendar Rejection";
	require(BASE_FOLDER . 'docpages/doc_meta.php');
	include(BASE_FOLDER . 'docpages/doc_top_admin.php');
	include(BASE_FOLDER . 'docpages/doc_nav_admin.php');
?>

<form method='post' action=''>
	
	<div class="AdminTopicHeader">Reasons for COE Rejection</div>
	<div class="AdminDirections" style='width: 690px;'>Please supply reasons for why these events were dissaproved</div>

	<form action='' method='post'>

	<table cellpadding="0" cellspacing="0" style='width: 100%;'>
<?	$count=0;
	while($row = mysql_fetch_assoc($result)) { ?>
		<tr>
			<td class="<?=$row['chrStyleClass']?>" style='width: 180px; font-size: 10px; padding: 5px 10px; <?=($count > 0 ? 'border-top: 1px solid gray;' : '')?>'>
				<div>
					<span style='font-size: 12px'><strong><?=$row['chrTitle']?></strong></span><br />
					<strong><?=$row['chrType']?></strong><br />
					<?=$row['dDate']?><br />
					<?=$row['tBegin']?> to <?=$row['tEnd']?> 
				</div>
			</td>
			<td style='width: 600px; border: none; <?=($count++ > 0 ? 'border-top: 1px solid gray;': '')?>'>
				<textarea name='txt<?=$row['ID']?>' cols='60' rows='2' style='font-size: 12px;'></textarea>			
			</td>
		</tr>
<?	} ?>
	</table>

	<div style='margin: 10px 0'>	
		<input type='submit' value='Submit Reasons' />
		<input type='hidden' value='<?=$_REQUEST['idStore']?>' name='idStore' />
		<input type='hidden' value='<?=$_REQUEST['intMonth']?>' name='intMonth' />
		<input type='hidden' value='<?=$_REQUEST['intYear']?>' name='intYear' />
	</div>
	
	</form>

<!-- Needed to close off the bottom of the page and the navigation on the left side -->
              </td>
              <td width="10">&nbsp;</td>
            </tr>
        </table>


	
<? include(BASE_FOLDER . 'docpages/doc_bottom.php'); ?>
