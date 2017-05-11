 <?php

function str_intersection($a, $b) {
	$result = '';
	$len = mb_strlen($a) > mb_strlen($b) ? mb_strlen($b) : mb_strlen($a);
	for ($i = 0; $i < $len; $i++) {
		if (mb_substr($a, $i, 1) == mb_substr($b, $i, 1)) {
			$result. = mb_substr($a, $i, 1);
		} else {
			break;
		}

	}
	return $result;
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

		$out = '8'.$data[5];
		$len = mb_strlen($out);
		if (preg_match('/(\d+)-(\d+)/', $data[6], $matches)) {
			$v1 = intval($matches[1]);
			$v2 = intval($matches[2]);

			$start = str_intersection($matches[1], $matches[2]);
			$nS = mb_strlen($start);

			$len += $nS;

			$out. = $start.'['.mb_substr($matches[1], $nS).'-'.mb_substr($matches[2], $nS).'].';

			$out. = '{'.(10 - $len).'}';

			$buf[] = $out;
		} else {
			$out = '8'.$data[5].$data[6];
			$len = 10 - mb_strlen($out);

			$out. = '[0-9].{'.$len.'}';

			$buf[] = $out;
		}

	}
}
fclose($handle);

file_put_contents('out.csv', implode('; ', $buf));

?  >
