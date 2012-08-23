<?php
Globals::requireClass('Table');

class AdverTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_adver'
	);
}

Config::extend('AdverTable', 'Table');
