<?php
	require_once('../_lib.php');

	default_sortby('Stores', 'chrName', 'ASC');

	$q = "SELECT ID, chrName FROM Stores
			WHERE !bDeleted ";

		if(@$_REQUEST['chrSearch'] != '') {	
			$q .= " AND (chrName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')";
		}
		
		$q .= "ORDER BY " . sortby_to_sql(get_sortby('Stores'));

		$store_result = do_mysql_query($q, 'get Stores');

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


	<div class='sectionInfo' style='width: 500px;'>
		<div class='sectionHeader'>Select Store</div>
		<div class='sectionContent'>

		<div style='padding-bottom: 10px;'>
		<form action='' method='get'>
			<input type='text' name='chrSearch' id='DocLoadFocus' value='<?=@$_REQUEST['chrSearch']?>' />
			<input type='hidden' name='idStore' value='<?=@$_REQUEST['idStore']?>' />
			<input type='hidden' name='d' value='<?=@$_REQUEST['d']?>' />
			<input type='submit' value='Search' />
			</form>
		</div>

<?	if(mysql_num_rows($store_result)) { ?>
	<table class='list' style='width: 100%;'>
		<thead>
			<tr>
				<? list_th('Stores', 'Store Name', 'chrName', 'width: 3in;', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&d=' . urlencode($_REQUEST['d'])); ?>
			</tr>
			</thead>
		<tbody>
<?		$count = 1;
		while($row = mysql_fetch_assoc($store_result)) { ?>
			<tr class='<?=($count++%2?'odd':'even')?>'
					onclick='associate("<?=$row['ID']?>", "<?=$row['chrName']?>");'
					onmouseover='RowHighlight(this);'
					onmouseout='RowUnHighlight(this);'
					style='cursor: pointer;'>
				<td><?=$row['chrName']?></td>
			</tr>
<?		} ?>
			</tbody>
		</table>
<?	} else { ?>
		<p>No stores found.</p>
<?	} ?>



<?	include(BASE_FOLDER . 'docpages/doc_bottom_poppup.php');	?>
