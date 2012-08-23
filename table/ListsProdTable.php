<?php
Globals::requireClass('Table');

class ListsProdTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_lists_prod'
	);
}

Config::extend('ListsProdTable', 'Table');
