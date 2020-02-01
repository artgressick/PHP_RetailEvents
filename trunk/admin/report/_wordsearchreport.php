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
		
	$result = mysqli_query($mysqli_connection, $_SESSION['REReport']);

	// create workbook
	$workbook = new Spreadsheet_Excel_Writer();
	$workbook -> setVersion(8);	
	// send the headers with this name
	$workbook->send('Word_Search_Report('. $_SESSION['word'].').xls');	
	
	// create format for column headers
	$format_column_header =& $workbook->addFormat();
	$format_column_header->setBold();
	$format_column_header->setSize(10);
	$format_column_header->setAlign('left');
	
	// create data format
	$format_data =& $workbook->addFormat();
	$format_data->setSize(10);
	$format_data->setAlign('left');
	$format_data->setTextWrap();
	
	// Create worksheet
	$worksheet =& $workbook->addWorksheet('wordsearchreport('. $_SESSION['word'].')');
	$worksheet->hideGridLines();
	
	$column_num = 0;
	$row_num = 0;

	
	$worksheet->setColumn($column_num, $column_num, 30);
	$worksheet->write($row_num, $column_num, 'Event Title', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 50);
	$worksheet->write($row_num, $column_num, 'Event Description', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 30);
	$worksheet->write($row_num, $column_num, 'Event Type', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Date', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Begin Time', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'End Time', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Store Name', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Store Eamil', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Store City', $format_column_header);
	$column_num++;	
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Store State', $format_column_header);
	$column_num++;	
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Store Zip Code', $format_column_header);
	$column_num++;	
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Store Country', $format_column_header);
	$column_num++;	
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Store Phone Number', $format_column_header);
	$column_num++;	
	$worksheet->setColumn($column_num, $column_num, 20);
	$worksheet->write($row_num, $column_num, 'Store Size', $format_column_header);
	$column_num++;	
	
	$row_num++;

	while($row = mysqli_fetch_assoc($result)) {
	
		$column_num = 0;
	
		$worksheet->write($row_num, $column_num, decode($row['chrTitle']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrDescription']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrEventTypeName']), $format_data);
		$column_num++;			
		$worksheet->write($row_num, $column_num, $row['dFormated'], $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, $row['tBegin'], $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, $row['tEnd'], $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrStoreName']), $format_data);
		$column_num++;
		$worksheet->write($row_num, $column_num, decode($row['chrStoreEmail']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrStoreCity']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrStoreState']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrStorePostal']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrStoreCountry']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrStorePhone']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrStoreSize']), $format_data);
		$column_num++;	

	
		
		$row_num++;
	}

	$workbook->close();
	
?>
