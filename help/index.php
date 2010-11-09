<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR."wp-load.php";

if(isset($_GET["name"])){
	$path = $lwb->dir.DS."help".DS."{$_GET['name']}.php";
	if(file_exists($path)){
		require_once $path;
	}
}
