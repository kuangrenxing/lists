<?php
Globals::requireClass('Table');

class MytagTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_mytag'
	);
	
	public function findTagItems($findArray , $pageSize = 0 , $pageId = 0)
	{
		$sql = "select i.* , tag_id from tb_myitem i "
				. "left join tb_mytag t on i.id = t.my_id where ((i.cat_1 < 2000 and i.cat_1 > 0) or i.cat_1 >= 4000 or (i.cat_2 < 2000 and i.cat_2 > 0) or i.cat_2 >= 4000 or (i.cat_3 < 2000 and i.cat_3 > 0) or i.cat_3 >= 4000 or (i.cat_1 = 0 and i.cat_2 = 0 and i.cat_3 = 0)) and i.del = 0 and i.flag = 1 ";
				
		if (isset($findArray['tag_id']) && "" != $findArray['tag_id'])
			$sql .= " and tag_id = ".$findArray['tag_id'];
		if (isset($findArray['no_id']) && "" != $findArray['no_id'])
			$sql .= " and i.id <> ".$findArray['no_id'];
		
		if(!isset($findArray['orderBy']) || "" == $findArray['orderBy'])
            $sql .= " order by i.id desc ";
        else
            $sql .= " order by ".$findArray['orderBy'];

        if($pageSize > 0){
        	$offsetInt = $pageId ? ($pageId - 1)*$pageSize : 0;
        	$rowsInt   = $pageSize;
        	$sql 	.= " limit ".$offsetInt.", ".$rowsInt;
        }
        	
        $res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data;
	}
	
	public function getTagItemsNum($findArray)
	{
		$sql = "select count(*) as cnt from tb_myitem i "
				. "left join tb_mytag t on i.id = t.my_id where ((i.cat_1 < 2000 and i.cat_1 > 0) or i.cat_1 >= 4000 or (i.cat_2 < 2000 and i.cat_2 > 0) or i.cat_2 >= 4000 or (i.cat_3 < 2000 and i.cat_3 > 0) or i.cat_3 >= 4000 or (i.cat_1 = 0 and i.cat_2 = 0 and i.cat_3 = 0)) and i.del = 0 and i.flag = 1 ";
				
		if (isset($findArray['tag_id']) && "" != $findArray['tag_id'])
			$sql .= " and tag_id = ".$findArray['tag_id'];
		if (isset($findArray['no_id']) && "" != $findArray['no_id'])
			$sql .= " and i.id <> ".$findArray['no_id'];
		
        $res = $this->database->query($sql);
        $row = $this->database->fetch($res);
        
        return intval($row['cnt']);
	}
	
	/**
	 * 根据tag查询单品总数
	 */
	public function getTagListCount($tagid){
		$sql="select count(distinct my_id) as tagcount from tb_mytag where tag_id=$tagid;";
        $res=$this->database->query($sql);
        $res=$this->database->getList($res);
        return $res[0]['tagcount'];
	}
	
	/**
	 * 根据tag查询单品的列表
	 */
	public function getTagList($tagid,$pageSize,$page = 1){
		if($page == 1){
			$pageCount = 0;
		}else{
			$pageCount = $pageSize*($page-1);
		}
		$sql = "select distinct my_id from tb_mytag where tag_id=$tagid;";
		$res=$this->database->query($sql);
        $res=$this->database->getList($res);
		return $res;
	}
	
}

Config::extend('MytagTable', 'Table');