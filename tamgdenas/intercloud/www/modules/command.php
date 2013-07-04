<?
//русский текст
function isAssoc($arr)
{
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function fixMongoId($obj)
{
	$out = null;
	if(is_array($obj))
	{
		$out = array();
		if(isAssoc($obj))
		{
			foreach($obj as $k=>$v)
			{
				if($k == '_id')
				{
					$out['id']=(string)$v;
				}else{
					$out[$k] = fixMongoId($v);
				}
			}		
		}else{
			foreach($obj as $v)
			{
				$out[] = fixMongoId($v);
			}	
		}
	}else{
		if(is_object($obj) && get_class($obj) == 'MongoId')
		{
			$out = (string)$obj;
		}else{
			$out = $obj;
		}
	}
	return $out;
}
?>