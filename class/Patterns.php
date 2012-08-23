<?php

class Patterns
{
	const MATCH_CENTER	= 0;
	const MATCH_LEFT	= 1;
	const MATCH_RIGHT	= 2;
	const MATCH_ALL		= 3;
	
	public static $self;
		
	public static $matchMode = self::MATCH_CENTER;	
	public static $pattern;
	public static $rawPattern;
	public static $delimiter;
	public static $modifier;
	public static $newModifier;
	
	private $patterns = array
	(
		'host'			=> '/([\d\w_\-]+\.)*[\d\w\-]+(\.\w{2,4})?/',
		'url'			=> '/(http:\/\/)?(([\d\w_\-\.]+:[\d\w_\-\.]+@)?([\d\w_\-]+\.)*[\d\w\-]+(\.\w{2,4})?(:\d{1,5})?(\/[\d\w_\-\.\/]*)?(\?([\d\w_\-\.=%&@\{\}]+)?)?(#([\d\w_\-\.]+)?)?)/ie',
		'email'			=> '/[\d\w_\-\.]+@([\d\w\-]+\.)+\w{2,4}/i',
		'fax'			=> '/\d{3,6}\-\d{7,8}/',
		'telephone'		=> '/\d{3,6}\-\d{7,8}/',
		'mobile'		=> '/1(3|5)\d{9}/',
		'phone'			=> '/(\d{3,6}\-\d{7,8})|(1(3|5)\d{9})/',
		'postalcode'	=> '/\d{6}/',
		'qq'			=> '/[1-9]\d{3,8}/',
		'idCardNumber'	=> '/^(\d{15}|\d{18})$/'
	);
	
	private $reservedNamesPattern = '/^(url|email)$/';
	
	public static function initialize()
	{
		if (self::$self instanceof Patterns) return;
		
		new Patterns();
	}
	
	private function __construct()
	{
		self::$self = $this;
	}
	
	public function __get($name)
	{
		if (isset($this->patterns[$name]))
		{
			self::validate($this->patterns[$name]);
			
			$s  = self::$matchMode & 1 ? '^' : '';
			$s .= self::$matchMode ? '(' : '';
			$e  = self::$matchMode ? ')' : '';
			$e .= self::$matchMode & 2 ? '$' : '';
						
			self::$pattern = self::$delimiter.$s.self::$rawPattern.$e.self::$delimiter;
			
			if (self::$newModifier)
			{
				self::$pattern		.= self::$newModifier;
				self::$newModifier	 = null;
			}
			else self::$pattern .= self::$modifier;
			
			return self::$pattern;
		}
		
		throw new Exception('Pattern '.$name.' doesn\'t exist.');
	}
	
	public function __set($name, $pattern)
	{
		self::validate($pattern);
		
		$this->patterns[$name] = self::$pattern;
	}
	
	public function __isset($name)
	{
		return isset($this->patterns[$name]);
	}
	
	public function __unset($name)
	{
		if (preg_match($this->reservedNamesPattern, $name))
			throw new Exception('Pattern '.$name.' is reserved, can not be unset.');
		
		unset($this->patterns[$name]);
	}
	
	public static function getPatterns()
	{
		return self::$self->patterns;
	}
	
	public static function getPattern($name, $matchMode = self::MATCH_CENTER, $modifier = null)
	{
		self::$matchMode	= $matchMode;
		self::$newModifier	= $modifier;
		
		return eval('return self::$self->'.$name.';');
	}
	
	public static function validate($pattern = null)
	{
		self::$pattern = (string) ($pattern === null ? self::$pattern : $pattern);
		
		if (self::$pattern === '')
			throw new Exception('Empty regular expression.');
			
		self::$delimiter = self::$pattern[0];
						
		if (preg_match('/[\d\w\\\\]/i', self::$delimiter))
			throw new Exception('Delimiter must not be alphanumeric or backslash.');

		$pos = strrpos(self::$pattern, self::$delimiter);
			
		if ($pos == 0)
			throw new Exception("No ending delimiter '".self::$delimiter."' found.");

		$fs = (int) (strpos(self::$pattern, '^') == 1);
		$fe = (int) (strrpos(self::$pattern, '$') == $pos - 1);
		
		self::$rawPattern	= substr(self::$pattern, 1 + $fs, $pos - (1 + $fs + $fe));
		self::$modifier		= substr(self::$pattern, $pos + 1);
	}
	
	public static function parse($name, $subject, $returnUrl = false)
	{
		switch ($name)
		{
			case 'url':
				if ($returnUrl) // 返回完整的URL
					return preg_replace(self::$self->url, "'\\1' ? '\\0' : 'http://\\3'", $subject);
				else // 返回链接
					return preg_replace(self::$self->url, "'\\1' ? '<a href=\"\\0\" target=\"_blank\">\\0</a>' : '<a href=\"http://\\3\" target=\"_blank\">\\0</a>'", $subject);
			
			case 'email':
				return preg_replace(self::$self->email, '<a href="mailto:\0" target="_blank">\0</a>', $subject);
		}
	}
}

Patterns::initialize();
