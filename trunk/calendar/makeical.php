<?
	include('retailevents-conf.php');

	$mysqli_connection = mysqli_connect($host, $user, $pass);
	mysqli_select_db($mysqli_connection, $db);
	unset($host, $user, $pass, $db);
	
	session_name('RetailEvents');
	session_start();

	function encode($val) {
		$val = str_replace("'",'&#39;',$val);
		$val = str_replace('"',"&quot;",$val);
		return $val;
	}

	# Random key generator.  This was make a rediculously secure key to search for values on.
	function makekey(){
		$tm_start = array_sum(explode(' ', microtime()));            # Starts the microtime for extreme exact values
		$i = 0;
		$pass = "";
		while($i < 4) {                                                # run through this loop 4 times for a 16 char string
			$num = str_shuffle("0123456789");                        # All digits
			$lower = str_shuffle("abcdefghijklmnopqrstuvwxyz");        # All lower case letters
			$upper = str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ");        # All upper case letters
			$random = str_shuffle("~!@#$%^&*()_-=+{[}];:,<.>/?");    # lots of random characters in there too
			# This takes the shuffled values, and grabs one of each, adds the current date and time.. and the
			#   total run time of the script
			$pass .= $num[mt_rand(0, 9)].$upper[mt_rand(0, 25)].$random[mt_rand(0, 26)].$lower[mt_rand(0, 25)];
			$i++;
		}
		$secs_total = array_sum(explode(' ', microtime())) - $tm_start;
		return sha1($pass.date('Y-m-dH:m:s').$secs_total);
	}
	#$results = ;
	
	$key = makekey();
	$query = "SELECT CalendarEvents.ID,CalendarEvents.chrKey,chrCalendarEvent,DAY(dBegin) as dDay, chrColorText,chrColorBG,
		  dBegin,dEnd,tBegin,tEnd,chrSeries,bAllDay,chrReoccur,
		  (SELECT MAX(dEnd) FROM CalendarEvents as CE WHERE !bDeleted AND CalendarEvents.chrSeries=CE.chrSeries) as dMaxDate
		FROM CalendarEvents
		JOIN CalendarTypes ON CalendarTypes.ID=CalendarEvents.idCalendarType
		WHERE !CalendarEvents.bDeleted
		". ($_SESSION['idCalTypes'] != "" ? " AND idCalendarType IN (". $_SESSION['idCalTypes'] .") " : '') ."
		ORDER BY chrSeries,dBegin,tBegin,chrCalendarEvent
	";

	$q = "INSERT INTO CalendarQueries SET 
		chrKEY='". $key ."',
		dtCreated=now(),
		idUser='". $_SESSION['idUser'] ."',
		chrCalendarQuery='". encode($query) ."'
	";
	
	if(mysqli_query($mysqli_connection,$q)) {
				
		header("Location: webcal://dtn-macbook.local/~dnitsch/svn/retailevents/trunk/calendar/ical.php?k=". $key);
		die();
	}