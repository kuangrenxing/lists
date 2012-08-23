<?php

class View extends Config
{
	public static $defaultConfig = array(
		'dir'		=> './view',
		'name'		=> 'index',
		'extension'	=> 'phtml'
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
	}
	
	public function render()
	{
		$config = $this->config;
		require($config['dir'].'/'.$config['name'].'.'.$config['extension']);
	}
}
