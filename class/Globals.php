<?php

class Globals extends Config
{
	public static $defaultConfig = array
	(
		'debug'			=> false,
		'messagePrefix'	=> 'Error: ',
		'timezone'		=> 'Asia/Shanghai',
		'encoding'		=> 'utf-8',
		'classDir'		=> './class',
		'modelDir'		=> './model',
		'tableDir'		=> './table',
		'controllerDir'	=> './controller',
		'viewDir'		=> './view'
	);
	
	public static $self;
	
	/******************** config setters ********************/
	protected function setDebug($value)
	{
		ini_set('display_errors', $value ? 'on' : 'off');
	}
	
	protected function setTimezone($value)
	{
		if (function_exists('date_default_timezone_set'))
			return date_default_timezone_set($value);
		else
			return false;
	}
	
	protected function setEncoding($value)
	{
		return mb_internal_encoding($value);
	}
	
	/******************** static methods ********************/
	public static function error($message = null, $object = null, $debug = null, $messagePrefix = null)
	{
		$prefix = self::$self->getConfig('messagePrefix', $messagePrefix);
		
		if (is_object($object))
			$prefix = '['.get_class($object).'] '.$prefix;
		
		$message= $prefix.$message;
		$debug	= isset(self::$self->debug) ? self::$self->debug : self::$self->getConfig('debug', $debug);
		
		if ($debug)
			throw new Exception($message);
		else
		{
			echo $message;
			die;
		}
	}
	
	public static function startDebug()
	{
		self::$self->debugBack = self::$self->debug;
		self::$self->debug = true;
	}
	
	public static function endDebug()
	{
		self::$self->debug = self::$self->debugBack;
	}
	
	/******************** static require methods ********************/
	public static function requireClass($name)
	{
		self::requireFile(self::$self->getConfig('classDir').'/'.$name.'.php');
	}
	
	public static function requireModel($name)
	{
		self::requireFile(self::$self->getConfig('modelDir').'/'.$name.'Model.php');
	}
	
	public static function requireTable($name)
	{
		self::requireFile(self::$self->getConfig('tableDir').'/'.$name.'Table.php');
	}
	
	public static function requireController($name)
	{
		self::requireFile(self::$self->getConfig('controllerDir').'/'.$name.'Controller.php');
	}
	
	public static function requireFile($filename)
	{
		if (!file_exists($filename))
			self::error('File \''.$filename.'\' doesn\'t exist');
		
		require_once($filename);
	}
	
	/******************** static misc methods ********************/
	public static function zeroFill($string, $length)
	{
		return str_pad($string, $length, '0', STR_PAD_LEFT);
	}
	
	public static function firstToUpper($string)
	{
		return strtoupper(substr($string, 0, 1)).substr($string, 1);
	}
	
	public static function firstToLower($string)
	{
		return strtolower(substr($string, 0, 1)).substr($string, 1);
	}
	
	public static function callback($callback, $paramName, &$paramValue, $self = null)
	{
		if (is_array($callback) && isset($callback[$paramName]))
			$callback = $callback[$paramName];
		
		if (is_string($callback))
			$callback = create_function('&$'.$paramName.', $self', $callback);
		
		if (is_callable($callback))
			return $callback($paramValue, $self);
		
		return true;
	}
	
	public static function convertEncoding($str, $encoding = null)
	{
		$encoding = !$encoding || $encoding == 'auto' ? mb_detect_encoding($str, 'UTF-8', 'GBK') : strtoupper($encoding);
		
		if ($encoding != 'UTF-8')
			$str = iconv($encoding, 'UTF-8//IGNORE', $str);
		
		return $str;
	}
	
	public static function getClientIp($returnLong = false)
	{
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			
			if ($pos = strpos($ip, ','))
				$ip = trim(substr($ip, 0, $pos));
			
			if (strpos($ip, '10.') === 0)
				$ip = $_SERVER['REMOTE_ADDR'];
		}
		else $ip = $_SERVER['REMOTE_ADDR'];
		
		return $returnLong ? ip2long($ip) : $ip;
	}
}
