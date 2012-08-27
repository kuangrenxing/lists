<?php
Globals::requireClass('Controller');

Globals::requireTable('Myitem');
Globals::requireTable('Mytag');

class MytagController extends Controller
{

	protected $myitem;
	protected $mytag;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> true,
		'layoutEnabled'	=> true,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		
		$this->mytag		= new MytagTable($config);
		$this->myitem		= new MyitemTable($config);
	}
	
	/*
	 * 根据tag获取单品列表
	 * ?m=mytag&a=myitemDetail&tag_id=26&page=2
	 * tag_id	单品tagid
	 * page		页数
	 */
	public function myitemDetailAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$tagid = $this->getIntParam("tag_id");
		$page = $this->getIntParam("page");
		if(!$tagid){
			exit;
		}
		//得到myitem表id my_id
		$whereMytag = array('tag_id'=>$tagid);
		
		$fields="my_id";
		$order = "id desc";
// 		$count = $this->mytag->listCount($whereMytag);
// 		$pageSize = 100000;//12条一页		
// 		$paging = $this->getPaging($count, $pageSize, $pageId, 3);		
// 		$mytagArr = $this->mytag->listPageWithFields($fields,$whereMytag, $order, $pageSize, $pageId);
		$mytagArr = $this->mytag->listAllWithFields($fields,$whereMytag,$order);
		
		foreach($mytagArr as $i=>$mytag){
			$myid[] = $mytag['my_id'];
		}
		
		$in = implode(",", $myid);
		$whereMyitem = "id in($in)  ";		
		// 得到单品信息
		$fieldsMyitem = "id,uid,ow,oh,maincat_id,subcat_id,third_id,type,title,price,discount,img_url,source_site_url,source_img_url,tags,summary,favor,likenum";
		
		
		$count = $this->myitem->listCount($whereMyitem);
		$pageSize = 20;//12条一页
		$paging = $this->getPaging($count, $pageSize, $pageId, 3);
// 		$myitem = $this->myitem->listAllWithFields($fieldsMyitem, $whereMyitem);
		$myitem = $this->myitem->listPageWithFields($fieldsMyitem,$whereMyitem, $order, $pageSize, $pageId);
		foreach($myitem as $i=>$v)
		{
			$myitem[$i]['img_url'] = IMAGE_DOMAIN.$myitem[$i]['img_url'];
		}
		
		if(!$myitem){
			exit;
		}
		
		$data['page'] = array(
				'count'=>$count,
				'pageSize'=>$pageSize,
				'page'=>$page,
				);
		$data['list'] = $myitem;
		echo $this->customJsonEncode($data);
	
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

Config::extend('MytagController', 'Controller');
