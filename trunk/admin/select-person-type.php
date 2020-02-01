<?php
	require_once('../_lib.php');

	default_sortby('Users', 'chrLastName', 'ASC');

	$q = "SELECT Users.ID, Users.chrFirstName, Users.chrLastName, Users.chrEmail
		FROM Users 
		JOIN ACL ON ACL.idUser=Users.ID
		WHERE !Users.bDeleted AND enPermission='" . ($_REQUEST['idType'] == 1 ? "Theater Coordinator" : "Store Manager") . "'";
		
	if($_REQUEST['chrSearch'] != '') {
		$q .= " AND (Users.chrFirstName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%'
				OR Users.chrLastName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%'
				OR Users.chrEmail LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')";
		}
		
	$q .= " ORDER BY " . sortby_to_sql(get_sortby('Users'));
		
	$presenter_result = do_mysql_query($q, 'get Users');

	
	// parse the popup data
	parse_str(base64_decode($_REQUEST['d']), $data);
	//_error_debug($data, 'data');
	

function insert_into_head(){
	global $data;
?>
<script type="text/javascript">
	function associate(id, section, firstname, lastname)	{
		dad = window.opener.document;
<?		if(isset($data['functioncall'])) { ?>
			window.opener.<?=$data['functioncall']?>(id, section, firstname, lastname);
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

	<div style='width: 580px;'>
	
		<div class="AdminTopicHeader" style='width: 570px'>Add Stores</div>
			<div class="AdminDirections" style='width: 570px'>Add info here.</div>
	

			<div class="AdminToolBar" style='width: 570px; padding: 5px; margin: 0;'>
				<form action='' method='get'>
					Search: <input type='text' name='chrSearch' id='DocLoadFocus' value='<?=@$_REQUEST['chrSearch']?>' />
					<input type='hidden' name='d' value='<?=@$_REQUEST['d']?>' />
					<input type='hidden' name='d' value='<?=@$_REQUEST['section']?>' />
					<input type='submit' value='Go' />
				</form>
			</div>

<table class='list' style='width: 100%;'>
		<thead>
			<tr>
				<? list_th('Users', 'First Name', 'chrFirstName', 'width: 1in;', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&d=' . urlencode($_REQUEST['d'])); ?>
				<? list_th('Users', 'Last Name', 'chrLastName', 'width: 1in;', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&d=' . urlencode($_REQUEST['d'])); ?>
				<? list_th('Users', 'Email Address', 'chrEmail', 'width: 1in;', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&d=' . urlencode($_REQUEST['d'])); ?>
		  </tr>
	  </thead>
		<tbody>
<?		$count = 0;
		while($row = mysql_fetch_assoc($presenter_result)) { ?>
			<tr class='<?=($count++%2?'odd':'even')?>'
					onclick='associate("<?=$row['ID']?>", "<?=$_REQUEST['section']?>", "<?=$row['chrFirstName']?>", "<?=$row['chrLastName']?>");'
					onmouseover='RowHighlight(this);'
					onmouseout='RowUnHighlight(this);'
					style='cursor: pointer;'>
				<td><?=$row['chrFirstName']?></td>
				<td><?=$row['chrLastName']?></td>
				<td><?=$row['chrEmail']?></td>
			</tr>
<?		} ?>
	  </tbody>
</table>
<?	if($count == 0) { ?>
		<p>No records were located for this request.</p>
<?  } ?>

	</div> 


<?	include(BASE_FOLDER . 'docpages/doc_bottom_poppup.php');	?>