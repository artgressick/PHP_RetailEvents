<?php
	require_once('../_lib.php');

	default_sortby('presenters', 'chrName', 'ASC');

	$q = "SELECT Presenters.ID, Presenters.chrName, Presenters.chrEmail, Presenters.chrOfficePhone, Presenters.chrCity
		FROM Presenters";
		
	$q .= " WHERE !Presenters.bDeleted ";
	
	if(@$_REQUEST['chrSearch'] != '') {  // if there is a search term 
		$q .= "AND ((chrName LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')
			OR (chrState LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')
			OR (chrOfficePhone LIKE '%" . str_replace(' ','%',$_REQUEST['chrSearch']) . "%')) ";
	}
	
	$sortby = array('chrName', 'ASC');
		$q .= " ORDER BY " . sortby_to_sql(get_sortby('presenters'));
		
	$presenter_result = do_mysql_query($q, 'get presenters');

	// parse the popup data
	parse_str(base64_decode($_REQUEST['d']), $data);
	_error_debug($data, 'data');

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

	$title = "Select Presenter";
	require(BASE_FOLDER . 'docpages/doc_meta.php');
	include(BASE_FOLDER . 'docpages/doc_top_poppup.php');	
?>

	<div style='width: 450px;'>
	
	<table class='Tabs' style='margin-bottom: -3px;'>
		<tr>
			<td class='Current'><a href='#'>Presenters</a></td>
			<td class=''><a href='select-add-presenter.php?idStore=<?=$_REQUEST['idStore']?>&d=<?=urlencode($_REQUEST['d'])?>'>Create New Presenter</a></td>
			<td class='TheRest'></td>
		</tr>
	</table>
	<div class='sectionInfo' style='width: 375px;'>
		<div class='sectionHeader'>Select Store Users</div>
		<div class='sectionContent' style='background: white;'>

		<div style='padding-bottom: 10px;'>
	
		<div style='float: right;'>
		<form action='' method='get'>
			Search: <input type='text' name='chrSearch' id='DocLoadFocus' value='<?=@$_REQUEST['chrSearch']?>' />
			<input type='hidden' name='d' value='<?=@$_REQUEST['d']?>' />
			<input type='submit' value='Go' />
		</form>
		</div>
	
	

<?	if(mysql_num_rows($presenter_result)) { ?>
	<table class='list' style='width: 100%;'>
		<thead>
			<tr>
				<? list_th('presenters', 'Presenter Name', 'chrName', 'width: 4in;', '', 'chrSearch=' . urlencode(@$_REQUEST['chrSearch']) . '&d=' . urlencode($_REQUEST['d'])); ?>
				</tr>
			</thead>
		<tbody>
<?		$count = 1;
		while($presenter = mysql_fetch_assoc($presenter_result)) { ?>
			<tr class='<?=($count++%2?'odd':'even')?>'
					onclick='associate("<?=$presenter['ID']?>", "<?=$presenter['chrName']?>");'
					onmouseover='RowHighlight(this);'
					onmouseout='RowUnHighlight(this);'
					style='cursor: pointer;'>
				<td><?=$presenter['chrName']?></td>
				</tr>
<?		} ?>
			</tbody>
		</table>
<?	} else { ?>
		<p>Search for a presenter by all or part of their name.  If the presenter you would like to add is not found, add them by clicking the "Add Presenter" tab above.</p>
		<p>A presenter can be a single person, or a band or other group of people.</p>
<?	} ?>

		</div>
	</div>

