#!/usr/local/php5/bin/php
<? 
	$BF = '';
	$auth_not_required = 1;
	require('_lib2.php');
	
	$stores = database_query("SELECT ID,chrName FROM Stores WHERE !bDeleted AND chrCountry='US'","US stores");

	while($row = mysqli_fetch_assoc($stores)) {
		$q = "INSERT INTO Events (idStore,idEventType,idEventTitle,dDate,tBegin,tEnd,chrTitle,intSeries) VALUES "; 
		$i = 16;
		while($i <= 31) {
			$date = date('N',strtotime('2007-08-'.$i));
			if($date != 6 && $date != 7) {
				$q .= "('". $row['ID'] ."','6','117','2007-08-". $i ."','14:00:00','15:00:00','iPhone Workshop: Getting Started','".mt_rand(10000,9999999999999999999999999999999999999999)."'),('". $row['ID'] ."','6','118','2007-08-". $i ."','15:00:00','16:00:00','iPhone Workshop: Going Further','".mt_rand(10000,9999999999999999999999999999999999999999)."'),";
			}
			$i++;
		}
		database_query(substr($q,0,-1),"insert for store ".$row['ID']);
		echo $row['chrName']." complete.\n";
	}
?>
