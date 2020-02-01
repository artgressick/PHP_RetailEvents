<?
	$auth_not_required = 1;
	require('_lib.php');
	
	$intMonth = 11;
	$intYear = 2006;
	
	$q = "select idStore FROM StoreMonths WHERE intYear=2006 AND intMonth=11 AND !bDeleted";
	$result = do_mysql_query($q, "checking disapproves");

	while($store = mysql_fetch_assoc($result)) { 

$host = 'localhost';
$user = 'retailevents';
$pass = 'st0res';
$db   = 'retailevents';

			if($connection = @mysql_connect('localhost', 'retailevents', 'st0res')) {
				if(@mysql_select_db('retailevents', $connection)) {

/* This is a count to see how many records were displayed as dissaproved */
		$q = "SELECT count(bApproved) as intCount
			FROM Events 
			JOIN EventTypes ON EventTypes.ID=idEventType  AND (idEventCategory=1 OR idEventCategory=2)
			WHERE !Events.bDeleted AND idStore='" . $store['idStore'] . "' AND MONTH(dDate)='" . $intMonth . "' AND YEAR(dDate)='" . $intYear . "' AND bApproved=0
			ORDER BY dDate ASC";
		$check = mysql_fetch_assoc(do_mysql_query($q, "checking disapproves"));
				
			$query = "SELECT Events.*, EventTypes.idEventCategory, txtEventDescription,
				DATE_FORMAT(dDate, '%W, %M %D') AS chrMonth, TIME_FORMAT(tBegin, '%H:%i %p') AS intStartHour, EventTypes.chrStyleClass
				FROM Events 
				JOIN EventTypes ON EventTypes.ID=Events.idEventType  AND (idEventCategory=1 OR idEventCategory=2)
				LEFT JOIN EventTypeNames ON EventTypeNames.chrEventTitle=Events.chrTitle
				WHERE !Events.bDeleted AND idStore='" . $store['idStore'] . "' AND MONTH(dDate)='" . $intMonth . "' AND YEAR(dDate)='" . $intYear . "' 
				ORDER BY dDate,tBegin ASC";
			$event = do_mysql_query($query, 'get events INSIDE');


				}
			}
# ---------------------------------------------------------------------------------------
# This is the connection to the Corporate Web Servers (Per Chad Little clittle@apple.com)
# ---------------------------------------------------------------------------------------

/*	We want to move all of the approved events to the corporate web server so that Kathy Rose
	can review the approved events. We have to enter the information into a table called "retail.RetailEvents"
	We also have to set a switch value in a table called "retail.stores.bUploaded = 1" everytime we upload
	any new events.*/
					
			
			
			$q = "INSERT INTO RetailEvents (ID,idStore,dDate,tBegin,tEnd,idEventType,chrTitle,chrDescription,intSeries) VALUES";
			$cnt=0;
			while($row = mysql_fetch_assoc($event)) {
				$q .= ($cnt++ == 0 ? '' : ',')."(". $row['ID'] . ",". $row['idStore'] . ",'". $row['dDate'] . "','". $row['tBegin'] . "',
				'". $row['tEnd'] . "',". $row['idEventType'] . ",'". addslashes(decode($row['chrTitle'])) . "','". ($row['chrDescription'] != "" ? addslashes(decode($row['chrDescription'])) : addslashes(decode($row['txtEventDescription']))) . "','". $row['intSeries'] ."')";
				$idStore = $row['idStore'];
			}
			
			echo '<p>'.$q.'</p>';
			if($connection = @mysql_connect('weblab11.apple.com', 'techit', 'dollap')) {
				if(@mysql_select_db('retail', $connection)) {
					do_mysql_query($q,"insert the whole thing to chads DB");
				}
			}			
	}
?>