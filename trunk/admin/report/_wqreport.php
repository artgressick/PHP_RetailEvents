<?php
// INSERT PROJECT NAME HERE
	session_name('RetailEvents');
	session_start();

	require_once "Spreadsheet/Excel/Writer.php";
	
	include('retailevents-conf.php');
	
	$mysqli_connection = mysqli_connect($host, $user, $pass);

	mysqli_select_db($mysqli_connection, $db);
	
	$time = date('Y-m', strtotime('today'));

	function decode($val) {
		#BAH!  IE doesn't accept &quot; ... have to add the numeric value in instead.  Old projects keep both on decode.
		$val = str_replace('&quot;','"',$val);
		$val = str_replace('&#39;',"'",$val);
		$val = str_replace("&apos;","'",$val);
		return $val;
	}
		
	$result = mysqli_query($mysqli_connection,$_SESSION['REReport']);

	// create workbook
	$workbook = new Spreadsheet_Excel_Writer();
	
	// send the headers with this name
	$workbook->send('Weekly-Quarterly_Report.xls');	
	
	// create format for column headers
	$format_column_header =& $workbook->addFormat();
	$format_column_header->setBold();
	$format_column_header->setSize(10);
	$format_column_header->setAlign('left');
	
	// create data format
	$format_data =& $workbook->addFormat();
	$format_data->setSize(10);
	$format_data->setAlign('left');
	
	// Create worksheet
	$worksheet =& $workbook->addWorksheet('Weekly-Quarterly Report');
	$worksheet->hideGridLines();
	
	$column_num = 0;
	$row_num = 0;

	
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Country', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Year', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Quarter', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Week', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Count', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 30);
	$worksheet->write($row_num, $column_num, 'Category', $format_column_header);
	$column_num++;

	
	$row_num++;

	while($row = mysqli_fetch_assoc($result)) {
	
		$column_num = 0;
	
		$worksheet->write($row_num, $column_num, decode($row['chrCountry']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, $row['dYear'], $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, $row['quarter'], $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, $row['dWeek'], $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, $row['intCount'], $format_data);
		$column_num++;					
		$worksheet->write($row_num, $column_num, decode($row['chrCategory']), $format_data);
		$column_num++;	
		
		$row_num++;
	}

	$workbook->close();
	
?>
