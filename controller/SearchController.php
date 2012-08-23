<?php
Globals::requireClass('Controller');
Globals::requireTable('User');

class SearchController extends Controller
{
	protected $user;
	protected $pinpai;
	protected $price;
	protected $star;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> true,
		'layoutEnabled'	=> true,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->user  	= new UserTable($config);
		
		$this->pinpai   = array(
			
			'px' => array(
				'2702' => '凡客诚品',
				'245' => 'Classified',
				'2745' => '欧梦达',
				'2797' => '麦包包',
				'2732' => '时尚起义',
				'1955' => 'Topshop',
				'423' => 'Juicy Couture',
				'1104' => 'BKE',
				'337' => '马克·雅可布之马克'
			),
			'prod' => array(
				'2702' => '凡客诚品',
				'245' => 'Classified',
				'2745' => '欧梦达',
				'2797' => '麦包包',
				'2732' => '时尚起义',
				'1955' => 'Topshop',
				'423' => 'Juicy Couture',
				'1104' => 'BKE',
				'337' => '马克·雅可布之马克'
			)
		);
		
		$this->price = array(
			'px' => array(
				',500' => '500以下',
				'500,1000' => '500~1000',
				'1000,3000' => '1000,3000',
				'3000,5000' => '3000~5000',
				'5000,10000' => '5000~10000',
				'5000,10000' => '5000~10000',
				'10000,20000' => '10000~20000',
				'20000,50000' => '20000~50000',
				'100000' => '100000以上'
			),
			'prod' => array(
				',100' => '100以下',
				'101,200' => '100-200元',
				'201,500' => '200-500元',
				'500,1000' => '500-1000元',
				'1000,1500' => '1000-1500元',
				'1500' => '1500以上'
			)
		);
		
		$this->star = array(
			'338'	=> '高圆圆',
			'803'	=> '尹恩惠',
			'626'	=> '奥利维亚•巴勒莫',
			'597'	=> '米兰达•可儿',
			'18'	=> '刘雯',
			'797'	=> '布莱克•莱弗利',
			'837'	=> '帕丽斯•希尔顿',
			'613'	=> '艾薇儿·拉维尼',
			'75'	=> '李孝利',
		);
	}
	
	public function indexAction()
	{
		header('Content-type: text/html; charset=utf-8');
		$timestamp = time();

		//1拼秀2单品3街拍
		$search_type_ar = array(1 , 2 , 3);
		$search_type	= $this->getIntParam('t');
		$search_type 	= in_array($search_type , $search_type_ar) ? $search_type : 2;
		
		$query 	= $this->getParam('q');
		$query  = urldecode($query);
		
		$q	= '';
		if($query){
			$q 	= trim($query) ? '*'.trim($query).'*' : '';
		}
		$sort 	= $this->getParam('s');
		$prices	= $this->getParam('price');
		$times	= $this->getParam('time');
		
		$d 		= in_array($this->getParam('d') , array('up','down')) ? $this->getParam('d') : 'up';
		$sort 	= in_array($sort , array('view','time','price')) ? $sort : 'time';
		
		$doWater = 4;
		$page 	= $this->getIntParam('page');
		$page 	= $page <= 0 ? 1 : $page;
		if($doWater)
			$page = $page == 1?$page:($page-1)*$doWater+1;
			
		$tagid	= $this->getIntParam('tagid');
		$bid	= $this->getIntParam('bid');
		$starid = $this->getIntParam('sid');
		$intmax	= 2147483647;
		
		if($this->hasParam('price')){
  			$prices 	= explode(',' , $this->getParam('price'));
  			$l_price 	= intval($prices[0]);
  			$s_price 	= intval($prices[1]);
  			$s_price 	= $s_price >0 ? $s_price : $intmax;
		}else{
  			unset($prices,$l_price,$s_price);
		}
		
		$searchday 	= array();
		$searchday['day']['s_time']	= strtotime(date('Y-m-d' , $timestamp));
		$searchday['day']['e_time'] = $searchday['day']['s_time']+86400;

		$now_time 	= strtotime(date('Y-m-d',$timestamp));
		$week_day 	= date('w',$timestamp);
		$searchday['week']['s_time'] 	= $now_time +( -1* $week_day * 86400);
		$searchday['week']['e_time'] 	= $now_time +( (7- $week_day) * 86400);

		$year	= intval(date('Y' , $timestamp));
		$mon	= intval(date('m' , $timestamp));

		$searchday['mon']['s_time'] 	= strtotime($year.'-'.$mon.'-1');
		if($mon == 12){
  			$searchday['mon']['e_time'] = strtotime(($year + 1).'-'.'01-1');
		}else{
  			$searchday['mon']['e_time'] = strtotime($year.'-'.($mon + 1).'-1');
		}
		if(in_array($times , array('day','week','mon'))){
    		$s_time 	= $searchday[$times]['s_time'];
    		$e_time 	= $searchday[$times]['e_time'];
		}else{
    		$s_time 	= $e_time = false;
		}

		$this->view->px_search_link		= '/s/?t=1&q='.rawurlencode($query);
		$this->view->prod_search_link	= '/s/?t=2&q='.rawurlencode($query);
		$this->view->pic_search_link	= '/s/?t=3&q='.rawurlencode($query);
		
		
		$sort_s	  = $this->getParam('s');
		$prices_s = $this->getParam('price');
		$times_s  = $this->getParam('time');
		$this->view->urlparam	= ($search_type == 1 ? '&t=1&q='.urlencode($query) : ($search_type == 3 ? '&t=3&q='.urlencode($query) : '&t=2&q='.urlencode($query)))
									.(!empty($sort_s)?'&s='.$sort_s:'')
										.(!empty($prices_s)?'&price='.$prices_s:'')
											.(!empty($times_s)?'&time='.$times_s:'')
												.(!empty($tagid)?'&tagid='.$tagid:'')
													.(!empty($bid)?'&bid='.$bid:'')
													.(!empty($starid)?'&sid='.$starid:'');
		$this->view->query 		= $query;
		$this->view->sort 		= $sort;
		$this->view->tagid		= $tagid;
		$this->view->bid 		= $bid;
		$this->view->starid 	= $starid;
		$this->view->d			= $d;
		$this->view->search_type	= $search_type;
		$this->layout->pageTitle	= "搜索".html($query);
		
		$host 	= "localhost";
		$port 	= 3312;
		Globals::requireClass('SphinxApi');
		$cl = new SphinxClient ();
		$cl->SetServer ( $host, $port );

		$conf = array();

		if($search_type == 1)
		{
  			Globals::requireTable('VPinxiu');
  			$VPinxiu	= new VPinxiuTable($this->config);
  			Globals::requireTable('Pinxiu');
  			$Pinxiu		= new PinxiuTable($this->config);
  			
  			$sort_type	= array(
        		'view'	=> 'view',
        		'price'	=> 'sum_price',
        		'time'	=> 'time_created'
        	);
 			$conf	= array(
  				'mode' 		=> SPH_MATCH_ALL,
  				'index'		=>'px;d_px',
  				'sortflag'	=> $d=="down" ? SPH_SORT_ATTR_ASC:SPH_SORT_ATTR_DESC,
  				'sortmode'	=> $sort_type[$sort],
  				'limit' 	=> 4,
  				'ranker' 	=> SPH_RANK_PROXIMITY_BM25
  			);
  			$cl->SetMatchMode( $conf['mode'] );
  			$cl->SetLimits(($page-1)*$conf['limit'] , $conf['limit']);
  			$cl->SetRankingMode( $conf['ranker'] );
  			$cl->SetArrayResult( true );
  			$cl->SetSortMode( $conf['sortflag'], $conf['sortmode']);
  			if($tagid > 0)
    			$cl->SetFilter('tag_id' , array($tagid));
  			if($bid > 0)
    			$cl->SetFilter('bid' , array($bid));
  			if($s_time > 0 && $e_time > 0)
    			$cl->SetFilterRange('time_created' , $s_time , $e_time);
  			if(isset($prices) && isset($l_price) && isset($s_price))
    			$cl->SetFilterRange('price' , $l_price , $s_price);
			$cl->SetFilter('status' , array(1));
  			$res 	= $cl->Query($q , $conf['index']);

  			$list 	= $docids = array();
  			$count	= 0;
  			

  			
  			if($res !== false){
    			if(is_array($res["matches"]))
    			{
      				foreach($res["matches"] as $val)
        				$docids[] 	= $val['id'];
    			}
    			$list   = array();
    			
    			if (count($docids)){
	    			$list	= $Pinxiu->listAll("id in (".implode(',' , $docids).")" , 'id desc');
	    			for($i = 0 ; $i < count($list) ; $i++){
				    	$val = $list[$i];
				        $val['msrc'] = IMAGE_DOMAIN.getPXpath($val['px_pic'],230);
				        $val['link'] = "/px/".$val['id'];
						$val['sum_price'] = $val['price'];
				        $list[$i]=$val;
				        $uidArr[] = $val['uid'];
				        unset($val);
	    			}
    			}
    			
    			//获取用户数据
				if (count($uidArr)){
					$userList = $this->user->getUserByIds($uidArr);
					$this->view->userList = $userList;
					unset($userList);
				}
		
    			$pageSize = $conf['limit']*$doWater;
    			$count	= intval($res['total_found']);
    			$this->view->paging = $this->getPaging($count, $pageSize, $pageId,2);
    			
  			}
  			$this->view->list = $list;
  			$this->view->count	= $count;
 			$this->view->siderPrice	 = $this->price['px'];
  			$this->view->config['name'] = "px";
  			
		}elseif ($search_type == 3){
  			Globals::requireTable('Pic');
      		$Pic  = new PicTable($this->config);
      		
      		if ($starid){
      			Globals::requireTable('Star');
      			$Star = new StarTable($this->config);
      			$starInfo = $Star->getRow($starid);
      			if ($starInfo){
      				$this->layout->pageTitle = "搜索 ".$starInfo['name'];
      				$this->view->query	= $starInfo['name'];
      			}
      		}
      		
  			$sort_type	= array(
		        'view'=>'view',
		        'price'=>'likenum',
		        'time'=>'time_created'
        	);
		  	$conf	= array(
		  		'mode' 		=> SPH_MATCH_ALL,
		  		'index'		=>'pic;d_pic',
		  		'sortflag'	=> $d=="down" ? SPH_SORT_ATTR_ASC:SPH_SORT_ATTR_DESC,
		  		'sortmode'	=> $sort_type[$sort],
		  		'limit' 	=>12,
		  		'ranker' 	=> SPH_RANK_PROXIMITY_BM25
		  	);
		  	$cl->ResetFilters();
		  	$cl->ResetGroupBy();
		  	$cl->SetMatchMode( $conf['mode'] );
		  	$cl->SetLimits(($page-1)*$conf['limit'],$conf['limit']);
		  	$cl->SetRankingMode( $conf['ranker'] );
		  	$cl->SetArrayResult( true );
		  	$cl->SetSortMode( $conf['sortflag'] , $conf['sortmode']);
		  	if($tagid > 0)
		    	$cl->SetFilter('tid' , array($tagid));
		    if($starid > 0)
		    	$cl->SetFilter('sid' , array($starid));
		  	if($s_time > 0 && $e_time > 0)
		    	$cl->SetFilterRange('time_created' , $s_time , $e_time);
		    $cl->SetFilter('del' , array(0));
			$cl->SetFilter('flag' , array(1));
		  	$res 	= $cl->Query($q, $conf['index']); 

		  	$list 	= $docids = array();
		  	$count	= 0;
			//$res = true; //测试用的
		  	if($res !== false){
		    	if(is_array($res["matches"])){
		      		foreach($res["matches"] as $val)
		        		$docids[] = $val['id'];
		    	}
				
		    	if (count($docids)){
			    	$list	= $Pic->listAll("id in (".implode(',' , $docids).")" , 'id desc');
					$uid_arr = array();
			    	for($i=0;$i<count($list);$i++){
			        	$val = $list[$i];
						$uid_arr[] = $val['uid'];	
			       	 	$val['msrc'] = IMAGE_DOMAIN.getPicPath($val['img_url'],210);
			        	$val['link'] = "/pic/".$val['id'];
			        	$list[$i]=$val;
			        	unset($val);
			    	}
					
					//获取用户数据
					if (count($uid_arr)){
						$userList = $this->user->getUserByIds($uid_arr);
						$this->view->userList = $userList;
						unset($userList);
					}
		    	}
		    	
		    	$pageSize 	= $conf['limit']*$doWater;
		    	$count		= $res['total_found'];
		    	$this->view->paging = $this->getPaging($count, $pageSize, $pageId,2);
		  	}
		  	
		  	$this->view->list = $list;
		  	$this->view->count	= $count;
 			$this->view->siderStar	 = $this->star;
 			$this->view->config['name'] = "pic";
		}else{
  			Globals::requireTable('Myitem');
      		$Product  = new MyitemTable($this->config);
      		
  			$sort_type	= array(
		        'view'=>'view',
		        'price'=>'price',
		        'time'=>'time_created'
        	);
		  	$conf	= array(
		  		'mode' 		=> SPH_MATCH_ALL,
		  		'index'		=>'prod;d_prod',
		  		'sortflag'	=> $d=="down" ? SPH_SORT_ATTR_ASC:SPH_SORT_ATTR_DESC,
		  		'sortmode'	=> $sort_type[$sort],
		  		'limit' 	=>12,
		  		'ranker' 	=> SPH_RANK_PROXIMITY_BM25
		  	);
		  	$cl->ResetFilters();
		  	$cl->ResetGroupBy();
		  	$cl->SetMatchMode( $conf['mode'] );
		  	$cl->SetLimits(($page-1)*$conf['limit'],$conf['limit']);
		  	$cl->SetRankingMode( $conf['ranker'] );
		  	$cl->SetArrayResult( true );
		  	$cl->SetSortMode( $conf['sortflag'] , $conf['sortmode']);
		  	if($tagid > 0)
		    	$cl->SetFilter('tag_id' , array($tagid));
		  	if($s_time > 0 && $e_time > 0)
		    	$cl->SetFilterRange('time_created' , $s_time , $e_time);
		  	if(isset($prices) && isset($l_price) && isset($s_price))
		      	$cl->SetFilterRange('price' , $l_price , $s_price);
		    $cl->SetFilter('del' , array(0));
			$cl->SetFilter('flag' , array(1));
		  	$res 	= $cl->Query($q, $conf['index']); 
		  	
		  	$list 	= $docids = array();
		  	$count	= 0;
			//$res = true; //测试用的
		  	if($res !== false){
		    	if(is_array($res["matches"])){
		      		foreach($res["matches"] as $val)
		        		$docids[] = $val['id'];
		    	}
				
		    	if (count($docids)){
			    	$list	= $Product->listAll("id in (".implode(',' , $docids).")" , 'id desc');
					$uid_arr = array();
			    	for($i=0;$i<count($list);$i++){
						
			        	$val = $list[$i];
						$uid_arr[] = $val['uid'];	
			       	 	$val['msrc'] = IMAGE_DOMAIN.getPropath($val['img_url'],200);
			        	$val['link'] = "/mt/".$val['id'];
			        	$val['wh'] = getWH(array($val['ow'],$val['oh']),95);
			        	$list[$i]=$val;
			        	unset($val);
			    	}
					
					//获取用户数据
					if (count($uid_arr)){
						$userList = $this->user->getUserByIds($uid_arr);
						$this->view->userList = $userList;
						unset($userList);
					}
		    	}
		    	
		    	$pageSize 	= $conf['limit']*$doWater;
		    	$count		= $res['total_found'];
		    	$this->view->paging = $this->getPaging($count, $pageSize, $pageId,2);
		  	}
		  	
		  	$this->view->list = $list;
		  	$this->view->count	= $count;
 			$this->view->siderPrice	 = $this->price['prod'];
 			$this->view->config['name'] = "prod";
		}
		
		$this->view->pageId = $page;
		
	}
	
	/**
	*搜索瀑布流action
	*/
	public function dataAction()
	{
		header('Content-type: text/html; charset=utf-8');
		$timestamp = time();
		
		//1拼秀2单品3街拍
		$search_type_ar = array(1 , 2 , 3);
		$search_type	= $this->getIntParam('t');
		$search_type 	= in_array($search_type , $search_type_ar) ? $search_type : 2;
		$this->config['layoutEnabled'] = false;
		if($search_type > 1){
			$this->config['viewEnabled'] = false;
		}
		
		$query 	= $this->getParam('q');
		$query  = urldecode($query);
		//echo 'query:'.$query;
		$q	= '';
		if($query){
			$q 	= trim($query) ? '*'.trim($query).'*' : '';
		}
		$sort 	= $this->getParam('s');
		$prices	= $this->getParam('price');
		$times	= $this->getParam('time');
		
		$d 		= in_array($this->getParam('d') , array('up','down')) ? $this->getParam('d') : 'up';
		$sort 	= in_array($sort , array('view','time','price')) ? $sort : 'time';
		$page 	= $this->getIntParam('page');
		$page 	= $page <= 0 ? 1 : $page;
		$tagid	= $this->getIntParam('tagid');
		$bid	= $this->getIntParam('bid');
		$starid = $this->getIntParam('sid');
		$intmax	= 2147483647;
		
		if($this->hasParam('price')){
  			$prices 	= explode(',' , $this->getParam('price'));
  			$l_price 	= intval($prices[0]);
  			$s_price 	= intval($prices[1]);
  			$s_price 	= $s_price >0 ? $s_price : $intmax;
		}else{
  			unset($prices,$l_price,$s_price);
		}
		
		$host 	= "localhost";
		$port 	= 3312;
		Globals::requireClass('SphinxApi');
		$cl = new SphinxClient ();
		$cl->SetServer ( $host, $port );

		$conf = array();
		
		if($search_type == 1)
		{
  			Globals::requireTable('VPinxiu');
  			$VPinxiu	= new VPinxiuTable($this->config);
  			Globals::requireTable('Pinxiu');
  			$Pinxiu		= new PinxiuTable($this->config);
  			$sort_type	= array(
        		'view'	=> 'view',
        		'favor'	=> 'favor',
        		'price'	=> 'sum_price',
        		'time'	=> 'time_created'
        	);
 			$conf	= array(
  				'mode' 		=> SPH_MATCH_ALL,
  				'index'		=>'px;d_px',
  				'sortflag'	=> $d=="down" ? SPH_SORT_ATTR_ASC:SPH_SORT_ATTR_DESC,
  				'sortmode'	=> $sort_type[$sort],
  				'limit' 	=>4,
  				'ranker' 	=> SPH_RANK_PROXIMITY_BM25
  			);
  			$cl->SetMatchMode( $conf['mode'] );
  			$cl->SetLimits(($page-1)*$conf['limit'] , $conf['limit']);
  			$cl->SetRankingMode( $conf['ranker'] );
  			$cl->SetArrayResult( true );
  			$cl->SetSortMode( $conf['sortflag'], $conf['sortmode']);
  			if($tagid > 0)
    			$cl->SetFilter('tag_id' , array($tagid));
  			if($bid > 0)
    			$cl->SetFilter('bid' , array($bid));
  			if(isset($prices) && isset($l_price) && isset($s_price))
    			$cl->SetFilterRange('price' , $l_price , $s_price);
  			$res 	= $cl->Query($q , $conf['index']);
  			$list 	= $docids = array();
  			$count	= 0;
  			
  			if($res !== false){
    			if(is_array($res["matches"]))
    			{
      				foreach($res["matches"] as $val)
        				$docids[] 	= $val['id'];
    			}
    			$list   = array();
    			
    			if (count($docids)){
	    			$list	= $Pinxiu->listAll("id in (".implode(',' , $docids).")" , 'id desc');
	    			for($i = 0 ; $i < count($list) ; $i++){
				    	$val = $list[$i];
				        $val['msrc'] = IMAGE_DOMAIN.getPXpath($val['px_pic'],230);
				        $val['link'] = "/px/".$val['id'];
						$val['sum_price'] = $val['price'];
				        $list[$i]=$val;
				        $uidArr[] = $val['uid'];
				        unset($val);
	    			}
    			}
    			
    			//获取用户数据
				if (count($uidArr)){
					$userList = $this->user->getUserByIds($uidArr);
					$this->view->userList = $userList;
					unset($userList);
				}
		
    			$pageSize = $conf['limit'];
    			$count	= intval($res['total_found']);
  			}
  			$this->view->list = $list;
  			$this->view->count	= $count;
  			$this->view->config['name'] = "search";
		}elseif ($search_type == 3){
  			Globals::requireTable('Pic');
      		$Pic  = new PicTable($this->config);
      		
  			$sort_type	= array(
		        'view'=>'view',
		        'price'=>'likenum',
		        'time'=>'time_created'
        	);
		  	$conf	= array(
		  		'mode' 		=> SPH_MATCH_ALL,
		  		'index'		=>'pic;d_pic',
		  		'sortflag'	=> $d=="down" ? SPH_SORT_ATTR_ASC:SPH_SORT_ATTR_DESC,
		  		'sortmode'	=> $sort_type[$sort],
		  		'limit' 	=>12,
		  		'ranker' 	=> SPH_RANK_PROXIMITY_BM25
		  	);
		  	$cl->ResetFilters();
		  	$cl->ResetGroupBy();
		  	$cl->SetMatchMode( $conf['mode'] );
		  	$cl->SetLimits(($page-1)*$conf['limit'],$conf['limit']);
		  	$cl->SetRankingMode( $conf['ranker'] );
		  	$cl->SetArrayResult( true );
		  	$cl->SetSortMode( $conf['sortflag'] , $conf['sortmode']);
		  	if($tagid > 0)
		    	$cl->SetFilter('tid' , array($tagid));
		    if ($starid > 0)
		    	$cl->SetFilter('sid' , array($starid));
		  	if($s_time > 0 && $e_time > 0)
		    	$cl->SetFilterRange('time_created' , $s_time , $e_time);
		    $cl->SetFilter('del' , array(0));
			$cl->SetFilter('flag' , array(1));
		  	$res 	= $cl->Query($q, $conf['index']); 
		
		  	$list 	= $docids = array();
		  	$count	= 0;
			//$res = true; //测试用的
		  	if($res !== false){
		    	if(is_array($res["matches"])){
		      		foreach($res["matches"] as $val)
		        		$docids[] = $val['id'];
		    	}
				
		    	if (count($docids)){
			    	$list	= $Pic->listAll("id in (".implode(',' , $docids).")" , 'id desc');
					$uid_arr = array();
			    	for($i=0;$i<count($list);$i++){
			        	$val = $list[$i];
						$uid_arr[] = $val['uid'];	
			       	 	$val['msrc'] = IMAGE_DOMAIN.getPicPath($val['img_url'],210);
			        	$val['link'] = "/pic/".$val['id'];
			        	$list[$i]=$val;
			        	unset($val);
			    	}
					
					//获取用户数据
					if (count($uid_arr)){
						$userList = $this->user->getUserByIds($uid_arr);
						if(!empty($userList))
						{
							$userData = array();
							foreach($userList as $k=>$v)
							{
								$userData[$k]['head_pic'] = IMAGE_DOMAIN.getUserPath($v['head_pic'] , 36);
								$userData[$k]['username'] = $v['username'];
								$userData[$k]['id'] = $v['id'];
								$userData[$k]['sex'] = $v['sex'];
							}
							unset($userList);
							$userList = $userData;
						}
						$all_arr['userlist'] = !empty($userList)?$userList:array();
						unset($userList);
					}
		    	}
		    	
		    	foreach($list as $k=>$v)
				{
					$list[$k]['time_created'] = transDate($row['time_created']);
					$list[$k]['commnum'] = $list[$k]['commnum'] ? $list[$k]['commnum'] : "";
					$list[$k]['zf'] = $list[$k]['zfnum']?$list[$k]['zfnum']:'&nbsp;';
					$list[$k]['likenum'] = $list[$k]['likenum']?$list[$k]['likenum']:'&nbsp;';
				}
				$all_arr['list'] = $list;
				echo json_encode($all_arr);
		  	}
		  	
		}else{
  			Globals::requireTable('Myitem');
      		$Product  = new MyitemTable($this->config);
      		
  			$sort_type	= array(
		        'view'=>'view',
		        'price'=>'price',
		        'time'=>'time_created'
        	);
		  	$conf	= array(
		  		'mode' 		=> SPH_MATCH_ALL,
		  		'index'		=>'prod;d_prod',
		  		'sortflag'	=> $d=="down" ? SPH_SORT_ATTR_ASC:SPH_SORT_ATTR_DESC,
		  		'sortmode'	=> $sort_type[$sort],
		  		'limit' 	=>12,
		  		'ranker' 	=> SPH_RANK_PROXIMITY_BM25
		  	);
		  	$cl->ResetFilters();
		  	$cl->ResetGroupBy();
		  	$cl->SetMatchMode( $conf['mode'] );
		  	$cl->SetLimits(($page-1)*$conf['limit'],$conf['limit']);
		  	$cl->SetRankingMode( $conf['ranker'] );
		  	$cl->SetArrayResult( true );
		  	$cl->SetSortMode( $conf['sortflag'] , $conf['sortmode']);
		  	if($tagid > 0)
		    	$cl->SetFilter('tag_id' , array($tagid));
		  	if($bid > 0)
		    	$cl->SetFilter('bid' , array($bid));
		  	if($s_time > 0 && $e_time > 0)
		    	$cl->SetFilterRange('time_created' , $s_time , $e_time);
		  	if(isset($prices) && isset($l_price) && isset($s_price))
		      	$cl->SetFilterRange('price' , $l_price , $s_price);
		  	$res 	= $cl->Query($q, $conf['index']); 
		
		  	$list 	= $docids = array();
		  	$count	= 0;
			//$res = true; //测试用的
		  	if($res !== false){
		    	if(is_array($res["matches"])){
		      		foreach($res["matches"] as $val)
		        		$docids[] = $val['id'];
		    	}
				
		    	//$docids = array(141343,141362,141367,141407,141412,141434,141413,141343,141377,141380,141320,141346,141347); //测试用的
		    	if (count($docids)){
			    	$list	= $Product->listAll("id in (".implode(',' , $docids).")" , 'id desc');
					$uid_arr = array();
			    	for($i=0;$i<count($list);$i++){
						
			        	$val = $list[$i];
						$uid_arr[] = $val['uid'];	
			       	 	$val['msrc'] = IMAGE_DOMAIN.getPropath($val['img_url'],200);
			        	$val['link'] = "/mt/".$val['id'];
			        	$val['wh'] = getWH(array($val['ow'],$val['oh']),95);
			        	$list[$i]=$val;
			        	unset($val);
			    	}
					//获取用户数据
					if (count($uid_arr)){
						$userList = $this->user->getUserByIds($uid_arr);
						if(!empty($userList))
						{
							$userData = array();
							foreach($userList as $k=>$v)
							{
								$userData[$k]['head_pic'] = IMAGE_DOMAIN.getUserPath($v['head_pic'] , 36);
								$userData[$k]['username'] = $v['username'];
								$userData[$k]['id'] = $v['id'];
								$userData[$k]['sex'] = $v['sex'];
							}
							unset($userList);
							$userList = $userData;
						}
						$all_arr['userlist'] = !empty($userList)?$userList:array();
						unset($userList);
					}
		    	}
		    	
		    	$pageSize 	= $conf['limit'];
		    	$count		= $res['total_found'];
		  	}
		  	
			foreach($list as $k=>$v)
			{
				$list[$k]['time_created'] = transDate($row['time_created']);
				$list[$k]['commnum'] = $list[$k]['commnum'] ? $list[$k]['commnum'] : "";
				$list[$k]['zf'] = $list[$k]['zfnum']?$list[$k]['zfnum']:'&nbsp;';
				$list[$k]['likenum'] = $list[$k]['likenum']?$list[$k]['likenum']:'&nbsp;';
				$list[$k]['price'] = !empty($list[$k]['discount']) && $list[$k]['discount'] != 0.00?$list[$k]['discount']:$list[$k]['price'];
			}
			$all_arr['list'] = $list;
			echo json_encode($all_arr);
		}
	}
	
	/**
	 * 用于搭配搜索的
	 */
	public function pxsearchAction(){
		header('Content-type: text/html; charset=utf-8');
		$timestamp = time();
		
		$doWater = 4;
		$page 	= $this->getIntParam('page');
		$page 	= $page <= 0 ? 1 : $page;
		if($doWater)
			$page = $page == 1?$page:($page-1)*$doWater+1;
			
		$tagid	= $this->getParam('tagid');
		$tagid_Arr = array();
		if($tagid){
			$tagid_Arr = explode(" ",$tagid);
		};
		
		$this->view->urlparam	= "tagid=".urlencode($tagid);
		$this->view->tagid		= $tagid;
		
		$host 	= "localhost";
		$port 	= 3312;
		Globals::requireClass('SphinxApi');
		$cl = new SphinxClient ();
		$cl->SetServer ( $host, $port );

		$conf = array();
		$sort 	= 'time';
		$d = 'up';
		$sort_type	= array(
			'view'	=> 'view',
			'price'	=> 'price',
			'time'	=> 'time_created'
		);
		$conf	= array(
			'mode' 		=> SPH_MATCH_ALL,
			'index'		=>'px;d_px',
			'sortflag'	=> $d=="down" ? SPH_SORT_ATTR_ASC:SPH_SORT_ATTR_DESC,
			'sortmode'	=> $sort_type[$sort],
			'limit' 	=> 4,
			'ranker' 	=> SPH_RANK_PROXIMITY_BM25
		);
		
		$cl->ResetFilters();
		$cl->ResetGroupBy();		
		$cl->SetMatchMode( $conf['mode'] );
		$cl->SetLimits(($page-1)*$conf['limit'] , $conf['limit']);
		$cl->SetRankingMode( $conf['ranker'] );
		$cl->SetArrayResult( true );
//		$cl->SetFieldWeights(array('id' => 2,'time_created' => 100));
		$cl->SetSortMode( $conf['sortflag'], $conf['sortmode']);
//		$cl->SetSortMode('SPH_SORT_EXPR','@weight');//按照权重排序

		if(count($tagid_Arr) > 0){
			$cl->SetFilter('tag_id' , $tagid_Arr);
		}
//		$cl->SetFilter('status' , array(1));
//		$cl->SetFilter('istop' , array(1));
		$cl->SetFilter('isgroup' , array(0),true);
		$s_time = mktime(0 , 0 , 0 , 1 , 1 , date('Y'));
		$e_time = time();
		$cl->SetFilterRange('time_created' , $s_time , $e_time);
		
		
//		//场合
//		$cl->SetFilter('occasion' , array(2,3,6,7));
//		//指数1-8
//		$cl->SetFilter('zs' , array(1));
//		//性别0-1
		$cl->SetFilter('sex' , array(0));
//		//风格1-12
//		if($maincat_id > 0){
//			$cl->SetFilter('maincat_id' , array(1));
//		}
		
		$q = "";
		$res = $cl->Query($q, $conf['index']);
		
		$list 	= $docids = array();
		$count	= 0;
		if($res !== false){
			if(is_array($res["matches"])){
				foreach($res["matches"] as $val)
					$docids[] 	= $val['id'];
			}
			unset($val);
			$list   = array();
			
			Globals::requireTable('VPinxiu');
  			$VPinxiu	= new VPinxiuTable($this->config);
  			Globals::requireTable('Pinxiu');
  			$Pinxiu		= new PinxiuTable($this->config);
  			
			if (count($docids)){
				$list	= $Pinxiu->listAll("id in (".implode(',' , $docids).")" , 'id desc');
				for($i = 0 ; $i < count($list) ; $i++){
					$val = $list[$i];
					$val['msrc'] = IMAGE_DOMAIN.getPXpath($val['px_pic'],230);
					$val['link'] = "/px/".$val['id'];
					$val['sum_price'] = $val['price'];
					$list[$i]=$val;
					$uidArr[] = $val['uid'];
					unset($val);
				}
			}
		
			//获取用户数据
			if (count($uidArr)){
				$userList = $this->user->getUserByIds($uidArr);
				$this->view->userList = $userList;
				unset($userList);
			}
			
			$pageSize = $conf['limit']*$doWater;
			if(is_array($res["matches"])){
				$count += intval($res['total_found']);
			}
//			$count	= intval($res['total_found']);
			$this->view->paging = $this->getPaging($count, $pageSize, $pageId,2);
		}
		
		$this->view->list = $list;
		$this->view->count	= $count;
		$this->view->siderPrice	 = $this->price['px'];
		$this->view->config['name'] = "pxsearch";
		
		$this->view->pageId = $page;
	}
	/**
	*搜索瀑布流action
	*/
	public function pxdataAction()
	{
		header('Content-type: text/html; charset=utf-8');
		$timestamp = time();
		$this->config['layoutEnabled'] = false;
		$d 		= 'up';
		$sort 	= 'time';
		$page 	= $this->getIntParam('page');
		$page 	= $page <= 0 ? 1 : $page;
		
		$tagid	= $this->getParam('tagid');
		$tagid_Arr = array();
		if($tagid){
			$tagid_Arr = explode(" ",$tagid);
		};
		
		$host 	= "localhost";
		$port 	= 3312;
		Globals::requireClass('SphinxApi');
		$cl = new SphinxClient ();
		$cl->SetServer ( $host, $port );

		$conf = array();
		
  		Globals::requireTable('VPinxiu');
  		$VPinxiu	= new VPinxiuTable($this->config);
  		Globals::requireTable('Pinxiu');
  		$Pinxiu		= new PinxiuTable($this->config);
  		
  		$sort_type	= array(
        	'view'	=> 'view',
        	'favor'	=> 'favor',
        	'price'	=> 'price',
        	'time'	=> 'time_created'
        );
 		$conf	= array(
  			'mode' 		=> SPH_MATCH_ALL,
  			'index'		=>'px;d_px',
  			'sortflag'	=> $d=="down" ? SPH_SORT_ATTR_ASC:SPH_SORT_ATTR_DESC,
  			'sortmode'	=> $sort_type[$sort],
  			'limit' 	=>4,
  			'ranker' 	=> SPH_RANK_PROXIMITY_BM25
  		);
  		
  		$cl->ResetFilters();
		$cl->ResetGroupBy();
  		$cl->SetMatchMode( $conf['mode'] );
  		$cl->SetLimits(($page-1)*$conf['limit'] , $conf['limit']);
  		$cl->SetRankingMode( $conf['ranker'] );
  		$cl->SetArrayResult( true );
  		$cl->SetSortMode( $conf['sortflag'], $conf['sortmode']);
//  		$cl->SetFieldWeights(array('uid' => 2));
//		$cl->SetSortMode('SPH_SORT_EXPR','@weight');//按照权重排序
		
  		if(count($tagid_Arr) > 0){
			$cl->SetFilter('tag_id' , $tagid_Arr);
		}
		$cl->SetFilter('status' , array(1));
		$cl->SetFilter('istop' , array(1));
		$cl->SetFilter('isgroup' , array(0),true);
		$s_time = mktime(0 , 0 , 0 , 1 , 1 , date('Y'));
		$e_time = time();
		$cl->SetFilterRange('time_created' , $s_time , $e_time);
		
		$q = "";
		$res = $cl->Query($q, $conf['index']);
		
  		$list 	= $docids = array();
  		$count	= 0;
  		
  		if($res !== false){
			if(is_array($res["matches"])){
				foreach($res["matches"] as $val)
					$docids[] 	= $val['id'];
			}
    		$list   = array();
    		
    		if (count($docids)){
	    		$list	= $Pinxiu->listAll("id in (".implode(',' , $docids).")" , 'id desc');
	    		for($i = 0 ; $i < count($list) ; $i++){
			    	$val = $list[$i];
			        $val['msrc'] = IMAGE_DOMAIN.getPXpath($val['px_pic'],230);
			        $val['link'] = "/px/".$val['id'];
					$val['sum_price'] = $val['price'];
			        $list[$i]=$val;
			        $uidArr[] = $val['uid'];
			        unset($val);
	    		}
    		}
    		
    		//获取用户数据
			if (count($uidArr)){
				$userList = $this->user->getUserByIds($uidArr);
				$this->view->userList = $userList;
				unset($userList);
			}
			$pageSize = $conf['limit'];
			if(is_array($res["matches"])){
				$count += intval($res['total_found']);
			}
  		}
  		$this->view->list = $list;
  		$this->view->count	= $count;
  		$this->view->config['name'] = "search";
		$this->view->pageId = $page;
	}
	
	protected function out()
	{
		$this->layout->nav		= 'search';
		parent::out();
	}
}

Config::extend('SearchController', 'Controller');