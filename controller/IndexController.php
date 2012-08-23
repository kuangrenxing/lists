<?php
Globals::requireClass('Controller');
Globals::requireTable('Brand');

class IndexController extends Controller
{
	protected $brand;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> true,
		'layoutEnabled'	=> true,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->brand 	= new BrandTable($config);
	}
	
	public function indexAction()
	{
		
		$this->view->title	= "最全服装风格搭配，自主时尚导购网站，拖！你最爱";
	}
	
	protected function out()
	{
		$this->layout->nav		= 'index';
		parent::out();
	}
}

Config::extend('IndexController', 'Controller');
