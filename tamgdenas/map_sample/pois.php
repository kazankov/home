<?
//русский текст
$mongo = new MongoClient("mongodb://217.199.220.183");
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
$limit = 10;

$cursor=null;
if(count($poitypes) > 0)
{
	$cursor = $mongo->poi->poi->find(array('types'=>array('$in'=>$poitypes)))->limit($limit);
}else{
	$cursor = $mongo->poi->poi->find()->limit($limit);
}
foreach($cursor as $iter)
{
	$buf = (object)array();
	$buf->name = $iter['name'];
	$buf->latitude = $iter['geoPoint']['lat'];
	$buf->longitude = $iter['geoPoint']['lon'];
	$out[] = $buf;		
}

echo json_encode($out);
?>