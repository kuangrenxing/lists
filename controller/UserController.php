<?php
Globals::requireClass('Controller');
Globals::requireTable('User');
Globals::requireTable('Connect');
Globals::requireTable('Tweet');
Globals::requireTable('Usermsg');
Globals::requireTable('Userstat');
Globals::requireTable('Lists');
Globals::requireTable('ListsZan');
Globals::requireTable('Likeitem');
Globals::requireTable('Myitem');



class UserController extends Controller
{
	protected $user;
	protected $connect;
	protected $tweet;
	protected $usermsg;
	protected $userstat;
	protected $lists;
	protected $listsZan;
	protected $likeitem;
	protected $myitem;
	
	public static $defaultConfig = array(
		'viewEnabled'	=> false,
		'layoutEnabled'	=> false,
		'title'			=> null
	);
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->user  	= new UserTable($config);
		$this->connect	= new ConnectTable($config);
		$this->tweet	= new TweetTable($config);
		$this->usermsg	= new UsermsgTable($config);
		$this->userstat		= new UserstatTable($config);
		$this->lists 	= new ListsTable($config);
		$this->listsZan 	= new ListsZanTable($config);
		$this->likeitem		= new LikeitemTable($config);
		$this->myitem 		= new MyitemTable($config);
	}
	
	//注册http://192.168.1.21/iphoneweibo/?m=user&a=reg&email=variety1@126.com&username=variety1&psw=admin&sex=1&ip=12.12.12.12&connfrom=1
	/**
	 * 注册参数
	 * email		电子邮箱
	 * username		用户名
	 * psw			密码
	 * sex			性别1--男,2--女
	 * ip			手机的序列号
	 * connfrom		用户信息的来源[1---iphone,2---android]默认：1---iphone
	 * return		用户ID|=|email|=|昵称|=|图片|=|性别
	 */
	public function regAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");

		$params = $this->getParams('email,username,psw,sex,ip,connfrom');
		foreach ($params as $key=>$value){
			if($value == ''){
				echo "";
				exit;
			}
		}
		$emailInfo = $this->user->getRow(array('email' => $params['email']));
		if (isset($emailInfo['id']) && $emailInfo['id']){
			echo "0|=|1|=|0|=|0|=|0";
			exit;
		}
		
		$unameInfo = $this->user->getRow(array('username' => $params['username']));
		if (isset($unameInfo['id']) && $unameInfo['id']){
			echo "0|=|0|=|1|=|0|=|0";
			exit;
		}
		
		if(empty($params['connfrom']) || trim($params['connfrom']) == ''){
			$connfrom = 903;
		}else{
			if(trim($params['connfrom']) == 1){
				$connfrom = 903;
			}else if(trim($params['connfrom']) == 2){
				$connfrom = 803;
			}else{
				$connfrom = 903;
			}
		}
		
		$new['username'] = trim($params['username']);
		$new['email'] = trim($params['email']);
		$new['password'] = md5($params['psw']);
//		$new['username'] = $this->Transmit($params['username']);
//		$new['email'] = $this->Transmit($params['email']);
//		$new['password'] = md5($this->Transmit($params['psw']));
		$new['sex'] = $params['sex'];
		if($new['sex'] == 2)
    		$new['head_pic'] = DEFAULT_NV_LOGO;
		else
    		$new['head_pic'] = DEFAULT_NAN_LOGO;
		$new['reg_ip'] = trim($params['ip']);
		$new['log_ip'] = trim($params['ip']);
		$new["log_time"] = time();
		$new['time_created'] = time();
		$new['connfrom'] = $connfrom;
		$uid = $this->user->add($new , true);
		if (isset($uid) && $uid){
			global $TRYOUT_IMG_URL;
			echo $uid."|=|".$new['email']."|=|".$new['username']."|=|".$TRYOUT_IMG_URL.$new['head_pic']."|=|".$new['sex'];
		}else{
			echo "0|=|0|=|0|=|0|=|0";
		}
		exit;
	}
	
	//登陆http://192.168.1.21/iphoneweibo/?m=user&a=login&username=variety&psw=admin&ip=12.12.12.12
	/**
	 * 登录参数
	 * username		登录名[会员登录名或者电子邮箱]
	 * psw			登录密码
	 * ip			手机的序列号
	 * return		用户ID|=|email|=|昵称|=|图片|=|性别
	 */
	public function loginAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");

		$params = $this->getParams('username,psw,ip');
		foreach ($params as $key=>$value){
			if($value == ''){
				echo "";
				return;
			}
		}
		$params['username'] = trim($params['username']);
		$params['psw'] = trim($params['psw']);
//		$params['username'] = $this->Transmit($params['username']);
//		$params['psw'] = $this->Transmit($params['psw']);
		
		$where = "(username = '".$params['username']."' or email = '".$params['username']."') and password = '".md5($params['psw'])."' and b_flag=0";
		$userInfo = $this->user->getRow($where);
		
		if (!$userInfo){
			echo "0|=|0|=|0|=|0|=|0";
		}else{
			$arruser["log_ip"] = $params['ip'];
			$arruser["log_time"] = time();
			$result = $this->user->modify($arruser, $userInfo["id"]);
			global $TRYOUT_IMG_URL;
			echo $userInfo["id"]."|=|".$userInfo["email"]."|=|".$userInfo["username"]."|=|".$TRYOUT_IMG_URL.$userInfo["head_pic"]."|=|".$userInfo["sex"];
		}
	}
	
	//http://192.168.1.21/iphoneweibo/?m=user&a=userinfo&uid=159881
	/**
	 * 用户的信息
	 * uid			用户ID
	 */
	public function userinfoAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");

		$params = $this->getParams('uid');
		
		if(empty($params['uid']) || trim($params['uid']) == ''){
			echo "";
			return;
		}
		//user查询字段
		$fieldsUser = "id, username, head_pic";		
		$userinfo = $this->user->getRowWithFields($fieldsUser, $params['uid']);
		
		//判断是否为空	
		if($userinfo==false || empty($userinfo)){
			echo "";
			exit;
		}
		
		$userinfo['head_pic'] = IMAGE_DOMAIN.$userinfo['head_pic'];
		//userstat查询字段
		$fieldsUserStat = "follow, fans, likenum";		
		
		$userstat = $this->userstat->getRowWithFields($fieldsUserStat, array('uid' => $params['uid']));
		$userinfo['follow'] = $userstat['follow'];
		$userinfo['fans'] = $userstat['fans'];
		$userinfo['likenum'] = $userstat['likenum'];

		echo $this->customJsonEncode($userinfo);
	}
	
	//http://192.168.1.21/iphoneweibo/?m=user&a=wbuser&id=1404376560&screen_name=zaku&province=11&city=5&gender=m&avatar_large=http://tp1.sinaimg.cn/1404376560/180/0/1&token=a66ad26eb4abca56f340774c1e035f023&token_secret=6d4a0663054029fa3bfaa1b2c23d11565&connfrom=1&ip=12.12.12.12
	/**
	 * 微博用户判断
	 * id			int64	用户UID
	 * screen_name	string	用户昵称
	 * province		int		用户所在地区ID
	 * city			int		用户所在城市ID
	 * gender		string	性别，m：男、f：女、n：未知
	 * avatar_large	string	用户大头像地址
	 * token				绑定后获取的值
	 * token_secret			绑定后获取的值
	 * connfrom				用户信息的来源[1---iphone,2---android]默认：1---iphone
	 * ip					手机的序列号
	 * return				用户ID|=|email|=|昵称|=|图片|=|性别
	 */
	public function wbuserAction(){

		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$params = $this->getParams('id,screen_name,province,city,gender,avatar_large,token,token_secret,connfrom,ip');
		if(empty($params['id']) || trim($params['id']) == ''){
			echo "";
			return;
		}
		if(empty($params['screen_name']) || trim($params['screen_name']) == ''){
			echo "";
			return;
		}
		if(empty($params['province']) || trim($params['province']) == ''){
			echo "";
			return;
		}
		if(empty($params['city']) || trim($params['city']) == ''){
			echo "";
			return;
		}
		if(empty($params['gender']) || trim($params['gender']) == ''){
			echo "";
			return;
		}
		if(empty($params['avatar_large']) || trim($params['avatar_large']) == ''){
			echo "";
			return;
		}
		if(empty($params['token']) || trim($params['token']) == ''){
			echo "";
			return;
		}
		/*if(empty($params['token_secret']) || trim($params['token_secret']) == ''){
			echo "";
			return;
		}*/
		if(empty($params['ip']) || trim($params['ip']) == ''){
			echo "";
			return;
		}
		$id = trim($params['id']);
		$screen_name = trim($params['screen_name']);
		global $WB_CITY_ARR,$LOTTERY_FREQUENCY,$TRYOUT_IMG_URL;
		$province = $WB_CITY_ARR[trim($params['province'])]["province"];
		$city = $WB_CITY_ARR[trim($params['province'])]["city"][trim($params['city'])];
		$gender = 1;
		if(trim($params['gender']) == 'f'){
			$gender = 2;
		}
		if(empty($params['connfrom']) || trim($params['connfrom']) == ''){
			$connfrom = 903;
		}else{
			if(trim($params['connfrom']) == 1){
				$connfrom = 903;
			}else if(trim($params['connfrom']) == 2){
				$connfrom = 803;
			}else{
				$connfrom = 903;
			}
		}
		$avatar_large = trim($params['avatar_large']);
		$email = 'weibo_'.$id.'@weibo.com';
		
		$token = trim($params['token']);
		$token_secret = trim($params['token_secret']);
		$ip = trim($params['ip']);

		$emailInfo = $this->user->getRow(array('email' => $email));
		//判断tb_user表中是否存在该用户Email
		if (isset($emailInfo['id']) && $emailInfo['id']){
			$arruser["log_ip"] = $ip;
			$arruser["log_time"] = time();
			$result = $this->user->modify($arruser, $emailInfo["id"]);
			//echo $emailInfo["id"]."|=|".$emailInfo["email"]."|=|".$emailInfo["username"]."|=|".$TRYOUT_IMG_URL.$emailInfo["head_pic"]."|=|".$emailInfo["sex"];
			$userinfo["userid"] = $emailInfo["id"];
			$userinfo["email"] = $emailInfo["email"];
			$userinfo["screen_name"] = $emailInfo["username"];
			$userinfo["head_pic"] = $TRYOUT_IMG_URL.$emailInfo["head_pic"];
			$userinfo["gender"] = $emailInfo["sex"];
			echo $this->customJsonEncode($userinfo);
		}else{
			$upDir = ".././img/user/";
			$monDir = $upDir.date("Ym");
			if(!is_dir($monDir)){
				mkdir($monDir , 0777);
			}
			$dayDir = $monDir."/".date("d");
			if(!is_dir($dayDir)){
				mkdir($dayDir , 0777);
			}
			$hourDir = $dayDir."/".date("d");
			if(!is_dir($hourDir)){
				mkdir($hourDir , 0777);
			}
			$hourDir = $hourDir."/".time().".png";
			$imgStr = file_get_contents($avatar_large);
			$fp = fopen($hourDir,'wb');   
			if(fwrite($fp, $imgStr)){
				$head_pic = $hourDir;
			}else{
				if($gender == 1){
					$head_pic = './img/user/default/male.jpg';
				}else{
					$head_pic = './img/user/default/female.jpg';
				}
				echo "no";
			}
			//随机生成8位数的密码[明文]
			$chars = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k","l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v","w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G","H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R","S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2","3", "4", "5", "6", "7", "8", "9");
			$charsLen = count($chars) - 1;
			shuffle($chars);
			$psw = "";
			for ($i=0; $i<8; $i++){
				$psw .= $chars[mt_rand(0, $charsLen)];
			}
			//添加新浪微博用户信息到tb_user表
			$adduser = array(
				'username'=>$screen_name,
				'head_pic'=>$head_pic,
				'password'=>$psw,
				'email'=>$email,
				'sex'=>$gender,
				'city'=>$city,
				'province'=>$province,
				'reg_ip'=>$ip,
				'time_created'=>time(),
				'log_time'=>0,
				'connfrom'=>$connfrom
			);
			$userid = $this->user->add($adduser , true);
			if (isset($userid) && $userid){
				//添加新浪微博用户到tb_connect表
				$addconnect = array(
					'type'=>1,
					'uid'=>$userid,
					'connuid'=>$id,
					'connuname'=>$screen_name,
					'token'=>$token,
					'token_secret'=>$token_secret,
					'isbind'=>0,
					'issync'=>0,
					'createtime'=>time(),
					'updatetime'=>0
				);
				$result = $this->connect->add($addconnect , true);
				if (isset($result) && $result){
					$userinfo["userid"] = $userid;
					$userinfo["email"] = $email;
					$userinfo["screen_name"] = $screen_name;
					$userinfo["head_pic"] = $TRYOUT_IMG_URL.$head_pic;
					$userinfo["gender"] = $gender;
					echo $this->customJsonEncode($userinfo);
				}else{
					$userinfo["userid"] = "";
					$userinfo["email"] = "";
					$userinfo["screen_name"] = "";
					$userinfo["head_pic"] = "";
					$userinfo["gender"] = "";
					echo $this->customJsonEncode($userinfo);
				}
			}else{
				$userinfo["userid"] = "";
				$userinfo["email"] = "";
				$userinfo["screen_name"] = "";
				$userinfo["head_pic"] = "";
				$userinfo["gender"] = "";
				echo $this->customJsonEncode($userinfo);
			}
		}
	}
	
	/**
	*该函数用于将加密的文件解密
	*@param string 加密过的字符串
	*@return string 返回解密过的字符串
	*/
	function Transmit($decryptStr){
		$iv = 'com.tuolar@2206S';
		$key = 'tuolar.com+tuolar.com+tuolar.com';
		
		//由于中文加密时会出现+号和/号,在用http协议传送时会丢失，所以在客户端传过来时会先将+号和/号替换成,号和.号，所以下面代码用于再替换回来	
		$decryptStr = strtr($decryptStr,',','+');
		$decryptStr = strtr($decryptStr,'.','/');
		$decryptStr = $this->strippadding(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($decryptStr), MCRYPT_MODE_CBC,$iv));
		
		return $decryptStr;
	}
	
	public function strippadding($string){
	    $slast = ord(substr($string, -1));
	    $slastc = chr($slast);
	    $pcheck = substr($string, -$slast);
	    if(preg_match("/$slastc{".$slast."}/", $string)){
	        $string = substr($string, 0, strlen($string)-$slast);
	        return $string;
	    } else {
	        return false;
	    }
	}
	

	
	/*
	 * 用户创建榜单信息
	 * id	用户id
	 * ?m=user&a=lists&id=2
	 */
	public function listsAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
		
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
		
		$uid=$this->getParam("id");
		if(!$uid){
			exit;
		}
		
		$fileds = "id, title , uid, content, cover, zannum, prodnum";
		$where = array('uid'=>$uid);
		$lists = $this->lists->listAllWithFields($fileds,$where);
		if(!$lists){
			exit;
		}
		echo $this->customJsonEncode($lists);
		exit;
	}
	
	/*
	 * 用户参加榜单
	 * ?m=user&a=joinLists&id=2
	 * id	用户id
	*/
	public function joinListsAction()
	{
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
	
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
	
		$id = $this->getParam("id");
	
		if(!$id){
			exit;
		}
		//查询得到lists id
		$listsZanFields = "lists_id, prod_id";
		$listZanArr = $this->listsZan->listAllWithFields($listsZanFields, array('uid'=>$id));
		if(!$listZanArr){
			exit;
		}		
	
		foreach($listZanArr as $listZan){
			$listsIdArr[]= $listZan['lists_id'];
		}
		
		$in = implode(",", $listsIdArr);
		$where = "id in ($in)";	
		//lists信息
		$fieldsLists = "id, title, uid, content, cover, zannum, prodnum, createtime";
		$lists = $this->lists->listAllWithFields($fieldsLists, $where);
		if(!$lists){
			exit;
		}
		echo $this->customJsonEncode($lists);
		exit;
	}
	
	
	/**
	 * 用户收藏单品列表
	 * uid			用户ID
	 * page			页数
	 */
	public function listItemAction(){
		$this->config['layoutEnabled'] = false;
		$this->config['viewEnabled'] = false;
	
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		header("Content-Type: text/html; charset=UTF-8");
	
		$uid = $this->getIntParam("uid");
		$where 		= array('uid' => $uid);
		$order		= "id desc";
		$count 		= $this->likeitem->listCount($where);
		$pageSize 	= 18;
		$this->view->paging 	= $this->getPaging($count , $pageSize , $pageId);
		$data 	= $this->likeitem->listPage($where , $order , $pageSize , $pageId);

		$arr_itemid = array();
		foreach ($data as $key_itemid => $value_itemid){
			$arr_itemid[] = $value_itemid["itemid"];
		}
		$itemid 	= '';
		$arr_itemid 	= array_unique($arr_itemid);
		$itemid 	= implode(',' , $arr_itemid);
		$itemid   	= trim($itemid , ',');		
		
		$fieldsMyitem = "id,uid,maincat_id,subcat_id,third_id,cat_1,cat_2,cat_3,bid,pid,type,tb_fav,title,price,discount,img_url,ow,oh,source_site_url,source_img_url,tags,color,site_name,summary,favor,likenum,time_created";
		$myitemArr = $this->myitem->listAllWithFields($fieldsMyitem, "id in ($itemid)");
		if(!$myitemArr){
			exit;
		}
		
		foreach($myitemArr as $j=>$myitem){
			$myitemArr[$j]['img_url'] = IMAGE_DOMAIN.$myitem['img_url'];
		}		

		echo $this->customJsonEncode($myitemArr);
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

Config::extend('UserController', 'Controller');