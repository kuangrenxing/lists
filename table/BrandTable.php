<?php
Globals::requireClass('Table');

class BrandTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_brand'
	);
}

Config::extend('BrandTable', 'Table');
