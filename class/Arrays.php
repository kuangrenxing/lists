<?php

class Arrays
{
	public static function rand(array $array, $count = 1)
	{
		$arrayCount	= count($array);
		
		if ($count < 1 || $arrayCount < 1)
			return array();
		
		if ($count > $arrayCount)
			$count = $arrayCount;
		//array_rand() 函数从数组中随机选出一个或多个元素，并返回。
		$keys		= array_rand($array, $count);
		$randArray	= array();
		
		if (!is_array($keys))
			$keys = array($keys);
		
		foreach ($keys as $key)
			$randArray[$key] = $array[$key];
		
		return $randArray;
	}
}
