<?php
Globals::requireClass('Controller');

Globals::requireTable('Lists');
Globals::requireTable('ListsProd');
Globals::requireTable('ListsRecomm');
Globals::requireTable('Myitem');

class ListsController extends Controller
{
	protected $lists;
	protected $listsProd;
	protected $myItem;
	protected $listsRecomm;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> true,
		'layoutEnabled'	=> true,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->lists 	= new ListsTable($config);
		$this->listsProd = new ListsProdTable($config);
		$this->myItem 	= new MyitemTable($config);
		$this->listsRecomm = new ListsRecommTable($config);
	}
	
	/*
	 * 榜单列表
	 * list/index.php?m=lists
	 */
	
	public function indexAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		//列表
		$where = array();
		$count 		= $this->lists->listCount($where);
		$pageSize 	= 10;
		$pagecount = ceil($count/$pageSize);
		$page = $this->getIntParam("page");
		if($pagecount < $page){
			echo "";exit;
		}
		$order 		= array('rank asc');
		$fields = "id,title,cover,zannum ";		
		$this->view->paging = $this->getPaging($count , $pageSize , $pageId);
		$lists = $this->lists->listPageWithFields($fields, $where , $order , $pageSize , $pageId);
		foreach ($lists as $i=>$v)
		{
			$lists[$i]['cover'] = IMAGE_DOMAIN.$lists[$i]['cover'];
			$lists[$i]['link'] = DOMAIN."?m=lists&a=detail&id=".$lists[$i]['id'];
		}
		
		//顶端滚动
		$toplist = $this->listsRecomm->listPageWithFields('id, title, lists_id, cover','',$order,5);
		//得榜单Id
		$toplistScope = array(); 
		foreach ($toplist as $i=>$v)
		{
			$toplistId[] = $v['lists_id'];
		}
		
		
		
		foreach ($toplist as $i=>$v)
		{
			$toplist[$i]['cover'] = IMAGE_DOMAIN.$toplist[$i]['cover'];
			$toplist[$i]['link'] = DOMAIN."?m=lists&a=detail&id=".$toplist[$i]['lists_id'];
		}
		
		
		$data['toplist'] = $toplist;
		$data['lists'] = $lists;
		
		echo $this->customJsonEncode($data);		
		exit;
	}
	
	/*
	 * 创建榜单
	 * ?m=lists&a=create&title=title1&uid=2&content=this%20content&cover=img.jpg&prodnum=12444&status=1
	 * title	标题
	 * uid		用户Id	
	 * content	内容	
	 * cover	 封面
	 * prodnum	产品号码
	 * status	状态
	 */
	public function createAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$params = $this->getParams("title, uid, content, cover ,prodnum, status");
		
		foreach($params as $i=>$param){
			if(!$param){
				exit;
			}
		}
		$params['createtime'] = time();
		$params['updatetime'] = time();
		if($id = $this->lists->insert($params, true)){
			echo  $id;
		}else{
			echo "";
		}

		exit;
	}
	
	/*
	 * 获取榜单详情
	 * ?m=Lists&a=detail&id=1
	 * 得到榜单所以单品信息
	 * id	榜单id
	 */
	public function detailAction()
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
		$listsProds = $this->listsProd->listAll(array("lists_id"=>$id));
		if($listsProds == null)
		{
			exit;
		}		
		foreach($listsProds as $listsProd){
			$prodIdArr[] = $listsProd['prod_id'];	
		}
		$scope = implode(",", $prodIdArr);
		$where = "id in ({$scope})";
		
		$fileds = "id, title, price, img_url";		

		$count 		= $this->myItem->listCount($where);
		$pageSize 	= 10;
		$pagecount = ceil($count/$pageSize);
		$page = $this->getIntParam("page");
		if($pagecount < $page){
			echo "";exit;
		}
		$order 		= array('id desc');	
		
		$this->view->paging = $this->getPaging($count , $pageSize , $pageId);
		$myitem = $this->myItem->listPageWithFields($fileds, $where , $order , $pageSize , $pageId);
		
		
		if(!$myitem){
			exit;
		}else 
		{
			foreach($myitem as $i=>$v)
			{
				$myitem[$i]['img_url'] = IMAGE_DOMAIN.$myitem[$i]['img_url'];
				$myitem[$i]['link'] = DOMAIN.'?m=myitem&a=detail&id='.$v['id'];
			}
			
		}
		//顶端详情
		$listsDetail = $this->lists->listAllWithFields('id,title,content,cover,zannum,prodnum',array('id'=>$id));
		$listsDetail[0]['cover'] = IMAGE_DOMAIN.$listsDetail[0]['cover'];
		$listsDetail[0]['zannumlink'] = DOMAIN.'?m=Lists&a=addZan&id='.$id;
		
		$data['detail'] = $listsDetail;
		$data['lists']['data'] = $myitem;	
		$data['lists']['page']=array(
				'count'=>$count,
				'pageSize'=>$pageSize,
				'pagecount'=>$pagecount,
				'page'=>$page,
		);		
		
		echo $this->customJsonEncode($data);
		
		exit;
	}
	
	/*
	 * 为榜单增加赞数
	 * ?m=Lists&a=addZan&id=1
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
		$lists = $this->lists->getRow(array("id"=>$id));
		
		if($lists == null)
		{
			exit;
		}
		
		$ret = $this->lists->update(array('zannum'=>$lists['zannum']+1),array('id'=>$id));
		if($ret)
		{
			$data = array('zannum'=>$lists['zannum']+1);
			echo $this->customJsonEncode($data);
		}		
		
		exit;
	}
	
	/*
	 * 榜单标题搜索
	 * 
	 */
	public function titleSearchAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$keyword = $this->getParam("keyword");
		
		if(!$keyword){
			exit;
		}
		$fields	= '';
		//列表
// 		$lists = $this->lists->listAllWithFields($fields,array("title"=>$keyword));
		
// 		if($lists == null)
// 		{
// 			exit;
// 		}
		
		////		
		//列表
		$where = "title like '%$keyword%'";

		$count 		= $this->lists->listCount($where);
		$pageSize 	= 10;
		$pagecount = ceil($count/$pageSize);
		$page = $this->getIntParam("page");
		if($pagecount < $page){
			echo "";exit;
		}
		$order 		= array('rank asc');
		$fields = "id,title,cover,zannum ";
		$this->view->paging = $this->getPaging($count , $pageSize , $pageId);
		$lists = $this->lists->listPageWithFields($fields, $where , $order , $pageSize , $pageId);
		foreach ($lists as $i=>$v)
		{
			$lists[$i]['cover'] = IMAGE_DOMAIN.$lists[$i]['cover'];
			$lists[$i]['link'] = DOMAIN."?m=lists&a=detail&id=".$lists[$i]['id'];
		}
		print_r($lists);
		exit;
		////
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

Config::extend('ListsController', 'Controller');
