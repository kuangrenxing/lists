<?php
Globals::requireClass('Table');

class LikeitemTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_like_item'
	);
}

Config::extend('LikeitemTable', 'Table');