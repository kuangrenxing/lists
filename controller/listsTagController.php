<?php
Globals::requireClass('Controller');

Globals::requireTable('ListsTag');
Globals::requireTable('Lists');
Globals::requireTable('Tag');

class listsTagController extends Controller
{
	protected $brand;
	protected $lists;
	protected $listsTag;
	protected $tag;
	
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
		$this->tag = new TagTable($config);
	}
	
	//榜单分类
	public $listTag = array(
			0=>'分类1',
			1=>'分类2',
			2=>'分类3',
			3=>'分类4',
			4=>'分类5',
			5=>'分类6',
			6=>'分类7',
			7=>'分类8',
			8=>'分类9',
	);
	
	/*
	 * 根据分类获取榜单列表
	 * ?m=listsTag&a=getlist&tag_word=分类2
	 * tag_id	榜单tagid 
	 */
	public function getListAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$tag_word = $this->getParam('tag_word');
		
		if($tag_word == false){
			exit;
		}
		
		$listTag = $this->listTag;		
		$tag_id = array_search($tag_word, $listTag);
		
		if(empty($tag_id))
		{
			exit;
		}
		//得到lists id
		$tagList = $this->listsTag->listAll(array('tag_id'=>$tag_id));
		if(!$tagList){		
			exit;
		}

		$fields = "id, title, uid, content, cover, zannum, prodnum";
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
	/*
	 * 榜单分类
	 * ?m=listsTag&a=taglits
	*/
	
	public function taglitsAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		//榜单分类
		$listTag = $this->listTag;
		echo $this->customJsonEncode($listTag);
	
		exit;
	}
	
	
	/*
	 * 根据分类搜索获取榜单列表
	* 
	* 
	*/
	public function typeSearchAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
	
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
	
		$keyword = $this->getParam('keyword');
		
		if($keyword == false){
			exit;
		}
		//得到tag id
		$tagIdArr="";
		$tagFields = "id, name";
		$TagWhere = "name like '%$keyword%'";
		$tag = $this->tag->listAllWithFields($tagFields,$TagWhere);
		
		foreach ($tag as $tagname)
		{
			$tagIdArr[]=$tagname['id'];
		}
		$tagId=implode(',', $tagIdArr);	
	

		$where = "tag_id in($tagId)";
		//得到lists id
		$tagList = $this->listsTag->listAll($where);
		if(!$tagList){
			exit;
		}
		
		$fields = "id, title, uid, content, cover, zannum";
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
