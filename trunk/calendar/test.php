<?
	$BF = "../";
	# Setting up initial variables to be used
	if(!isset($_REQUEST['dDate']) || $_REQUEST['dDate'] == '') {
		$intCurDay = idate('d'); 	# int value of current day (ex: 30)
		$intCurMonth = idate('m');	# int value of current month (ex: 7)
		$intCurYear = idate('Y');	# int value of current year (ex: 2007)

		header("Location: ". $BF ."calendar/test.php?dDate=".$intCurYear.'-'.$intCurMonth.'-'.$intCurDay);
		die();
	} else {
		$intDay = idate('d',strtotime($_REQUEST['dDate']));
		$intMonth = idate('m',strtotime($_REQUEST['dDate']));
		$intYear = idate('Y',strtotime($_REQUEST['dDate']));
	}


	$b[2] = "09:15:00";
	$e[2] = "09:30:00";
	$color[2] = "#aaa";

	$b[1] = '09:15:00';
	$e[1] = "10:15:00";
	$color[1] = "#9cc";
	
	$b[3] = "09:30:00";
	$e[3] = "11:15:00";
	$color[3] = "#a3f";
	
	$b[4] = "10:00:00";
	$e[4] = "11:45:00";
	$color[4] = "#83f";
	
	$b[5] = "11:30:00";
	$e[5] = "12:15:00";
	$color[5] = "#b92";
	
	$b[6] = "11:45:00";
	$e[6] = "13:00:00";
	$color[6] = "#bbc";
	
	
	$cnt = 0;
	$i = 0;
	while($i < 7) {
		$tmpb = strtotime($b[$i]);
		$tmpe = strtotime($e[$i]);
	
		$j = 0;
		$match = 1;
		while($j < (7-$i)) {
			$testb = strtotime($b[$j]);
			$teste = strtotime($e[$j]);
			if($testb >= $tmpb && $teste <= $tmpe) { $match++; }
			$j++;
		}
		if($match > $cnt) { $cnt = $match; }
		$i++;
	}

?>
	

		<table cellspacing="0" cellpadding="0" style='width: 880px; border: 1px solid gray;'>
<?	$bb = 0;
	$time = "00:00:00";
	$tcnt = 0;
	while($time != "00:00:00" || $tcnt++ == 0) {
		if(in_array($time, $b)) { 
			$pos = array_search($time, $b);
?>
			<tr>
				<td style='border-bottom: 1px <?=(($bb % 4) == 0 && $bb != 0 ? 'solid' : 'dotted')?> gray; vertical-align: top; width: 46px; font-size: 10px; color: #666; background: #eee;'><?=$time?></td>

<?			while($pos) {?>
				<td colspan='1' align="left" valign="top" rowspan='<?=((strtotime($e[$pos])-strtotime($b[$pos]))/60)/15?>' style='border-bottom: 1px <?=(($bb % 4) == 0 && $bb != 0 ? 'solid' : 'dotted')?> gray; background: <?=$color[$pos]?>; width: <?=floor(880/$cnt)?>px;'><?=$b[$pos]?> - <?=$e[$pos]?></td>
<?				unset($b[$pos]);unset($e[$pos]);
				$pos = array_search($time, $b);
			} ?>
			</tr>

			
<?		} else { 
?>
			<tr>
				<td style='border-bottom: 1px <?=(($bb % 4) == 0 && $bb != 0 ? 'solid' : 'dotted')?> gray; vertical-align: top; width: 46px; font-size: 10px; color: #666; background: #eee;'><?=$time?></td>
				<td colspan='<?=$cnt?>' style='border-bottom: 1px <?=(($bb % 4) == 0 && $bb != 0 ? 'solid' : 'dotted')?> gray;'>&nbsp;</td>
			</tr>
<?
		
		 }
		$time = date('H:i:00',strtotime($time." + 15 minutes"));
		$bb++;
	} ?>
		</table>	
