<?php
Globals::requireClass('Table');

class UserstatTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_userstat'
	);
}

Config::extend('UserstatTable', 'Table');