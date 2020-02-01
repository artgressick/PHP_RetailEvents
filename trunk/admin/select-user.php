<?php
	require_once('../_lib.php');

	default_sortby('users', 'chrLastName,chrFirstName', 'ASC');

	$q = "SELECT Users.chrFirstName, Users.chrLastName, Users.ID FROM Users 
			LEFT JOIN ACL ON ACL.idUser=Users.ID AND 
				((ACL.enType='Stores' AND ACL.idItem='" . $_REQUEST['idStore'] . "')
				OR (ACL.enType='Special' AND ACL.chrSpecial='Corporate'))
			WHERE !bDeleted AND ACL.ID IS NULL ";
			if(@$_REQUEST['chrSearch'] != '') {	
	
		$q .= "AND ((chrFirstName REGEXP '" . $_REQUEST['chrSearch'] . "') OR 
				(chrLastName REGEXP '" . $_REQUEST['chrSearch'] . "') OR 
				(chrEmail REGEXP '" . $_REQUEST['chrSearch'] . "'))";
			}
		$q .= "ORDER BY " . sortby_to_sql(get_sortby('users'));

		$user_result = do_mysql_query($q, 'get users');

	// parse the popup data
	parse_str(base64_decode($_REQUEST['d']), $data);

function insert_body_params()
{
	if(isset($_REQUEST['idSelected'])) {
		?> onload="associate('<?=$_REQUEST['idSelected']?>', '<?=$_REQUEST['chrSelected']?>');" <?
	} else {
		?> onload="defaultOnLoad();" <?
	}
}

function insert_into_head(){
	global $data;
?>
<script type="text/javascript">
	function associate(id, entryname)
	{

		dad = window.opener.document;
<?		if(isset($data['functioncall'])) { ?>
			window.opener.<?=$data['functioncall']?>(id, entryname);
<?		} ?>
<?		if(isset($data['namediv'])) { ?>
			dad.getElementById('<?=$data['namediv']?>').innerHTML = entryname;
<?		} ?>
<?		if(isset($data['namefield'])) { ?>
			dad.getElementById('<?=$data['namefield']?>').value = entryname;
<?		} ?>
<?		if(isset($data['idfield'])) { ?>
			dad.getElementById('<?=$data['idfield']?>').value = id;
<?		} ?>
<?		if(isset($data['submitform'])) { ?>
			dad.forms.<?=$data['submitform']?>.submit();
<?		} ?>

		window.close();
	}
</script>

<?
}

$title = "Add Person Poppup";
	require(BASE_FOLDER . 'docpages/doc_meta.php');
	include(BASE_FOLDER . 'docpages/doc_top_poppup.php');	
?>


		<fieldset class='Search'>
		<form action='' method='get'>
			Search: <input type='text' name='chrSearch' id='DocLoadFocus' value='<?=@$_REQUEST['chrSearch']?>' />
			<input type='hidden' name='d' value='<?=@$_REQUEST['d']?>' />
			<input type='submit' value='Go' />
			</form>
		</fieldset>

<?	if(mysql_num_rows($user_result)) { ?>
	<table class='list' style='width: 100%;'>
		<thead>
			<tr>
				<? list_th('presenters', 'Presenter Name', 'chrName', 'width: 4in;', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&d=' . urlencode($_REQUEST['d'])); ?>
				</tr>
			</thead>
		<tbody>
<?		$count = 1;
		while($row = mysql_fetch_assoc($user_result)) { ?>
			<tr class='<?=($count++%2?'odd':'even')?>'
					onclick='associate("<?=$row['ID']?>", "<?=$row['chrFirstName']?> <?=$row['chrLastName']?>");'
					onmouseover='RowHighlight(this);'
					onmouseout='RowUnHighlight(this);'
					style='cursor: pointer;'>
				<td><?=$row['chrFirstName']?> <?=$row['chrLastName']?></td>
				</tr>
<?		} ?>
			</tbody>
		</table>
<?	} else { ?>
		<p>No users found.</p>
<?	} ?>



<?	include(BASE_FOLDER . 'docpages/doc_bottom_poppup.php');	?>