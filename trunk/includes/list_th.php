<?
	list($col, $ord) = get_sortby($table_name);
	
	if ($col == $column_name) {
		if ($ord == 'ASC') {
			$link = '?sort_' . $table_name . '_column=' . $column_name . '&amp;sort_' . $table_name . '_order=DESC' . ($morecgi!=''?'&amp;' . $morecgi:'');
			if(isset($_REQUEST['id'])) {
				$link .= '&amp;id=' . $_REQUEST['id'];
			}
			if(isset($_REQUEST['idShow'])) {
				$link .= '&amp;idShow=' . $_REQUEST['idShow'];
			}
			$link_title = 'Sort by ' . $label . ' in descending order';
			$graphic = 'column_sorted_asc.gif';
			$image_alt = 'Currently sorted ascending';
		} else {
			$link = '?sort_' . $table_name . '_column=' . $column_name . '&amp;sort_' . $table_name . '_order=ASC' . ($morecgi!=''?'&amp;' . $morecgi:'');
			if(isset($_REQUEST['id'])) {
				$link .= '&amp;id=' . $_REQUEST['id'];
			}
			if(isset($_REQUEST['idShow'])) {
				$link .= '&amp;idShow=' . $_REQUEST['idShow'];
			}
			$link_title = 'Sort by ' . $label . ' in ascending order';
			$graphic = 'column_sorted_desc.gif';
			$image_alt = 'Currently sorted descending';
		}
	} else {
		$link = '?sort_' . $table_name . '_column=' . $column_name . '&amp;sort_' . $table_name . '_order=ASC' . ($morecgi!=''?'&amp;' . $morecgi:'');
		if(isset($_REQUEST['id'])) {
			$link .= '&amp;id=' . $_REQUEST['id'];
		}
		if(isset($_REQUEST['idShow'])) {
			$link .= '&amp;idShow=' . $_REQUEST['idShow'];
		}
		$link_title = 'Sort by ' . $label . ' in ascending order';
		$graphic = 'column_unsorted.gif';
		$image_alt = 'Currently not sorted by this column';
	}
	
?>
	<th style='cursor: pointer; <?=$style?>' onclick='location.href="<?=$link?>";' class='<?=($col == $column_name ? 'sortedby' : '')?>' <?=$params?>>
		<div style='text-align: left;'>
			<a class='<?$col==$column_name?'current':''?>' title='<?=$link_title?>' href='<?=$link?>'><?=$label?></a>&nbsp;<!--
			--><a class='ScreenOnly<?$col==$column_name?'current':''?>' title='<?=$link_title?>' href='<?=$link?>'><img src='<?=BASE_FOLDER?>images/<?=$graphic?>' alt='<?=$image_alt?>' /></a>
			</div>
		</th>
<?
?>