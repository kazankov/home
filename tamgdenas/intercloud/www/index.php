<?php
clearstatcache();

function error($errstr)
{
	throw new Exception($errstr);
}

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
	throw new Exception("{$errfile}, {$errline}: {$errstr}");
}
set_error_handler("myErrorHandler", E_ERROR | E_WARNING);

function myAssertHandler($file, $line, $code)  
{  
  echo "Assertion Failed:<br> 
        <b>File</b> '$file' <br>
        <b>Line</b> '$line' <br>
        <b>Code</b> '$code'<br>";  
}  

assert_options(ASSERT_ACTIVE, 1);  
assert_options(ASSERT_WARNING, 0);  
assert_options(ASSERT_BAIL, 1);  
assert_options(ASSERT_QUIET_EVAL, 1);  
assert_options(ASSERT_CALLBACK, 'myAssertHandler');  



ob_start();
try
{
	$cmdName = null;
	require_once 'Net/URL2.php';
	$urlObj = new Net_URL2('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	$pathArr = explode('/', $urlObj->getPath());
	if(count($pathArr) > 1 && $pathArr[1])
	{
		$cmdName = $pathArr[1];
	}
	
	if(!$cmdName)
	{
	?>
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body>
	<?php
		require_once 'reflection.php';
		foreach (glob("commands/*.php") as $fileName) 
		{
			require_once $fileName;
			$cmdName = basename($fileName, '.php');
			showCmd($cmdName);
		}
	?>
	</body>
	</html>
	<?php
	}else{
		function json_safe_encode($var)
		{
		   return json_encode(json_fix_cyr($var));
		}

		function json_fix_cyr($var)
		{
		   if (is_array($var)) {
			   $new = array();
			   foreach ($var as $k => $v) {
				   $new[json_fix_cyr($k)] = json_fix_cyr($v);
			   }
			   $var = $new;
		   } elseif (is_object($var)) {
			   $vars = get_object_vars($var);
			   foreach ($vars as $m => $v) {
				   $var->$m = json_fix_cyr($v);
			   }
		   } elseif (is_string($var)) {
			   $var = iconv('cp1251', 'utf-8', $var);
		   }
		   return $var;
		}	
	
	
		require_once "commands/{$cmdName}.php";
		$className = ucfirst($cmdName);
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		
		
		//получение переменных запроса
		$vars = null;
		switch($method)
		{
			case 'get':
				$vars = $_GET;
			break;
			case 'post':
				$vars = $_POST;
			break;
			default:
				parse_str(file_get_contents("php://input"),$vars);
		}
		
		//преобразование переменных запроса в аргументы функции
		$_class = new ReflectionClass($className); 		
		$_method = $_class->getMethod($method);
		$params = $_method->getParameters();
		$buf = array();
		for($i = 0; $i < count($params); $i++)
		{	
			$buf[] = $vars[$params[$i]->getName()];
		}
		
		$obj = new $className;
		echo json_encode($_method->invokeArgs($obj, $buf), JSON_UNESCAPED_UNICODE);
	}
}catch (Exception $e)
{
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-Type: text/html; charset=utf-8'); 
	echo $e->getMessage();
}

?>