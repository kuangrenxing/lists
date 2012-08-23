<?php
/**
 * 用户动态信息表
 * zz@09.14
 */
Globals::requireClass('Table');

class TweetTable extends Table
{
	public static $defaultConfig = array(
		'table' => 'tb_tweet'
	);
	
	public function getTweetByIds($idArr)
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

Config::extend('TweetTable', 'Table');