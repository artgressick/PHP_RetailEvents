<?php
	require_once('../_lib.php');

	default_sortby('users', 'chrLastName,chrFirstName', 'ASC');

	if(@$_REQUEST['chrSearch'] != '') {  // if they have entered a search term, use it
		$q = "SELECT DISTINCT Users.* FROM Users 
			LEFT JOIN ACL ON ACL.idUser=Users.ID AND 
				((ACL.enType='Stores' AND ACL.idItem='" . $_REQUEST['idStore'] . "')
				OR (ACL.enType='Special' AND ACL.chrSpecial='Corporate'))
			WHERE !bDeleted AND ACL.ID IS NULL 
				AND ((chrFirstName REGEXP '" . $_REQUEST['chrSearch'] . "') OR 
				(chrLastName REGEXP '" . $_REQUEST['chrSearch'] . "') OR 
				(chrEmail REGEXP '" . $_REQUEST['chrSearch'] . "'))
			ORDER BY " . sortby_to_sql(get_sortby('users'));

		$user_result = do_mysql_query($q, 'get users');
	}

function insert_body_params()
{
	if(isset($_REQUEST['idSelected'])) {
		?> onload="associate('<?=$_REQUEST['idSelected']?>');" <?
	} else {
		?> onload="document.getElementById('DocLoadFocus').focus();" <?
	}
}

function insert_into_head()
{
?>
<script type="text/javascript">
	function associate(id)
	{
		theform = window.opener.document.getElementById('Form');

		if(!theform) {
			alert('There was a problem finding the window or the form.');
		} else {
			theform.idItem.value=id;
			theform.theaction.value='add';
			theform.submit();
			window.close();
		}
	}
</script>

<?
}

$title = "Select Person Poppup";
	require(BASE_FOLDER . 'docpages/doc_meta.php');
	include(BASE_FOLDER . 'docpages/doc_top_poppup.php');	
?>

	<table class='Tabs' style='margin-bottom: -3px;'>
		<tr>
			<td class='Current'><a href='#'>Users</a></td>
			<td class=''><a href='select-store-adduser.php?d=<?=@$_REQUEST['d']?>'>Create New User</a></td>
			<td class='TheRest'></td>
		</tr>
	</table>
	<div class='sectionInfo' style='width: 500px;'>
		<div class='sectionHeader'>Select Store Users</div>
		<div class='sectionContent'>

		<div style='padding-bottom: 10px;'>
		<form action='' method='get'>
			<input type='text' name='chrSearch' id='DocLoadFocus' value='<?=@$_REQUEST['chrSearch']?>' />
			<input type='hidden' name='idStore' value='<?=@$_REQUEST['idStore']?>' />
			<input type='hidden' name='d' value='<?=@$_REQUEST['d']?>' />
			<input type='submit' value='Search' />
			</form>
		</div>

<?	if(isset($user_result)) { ?>
	<table class='list Indented'>
		<thead>
			<tr>
				<? list_th('users', 'First Name', 'chrFirstName,chrLastName', 'width: 1.5in;', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&idStore=' . $_REQUEST['idStore']); ?>
				<? list_th('users', 'Last Name', 'chrLastName,chrFirstName', 'width: 2in;', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&idStore=' . $_REQUEST['idStore']); ?>
				<? list_th('users', 'Email', 'chrEmail', 'width: 2in;', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&idStore=' . $_REQUEST['idStore']); ?>
				</tr>
			</thead>
		<tbody>
<?		$count = 1;
		while($user = mysql_fetch_assoc($user_result)) { ?>
			<tr class='<?=($count++%2?'odd':'even')?>'
					onclick='associate("<?=$user['ID']?>");'
					onmouseover='RowHighlight(this);'
					onmouseout='RowUnHighlight(this);'
					style='cursor: pointer;'>
				<td><?=$user['chrFirstName']?></td>
				<td><?=$user['chrLastName']?></td>
				<td><?=$user['chrEmail']?></td>
				</tr>
<?		} ?>
			</tbody>
		</table>
<?	} else { ?>
		<p>Please query the database prior to adding a new user to the system to avoid duplication.</p>
<?	} ?>
		</div>
	</div>



<?	include(BASE_FOLDER . 'docpages/doc_bottom_poppup.php');	?>