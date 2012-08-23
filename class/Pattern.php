<?php
Globals::requireClass('Patterns');

class Pattern
{
	public $pattern;
	public $isRegex;
	public $regexDelimiter;
	public $regexModifier;
	public $reverseEnabled;
	public $appendEnabled;
	
	public $match;
	public $matchIndex;
	public $matchLength;
	
	public function __construct($pattern = '', $isRegex = false, $reverseEnabled = false, $appendEnabled = false)
	{
		$this->pattern			= (string) $pattern;
		$this->isRegex			= (bool) $isRegex;		
		$this->reverseEnabled	= (bool) $reverseEnabled;		
		$this->appendEnabled	= (bool) $appendEnabled;
		$this->match			= '';
		$this->matchIndex		= false; // indicate match result
		$this->matchLength		= 0;
		
		if ($this->isRegex)
		{
			Patterns::validate($this->pattern);
			
			$this->regexDelimiter	= Patterns::$delimiter;
			$this->regexModifier	= Patterns::$modifier;
		}
	}
	
	public function __get($name)
	{
		switch ($name)
		{
			case 'pattern': 
			
			    return $this->pattern;
			    break;
			    
			case 'isRegex': 
			
			    return $this->isRegex;
			    break;
			    
			case 'reverseEnabled': 
			
			    return $this->reverseEnabled;
			    break;
			    
			case 'appendEnabled': 
			
			    return $this->appendEnabled;
			    break;
			    
			default: throw new Exception("Private member variable ".$name." doesn't exist.");
		}
	}
	
	public function pattern2String()
	{
		if ($this->isRegex)
		{
			$pos			= strrpos($this->pattern, $this->regexDelimiter);
			$this->pattern	= substr($this->pattern, 1, $pos - 1);
			$this->isRegex	= false;
		}
	}
	
	public function pattern2Regex()
	{
		if (!$this->isRegex)
		{
			if ($this->regexDelimiter === null) $this->regexDelimiter = '/';
			
			$this->pattern	= $this->regexDelimiter.$this->pattern
							. $this->regexDelimiter.$this->regexModifier;
			$this->isRegex	= true;
		}
	}
	
	public function isPatternEmpty()
	{
		if ($this->isRegex) return strrpos($this->pattern, $this->regexDelimiter) == 1;
		else return $this->pattern == '';
	}
	
	public function enableReverse()
	{
		$this->reverseEnabled = true;
	}
	
	public function disableReverse()
	{
		$this->reverseEnabled = false;
	}
	
	public function enableAppend()
	{
		$this->appendEnabled = true;
	}
	
	public function disableAppend()
	{
		$this->appendEnabled = false;
	}
	
	public function append()
	{
		return $this->appendEnabled ? $this->match : '';
	}
	
	public static function toPattern($string)
	{
		if ($string instanceof Pattern) return $string;
		
		$i = 0;
		while (substr($string, $i, 1) == '\\') $i++;
		
		if ($i && self::isRegex(substr($string, $i)))
			return new Pattern(substr($string, 1), false);
		
		return new Pattern($string, self::isRegex($string));
	}
	
	public static function isRegex($string)
	{
		$string = (string) $string;
		
		if (strlen($string) < 2)
			return false;
		
		$delimiter = $string[0];
		
		if (preg_match('/[0-9a-z\\\\]/i', $delimiter))
			return false;
		
		$rpos = strrpos($string, $delimiter);
		
		if ($rpos == 0)
			return false;
		
		$modifiers = substr($string, $rpos + 1);
		
		if (strlen($modifiers) && !preg_match('/^[a-z]+$/i', $modifiers))
			return false;
		
		return true;
	}
}
