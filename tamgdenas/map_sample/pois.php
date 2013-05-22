<?
//русский текст
$mongo = new MongoClient("mongodb://217.199.220.182");//"mongodb://217.199.220.183"
$poitypes = array();
if($_GET['types']) 
{
	$buf = json_decode($_GET['types']);
	foreach($buf as $iter)
	{
		$poitypes[]=new MongoId($iter);
	}
}
$out = array();
$limit = 100000;

$cursor=null;
$params = array();
if(count($poitypes) > 0)
{
	$params = array('types'=>array('$in'=>$poitypes));
}
if($_GET['bounds'])
{
	list($latBottom, $lonLeft, $latTop, $lonRight) = explode(',', $_GET['bounds']);
	$latBottom = (int)$latBottom;
	$lonLeft = (int)$lonLeft;
	$latTop = (int)$latTop;
	$lonRight = (int)$lonRight;
	$params['geoPoint'] = array('$within'=>
		array('$box'=>
			array(
				array($latBottom, $lonLeft),
				array($latTop, $lonRight)
			)
		)
	);	
}

$cursor = $mongo->poi->poi->find($params)->limit($limit);
for($i=0; $i < $limit; $i++)
{
	if(!$iter = $cursor->getNext()) break;
	$buf = (object)array();
	$buf->name = $iter['name'];
	$buf->latitude = $iter['geoPoint']['lat'];
	$buf->longitude = $iter['geoPoint']['lon'];
	$out[] = $buf;		
}

echo json_encode($out);
?>