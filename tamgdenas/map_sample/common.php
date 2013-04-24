<?php

$_conn = null;

function getConn()
{
	global $_conn;
	if(!$_conn)
	{
		    //$_conn = pg_connect("host=217.199.220.183 dbname=poi user=postgres password=postgres") 
			$_conn = pg_connect("host=localhost dbname=poi user=postgres password=qwerty")
			or die('Could not connect: ' . pg_last_error());
	}
	return $_conn;
}

function freeConn()
{
	global $_conn;
	if($_conn)
	{
		pg_close($_conn);
		$_conn = null;
	}
}
	
clearstatcache();
date_default_timezone_set('Europe/Moscow');
//русский текст
function echoCharset()
{
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
}


class Unit
{
    function head(){}
	function doLoad(){}
}


class Loader
{
	function Loader() {$this->units = array();}
	
	function addUnit($unitName)
	{
		require_once "$unitName.php";
		eval('$out = new '.$unitName.'();');
		return $this->addUnitByObject($out);
	}
	
	function addUnitByObject($obj)
	{
		$this->units[] = $obj;
		return $obj;
	}
	
	function addUnits($unitsNames)
	{
		foreach($unitsNames as $unitName)
		{
			$this->addUnit($unitName);
		}
	}
	
	
	function head()
	{
		if(!$this->units) return;
		foreach($this->units as $unit)
		{
			$unit->head();
		}
	}
	
	function doLoad()
	{
		if(!$this->units) return;
		foreach($this->units as $unit)
		{
			$unit->doLoad();
		}
	}		
}


?>