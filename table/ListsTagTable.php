<?php
Globals::requireClass('Table');

class ListsTagTable extends Table
{
	public static $defaultConfig = array(
			'table' => 'tb_lists_tag'
	);
}

Config::extend('ListsTagTable', 'Table');
