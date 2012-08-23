<?php
Globals::requireClass('Controller');

Globals::requireTable('ListsTag');
Globals::requireTable('Lists');

class listsTagController extends Controller
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
		$this->lists 	= new ListsTable($config);
		$this->listsTag 	= new ListsTagTable($config);
	}
	
	/*
	 * 根据分类获取榜单列表
	 * ?m=listsTag&a=getlist&tag_id=1
	 * tag_id	榜单tagid 
	 */
	public function getListAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$tag_id = $this->getParam('tag_id');
		if($tag_id == false){
			exit;
		}		

		//得到lists id
		$tagList = $this->listsTag->listAll(array('tag_id'=>$tag_id));
		if(!$tagList){		
			exit;
		}

		$fields = "id, title, uid, content, cover, zannum, prodnum, createtime";
		$order = "rank desc, createtime desc";
		
		$listsIds="";
		foreach($tagList as $listsId){
			$listsIdArr[] = $listsId['lists_id'];
		}
		$listsIds = implode(",", $listsIdArr);

		//in $listsIdArr 查询
		$where = "id in ({$listsIds})";

		$listsList = $this->lists->listAllWithFields($fields, $where, $order);
		
		echo $this->customJsonEncode($listsList);
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

Config::extend('listsTagController', 'Controller');
