#!/usr/local/php5/bin/php
<? 
	$BF = '../';
	$auth_not_required = 1;
	require($BF .'_lib2.php');
	
	$name = "King of Prussia";
	$store = 48;

	$title_results = database_query("select ID,chrEventTitle FROM EventTypeNames WHERE ID IN (120,121,124,118,123,119,5,117,7)","getting titles");
	while($row = mysqli_fetch_assoc($title_results)) {
		$titles[$row['ID']] = $row['chrEventTitle'];
	}

		$q = "INSERT INTO Events (idStore,idEventType,idEventTitle,dDate,tBegin,tEnd,intSeries,dtCreated,chrTitle) VALUES "; 
		$i = 1;
		while($i <= 31) {
			$date = date('N',strtotime('2007-10-'.($i < 10 ? '0'.$i : $i)));
			if($date == 1) { 			# Monday
				$q .= "('". $store ."','6','120','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."',now(),'".$titles['120']."'),";
			} else if($date == 2) {		# Tuesday
				$q .= "('". $store ."','6','121','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."',now(),'".$titles['121']."'),";
			} else if($date == 3) {		# Wednesday
				$q .= "('". $store ."','6','124','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."',now(),'".$titles['124']."'),('". $store ."','6','118','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','".mt_rand(1000000000,9999999999)."',now(),'".$titles['118']."'),";
			} else if($date == 4) {		# Thursday
				$q .= "('". $store ."','6','123','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."',now(),'".$titles['123']."'),";
			} else if($date == 5) {		# Friday
				$q .= "('". $store ."','6','119','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."',now(),'".$titles['119']."'),";
			} else if($date == 6) {		# Saturday
				$q .= "('". $store ."','6','5','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '09:00:00','10:00:00','".mt_rand(1000000000,9999999999)."',now(),'".$titles['5']."'),('". $store ."','6','117','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','".mt_rand(1000000000,9999999999)."',now(),'".$titles['117']."'),";
			} else if($date == 7) {		# Sunday
				$q .= "('". $store ."','6','5','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '10:00:00','11:00:00','".mt_rand(1000000000,9999999999)."',now(),'".$titles['5']."'),('". $store ."','6','7','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','".mt_rand(1000000000,9999999999)."',now(),'".$titles['7']."'),";
			}
			$i++;
		}
		if(database_query(substr($q,0,-1),"insert for store ".$store)) {
			echo $name." complete.<br />\n";
		} else {
			echo " !!!!!!!!!!! - ".$name." Failed!<br />\n";
		}
?>