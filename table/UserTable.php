<?php
Globals::requireClass('Table');

class UserTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_user'
	);
	
	public function findUserStat($findArray)
	{
		$sql = "select u.*,t.uid,t.follow,t.fans,t.likenum,t.tweet,t.thread,t.pinxiu,t.goods,t.newfans,t.newmsg,t.newat from tb_user u "
				. " left join tb_userstat t on u.id = t.uid where 1 ";
				
		if (isset($findArray['uid']) && $findArray['uid'])
			$sql .= " and u.id = ".$findArray['uid'];
		if (isset($findArray['in_uid']) && "" != $findArray['in_uid'])
			$sql .= " and u.id in (".$findArray['in_uid'].")";
			
        $sql .= " order by u.id desc";
       	
		$res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
       
        return $data;
	}
	
	public function getUserByIds($idArr)
	{
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
}

Config::extend('UserTable', 'Table');