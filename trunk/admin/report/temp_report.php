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
	$q = "SELECT E.chrTitle, E.dDate, E.tBegin, E.tEnd, S.chrName
			FROM Events AS E
			JOIN Stores AS S ON E.idStore=S.ID
			WHERE E.dDate = '2008-03-24' OR E.dDate = '2008-03-25' OR E.dDate = '2008-03-26'
			ORDER BY dDate, chrName, tBegin, tEnd
			";
	
	
	$result = mysqli_query($mysqli_connection,$q);

	// create workbook
	$workbook = new Spreadsheet_Excel_Writer();
	
	// send the headers with this name
	$workbook->send('March_24-26_report.xls');	
	
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
	$worksheet =& $workbook->addWorksheet('March Report');
	$worksheet->hideGridLines();
	
	$column_num = 0;
	$row_num = 0;

	
	$worksheet->setColumn($column_num, $column_num, 25);
	$worksheet->write($row_num, $column_num, 'Store', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 25);
	$worksheet->write($row_num, $column_num, 'Event', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Date', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'Start Time', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 15);
	$worksheet->write($row_num, $column_num, 'End Time', $format_column_header);
	$column_num++;

	
	$row_num++;

	while($row = mysqli_fetch_assoc($result)) {
	
		$column_num = 0;
	
		$worksheet->write($row_num, $column_num, decode($row['chrName']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrTitle']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, date('n/j/Y',strtotime($row['dDate'])), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, date('g:i a',strtotime($row['tBegin'])), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, date('g:i a',strtotime($row['tEnd'])), $format_data);
		$column_num++;					
		
		$row_num++;
	}

	$workbook->close();
	
?>
