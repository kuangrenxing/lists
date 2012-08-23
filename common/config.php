<?php
require_once('./class/Config.php');
require_once('./class/Globals.php');

Globals::$self = new Globals(array('debug' => true));

$config = array(
	// db
	'server'		=> 'localhost',
	'username'		=> 'root',
	'password'		=> '',
	'database'		=> 'pinxiu',
	'charset'		=> 'utf8',
	'tablePrefix'	=> '',
	
	'viewEnabled'	=> true,
	'layoutEnabled'	=> true,
);