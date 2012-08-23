<?php
/*
*2012年4月16日增加getClothesList方法
*2012年4月16日增加getClothesListCount方法
*2012年4月16日增加getWhere方法
*2012年4月16日增加getOrder方法
*/
Globals::requireClass('Table');

class MyitemTable extends Table{
	protected $cascade=7;//瀑布流可以下拉多少屏
	public static $defaultConfig = array(
		'table' => 'tb_myitem'
	);
	
	public function getItemByIds($idArr){
		$ids 	= '';
		$idArr 	= array_unique($idArr);
		$ids 	= implode(',' , $idArr);
		$ids   	= trim($ids , ',');
		
		$data   = array();
		if (count($idArr) && '' != $ids){
        	$list  	= $this->listAll("id in (".$ids.")" , 'id desc');
        	if (is_array($list) && count($list)){
        		foreach ($list as $row){ $data[$row['id']] = $row;}
        	}
		}
		
		return $data;
	}
	
	public function getStarByIds($idArr){
		if(is_array($idArr) && !is_null($idArr)){
			$ids 	= '';
			$idArr 	= array_unique($idArr);
			$ids 	= implode(',' , $idArr);
			$ids   	= trim($ids , ',');
			
			$sql = "SELECT tpr.mid,tp.* 
					FROM tb_pic_relation tpr
					JOIN tb_pic tp ON (tpr.pid = tp.id)
					WHERE tpr.mid IN (".$ids.")  AND tpr.type = 1";
			//echo $sql;		
			$res  	= $this->database->query($sql);
			$data  	= $this->database->getList($res);
			if (is_array($data) && count($data)){
					foreach ($data as $row){ $list[$row['mid']] = $row;}
			}
		}else
			$list = array();
		        return $list;
	}
	public function getClothesList($where,$order,$pageSize=0,$pageId=0,$togo=0){
		$sql="SELECT * FROM `".$this->config['table']."`";
		$sql.=$this->getWhere($where,$order);
		if(!empty($order)){
			$sql.=' ORDER BY '.$this->getOrder($order);
		}
		if(is_numeric($pageSize)&&$pageSize){
			if(!$togo){//点击分页按钮时候
				$offsetInt=is_numeric($pageId)&&$pageId?($pageId-1)*$pageSize*$this->cascade:0;
			}else{//瀑布流加载时候
				$offsetInt=(($pageId-1)*$this->cascade+($togo-1))*$pageSize;
			}
        	$sql.=" LIMIT ".$offsetInt.",".$pageSize;
        }
       
		$res=$this->database->query($sql);
        $res=$this->database->getList($res);
		if($order!='new'){
			shuffle($res);
		}
		return $res;
	}
	public function getClothesListCount($where,$order=''){
		$sql="SELECT count(*) AS C FROM `".$this->config['table']."`";
		$sql.=$this->getWhere($where,$order);
		
        $res=$this->database->query($sql);
        $res=$this->database->getList($res);
        return $res[0]['C'];
	}
	public function getWhere($where,$order=''){
		$sql=" WHERE `del`='0' AND `flag`='1'";
		if(!isset($where['c_id'])){
			//$sql.=" AND ((cat_1>=1000 AND cat_1<1900) OR (cat_2>=1000 AND cat_2<1900) OR (cat_3>=1000 AND cat_3<1900))";
			$sql.=" AND ((cat_1>=1000 AND cat_1<1900))";
		}else{
			switch($where['c_id']){
				case 1://衣服
					$sql.=" AND ((`cat_1`>=1100 AND `cat_1`<1400))";
				break;
				case 2://鞋子
					$sql.=" AND ((`cat_1`>=1600 AND `cat_1`<1700))";
				break;
				case 3://包包
					$sql.=" AND ((`cat_1`>=1400 AND `cat_1`<1600))";
				break;
				case 4://配饰
					$sql.=" AND ((`cat_1`>=1700 AND `cat_1`<1800))";
				break;
				case 5://内衣
					$sql.=" AND ((`cat_1`>=1800 AND `cat_1`<1900))";
				break;
				case 6://婚庆
					$sql.="";
				break;
				case 7://男士
					$sql.=" AND ((`cat_1`>=2000 AND `cat_1`<3000))";
				break;
				case 8://童装
					$sql.=" AND ((`cat_1`>=4000 AND `cat_1`<5000))";
				break;
				default:
					$sql.=" AND ((cat_1>=1000 AND cat_1<1900))";
				break;
			}
		}
		switch($where['price']){
			case '1'://100元以下
				$sql.=" AND `price`<=100";
			break;
			case '12'://100元-200元
				$sql.=" AND `price`>100 AND `price`<=200";
			break;
			case '25'://200元-500元
				$sql.=" AND `price`>200 AND `price`<=500";
			break;
			case '5'://500元以上
				$sql.=" AND `price`>500";
			break;
		}
		switch($order){
			case 'tide':
				//$sql.=" AND `time_created`>'".strtotime('-7 day')."'";
				$sql.="";
			break;
			default:
				$sql.="";
			break;
		}
		return $sql;
	}
	public function getOrder($order){
		switch($order){
			case 'hot':
				//$order=array('`rank` DESC','`likenum` DESC');
				$order=array('`rank` DESC');
			break;
			case 'new':
				$order=array('`id` DESC');
			break;
			case 'tide'://潮流排序
				$order=array('`rank` DESC');
			break;
			default://潮流排序，延续以前的最热的算法
				$order=array('`rank` DESC');
			break;
		}
		return $this->database->getOrder($order);
	}
	
	/**
	*根据单品id的array和提过的tag_id搜索相关单品
	*/
	public function getItemByTag($item_arr,$tag_id,$offset=0,$limit)
	{
		$limit_str = $limit?' limit '.$offset.','.$limit:'';
		$item_str = !empty($item_arr) ?(is_array($item_arr)?' AND tm.id IN ('.implode(',',$item_arr).')':' AND'.$item_arr):'';
		$tag_str  = !empty($tag_id) && is_array($tag_id)?' AND tmt.tag_id IN ('.implode(',',$tag_id).')':'AND tmt.tag_id = '.$tag_id;	
		$sql = 'SELECT tm.*
				FROM tb_myitem tm
				JOIN tb_mytag tmt ON (tm.id = tmt.my_id '.$tag_str.')
				WHERE tm.del=0 AND tm.flag = 1 '.$item_str.'
				GROUP BY tm.id
				ORDER BY tm.id desc '.$limit_str;
		//echo $sql;
		$sql=$this->database->query($sql);
		$result=$this->database->getList($sql);
		return $result;
	}
	public function getItemsWithUsers($id){
		if(!is_numeric($id)&&$id){return '';}
		if(!is_array($id)){
			$id=explode(',',$id);
		}
		$id=implode(',',$id);
		$sql="SELECT A.*,B.`username`,B.`head_pic` FROM `".$this->config['table']."` AS A,`tb_user` AS B WHERE A.`uid`=B.`id` AND A.`id` IN ('".$id."')";
		$sql=$this->database->query($sql);
		$sql=$this->database->getList($sql);
		return $sql;
	}
	public function tag_idGetItem($tag_id,$cat,$randNum,$remove='',$order='tide'){
		$sql="SELECT A.* FROM `".$this->config['table']."` AS A,`tb_mytag` AS B WHERE A.`id`=B.`my_id` AND B.`tag_id`='".$tag_id."'";
		if(is_array($remove)&&!empty($remove)){
			foreach($remove as $v){
				$sql.=" AND A.`id`!='".$v."'";
			}
		}
		foreach($cat as $c){
			$sql.=$this->getCat($c);
		}
		$sql.=' GROUP BY A.`id`';
		$sql.=' ORDER BY '.$this->getOrder($order);
		if($randNum){
			$sql.=" LIMIT 0,".$randNum;
		}
		$sql=$this->database->query($sql);
		$sql=$this->database->getList($sql);
		return $sql;
	}
	public function catGetItems($cat,$order='tide',$num=32){//根据所属cat查找相同cat的所有单品
		$sql="SELECT A.* FROM `".$this->config['table']."` AS A WHERE 1";
		foreach($cat as $c){
			$sql.=$this->getCat($c);
		}
		$sql.=' ORDER BY '.$this->getOrder($order);
		$sql.=' LIMIT 0,'.$num;
		$sql=$this->database->query($sql);
		$sql=$this->database->getList($sql);
		return $sql;
	}
	public function getCat($cat){
		$sql="";
		if($cat){
			if($cat>=1100&&$cat<1400){
				$sql.=" AND ((A.`cat_1`>=1100 AND A.`cat_1`<1400))";
			}
			if($cat>=1400&&$cat<1600){
				$sql.=" AND ((A.`cat_1`>=1400 AND A.`cat_1`<1600))";
			}
			if($cat>=1600&&$cat<1700){
				$sql.=" AND ((A.`cat_1`>=1600 AND A.`cat_1`<1700))";
			}
			if($cat>=1700&&$cat<1800){
				$sql.=" AND ((A.`cat_1`>=1700 AND A.`cat_1`<1800))";
			}
			if($cat>=1800&&$cat<1900){
				$sql.=" AND ((A.`cat_1`>=1800 AND A.`cat_1`<1900))";
			}
			if($cat>=2000&&$cat<3000){
				$sql.=" AND ((A.`cat_1`>=2000 AND A.`cat_1`<3000))";
			}
			if($cat>=4000&&$cat<5000){
				$sql.=" AND ((A.`cat_1`>=4000 AND A.`cat_1`<5000))";
			}
		}
		return $sql;
	}
}
Config::extend('MyitemTable', 'Table');