<?php
	$BF = "../"; //This is the BASE FOLDER.  This should be located at the top of every page with the proper set of '../'s to find the root folder 
	$title = 'Add Popup';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrName"; }
	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ""; }
	
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/popup.js"></script>
<script type="text/javascript">

function associate(id,name,lev) {
	if(lev != "0") {
		var tbl = window.opener.document.getElementById("<?=$_REQUEST['tbl']?>").innerHTML;

		var post = 0;
		if(!window.opener.document.getElementById("<?=$_REQUEST['tbl']?>" +"tr"+id) ) {
			window.opener.document.getElementById("<?=$_REQUEST['tbl']?>").innerHTML = tbl + "<tr id='<?=$_REQUEST['tbl']?>tr"+ id +"'onmouseover='RowHighlight(\"<?=$_REQUEST['tbl']?>tr"+ id +"\");' onmouseout='UnRowHighlight(\"<?=$_REQUEST['tbl']?>tr"+ id +"\");'> " +
			"<td style='cursor: pointer;'>"+ name +"</td> " +
			
			"<td><select name='enPermission' id='enPermission' onchange=\"javascipt:quickassoc('ajax_addstore.php?postType=quickInsert&ID="+ id +"&enPermission='+this.value)\"><option value=''>-Select Role-</option><option value=\"Store Manager\">Store Manager</option><option value=\"Theater Coordinator\">Theater Coordinator</option></select></td>" +
			
			"<td>&nbsp;</tr>";	
			
			post = 1;
		} else {
			if(window.opener.document.getElementById("<?=$_REQUEST['tbl']?>" +"tr"+id).style.display == "none") {
				window.opener.document.getElementById("<?=$_REQUEST['tbl']?>" +"tr"+id).style.display = "";
				post = 1;
			}
		}

		if(post == 1) {
			repaintmini("<?=$_REQUEST['tbl']?>");

			var poststr = "idItem=" + id +
				"&enPermission=" + (lev == 3 ? 'Store Manager' : 'Theater Coordinator') +
				"&idUser=<?=$_REQUEST['idUser']?>" +
	        	"&postType=" + encodeURI( "insert" );

	      	postInfo('ajax_addstore.php', poststr);
		}
	}
}


</script>
<?
	include($BF. 'includes/top_popup.php');
?>

	<form action="" method="post">
		<div class="AdminTopicHeader">Add Store</div>
			<div class="AdminInstructions2">Please type in a name to search for. (Enter % to show all Stores)</div>
			<div class='form'>
				<div class='formHeader'>Search for Store <span class='Required'>(Required)</span></div>
				<input type='text' id='chrSearch' name='chrSearch' value='<?=$_REQUEST['chrSearch']?>' /> <input type='submit' name='search' value='Search' />
			</div>
<?
	if($_REQUEST['chrSearch'] != "" ) {
	
		$q = "SELECT ID, chrName FROM Stores WHERE !bDeleted ";
		if(@$_REQUEST['chrSearch'] != '') { 
			$q .= " AND ((chrName LIKE '%" . $_REQUEST['chrSearch'] . "%'))";
		}
		$q .= " ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		$result = database_query($q,"Getting all stores");
	
?>
	
	<div class='innerbody'>	

	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
<?
			sortList('Store Name', 'chrName');
?>
		</tr>
<?
	  	$count=0;	
		while ($row = mysqli_fetch_assoc($result)) { 
			$link = "associate('". $row['ID'] ."','". $row['chrName'] ."',this.value)";
?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
				onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrName']?></td>
			</tr>
<?
		}

		if($count == 0) { 
?>
		<tr>
			<td height="20" style="text-align:center;" colspan="3">No Stores to display</td>
		</tr>
<?
		}
?>
	</table>
	</div>
<?	} ?>
	<div align='center' style="padding-top:10px;">
		<input type="button" name="close" id="close" onclick="javascript:window.close();" value="Close this Window" />
	</div>

</form>

<?
	include($BF. 'includes/bottom_popup.php');
?>
