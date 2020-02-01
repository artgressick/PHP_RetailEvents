#!/usr/local/php5/bin/php
<? 
	$BF = '../';
	$auth_not_required = 1;
	require($BF .'_lib2.php');
	
	# 75 = Aventura
	$store = 75;

		$q = "INSERT INTO Events (idStore,idEventType,idEventTitle,dDate,tBegin,tEnd,intSeries,dtCreated) VALUES "; 
		$i = 1;
		while($i <= 31) {
			$date = date('N',strtotime('2007-10-'.($i < 10 ? '0'.$i : $i)));
			if($date == 1) { 			# Monday
				$q .= "('". $store ."','6','120','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."',now()),";
			} else if($date == 2) {		# Tuesday
				$q .= "('". $store ."','6','121','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."',now()),";
			} else if($date == 3) {		# Wednesday
				$q .= "('". $store ."','6','124','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."',now()),";
			} else if($date == 4) {		# Thursday
				$q .= "('". $store ."','6','123','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."',now()),";
			} else if($date == 5) {		# Friday
				$q .= "('". $store ."','6','119','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."',now()),";
			} else if($date == 7) {		# Sunday
				$q .= "('". $store ."','6','7','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','".mt_rand(1000000000,9999999999)."',now()),";
			}
			$i++;
		}
		if(database_query(substr($q,0,-1),"insert for store ".$store)) {
			echo "Aventura complete.<br />\n";
		} else {
			echo " !!!!!!!!!!! - Aventura Failed!<br />\n";
		}
?>