<?/*** Тестовая команда*/class Test{	/**	*	получить тестовый объект	*/	function get($argument, $argument2)	{		return array('command'=>'test', 'method'=>'get', 'argument'=>$argument, 'argument2'=>$argument2);	}	/**	*	удалить тестовый объект	*/	function delete($argument)	{		return array('command'=>'test', 'method'=>'delete', 'argument'=>$argument);	}		}?>