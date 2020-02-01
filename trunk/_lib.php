<?php

	// The verision of the project we are working on
	define('PROJECT_VERSION','2.0');
	define('PROJECT_NAME','Apple Retail Events');

	// This is needed for the Date Functions
	if(phpversion() > '5.0.1') { date_default_timezone_set('America/Los_Angeles'); }
	
	// we'll start with the location of this file. we know it's the root of the project.
	$root_server_folder = dirname(__FILE__);
	// this is the location of the file that included this one.
	$current_file_folder = dirname($_SERVER['SCRIPT_FILENAME']);
	// now determine the difference.  hmmmmm....
	if(substr($current_file_folder, 0, strlen($root_server_folder)) != $root_server_folder) {
		die("The current file and the included library are in completely different locations.  Perhaps you have this library in the include folder?  Well, it won't work!");
	} else {
		define('CURRENT_FOLDER', substr($current_file_folder, strlen($root_server_folder), 1028));
		// count the number of slashes in the path difference
		$num = substr_count(CURRENT_FOLDER, '/');
		define('BASE_FOLDER', str_repeat('../', $num));
		define('CURRENT_PAGE', basename($_SERVER['SCRIPT_NAME'], '.php'));
	}
	
	require_once(BASE_FOLDER . 'includes/JSON.php');
	$json = new Services_JSON();
	
	// INSERT -conf.php FILE HERE!
	include('retailevents-conf.php');
	
	// set up error reporting
	require(BASE_FOLDER . 'includes/error_handler.php');
		
	// INSERT PROJECT NAME HERE
	session_name('RetailEvents');
	session_start();

	if(!isset($host)) {
		error_report("Include conf failed");   
		$connected = false;
	} else {
		$connected = true;
		if($connection = @mysql_connect($host, $user, $pass)) {
			if(!@mysql_select_db($db, $connection)) {
				error_report("mysql_select_db(): " . mysql_error());
			}
		} else {
			error_report("mysql_connect(): " . mysql_error());
		}
	}
	// clean up so that these variables aren't exposed through the debug console
	unset($host, $user, $pass, $db);

	if (isset($_REQUEST['auth_destroy'])) {
		setcookie(session_name(), "", 0, "/");
		$_SESSION = array();
		session_unset();
		session_destroy();
		header("Location: " . BASE_FOLDER . "index.php");
		die();
	}
		auth_check();


function ErrorPage($msg) {
	header("Location: ".BASE_FOLDER."error.php");
	die;
}


	
/* This function is used at the top of every page.  Supply the page title as the only parameter.  Create a function called insert_into_head() to provide additional stuff in the <head> section.  Create a function called insert_body_params() to provide additional parameters in the <body> tag. */
/* MYSQL Information */
function do_mysql_query($query, $description)
{
	include('mysql/do_mysql_query.php');
	return($result);
}

/* performs the MySQL query given and returns an array of the rows returned (each is an associative array).
 */
function get_mysql_rows($query, $description, $by_id=false)
{
	include('mysql/get_mysql_rows.php');
	return($rows);
}

function update_record($table, $column, $id, $newval)
{
	include('mysql/update_record.php');
	return(true);
}

function audit_new_record($table, $id)
{
	return(do_mysql_query("INSERT INTO Audit SET 
		dtDateTime=NOW(), 
		chrTableName='$table', 
		idRecord='$id', 
		chrColumnName='', 
		txtOldValue='', 
		idUser='" . $_SESSION['idUser'] . "'
		", 'audit_new_record'));
}

/* $record_added: Supply true for an addition, false for a removal.
 */
function audit_assoc_record($table, $id, $column, $record_added, $lookup_table, $lookup_id)
{
	return(do_mysql_query("INSERT INTO Audit SET 
		dtDateTime=NOW(), 
		chrTableName='$table', 
		idRecord='$id', 
		chrColumnName='-$column', 
		txtOldValue='" . ($record_added?'+':'-') . "$lookup_table/$lookup_id', 
		idUser='" . $_SESSION['idUser'] . "'
		", 'audit_assoc_record'));
}

/* $record_added: Supply true for an addition, false for a removal.
	$change is the text of what has changed.
 */
function audit_assoc_changed($table, $id, $column, $change, $lookup_table, $lookup_id)
{
	return(do_mysql_query("INSERT INTO Audit SET 
		dtDateTime=NOW(), 
		chrTableName='$table', 
		idRecord='$id', 
		chrColumnName='-$column', 
		txtOldValue='=" . $lookup_table . '/' . $lookup_id . ' (' . $change . ")', 
		idUser='" . $_SESSION['idUser'] . "'
		", 'audit_new_record'));
}


function error_report($message)
{
	ob_start();
	print_r(debug_backtrace());
	$trace = ob_get_contents();
	ob_end_clean();

	//mail((defined('BUG_REPORT_ADDRESS')?BUG_REPORT_ADDRESS:'appbugreports@techitsolutions.com'), '[WWDC] Error',
	//	"- ERROR\n----------------\n" . $message . "\n\n\n- STACK\n----------------\n" . $trace
	//	);

	maintenance_page();		
}

function maintenance_page()
{
	doc_top('Maintenance');
?>
	<h1>We&apos;re Sorry...</h1>
	<p>Could not connect to the database server.  We could be experiencing trouble, or the site may be down for maintenance.</p>
	<p>You can press the Refresh button to see if the site is available again.</p>
<?
	doc_bottom();
	die();
}



/* Outputs a column label and image to identify the current "sort" status of a column, with a link to change the current status.  The setting is stored in the session, using the table_name.  To get the current sorted column, use get_sortorder().  $style contains any CSS to attach to the TH, including width.
*/
function list_th($table_name, $label, $column_name, $style, $params='', $morecgi='')
{
	include('includes/list_th.php');
}

/* Returns the current sort column for the table named.  Values are in an array(0 => 'column', 1 => 'order').  Also checks the _REQUEST to see if the sort order/column are supposed to change.
 */
function get_sortby($table_name)
{
	if (isset($_REQUEST['sort_' . $table_name . '_column'])) {
		$_SESSION['sort_' . $table_name . '_column'] = $_REQUEST['sort_' . $table_name . '_column'];
		$_SESSION['sort_' . $table_name . '_order'] = $_REQUEST['sort_' . $table_name . '_order'];
	}

	return(array(
		@$_SESSION['sort_' . $table_name . '_column'],
		@$_SESSION['sort_' . $table_name . '_order'])
		);
}

/* Sets the current sort column for the table named.  $order should be 'ASC' or 'DESC'.
 */
function set_sortby($table_name, $column_name, $order)
{
	$_SESSION['sort_' . $table_name . '_column'] = $column_name;
	$_SESSION['sort_' . $table_name . '_order'] = $order;
}

/* Sets the session default sort column for the table named.  $order should be 'ASC' or 'DESC'.  If the table sort order has been set by choice, this will not override it.
 */
function default_sortby($table_name, $column_name, $order)
{
	if (!isset($_SESSION['sort_' . $table_name . '_column'])) {
		$_SESSION['sort_' . $table_name . '_column'] = $column_name;
		$_SESSION['sort_' . $table_name . '_order'] = $order;
	}
}

/* Turns the array that comes from get_sortby() into the text to use after the "ORDER BY" in SQL.
 */
function sortby_to_sql($sortby)
{
	return(str_replace(array(',', '%'), array(' ' . $sortby[1] . ',', ','), $sortby[0]) . ' ' . $sortby[1]);
}







/* Additional Functions */

function format_date($date)
{
	$str = strftime($_SESSION['chrDateFormat'], $date);
	return($str);
}



function auth_check()
{
	$auth = false;

	if (isset($_SESSION['idUser'])) {  // if this variable is set, they are already authenticated in this session
		$auth = true;
	} else {
			include('includes/auth_check.php');
	}
}

//-----------------------------------------------------------------------------------------------
// New Functions designed by Daniel Tisza-Nitsch and Arthur Gressick
// ** Don't erase these functions as they are new
//-----------------------------------------------------------------------------------------------

function set_mysql_str($str,$field_info,$info_old) { //This function does the additions to an update script
	if($info_old != $_POST[$field_info]) {
		return(($str == '' ? '' : ',')." ". encode($field_info) . "='". encode($_POST[$field_info]) ."' ");
	}
}

function set_mysql_date($str,$field_info,$info_old, $format='') { //This function does the additions to an update script
	if($info_old != date('Y-m-d',strtotime($_POST[$field_info]))) {
		return(($str == '' ? '' : ',')." ". $field_info. "='". date('Y-m-d',strtotime($_POST[$field_info])) ."' ");
	}
}

function set_mysql_time($str,$field_info,$info_old, $format='') { //This function does the additions to an update script
	if($info_old != date('H:i:s',strtotime($_POST[$field_info]))) {
		return(($str == '' ? '' : ',')." ". $field_info. "='". date('H:i:s',strtotime($_POST[$field_info])) ."' ");
	}
}

function set_mysql_datetime($str,$field_info,$info_old, $format='') { //This function does the additions to an update script
	if($info_old != date('Y-m-d H:i:s',strtotime($_POST[$field_info]))) {
		return(($str == '' ? '' : ',')." ". $field_info. "='". date('Y-m-d H:i:s',strtotime($_POST[$field_info])) ."' ");
	}
}

function set_audit_str($str,$field_info,$info_old,$table) { //This fucntion builds the audit_record function
	if($info_old != $_POST[$field_info]) {
		return((($str == '' ? '' : ',')." (". $_SESSION['idUser'] .",'" . $_REQUEST['id'] . "',NOW(),'". $table ."','". encode($field_info) ."','". encode($info_old) ."')"));
	}
}

function audit_record($str) { //This function does the insert into the database for the Audit - Reference the set_audit_str
	return(
		do_mysql_query(
		"INSERT INTO Audit (idUser, idRecord, dtDateTime,chrTablename, chrColumnName, txtOldValue) VALUES ". 
		$str
		,'insert new audit information'));
}

//-----------------------------------------------------------------------------------------------
// New Functions designed by Jason Summers and written by Daniel Tisza-Nitsch
// ** These functions were created to simplify the uploading of information to the database.
//    With these functions, you can send encode/decode all quotes from a given text and ONLY the quotes.
//      This script assumes that you are setting up database tables to accept UTF-8 characters for all 
//		entities.
//-----------------------------------------------------------------------------------------------

function encode($val,$extra="") {
	$val = stripslashes($val);
	$val = str_replace("'",'&#39;',$val);
	$val = str_replace('"',"&quot;",$val);
	if($extra == "tags") { 
		$val = str_replace("<",'&lt;',stripslashes($val));
		$val = str_replace('>',"&gt;",$val);
	}
	if($extra == "amp") { 
		$val = str_replace("&",'&amp;',stripslashes($val));
	}
	return $val;
}

function decode($val,$extra="") {
	#BAH!  IE doesn't accept &quot; ... have to add the numeric value in instead.  Old projects keep both on decode.
	$val = str_replace('&quot;','"',$val);
	$val = str_replace('&#39;',"'",$val);
	$val = str_replace("&apos;","'",$val);
	if($extra == "tags") { 
		$val = str_replace('&lt;',"<",$val);
		$val = str_replace("&gt;",'>',$val);
	}
	if($extra == "amp") { 
		$val = str_replace("&amp;",'&',stripslashes($val));
	}
	return $val;
}
