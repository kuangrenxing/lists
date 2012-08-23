<?php
Globals::requireClass('Table');

class ConnectTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_connect'
	);
}

Config::extend('ConnectTable', 'Table');
