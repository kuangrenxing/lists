<?php
Globals::requireClass('Controller');

Globals::requireTable('ListsProd');

class ListsProdController extends Controller
{
	protected $listsProd;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> true,
		'layoutEnabled'	=> true,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->listsProd 	= new ListsProdTable($config);
	}
	/*
	 * 榜单与单品进行关联
	 * ?m=listsProd&a=create&lists_id=1&prod_id=1&zannum=20
	 * lists_id		榜单Id
	 * prod_id		单品Id	
	 * zannum		赞数
	 */
	public function createAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$params = $this->getParams("lists_id, prod_id, zannum");		
		
		foreach($params as $i=>$param){
			if(!$param){
				exit;
			}
		}
		//插入数据库
		if($id = $this->listsProd->insert($params, true)){
			echo $id;
		}else{
			echo "";
		}
				
		exit;
	}
	
	protected function out()
	{
		$this->layout->nav		= 'index';
		parent::out();
	}
}

Config::extend('ListsProdController', 'Controller');
