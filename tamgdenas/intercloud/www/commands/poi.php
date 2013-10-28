<?require_once $_SERVER['DOCUMENT_ROOT'].'/modules/command.php';/*** POI*/class Poi{	/**	*	получить POI (тестовый id = 519d20099fe5c37c6b00000a )	*/	function get($id)	{		global $db;		return fixMongoId($db->poi->findOne(array('_id' => new MongoId($id))));	}	/**	*	Удалить POI 	*/	function delete($id)	{		global $db;		$buf = $db->poi->remove(array('_id' => new MongoId($id)), array("justOne" => true));		return !!$buf['ok'];	}			/**	*	Создать POI 	*/	function put($name, $geoPoint, $types)	{		if(!$name)error('Введите название');		if(!$geoPoint)error('Введите координаты');				$geoPoint = json_decode($geoPoint);		if($types) $types = json_decode($types);		global $db;				$obj = 	array(			'name'=>$name,			'geoPoint'=>$geoPoint,			'types'=>$types		);				if(!$db->poi->insert($obj))error('Ошибка записи в базу');		return fixMongoId($obj);	}	/**	*	Редактировать POI  	*/		function post($id, $sourceId, $addFields, $images, $types, $desc, $geoPoint)	{		if(!$id)error('Id не указан');		global $db;				if($geoPoint) $geoPoint = json_decode($geoPoint);		if($types) $types = json_decode($types);				if($images) $images = json_decode($images);		if($addFiles) $addFields = json_decode($addFields);						$obj = $db->poi->findOne(array('_id' => new MongoId($id)));				if(!$obj) error("POI с идентификатором {$id} не существует");				$obj['sourceId'] = $sourceId;		$obj['addFields'] = $addFields;		$obj['images'] = $images;		$obj['types'] = $types;		$obj['desc'] = $desc;		$obj['geoPoint'] = $geoPoint;				if(!$db->poi->update(array('_id' => new MongoId($id)), $obj))error('Ошибка записи в базу');		return fixMongoId($obj);		}		}?>