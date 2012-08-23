<?php
Globals::requireClass('Table');

class ListsTable extends Table
{
	public static $defaultConfig = array(
			'table' => 'tb_lists'
	);
}

Config::extend('ListsTable', 'Table');
