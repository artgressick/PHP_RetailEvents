<?php
	$BF = '../';
	$title = 'Edit Section';
	require($BF. '_lib2.php');
	// Checking request variables
	($_REQUEST['id'] == "" || !is_numeric($_REQUEST['id']) ? ErrorPage() : "" );
	include($BF. 'includes/meta2.php');

	/* This is a Permissions check.  If they don't have permission, remember the Referer and spit them out on the nopermission page in the root
		idType of 1 = Super user 
		idType of 2 = Editor
		idType of 3 = Corp Events
		idType of 4 = Store
		
		$pageaccess is array of user types that can access the page
	*/
	
	$pageaccess = array("1","3");
	if(!in_array($_SESSION['idType'],$pageaccess)) { $_SESSION['chrReferer'] = $_SERVER['HTTP_REFERER']; header('Location: '. $BF ."nopermission.php"); die(); }
	// End Security Check
			
	$info = fetch_database_query("SELECT * FROM Content_Dynamic WHERE ID=". $_REQUEST['id'],"Getting Section Info");
		
	// if this is a form submission
	if(isset($_POST['chrSectionName'])) {

		$table = 'Content_Dynamic';
		$mysqlStr = '';
		$audit = '';

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrPageTitle',$info['chrPageTitle'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'chrSectionName',$info['chrSectionName'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'txtSourceCode',$info['txtSourceCode'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'idStatus',$info['idStatus'],$audit,$table,$_POST['id']);
		list($mysqlStr,$audit) = set_strs($mysqlStr,'dtUpdated',$info['dtUpdated'],$audit,$table,$_POST['id']);
				

		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']); }

		// When the page is done updating, move them back to whatever the list page is for the section you are in.
		header("Location: sections.php");
		die();
	}

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrSectionName"; }

	// The Forms js is for all the error checking that is involved with the forms Add / Edit Pages
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script type="text/javascript" src="<?=$BF?>components/tiny_mce/tiny_mce_gzip.js"></script>
<script type="text/javascript">
tinyMCE_GZ.init({
	plugins : 'style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',
	themes : 'simple,advanced',
	languages : 'en',
	disk_cache : true,
	debug : false
});
</script>
<!-- Needs to be seperate script tags! -->
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		plugins : "style,layer,table,save,advhr,advimage,advlink,emotions,insertdatetime,preview,zoom,flash,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,filemanager",
		theme_advanced_buttons1_add : "fontselect,fontsizeselect",
		theme_advanced_buttons2_add : "separator,forecolor,backcolor",
		theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator",
		theme_advanced_buttons3_add : "emotions,flash,advhr,separator,print,separator,ltr,rtl,separator,fullscreen",
		theme_advanced_toolbar_location : "top",
		theme_advanced_path_location : "bottom",
		content_css : "/example_data/example_full.css",
	    plugin_insertdate_dateFormat : "%Y-%m-%d",
	    plugin_insertdate_timeFormat : "%H:%M:%S",
		extended_valid_elements : "hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
		external_link_list_url : "example_data/example_link_list.js",
		external_image_list_url : "example_data/example_image_list.js",
		flash_external_list_url : "example_data/example_flash_list.js",
		file_browser_callback : "mcFileManager.filebrowserCallBack",
		theme_advanced_resize_horizontal : false,
		theme_advanced_resizing : true,
		apply_source_formatting : true,
		
		filemanager_rootpath : "<?=realpath($BF . 'images')?>",
		filemanager_path : "<?=realpath($BF . 'images')?>",
		relative_urls : true,
		document_base_url : "http://retailmarketing.apple.com/"
	});
</script>
<script language="javascript">
	function error_check(addy) {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrSectionName', "You must enter a Section Name.");
		total += ErrorCheck('chrPageTitle', "You must enter a Page Title");
		total += ErrorCheck('idStatus', "You must pick a Status.");
		if( tinyMCE.getContent() == "" ) {
		
			document.getElementById('errors').innerHTML += "<div class='ErrorMessage'> You must enter some code for the page.</div>";
		
		}
		if(total == 0) { document.getElementById('idForm').submit(); }
	}
</script>
<?

	include($BF. 'includes/top_admin2.php');
?>


		<div class="AdminTopicHeader">Edit Section</div>
		<div class="AdminInstructions">Please remember to use FireFox for editing this section. This does not affect the user viewing the page with Safari. You are about to add a section to the website. A Section is like adding a chapter. The page below will be the landing page for the use to see what the section is all about. Once you have added a section then you can add supporting pages to the section by going to Dynamic Pages.</div>

		<form id='idForm' name='idForm' method='post' action=''>

		<div id='errors'></div>

		<table class='OneColumn'>
			<tr>
				<td>
					<div class='formHeader'>Section Name <span class='Required'>(Required)</span></div>
					<div>
						<input type='text' size='40' maxlength='80' name='chrSectionName' id='chrSectionName' value='<?=$info['chrSectionName']?>' />
					</div>
					<br />
					<div class='formHeader'>Page Title<span class='Required'>(Required)</span></div>
					<div>
						<input type='text' size='40' maxlength='80' name='chrPageTitle' id='chrPageTitle' value='<?=$info['chrPageTitle']?>' />
					</div>
					<br />
					<div class='formHeader'>View Status<span class="Required">(Required)</span></div>
					<div style='margin-bottom: 10px;'>
					<select name='idStatus' id='idStatus'>
							<option value='1' <?= ($info['idStatus'] == 1) ? ' selected' : '' ?>> Viewable </option>
							<option value='2' <?= ($info['idStatus'] == 2) ? ' selected' : '' ?>> Hidden</option>
						</select>
					</div>
					<br />
					<div class='formHeader'>Page Code<span class="Required">(Required)</span></div>
					<div><textarea name="txtSourceCode" cols="100" rows="40" wrap="virtual" class="formField" tabindex="3"><?=decode($info['txtSourceCode'])?></textarea></div>
				</td>
			</tr>
		</table>
		<br />
		<div class='FormButtons'>
			<input type='button' name='SubmitAddSection' value='Save Section' onclick='error_check()' />
			<input type='hidden' name='id' value='<?=$_REQUEST["id"]?>' />
			<input type='hidden' id='dtUpdated' name='dtUpdated' value='<?= date('Y-m-d H:m:s') ?>'>						
		</div>
		
		</form>

<?
	//Include the bottom of the page.
	include($BF. 'includes/bottom2.php');
?>