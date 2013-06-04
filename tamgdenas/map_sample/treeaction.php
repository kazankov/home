<?
//русский текст
$cfg = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config.ini', true);
foreach($cfg as $k=>$v)
{
	$cfg[$k] = (object)$v; 
}
$cfg = (object)$cfg;

$mongo = new MongoClient("mongodb://{$cfg->mongo->host}");

class Our
{
	function edit()
	{
		global $mongo;
		$buf = null;
		if($_GET['alias'])
		{
			$buf = explode(',', $_GET['alias']);
			foreach($buf as $k=>$v)
			{
				$buf[$k] = trim($v);
			}
		}
		
		return json_encode($mongo->poi->poiTypes->update(
			array('_id'=>new MongoId($_GET['id'])),
			array(
				'name'=>$_GET['name'],
				'sourceNames'=>$buf
			),
			array('safe'=>true)
		)); 
	}
}


$className = ucfirst($_GET['tree']);
$tree = new $className;
$action = $_GET['action'];
echo  $tree->$action();

?>