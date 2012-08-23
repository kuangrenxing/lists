<?php
/**
 * 推送给用户的信息
 * zz@09.14
 */
Globals::requireClass('Table');

class UsermsgTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_usermsg'
	);
	
	public function create($table = null)
	{
		if (!$table)
			$table = $this->getTable();
		
		$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$table` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `receive_uid` int(11) unsigned DEFAULT '0',
 `act_id` bigint(20) unsigned DEFAULT '0',
 `createtime` int(10) unsigned NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin
EOF;
		
		return $this->database->query($query);
	}
	
	//推送消息
	public function pushMsg($pushUid , $actId)
	{
		//获取用户好友
		$sql = "select * from tb_friend where friend_uid = $pushUid";
		
		$res  	= $this->database->query($sql);
        $data  	= $this->database->getList($res);
        
        $mutFriend = $fans = array($pushUid);
        if (count($data) > 0){
        	foreach ($data as $row)
        	{
        		if ($row['status'] == FRIEND_STATUS_YES){
        			//相互关注
        			$mutFriend[] = $row['uid'];
        		}
        		
        		//关注该用户的粉丝
        		$fans[] = $row['uid'];
        	}
        }
        
        $mutFriend = array_unique($mutFriend);
        $fans	= array_unique($fans);
        
        //push 消息(相互关注)
        if (count($mutFriend) > 0){
        	$sql_insert = "";
        	$ins_time = time();
        	foreach ($mutFriend as $mid){
        		$sql_insert .= "($mid , $actId , ".USER_MSG_TYPE_FOLLOWS." , $ins_time),";
        	}
        	if ('' != $sql_insert){
        		$insertSQL = "insert into tb_usermsg (receive_uid , act_id , type , createtime) values ".trim($sql_insert , ',');
        		$this->database->query($insertSQL);
        		unset($insertSQL);
        	}
        	unset($sql_insert , $ins_time , $mid);
        }
        
        //push 消息(一般)
        if (count($fans) > 0){
        	$sql_insert = "";
        	$ins_time = time();
        	foreach ($fans as $fid){
        		$sql_insert .= "($fid , $actId , ".USER_MSG_TYPE_NORMAL." , $ins_time),";
        	}
        	if ('' != $sql_insert){
        		$insertSQL = "insert into tb_usermsg (receive_uid , act_id , type , createtime) values ".trim($sql_insert , ',');
        		$this->database->query($insertSQL);
        		unset($insertSQL);
        	}
        	unset($sql_insert , $ins_time , $fid);
        }
	}
	
	//拉消息
	public function findUserMsg($uid = 0, $pageSize = 0 , $pageId = 0)
	{
		
	}
}

Config::extend('UsermsgTable', 'Table');