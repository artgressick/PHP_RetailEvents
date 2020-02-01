<?php
	$BF = "../";
	$title = 'Expertise';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');

	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ""; }

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrName"; }
	
	$q = "SELECT ID,chrName
		FROM Expertise
		WHERE !bDeleted
		". ($_REQUEST['chrSearch'] != '' ? " AND chrName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%'" : '') ." 
		ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];	

	$results = database_query($q,"getting expertise");
	
	// parse the popup data
	parse_str(base64_decode($_REQUEST['d']), $data);
	//_error_debug($data, 'data');
	
?>
<script type="text/javascript">
	function associate(id, name)	{
<?		if(isset($data['functioncall'])) { ?>
			window.opener.<?=$data['functioncall']?>(id, name);
<?		} ?>

		//window.close();
	}
</script>

<?
	include($BF .'docpages/doc_top_poppup.php');	
?>

	<div style='width: 400px;'>
	
		<div class="AdminTopicHeader" style='width: 400px'>Select Experience</div>

			<div class="AdminToolBar" style='width: 390px; padding: 5px;'>
				<form action='' method='get'>
					Search: <input type='text' name='chrSearch' id='DocLoadFocus' value='<?=@$_REQUEST['chrSearch']?>' />
					<input type='hidden' name='d' value='<?=@$_REQUEST['d']?>' />
					<input type='hidden' name='d' value='<?=@$_REQUEST['section']?>' />
					<input type='submit' value='Go' />
				</form>
			</div>


	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
		<?  sortList('Expertise', 'chrName'); ?>
		</tr>
<?
	$count=0;	
	while ($row = mysqli_fetch_assoc($results)) { 
?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'
			onclick='associate("<?=$row['ID']?>", "<?=$row['chrName']?>");'>
				<td><?=$row['chrName']?></td>
			</tr>
<?	} ?>
					</table>
<?	if($count == 0) { ?>
					<div style='padding: 3px; border: 1px solid gray; border-top: none; text-align:center'>No records to display</div>
<?	} ?>


	<div style='text-align: center;'><a href="javascript:window.close();">Close Window</a></div>
	</div> 
<?
	require($BF .'docpages/doc_bottom_poppup.php');
?>
