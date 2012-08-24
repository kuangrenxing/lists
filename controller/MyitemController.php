<?php
Globals::requireClass('Controller');
Globals::requireTable('Brand');
Globals::requireTable('ListsProd');
Globals::requireTable('Myitem');

class MyitemController extends Controller
{
	protected $brand;
	protected $myitem;
	protected $listsProd;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> true,
		'layoutEnabled'	=> true,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->brand 	= new BrandTable($config);
		$this->myitem 		= new MyitemTable($config);
		$this->listsProd 	= new ListsProdTable($config);
		
	}
	/*
	 * 获取单品详情
	 * ?m=myitem&a=detail&id=1
	 * id	单品id
	 * 
	 */
	public function detailAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$id = $this->getIntParam("id");
		if(!$id){
			exit;
		}
		
		$fieldsMyitem = "id,title,price,summary,img_url";
		$myitem = $this->myitem->getRowWithFields($fieldsMyitem, $id);
		if(!$myitem)
		{
			exit;
		}
		else 
		{			
			$myitem['img_url'] = IMAGE_DOMAIN.$myitem['img_url'];
			$myitem['myid'] = $myitem['id'];
			unset($myitem['id']);
		}
		$listProd = $this->listsProd->getRowWithFields('id,prod_id,zannum',array('prod_id'=>$id));
		
		if($listProd)
		{
			$myitem['zannum'] = $listProd['zannum'];
			$myitem['zannumlink'] = DOMAIN.'?m=myitem&a=addZan&id='.$id;			
		}
		
		echo $this->customJsonEncode($myitem);				
		exit;
	}
	
	
	/**
	 * 单品分类
	 *
	 */
	public function myitencatAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
	
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
	
		$arr = array(
				0 => array('id' => 73 , 'name' => '背心'),
				1 => array('id' => 1582 , 'name' => 'T恤'),
				2 => array('id' => 1774 , 'name' => '短裤'),
				3 => array('id' => 649 , 'name' => '凉鞋'),
				4 => array('id' => 1175 , 'name' => '短裙'),
				5 => array('id' => 2125 , 'name' => '挎包'),
				6 => array('id' => 30 , 'name' => '连衣裙'),
				7 => array('id' => 97 , 'name' => '帽子')
		);
	
		echo customJsonEncode($arr);
	
		exit;
	}
	
	/**
	 * 热门单品tag
	 */
	public function myitentopAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
	
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
	
		$arr = array(
				0 => array('id' => 196 , 'name' => '性感'),
				1 => array('id' => 2686 , 'name' => '职场'),
				2 => array('id' => 1830 , 'name' => '糖果色'),
				3 => array('id' => 1884 , 'name' => '长裙'),
				4 => array('id' => 208 , 'name' => '甜美'),
				5 => array('id' => 1895 , 'name' => '摩登'),
				6 => array('id' => 2697 , 'name' => '显瘦'),
				7 => array('id' => 423 , 'name' => '欧美')
		);
	
		echo customJsonEncode($arr);
	
		exit;
	}
	
	
	/*
	 * 单品增加赞
	 * ?m=myitem&a=addZan&id=1
	 */
	public function addZanAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
	
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
	
		$id = $this->getParam("id");
	
		if(!$id){
			exit;
		}
		//列表
		$myitem = $this->myitem->getRow(array("id"=>$id));
	
		if($myitem == null)
		{
			exit;
		}
		$listProd = $this->listsProd->getRow(array("prod_id"=>$id));
		$ret = $this->listsProd->update(array('zannum'=>$listProd['zannum']+1),array('prod_id'=>$id));
		if($ret)
		{
			$data = array('zannum'=>$listProd['zannum']+1);
			echo $this->customJsonEncode($data);
		}
	
		exit;
	} 
	
	protected function out()
	{
		$this->layout->nav		= 'index';
		parent::out();
	}
	
	
	/**
	 * 由于php的json扩展自带的函数json_encode会将汉字转换成unicode码
	 * 所以我们在这里用自定义的json_encode，这个函数不会将汉字转换为unicode码
	 */
	public function customJsonEncode($a = false) {
		if(is_null($a)) return 'null';
		if($a === false) return 'false';
		if($a === true) return 'true';
		if(is_scalar($a)){
			if(is_float($a)){
				//Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			}
			if(is_string($a)){
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\', '/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
			}else{
				return $a;
			}
		}
		$isList = true;
		for($i = 0,reset($a);$i < count($a);$i++,next($a)){
			if(key($a) !== $i){
				$isList = false;
				break;
			}
		}
		$result = array();
		if($isList){
			foreach($a as $v) $result[] = $this->customJsonEncode($v);
			return '[' . join(',', $result) . ']';
		}else{
			foreach ($a as $k => $v) $result[] = $this->customJsonEncode($k).':'.$this->customJsonEncode($v);
			return '{' . join(',', $result) . '}';
		}
	}
}

Config::extend('MyitemController', 'Controller');
