<?php
	require_once "Spreadsheet/Excel/Writer.php";
	
	include('retailevents-conf.php');
	
	$connection = mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	
	$tmp = date('Y-m', strtotime('+1 month'));
	list($year,$month) = explode('-',$tmp);
	
	$num = date('N',strtotime($tmp.'-1'));
	$LDoM = $tmp.'-'.date('t',strtotime($tmp.'-1'));
	
	if($num != 7) {
		$FDoM = date('Y-m-d', strtotime("-".$num." days",strtotime($tmp.'-1')));
	} else {
		$FDoM = $tmp.'-1';
	}

	function decode($val) {
		#BAH!  IE doesn't accept &quot; ... have to add the numeric value in instead.  Old projects keep both on decode.
		$val = str_replace('&quot;','"',$val);
		$val = str_replace('&#39;',"'",$val);
		$val = str_replace("&apos;","'",$val);
		return $val;
	}
	
	$q = "SELECT Stores.chrName, chrTitle, chrDescription, txtEventDescription, dDate, DATE_FORMAT(dDate,'%w %d') as chrSortDate, EventTypes.chrName as chrEventType,
		DATE_FORMAT(dDate, '%W, %M %D') AS chrMonth, TIME_FORMAT(tBegin, '%l:%i %p') AS intStartHour, TIME_FORMAT(tEnd, '%l:%i %p') AS intEndHour
		FROM Events 
		JOIN EventTypes ON EventTypes.ID=Events.idEventType  AND (idEventCategory=1 OR idEventCategory=2)
		JOIN Stores ON Stores.ID=Events.idStore
		LEFT JOIN EventTypeNames ON EventTypeNames.chrEventTitle=Events.chrTitle
		WHERE idStore IN (SELECT idStore FROM StoreMonths WHERE intMonth='". $month ."' AND intYear='". $year ."' AND enStatus='Approved')
		AND dDate >= '". $FDoM ."' AND dDate <= '". $LDoM ."'
		ORDER BY Stores.chrName,chrSortDate";

	$result = mysql_query($q);

	// create workbook
	$workbook = new Spreadsheet_Excel_Writer();
	
	// send the headers with this name
	$workbook->send('COE-Report.xls');	
	
	// create format for column headers
	$format_column_header =& $workbook->addFormat();
	$format_column_header->setBold();
	$format_column_header->setSize(10);
	$format_column_header->setAlign('left');
	
	// create data format
	$format_data =& $workbook->addFormat();
	$format_data->setSize(10);
	$format_data->setAlign('left');
	
	$newStr = "";
	while($row = mysql_fetch_assoc($result)) {
		if($newStr != $row['chrName']) {
			$newStr = $row['chrName'];

			// Create worksheet
			$worksheet =& $workbook->addWorksheet('Events - '. $row['chrName']);
			$worksheet->hideGridLines();
		
			$column_num = 0;
			$row_num = 0;
		
			$worksheet->setColumn($column_num, $column_num, 25);
			$worksheet->write($row_num, $column_num, 'Store Name', $format_column_header);
			$column_num++;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Event Title', $format_column_header);
			$column_num++;
			$worksheet->setColumn($column_num, $column_num, 20);
			$worksheet->write($row_num, $column_num, 'Event Type', $format_column_header);
			$column_num++;
			$worksheet->setColumn($column_num, $column_num, 25);
			$worksheet->write($row_num, $column_num, 'Event Date', $format_column_header);
			$column_num++;
			$worksheet->setColumn($column_num, $column_num, 10);
			$worksheet->write($row_num, $column_num, 'Start Hour', $format_column_header);
			$column_num++;
			$worksheet->setColumn($column_num, $column_num, 10);
			$worksheet->write($row_num, $column_num, 'End Hour', $format_column_header);
			$column_num++;
			$worksheet->setColumn($column_num, $column_num, 10);
			$worksheet->write($row_num, $column_num, 'Description', $format_column_header);
			$column_num++;
		
			$row_num++;

			$column_num = 0;
			
			$worksheet->write($row_num, $column_num, decode($row['chrName']), $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, decode($row['chrTitle']), $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, $row['chrEventType'], $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, $row['chrMonth'], $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, $row['intStartHour'], $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, $row['intEndHour'], $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, ($row['txtEventDescription'] == '' ? decode($row['chrDescription']) : decode($row['txtEventDescription'])), $format_data);
			$column_num++;	
		
			$row_num++;
		} else {			
	
			$column_num = 0;
			
			$worksheet->write($row_num, $column_num, decode($row['chrName']), $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, decode($row['chrTitle']), $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, $row['chrEventType'], $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, $row['chrMonth'], $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, $row['intStartHour'], $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, $row['intEndHour'], $format_data);
			$column_num++;	
			$worksheet->write($row_num, $column_num, ($row['txtEventDescription'] == '' ? decode($row['chrDescription']) : decode($row['txtEventDescription'])), $format_data);
			$column_num++;	

		
			$row_num++;
		}
	}

	$workbook->close();
	
?>
