<?php
clearstatcache();

function errorHandler($errno, $errstr, $errfile, $errline)
{
	header('HTTP/1.1 500 Internal Server Error');
	die($errstr);
}

function stripCommentSymbols($s)
{
	$s = preg_replace('/\/\*\*/', '', $s);
	$s = preg_replace('/\*\//', '', $s);
	return preg_replace('/\*/', '', $s);
}

function showCmds($method)
{
	$srv = new ReflectionClass($method); 
	echo "<p align='center'><b>".stripCommentSymbols($srv->getDocComment())."</b></p>";
	$farr = $srv->getMethods();
	foreach($farr as $cmd)
	{
		$refl = $srv->getMethod($cmd->name);
		$params = $refl->getParameters();
		$buf = array();
		for($i = 0; $i < count($params); $i++)
		{	
			$buf[] = $params[$i]->getName();
		}
		echo "<p><b>{$cmd->name}(".implode(', ', $buf).")</b></p>";
		echo stripCommentSymbols($cmd->getDocComment());
		//здесь нужно сделать форму, чтобы выполнять не только GET
		echo "<br><br><table><tr><td><form name='{$method}_{$cmd->name}'><table>";
		echo "<input type='hidden' name='method' value='$method'>";
		foreach($buf as $p)
		{
			echo "<tr><td width='80'>$p: </td><td><input name='$p'></td></tr>";
		}
		echo "</table></form></td><td valign='top'><button name='{$cmd->name}' title='$method' onclick='alert(\"Не готово\");'>Выполнить</button> <a>&nbsp;</a></td></tr></table><hr>";		
	}
}

set_error_handler(errorHandler, E_ALL^E_NOTICE);


$cmdName = null;
if(!$cmdName)
{
header('Content-type: text/html; charset=utf-8');
?>
<html>
<head>

</head>
<body>
<?php
	showCmds('Test');
?>
</body>
</html>
<?php
}else{
	//execCmd($cmdName, $_GET, $method);
}

//************* команды ***************
/**
* Тестовая команда
*/
class Test{
	/**
	*	получить тестовый объект (пока не работает)
	*/
	function get($argument)
	{
	
	}
}


?>