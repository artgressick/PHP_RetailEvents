#!/usr/local/php5/bin/php
<? 
	$BF = '../';
	$auth_not_required = 1;
	require($BF .'_lib2.php');
	
	$stores = database_query("Select chrName,ID from stores where stores.idLocalization=1 AND ID NOT IN (141,32,59,61,34,77)","get stores");

	while($row = mysqli_fetch_assoc($stores)) {
		$q = "INSERT INTO Events (idStore,idEventType,idEventTitle,dDate,tBegin,tEnd,chrTitle,intSeries) VALUES "; 
		$i = 1;
		while($i <= 30) {
			$date = date('N',strtotime('2007-09-'.($i < 10 ? '0'.$i : $i)));
			if($date == 1) {
				$q .= "('". $row['ID'] ."','6','120','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','iPhoto Workshop','".mt_rand(10000,99999999999999999999)."'),";
			} else if($date == 2) {
				$q .= "('". $row['ID'] ."','6','121','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','GarageBand Workshop','".mt_rand(10000,99999999999999999999)."'),";
			} else if($date == 3) {
				$q .= "('". $row['ID'] ."','6','124','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','iWork &#39;08 Workshop','".mt_rand(10000,99999999999999999999)."'),";
			} else if($date == 4) {
				$q .= "('". $row['ID'] ."','6','123','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','iMovie and iDVD Workshop','".mt_rand(10000,99999999999999999999)."'),";
			} else if($date == 5) {
				$q .= "('". $row['ID'] ."','6','119','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '15:00:00','16:00:00','iLife &#39;08','".mt_rand(10000,99999999999999999999)."'),";
			} else if($date == 6) {
				$q .= "('". $row['ID'] ."','6','5','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '09:00:00','10:00:00','Getting Started Workshop','".mt_rand(10000,99999999999999999999)."'),";
			} else if($date == 7) {
				$q .= "('". $row['ID'] ."','6','5','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '10:00:00','11:00:00','Getting Started Workshop','".mt_rand(10000,99999999999999999999)."'),('". $row['ID'] ."','6','7','2007-09-". ($i < 10 ? '0'.$i : $i) ."', '14:00:00','15:00:00','Mac OS X Workshop','".mt_rand(10000,99999999999999999999)."'),";
			}
			$i++;
		}
		database_query(substr($q,0,-1),"insert for store ".$row['ID']);
		echo $row['chrName']." complete.<br />\n";
	}
?>