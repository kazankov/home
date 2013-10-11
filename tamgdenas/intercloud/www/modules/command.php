<?
define('FILE_CONSTANT', null);
//русский текст
require_once 'Net/URL2.php';

function normUrl($url)
{
	$buf = new Net_URL2($url);
	if(!$buf->getScheme())
	{
		$buf = new Net_URL2('http://'.$url);
	}
	return $buf->getNormalizedURL();
}

define('DATETIME_FORMAT', 'Y-m-d H:i:s');
function getDateTime()
{
	return date_format(date_create(), DATETIME_FORMAT);
}

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