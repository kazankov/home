 <?php

function str_intersection($a, $b) {
	$result = '';
	$len = mb_strlen($a) > mb_strlen($b) ? mb_strlen($b) : mb_strlen($a);
	for ($i = 0; $i < $len; $i++) {
		if (mb_substr($a, $i, 1) == mb_substr($b, $i, 1)) {
			$result.= mb_substr($a, $i, 1);
		} else {
			break;
		}

	}
	return $result;
}


function gotcha(&$buf, $begin, $s){
	$out = '8'.$begin;
	if (preg_match('/(\d+)-(\d+)/', $s, $matches)) {
	
		$v1 = $matches[1];
		$v2 = $matches[2];
		
		
		$dif = intval($v2) - intval($v1);
		while($dif > 1) { 
			gotcha($buf, $begin, "{$v1}-".($v1+1));
			$v1 = $v1 + 1;
			$dif = intval($v2) - intval($v1);
		}
		if($dif <= 1)
		{
			$start = str_intersection($v1, $v2);
			$nS = mb_strlen($start);
			
			if(mb_strlen($v1) > 1 || mb_strlen($v2) > 1) return;

			$out.= $start.'['.mb_substr($v1, $nS).'-'.mb_substr($v2, $nS).']';
			
			$len = 10 - $nS;
			if($len < 1) $len = 1;
			if($len > 7) $len = 7;

			$out.= '.{'.$len.'}';
		}
	} else {
		$out.= $s;
		$len = 10 - mb_strlen($out);

		$out.= '[0-9].{'.$len.'}';
	}
	
	$buf[]=$out;
	
	//echo $out."\r\n";
	
	return $out;
}

$handle = fopen("in.csv", "r");
$header = true;
$i = 0;
$buf = array();
if ($handle) {
	while (($line = fgets($handle)) !== false) {
		if ($header) {
			$header = false;
			continue;
		}
		$i++;
		$data = explode(';', $line);
		$out = gotcha($buf, $data[5], $data[6]);
	}
}
fclose($handle);

file_put_contents('out.csv', implode('; ', $buf));

?>
