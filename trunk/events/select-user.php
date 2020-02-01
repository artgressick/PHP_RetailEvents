<?php
	$BF = "../";
	require($BF. "_lib2.php");
	$title = 'Add Person Poppup';
	include($BF. 'includes/meta2.php');

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLastName,chrFirstName"; }

	$q = "SELECT Users.chrFirstName, Users.chrLastName,chrEmail, Users.ID FROM Users 
			LEFT JOIN ACL ON ACL.idUser=Users.ID AND 
				(ACL.enType='Special' AND ACL.chrSpecial='Corporate')
			WHERE !bDeleted AND ACL.ID IS NULL ";
			if(@$_REQUEST['chrSearch'] != '') {	
	
		$q .= "AND ((chrFirstName LIKE '%" . $_REQUEST['chrSearch'] . "%') OR 
				(chrLastName LIKE '%" . $_REQUEST['chrSearch'] . "%') OR 
				(chrEmail LIKE '%" . $_REQUEST['chrSearch'] . "%'))";
			}
		$q .= "ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];

		$user_result = database_query($q, 'get users');

	// parse the popup data
	parse_str(base64_decode($_REQUEST['d']), $data);

	if(isset($_REQUEST['idSelected']) && $_REQUEST['idSelected'] != "") {
		$bodyParams = 'onload="associate(\''.$_REQUEST['idSelected'].'\', \''.$_REQUEST['chrSelected'].'\');"';
	}

	global $data;
?>
<script type="text/javascript">
	function associate(id, entryname) {

<?
	$lvls = database_query("SELECT * FROM Levels WHERE ID>2","getting levels");
	$levels = '<select name="enPermission" id="enPermission"><option value="">-Select Level-</option>';
	while($row = mysqli_fetch_assoc($lvls)) {
		if($row['chrName'] == 'Regional Director' && !$_SESSION['idType']) {
//			continue;
		}
		$levels .= '<option value="'. encode($row['chrName'],amp) .'">'. encode($row['chrName'],amp) .'</option>';
	}
	$levels .= '</select>';
?>

		dad = window.opener.document;
		var level = '<?=$levels?>';
		var tmplevel = level.replace(/enPermission/g,"enPermission"+id);
<?
		if(isset($data['functioncall'])) {
?>
			window.opener.<?=$data['functioncall']?>(id, entryname, tmplevel);
<?
		}
?>
		window.close();
	}
</script>

<?
	include($BF . 'includes/top_popup.php');
?>


	<table class='Tabs' style='margin-bottom: -3px;'>
		<tr>
			<td class='Current'><a href='#'>Users</a></td>
			<td class=''><a href='select-store-adduser.php?d=<?=$_REQUEST['d']?>'>Create New User</a></td>
			<td class='TheRest'></td>
		</tr>
	</table>
	<div class='sectionInfo'>
		<div class='sectionHeader'>Select Store Users</div>
		<div class='sectionContent'>

		<div style='padding-bottom: 10px;'>
		<form action='' method='get'>
			<input type='text' name='chrSearch' id='DocLoadFocus' value='<?=@$_REQUEST['chrSearch']?>' />
			<input type='hidden' name='d' value='<?=@$_REQUEST['d']?>' />
			<input type='submit' value='Search' />
			</form>
		</div>

		<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
			<tr>
<?
				$extra = "d=".$_REQUEST['d']."&chrSearch=".@$_REQUEST['chrSearch'];
				sortList('First Name', 'chrFirstName','',$extra);
				sortList('Last Name', 'chrLastName','',$extra);
				sortList('Email Address', 'chrEmail','',$extra);
?>
			</tr>
<?
		$count = 0;
		while($row = mysqli_fetch_assoc($user_result)) {
			$link = 'associate("'.$row['ID'].'", "'.$row['chrFirstName'].' '.$row['chrLastName'].'");';
?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
						onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrFirstName']?></td>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrLastName']?></td>
				<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['chrEmail']?></td>
			</tr>
<?
		} 

	if ($count==0) { 
?>
			<tr>
				<td colspan='3'>No users found.</td>
			</tr>
<?
	} 
?>
		</table>


<?
	include($BF. 'includes/bottom_popup.php');
?>
