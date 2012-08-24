<?php
Globals::requireClass('Table');

class TagTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_tag'
	);
}

Config::extend('TagTable', 'Table');