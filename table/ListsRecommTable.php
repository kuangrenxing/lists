<?php
Globals::requireClass('Table');

class ListsRecommTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_lists_recomm'
	);
}

Config::extend('ListsRecommTable', 'Table');
