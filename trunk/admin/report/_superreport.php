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
		
	$result = mysqli_query($mysqli_connection, $_SESSION['EXCEL']);

	// create workbook
	$workbook = new Spreadsheet_Excel_Writer();
	
	// send the headers with this name
	$workbook->send('Super_Report.xls');	
	
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
	$worksheet =& $workbook->addWorksheet('Super_Report');
	$worksheet->hideGridLines();
	
	$column_num = 0;
	$row_num = 0;
	
							$chrStoreName = 0;
							$chrStoreCity = 0;
							$chrStoreState = 0;
							$chrStoreCountry = 0;
							$EventsName = 0;
							$chrETName = 0;
							$EventDescription = 0;
							$chrEventType = 0;
							$dtEvent = 0;
							$chrRecapAttendance = 0;
							$chrRecapSales = 0;
							$rRecapSuccess = 0;
							$rRecapAddStaff = 0;
							$rRecapPresended = 0;
										
	if (in_array("chrStoreName", $_SESSION['xlscols'])) {
			$chrStoreName = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Store Name', $format_column_header);
			$column_num++;
	}
	if (in_array("chrStoreCity", $_SESSION['xlscols'])) {
			$chrStoreCity = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Store City', $format_column_header);
			$column_num++;
	}	
	if (in_array("chrStoreState", $_SESSION['xlscols'])) {
			$chrStoreState = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Store State', $format_column_header);
			$column_num++;
	}	
	if (in_array("chrStoreCountry", $_SESSION['xlscols'])) {
			$chrStoreCountry = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Store Country', $format_column_header);
			$column_num++;
	}	
	if (in_array("EventsName", $_SESSION['xlscols'])) {
			$EventsName = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Event Name', $format_column_header);
			$column_num++;
	}
	if (in_array("chrETName", $_SESSION['xlscols'])) {
			$chrETName = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Event Type', $format_column_header);
			$column_num++;		
	}
	if (in_array("EventDescription", $_SESSION['xlscols'])) {
			$EventDescription = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Event Description', $format_column_header);
			$column_num++;		
	}
	if (in_array("dtEvent", $_SESSION['xlscols'])) {
			$dtEvent = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Event Date/Time', $format_column_header);
			$column_num++;
	}
	if (in_array("chrRecapAttendance", $_SESSION['xlscols'])) {
			$chrRecapAttendance = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Event Attendance', $format_column_header);
			$column_num++;
	}
	if (in_array("chrRecapSales", $_SESSION['xlscols'])) {
			$chrRecapSales = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Event Sales Increase', $format_column_header);
			$column_num++;
	}
	if (in_array("rRecapSuccess", $_SESSION['xlscols'])) {
			$rRecapSuccess = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Event Success', $format_column_header);
			$column_num++;
	}
	if (in_array("rRecapAddStaff", $_SESSION['xlscols'])) {
			$rRecapAddStaff = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'Additional Staff Needed', $format_column_header);
			$column_num++;
	}
	if (in_array("rRecapPresended", $_SESSION['xlscols'])) {
			$rRecapPresended = 1;
			$worksheet->setColumn($column_num, $column_num, 30);
			$worksheet->write($row_num, $column_num, 'How Presented', $format_column_header);
			$column_num++;
	}


	$row_num++;

	while($row = mysqli_fetch_assoc($result)) {
	
		$column_num = 0;

	
		if ($chrStoreName == 1) {
			$worksheet->write($row_num, $column_num, decode($row['chrStoreName']), $format_data);
			$column_num++;
		}
		if ($chrStoreCity == 1) {
			$worksheet->write($row_num, $column_num, decode($row['chrStoreCity']), $format_data);
			$column_num++;
		}
		if ($chrStoreState == 1) {
			$worksheet->write($row_num, $column_num, decode($row['chrStoreState']), $format_data);
			$column_num++;
		}
		if ($chrStoreCountry == 1) {
			$worksheet->write($row_num, $column_num, decode($row['chrStoreCountry']), $format_data);
			$column_num++;
		}
		if ($EventsName == 1) {
			($row['chrEventName'] != "" ? $EName = $row['chrEventName'] : ($row['chrTitle'] != "" ? $EName = $row['chrTitle'] : $EName = $row['chrEventTitle'] ));
			$worksheet->write($row_num, $column_num, decode($EName), $format_data);
			$column_num++;
		}
		if ($chrETName == 1) {
			$worksheet->write($row_num, $column_num, decode($row['chrETName']), $format_data);
			$column_num++;
		}
		if ($EventDescription == 1) {
			($row['chrEventName'] != "" || $row['chrTitle'] != "" ? $EDescription = $row['chrDescription'] : $EDescription = $row['txtEventDescription'] );	
			$worksheet->write($row_num, $column_num, decode($EDescription), $format_data);
			$column_num++;
		}
		if ($chrEventType == 1) {
			$worksheet->write($row_num, $column_num, decode($row['chrEventType']), $format_data);
			$column_num++;
		}
		if ($dtEvent == 1) {
			$worksheet->write($row_num, $column_num, $row['dtEvent'], $format_data);
			$column_num++;
		}
		if ($chrRecapAttendance == 1) {
			$worksheet->write($row_num, $column_num, $row['chrRecapAttendance'], $format_data);
			$column_num++;
		}
		if ($chrRecapSales == 1) {
			$worksheet->write($row_num, $column_num, $row['chrRecapSales'], $format_data);
			$column_num++;
		}
		if ($rRecapSuccess == 1) {
			$worksheet->write($row_num, $column_num, $row['rRecapSuccess'], $format_data);
			$column_num++;
		}
		if ($rRecapAddStaff == 1) {
			$worksheet->write($row_num, $column_num, $row['rRecapAddStaff'], $format_data);
			$column_num++;
		}
		if ($rRecapPresended == 1) {
			$worksheet->write($row_num, $column_num, $row['rRecapPresended'], $format_data);
			$column_num++;
		}
	
		$row_num++;
	}

	$workbook->close();
	
?>
