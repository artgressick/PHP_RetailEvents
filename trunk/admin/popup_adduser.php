<?php
	$BF = "../"; //This is the BASE FOLDER.  This should be located at the top of every page with the proper set of '../'s to find the root folder 
	$title = 'Add Popup';
	require($BF. '_lib2.php');
	include($BF. 'includes/meta2.php');

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLastName, chrFirstName"; }
	if(!isset($_REQUEST['chrSearch'])) { $_REQUEST['chrSearch'] = ""; }
	
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/popup.js"></script>
<script type="text/javascript">
var TableName = "";
function associate(id,name,lev) {
	if(lev != "0") {
		var tbl = window.opener.document.getElementById("<?=$_REQUEST['tbl']?>").innerHTML;
		TableName = "<?=$_REQUEST['tbl']?>";
		var post = 0;
		if(!window.opener.document.getElementById("<?=$_REQUEST['tbl']?>" +"tr"+id) ) {
			window.opener.document.getElementById("<?=$_REQUEST['tbl']?>").innerHTML = tbl + "<tr id='people_assoctrAJAX_NEWID' onmouseover='RowHighlight(\"people_assoctrAJAX_NEWID\");' onmouseout='UnRowHighlight(\"people_assoctrAJAX_NEWID\");'> " +
			"<td style='cursor: pointer;'>"+ name +"</td> " +
			
			"<td><select name='enPermission' id='enPermission' onchange=\"javascipt:quickassoc('ajax_adduser.php?postType=quickInsert&ID=AJAX_NEWID&enPermission='+this.value)\"><option value='0'>-Select Role-</option><option value=\"Store Manager\""+ (lev == 3 ? ' selected="selected"' : '') +">Store Manager</option><option value=\"Theater Coordinator\""+ (lev != 3 ? ' selected="selected"' : '') +">Theater Coordinator</option></select></td>" +
			
			"<td class='options'><div class='deleteImage' onmouseover='document.getElementById(\"deleteButtonAJAX_NEWID\").src=\"<?=$BF?>images/button_delete_on.png\"' onmouseout='document.getElementById(\"deleteButtonAJAX_NEWID\").src=\"<?=$BF?>images/button_delete.png\"'><a href=\"javascript:quickdel('<?=$BF?>ajax_delete.php?postType=permDelete&tbl=ACL&idUser=<?=$_SESSION['idUser']?>&id=AJAX_NEWID',AJAX_NEWID,'people_assoc');\"><img id='deleteButtonAJAX_NEWID' src='<?=$BF?>images/button_delete.png' alt='delete button' /></a></tr>";
			post = 1;
		} else {
			if(window.opener.document.getElementById("<?=$_REQUEST['tbl']?>" +"tr"+id).style.display == "none") {
				window.opener.document.getElementById("<?=$_REQUEST['tbl']?>" +"tr"+id).style.display = "";
				post = 1;
			}
		}

//alert (window.opener.document.getElementById("<?=$_REQUEST['tbl']?>").innerHTML);


		if(post == 1) {
			repaintmini("<?=$_REQUEST['tbl']?>");

			var poststr = "idUser=" + id +
				"&enPermission=" + (lev == 3 ? 'Store Manager' : 'Theater Coordinator') +
				"&idItem=<?=$_REQUEST['idItem']?>" +
	        	"&postType=" + encodeURI( "insert" );

			postInfo('ajax_adduser.php', poststr, 'newID');
	      	
	      	
	      	var trs = window.opener.document.getElementById("<?=$_REQUEST['tbl']?>").getElementsByTag("tr");
	      	trs[trs.length-1]
		}
	}
}


</script>
<?
	include($BF. 'includes/top_popup.php');
?>

	<form action="" method="post">
		<div class="AdminTopicHeader">Add User</div>
			<div class="AdminInstructions2">Please type in a name to search for. (Enter % to show all People)</div>
			<div class='form'>
				<div class='formHeader'>Search for People <span class='Required'>(Required)</span></div>
				<input type='text' id='chrSearch' name='chrSearch' value='<?=$_REQUEST['chrSearch']?>' /> <input type='submit' name='search' value='Search' />
			</div>
<?
	if($_REQUEST['chrSearch'] != "" ) {
	
		$q = "SELECT ID, chrFirstName, chrLastName, chrEmail FROM Users WHERE !bDeleted ";
		if(@$_REQUEST['chrSearch'] != '') { 
			$q .= " AND ((chrFirstName LIKE '%" . $_REQUEST['chrSearch'] . "%') OR
			(chrLastName LIKE '%" . $_REQUEST['chrSearch'] . "%') OR
			(chrEmail LIKE '%" . $_REQUEST['chrSearch'] . "%'))";
		}
		$q .= " ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		$result = database_query($q,"Getting all people");
	
?>
	
	<div class='innerbody'>	

	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
<?
			sortList('Last Name', 'chrLastName');
			sortList('First Name', 'chrFirstName');
			sortList('Email Address', 'chrEmail');
?>
		</tr>
<?
	  	$count=0;	
		while ($row = mysqli_fetch_assoc($result)) { 
			$link = "associate('". $row['ID'] ."','". $row['chrFirstName'] ." ". $row['chrLastName'] ."',this.value)";
?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
				onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrLastName']?></td>
				<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrFirstName']?></td>
				<td style='cursor: pointer;' onclick="<?=$link?>"><?=$row['chrEmail']?></td>
			</tr>
<?
		}

		if($count == 0) { 
?>
		<tr>
			<td height="20" style="text-align:center;" colspan="3">No User to display</td>
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
