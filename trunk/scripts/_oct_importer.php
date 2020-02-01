#!/usr/local/php5/bin/php
<? 
	$BF = '../';
	$auth_not_required = 1;
	require($BF .'_lib2.php');
	
	$stores = database_query("SELECT chrName,ID FROM stores WHERE stores.idLocalization=1","get stores");

	while($row = mysqli_fetch_assoc($stores)) {
		$q = "INSERT INTO Events (idStore,idEventType,idEventTitle,dDate,tBegin,tEnd,intSeries) VALUES "; 
		$i = 1;
		while($i <= 31) {
			$date = date('N',strtotime('2007-10-'.($i < 10 ? '0'.$i : $i)));
			if($date == 1) { 			# Monday
				$q .= "('". $row['ID'] ."','6','120','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."'),";
			} else if($date == 2) {		# Tuesday
				$q .= "('". $row['ID'] ."','6','121','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."'),";
			} else if($date == 3) {		# Wednesday
				$q .= "('". $row['ID'] ."','6','124','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."'),('". $row['ID'] ."','6','118','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','".mt_rand(1000000000,9999999999)."'),";
			} else if($date == 4) {		# Thursday
				$q .= "('". $row['ID'] ."','6','123','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."'),";
			} else if($date == 5) {		# Friday
				$q .= "('". $row['ID'] ."','6','119','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','".mt_rand(1000000000,9999999999)."'),";
			} else if($date == 6) {		# Saturday
				$q .= "('". $row['ID'] ."','6','5','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '09:00:00','10:00:00','".mt_rand(1000000000,9999999999)."'),('". $row['ID'] ."','6','117','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','".mt_rand(1000000000,9999999999)."'),";
			} else if($date == 7) {		# Sunday
				$q .= "('". $row['ID'] ."','6','5','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '10:00:00','11:00:00','".mt_rand(1000000000,9999999999)."'),('". $row['ID'] ."','6','7','2007-10-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','".mt_rand(1000000000,9999999999)."'),";
			}
			$i++;
		}
		if(database_query(substr($q,0,-1),"insert for store ".$row['ID'])) {
			echo $row['chrName']." complete.<br />\n";
		} else {
			echo " !!!!!!!!!!! - ". $row['chrName']." Failed!<br />\n";
		}
	}
?>