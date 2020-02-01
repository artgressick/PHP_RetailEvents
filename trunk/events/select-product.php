<?php
	require_once('../_lib.php');

	default_sortby('products', 'chrName', 'ASC');

	if(isset($_REQUEST['ProductType'])) {
		$_SESSION['ProductType'] = $_REQUEST['ProductType'];
	} else if(!isset($_SESSION['ProductType'])) {
		$_SESSION['ProductType'] = 'Apple';
	}

	$q = "SELECT Products.*
		FROM Products 
		WHERE !bDeleted AND 
		enType='" . $_SESSION['ProductType'] . "' ";

	if(@$_REQUEST['chrSearch'] != '') {  // if they have entered a search term, use it
		$q .= " AND ((chrName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')) ";
	}

	$q .= " ORDER BY " . sortby_to_sql(get_sortby('products'));

	$product_result = do_mysql_query($q, 'get products');
	
	// parse the popup data
	parse_str(base64_decode($_REQUEST['d']), $data);
	_error_debug($data, 'data');

function insert_body_params()
{
	if(isset($_REQUEST['idSelected'])) {
		?> onload="associate('<?=$_REQUEST['idSelected']?>');" <?
	} else {
		?> onload="defaultOnLoad();" <?
	}
}

function insert_into_head()
{
	global $data;
?>
<script type="text/javascript">
	function associate(id, entryname, entryemail)
	{
		dad = window.opener.document;
<?		if(isset($data['functioncall'])) { ?>
			window.opener.<?=$data['functioncall']?>(id, entryname, entryemail);
<?		} ?>
<?		if(isset($data['namediv'])) { ?>
			dad.getElementById('<?=$data['namediv']?>').innerHTML = entryname;
<?		} ?>
<?		if(isset($data['namefield'])) { ?>
			dad.getElementById('<?=$data['namefield']?>').value = entryname;
<?		} ?>
<?		if(isset($data['emailfield'])) { ?>
			dad.getElementById('<?=$data['emailfield']?>').value = entryemail;
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

	$title = "Select Product";
	require(BASE_FOLDER . 'docpages/doc_meta.php');
	include(BASE_FOLDER . 'docpages/doc_top_poppup.php');	
?>


	<div style='width: 560px;'>
	
	<table class='Tabs borderbottom' style='margin-bottom: -3px;'>
		<tr>
			<td class='<?=($_SESSION['ProductType']=='Apple'?'Current':'')?>'><a href='?d=<?=urlencode($_REQUEST['d'])?>&amp;chrSearch=<?=$_REQUEST['chrSearch']?>&amp;ProductType=Apple'>Apple Products</a></td>
			<td class='<?=($_SESSION['ProductType']=='Third-Party'?'Current':'')?>'><a href='?d=<?=urlencode($_REQUEST['d'])?>&amp;chrSearch=<?=$_REQUEST['chrSearch']?>&amp;ProductType=Third-Party'>Third-Party Products</a></td>
			<td class='TheRest'></td>
		</tr>
		</table>

	
	<div class='sectionInfo' style='width: 300px;'>
		<div class='sectionHeader'>Select Store Users</div>
		<div class='sectionContent'>

		<div style='padding-bottom: 10px;'>
	
	<div style='text-align: right;'>
		<div>
		<form action='' method='get'>
			Filter: <input type='text' name='chrSearch' id='DocLoadFocus' value='<?=@$_REQUEST['chrSearch']?>' />
			<input type='hidden' name='d' value='<?=@$_REQUEST['d']?>' />
			<input type='hidden' name='type' value='<?=@$_REQUEST['type']?>' />
			<input type='submit' value='Go' />
		</form>
		</div>
	</div>


<?	if(mysql_num_rows($product_result)) { ?>
	<table class='list'>
		<thead>
			<tr>
				<? list_th('products', 'Product Name', 'chrName', 'width: 4in;', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&d=' . urlencode($_REQUEST['d'])); ?>
				</tr>
			</thead>
		<tbody>
<?		$count = 1;
		while($product = mysql_fetch_assoc($product_result)) { ?>
			<tr class='<?=($count++%2?'odd':'even')?>'
					onclick='associate("<?=$product['ID']?>", "<?=$product['chrName']?>", "<?=$product['chrEmail']?>");'
					onmouseover='RowHighlight(this);'
					onmouseout='RowUnHighlight(this);'
					style='cursor: pointer;'>
				<td><?=$product['chrName']?></td>
				</tr>
<?		} ?>
			</tbody>
		</table>
<?	} else { ?>
		<p>No products were found that met your criteria.</p>
<?	} ?>

		</div>
	</div>

<? doc_bottom_popup(); ?>
