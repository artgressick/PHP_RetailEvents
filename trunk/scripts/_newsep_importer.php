#!/usr/local/php5/bin/php
<? 
	$BF = '../';
	$auth_not_required = 1;
	require($BF .'_lib2.php');

		$q = "INSERT INTO Events (idStore,idEventType,idEventTitle,dDate,tBegin,tEnd,chrTitle,intSeries) VALUES "; 
		$i = 1;
		while($i <= 30) {
			$date = date('N',strtotime('2007-09-'.($i < 10 ? '0'.$i : $i)));
			if($date == 1) { // Monday
			} else if($date == 3) { // Wednesday
				$q .= "('78','6','118','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','iPhone Workshop: Going Further','".mt_rand(10000,99999999999999999999)."'),";
				$q .= "('6' ,'6','118','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','iPhone Workshop: Going Further','".mt_rand(10000,99999999999999999999)."'),";
				$q .= "('50','6','118','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','iPhone Workshop: Going Further','".mt_rand(10000,99999999999999999999)."'),";
				$q .= "('112','6','118','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','iPhone Workshop: Going Further','".mt_rand(10000,99999999999999999999)."'),";
			} else if($date == 6) { // Saturday
				$q .= "('78','6','117','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','iPhone Workshop: Getting Started','".mt_rand(10000,99999999999999999999)."'),";
				$q .= "('6' ,'6','117','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','iPhone Workshop: Getting Started','".mt_rand(10000,99999999999999999999)."'),";
				$q .= "('50','6','117','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','iPhone Workshop: Getting Started','".mt_rand(10000,99999999999999999999)."'),";
				$q .= "('112','6','117','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','iPhone Workshop: Getting Started','".mt_rand(10000,99999999999999999999)."'),";

			}
			$i++;
		}
		database_query(substr($q,0,-1),"insert new events ");
		echo "complete.";
?>