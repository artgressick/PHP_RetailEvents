<?
include('retailevents-conf.php');
$connection = @mysql_connect($host, $user, $pass);
mysql_select_db($db, $connection);


$row = mysql_fetch_assoc(mysql_query("SELECT blobPhoto FROM Presenters WHERE ID='" . $_REQUEST['id'] . "'"));

header('Content-type: image/jpeg');

if(!$row) {
	cop_out();
} else if(!strlen($row['blobPhoto'])) {
	cop_out();
} else {
	if(@$_REQUEST['size'] == 'thumb') {
		$fullimg = imagecreatefromstring($row['blobPhoto']);

		$x = imagesx($fullimg);
		$y = imagesy($fullimg);

		if(!$x || !$y) {
			cop_out();
		}

		if($x < $y) {
			$new_y = 200;
			$new_x = round(($new_y/$y)*$x);
		} else {
			$new_x = 200;
			$new_y = round(($new_x/$x)*$y);
		}

		$img = imagecreatetruecolor($new_x, $new_y);
		imagecopyresampled($img, $fullimg, 0, 0, 0, 0, $new_x, $new_y, $x, $y);
		imagejpeg($img);
		imagedestroy($fullimg);
		imagedestroy($img);

	} else {
		$img = imagecreatefromstring($row['blobPhoto']);
		imagejpeg($img);
		imagedestroy($img);
	}
}

function cop_out()
{
	$img = imagecreatetruecolor(1, 1);
	$color = imagecolorallocate($img, 255, 255, 255);
	imagesetpixel($img, 0, 0, $color);
	imagejpeg($img);
	imagedestroy($img);
	die();
}
?>

