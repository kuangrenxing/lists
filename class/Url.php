<?php

class Url
{
	const SIGNATURE_NAME = 'sign';
	
	/******************** query string ********************/
	public static function parseQuery($url, $urlAsQuery = false)
	{
		$params = array();
		
		if ($urlAsQuery)
			$query = $url;
		else
		{
			$portions	= parse_url($url);
			$query		= isset($portions['query']) ? $portions['query'] : null;
		}
		
		if (!empty($query))
		{
			$pairs = explode('&', $query);
			
			foreach ($pairs as $pair)
			{
				$pairPortions	= explode('=', $pair, 2);
				$key			= urldecode($pairPortions[0]);
				$value			= isset($pairPortions[1]) ? urldecode($pairPortions[1]) : null;
				$params[$key] 	= $value;
			}
		}
		
		return $params;
	}
	
	public static function buildQuery(array $params)
	{
		foreach ($params as $key => $value)
			$params[$key] = urlencode($key).'='.urlencode($value);
		
		return implode('&', $params);
	}
	
	public static function appendQuery($url, $query)
	{
		if (empty($query))
			return $url;
		
		$pos = strpos($url, '?');
		
		if ($pos === false)
			$glue = '?';
		else if ($pos == strlen($url) - 1)
			$glue = '';
		else
			$glue = '&';
		
		return $url.$glue.$query;
	}
	
	public static function encodeQuery($query)
	{
		return base64_encode(gzdeflate($query, 9));
	}
	
	public static function decodeQuery($query)
	{
		return gzinflate(base64_decode($query));
	}
	
	/******************** signature ********************/
	public static function getSignature($url, $key, $name = self::SIGNATURE_NAME, &$value = null)
	{
		$params = self::parseQuery($url);
		
		if (isset($params[$name]))
		{
			$value = $params[$name];
			unset($params[$name]);
		}
		
		ksort($params);
		$query = self::buildQuery($params);
		
		return md5($query.$key);
	}
	
	public static function sign($url, $key, $name = self::SIGNATURE_NAME)
	{
		$signature = self::getSignature($url, $key, $name);
		return self::appendQuery($url, $name.'='.$signature);
	}
	
	public static function validateSignature($url, $key, $name = self::SIGNATURE_NAME)
	{
		$signature = self::getSignature($url, $key, $name, $value);
		return $signature == $value;
	}
	
	/******************** misc ********************/
	public static function getAbsoluteUrl($url, $referenceUrl)
	{
		$portions	= parse_url($referenceUrl);
		$scheme		= isset($portions['scheme']) ? $portions['scheme'] : 'http';
		
		if (strpos($url, $scheme) !== 0)
		{
			$upass	= isset($portions['user']) ? $portions['user'].(isset($portions['pass']) ? ':'.$portions['pass'] : null).'@' : null;
			$host	= isset($portions['host']) ? $portions['host'] : 'localhost';
			$port	= isset($portions['port']) ? ':'.$portions['port'] : null;
			$base	= $scheme.'://'.$upass.$host.$port;
			
			if (isset($portions['path']))
			{
				$path = $portions['path'];
				
				if (($pos = strrpos($path, '/')) !== false)
					$path = substr($path, 0, $pos);
				
				if (substr($path, 0, 1) != '/')
					$path = '/'.$path;
			}
			else $path = '/';
			
			if (substr($url, 0, 1) == '/')
			{
				$path	= '/';
				$url	= substr($url, 1);
			}
			else if (substr($url, 0, 2) == './')
				$url = substr($url, 2);
			else
			{
				while (substr($url, 0, 3) == '../')
				{
					$path	= substr($path, 0, strrpos($path, '/'));
					$url	= substr($url, 3);
				}
			}
			
			$url = $base.($path == '/' ? null : $path).'/'.$url;
		}
		
		return $url;
	}
}
