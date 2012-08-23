<?php
Globals::requireClass('Table');

class ListsZanTable extends Table
{
	public static $defaultConfig = array(
			'table' => 'tb_lists_zan'
	);
}

Config::extend('ListsZanTable', 'Table');
