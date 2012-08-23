<?php

class Params
{
	public static function parse($string)
	{
		if (preg_match_all('/\{([^\}]+)\}/', $string, $matches, PREG_SET_ORDER))
		{
			$params = array();
			
			foreach ($matches as $match)
			{
				$pair	= explode(':', $match[1], 2);
				$key	= $pair[0];
				$value	= $pair[1];
				
				$params[$key] = $value;
			}
			
			return $params;
		}
		else return false;
	}
	
	public static function strip($string)
	{
		return preg_replace('/\{[^\}]+\}/', '', $string);
	}
	
	public static function assign($string, array $params)
	{
		return @preg_replace('/\{([^\}]+)\}/e', '$params["\1"]', $string);
	}
}
