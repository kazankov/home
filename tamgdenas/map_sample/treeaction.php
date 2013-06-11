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
		
		$cur = $mongo->poi->poiTypes_buf->findOne(array('_id'=>new MongoId($_GET['id'])));
		
		$cur['name'] = $_GET['name'];
		$cur['sourceNames'] = $buf;
		return json_encode($mongo->poi->poiTypes_buf->save($cur, array('safe'=>true)));
		
		/*return json_encode($mongo->poi->poiTypes_buf->update(
			array('_id'=>new MongoId($_GET['id'])),
			array(
				'name'=>$_GET['name'],
				'sourceNames'=>$buf,
				'parent'=>$cur['parent'] 
			),
			array('safe'=>true)
		)); */
	}
	
	function delete()
	{
		global $mongo;
		if(!$_GET['id']) return false;
		
		return
			$mongo->poi->poiTypes_buf->remove(array('parent'=>new MongoId($_GET['id']))) && 
			$mongo->poi->poiTypes_buf->remove(array('_id'=>new MongoId($_GET['id'])));		
	}
	
	function add()
	{
		global $mongo;
		$cur = $mongo->poi->poiTypes_buf->findOne(array('_id'=>new MongoId($_GET['id'])));
		if(!$cur) return false; 
		
		$obj = array('name'=>'Новый тип');
		if($cur['parent']) $obj['parent'] = $cur['parent'];
		$mongo->poi->poiTypes_buf->insert($obj);
		return json_encode($obj);
	}
	
	function addchild()
	{
		global $mongo;
		$cur = $mongo->poi->poiTypes_buf->findOne(array('_id'=>new MongoId($_GET['id'])));
		if(!$cur) return false; 
		
		$obj = array('name'=>'Новый тип');
		$obj['parent'] = $cur['_id'];
		$mongo->poi->poiTypes_buf->insert($obj);
		return json_encode($obj);	
	}
}


$className = ucfirst($_GET['tree']);
$tree = new $className;
$action = $_GET['action'];
echo  $tree->$action();

?>