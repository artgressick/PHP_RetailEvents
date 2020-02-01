<?php
	require_once "Spreadsheet/Excel/Writer.php";
	
	include('retailevents-conf.php');

	$connection = mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	
	$time = date('Y-m', strtotime('today'));

	function decode($val) {
		#BAH!  IE doesn't accept &quot; ... have to add the numeric value in instead.  Old projects keep both on decode.
		$val = str_replace('&quot;','"',$val);
		$val = str_replace('&#39;',"'",$val);
		$val = str_replace("&apos;","'",$val);
		return $val;
	}


		
	$q = "select chrTitle, Stores.chrName, DATE_FORMAT(dDate, '%M %D, %Y') as fDate
		FROM Events 
		JOIN Stores ON Stores.ID=Events.idStore
		WHERE idEventType='18' AND dDate LIKE '". $time ."-%'
		ORDER BY chrName, fDate";

	$result = mysql_query($q);

	// create workbook
	$workbook = new Spreadsheet_Excel_Writer();
	
	// send the headers with this name
	$workbook->send('Business_day_report.xls');	
	
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
	$worksheet =& $workbook->addWorksheet('Business Day Workshops');
	$worksheet->hideGridLines();
	
	$column_num = 0;
	$row_num = 0;
	
	$worksheet->setColumn($column_num, $column_num, 25);
	$worksheet->write($row_num, $column_num, 'Store Name', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 30);
	$worksheet->write($row_num, $column_num, 'Event Title', $format_column_header);
	$column_num++;
	$worksheet->setColumn($column_num, $column_num, 25);
	$worksheet->write($row_num, $column_num, 'Event Date', $format_column_header);
	$column_num++;
	
	$row_num++;

	while($row = mysql_fetch_assoc($result)) {
	
		$column_num = 0;
		
		$worksheet->write($row_num, $column_num, decode($row['chrName']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, decode($row['chrTitle']), $format_data);
		$column_num++;	
		$worksheet->write($row_num, $column_num, $row['fDate'], $format_data);
		$column_num++;	
		
		$row_num++;
	}

	$workbook->close();
	
?>
