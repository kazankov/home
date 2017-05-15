 <?php

function str_intersection($a, $b) {
	$out = '';
	$l1 = mb_strlen($a);
	$l2 = mb_strlen($a);
	
	for($i = 0; $i < min($l1, $l2); $i++){
		if($a[$i] == $b[$i]) {
			$out .= $a[$i];
		}else{
			return $out;
		}
	}
	
	return $out;
}


function process($s){
	$periods = array();
	
	$start = $s;
	if (preg_match('/(\d+)-(\d+)/', $s, $matches)) {
		$v1 = $matches[1];
		$v2 = $matches[2];
		
		$start = str_intersection($v1, $v2);
		
		$v1 = mb_substr($v1, mb_strlen($start)); 
		$v2 = mb_substr($v2, mb_strlen($start)); 
		
		$v1 = ltrim($v1, '0');
		$v2 = ltrim($v2, '0');			
		
		if(mb_strlen($v1) != mb_strlen($v2))
		{
			
			$l1 = mb_strlen($v1);
			$l2 = mb_strlen($v2);
			
			if($l2 > $l1) {
				$v1 = str_repeat('0', $l2 - $l1). $v1;
			}else{
				$v2 = str_repeat('0', $l1 - $l2). $v2;
			}
		}
		
		$getTail = false;
		if(mb_strlen($v1) > 1){
			$v1 = intval($v1);
			
			$tail = $v1 % 10;
			$main = $v1 - $tail;
			
			$periods[]= [$start.($main / 10), 0, $tail];
			$getTail = true;
		}
			
		if(mb_strlen($v2) > 1){
			$v2 = intval($v2);
			
			$tail = $v2 % 10;
			$main = $v2 - $tail;	

			$periods[]= [$start.($main / 10), 0, $tail];
			
			$getTail = true;
		}
		

		if(!$getTail){
			
			$v1 = intval($v1);
			$v2 = intval($v2);
			
			if($v2 - $v1 <= 0) throw new Exception('difference <= 0');
			
			$periods[]= [$start, $v1, $v2];
		}
	}else{
		$periods[] = [$start, 0, 9];
	}
	
	return $periods;
}


function gotcha(&$buf, $begin, $s){
	$periods = process($s);
	foreach($periods as $p){
		$out = '8'.$begin;
		
		$start = $p[0];
		$v1 = $p[1];
		$v2 = $p[2];
	
		$out.= $start;
		$len = mb_strlen($out);
		
		if($len >  10) {$out = '8'.$begin.$s; echo $begin.'/'.$s. " > 10 \r\n";} //throw new Exception("{$len} >  9");
		else{
			if($v1 || $v2) {
				if(!$v1) $v1 = 0;
				if(!$v2) throw new Exception('not $v2');
				
				$out.= '['.$v1.'-'.$v2.']';
			}else{
				$out.='[0-9]';
			}
			
			if($len < 10) $out .= '.{'.(10 - $len).'}'; 
		}
		
		$buf[]= $out;
	}
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
		//echo $data[5].'/'.$data[6]."\r\n";
	}
}
fclose($handle);

file_put_contents('out.csv', implode(';', $buf));

?>

