<?php
Globals::requireClass('Pattern');

class String
{	
	public static function index($str, $p, $offset = null) 
	{
		$p = Pattern::toPattern($p);
		$f = false;
		
		if ($offset !== null)
		{
			$offset = (int) $offset;
			
			if ($offset >= 0 && $offset <= strlen($str))
			{
				$f = true;
				
				if ($p->reverseEnabled) $str = substr($str, 0, $offset);
				else $str = substr($str, $offset);
			}
		}
		 
		$p->match		= '';
		$p->matchLength	= 0;
		
		if (strlen($str) == 0)
		{
			if ($p->isPatternEmpty())
			{
				return $p->matchIndex = 0;
			}
			else return $p->matchIndex = false;
		}
		
	  	if ($p->isRegex) 
	  	{
	    	$arr = preg_split($p->pattern, $str, $p->reverseEnabled ? -1 : 2, PREG_SPLIT_OFFSET_CAPTURE);
	    	$num = count($arr);
	
	    	if ($num == 1) return false;
	
	    	$p->matchIndex	= $arr[$num - 2][1] + strlen($arr[$num - 2][0]);
	    	$p->matchLength	= $arr[$num - 1][1] - $p->matchIndex;
	    	$p->match		= substr($str, $p->matchIndex, $p->matchLength);
	    } 
	  	else
	  	{
	    	if ($p->pattern === '') return $p->reverseEnabled ? strlen($str) : 0;
	
	      	$p->matchIndex = $p->reverseEnabled ? strpos(strrev($str), strrev($p->pattern)) 
	      										: strpos($str, $p->pattern);
	
	      	if ($p->matchIndex === false) return false;
			
	      	$p->matchLength	= strlen($p->pattern);
	        $p->match		= $p->pattern;
			
	        if ($p->reverseEnabled) $p->matchIndex = strlen($str) - ($p->matchIndex + $p->matchLength);
	 	}
	 	
		if ($offset !== null && $p->reverseEnabled)
		{
			if ($p->reverseEnabled) $str = substr($str, 0, $offset);
			else $str = substr($str, $offset);
		}
		
		if ($f && !$p->reverseEnabled) $p->matchIndex += $offset;
		
	 	return $p->matchIndex;
	}
	
	public static function retrieve($str, $ps, $pe = null)
	{
		$ps	= Pattern::toPattern($ps);
		$is = self::index($str, $ps);
		if ($is === false) return false;
		
		$str = substr($str, $is + $ps->matchLength);
		if ($pe === null) return $ps->append().$str;
		
		$pe = Pattern::toPattern($pe);
		$ie = self::index($str, $pe);
		if ($ie === false) return false;
		$pe->matchIndex += $is + $ps->matchLength;
		
		return $ps->append().substr($str, 0, $ie).$pe->append();
	}
	
	public static function loopRetrieve($str, $p, $x1 = null, $x2 = null, $x3 = null)
	{
		if (is_array($p))
		{
			$r = array();
			
			foreach ($p as $k => $pair)
				$r[$k] = is_array($pair) ? self::retrieve($str, @$pair[0], @$pair[1]) : self::retrieve($str, $pair);
			
			return $r;
		}
		else if (is_array($x1))
			return self::doLoopRetrieve($str, $p, $x1, $x2);
		else if (is_integer($x2) || $x2 === null)
			return self::doLoopRetrieveSimple($str, $p, $x1, $x2);
		else
			return self::doLoopRetrieve($str, $p, array($x1, $x2), $x3);
	}
	
	public static function replace($str, $search, $replace, $limit = null)
	{
		$search	 = Pattern::toPattern($search); // $search is pattern pair
		$replace = Pattern::toPattern($replace);
		
		if ($search->isPatternEmpty())
			throw new Exception('Search pattern is empty.');
		
		$r		= '';
		$limit	= $limit === null ? - 1 : (int) $limit;
		$count	= 0;
		
		while ($limit == -1 || $count < $limit) 
		{
			$i = self::index($str, $search);
			
			if ($i === false)
			{
				if ($count == 0) return $str;
				else break;
			}
			
			$left	= substr($str, 0, $i);
			$right	= substr($str, $i + $search->matchLength);
			
			if ($search->isRegex)
				$center = preg_replace($search->pattern, $replace->pattern, $search->match);
			else
				$center = $replace->pattern;
			
			$center = $replace->reverseEnabled ? $center.$search->append() : $search->append().$center;
 			
			if ($search->reverseEnabled)
			{
				$r	.= $center.$right;
				$str = $left;
			}
			else
			{
				$r	.= $left.$center;
				$str = $right;
			}
			
			$count++;
		}
		
		return $search->reverseEnabled ? $str.$r : $r.$str;
	}
	
	public static function insert($str, $search, $replace, $limit = null, $option = null)
	{
		$search	 = Pattern::toPattern($search);
		$replace = Pattern::toPattern($replace);

		return self::replace(
			$str,
			new Pattern($search->pattern, $search->isRegex, $search->reverseEnabled, true),
			new Pattern($replace->pattern, $replace->isRegex, $option === null ? $replace->reverseEnabled : (bool) $option),
			$limit
		);
	}
	
	public static function insertAfter($str, $search, $replace, $limit = null)
	{
		return self::insert($str, $search, $replace, $limit, false);
	}
	
	public static function insertBefore($str, $search, $replace, $limit = null)
	{
		return self::insert($str, $search, $replace, $limit, true);
	}
	
	public static function stripTags($var)
	{
		if (!is_array($var)) return trim(strip_tags($var));
		
		foreach ($var as $k => $v)
			$var[$k] = trim(strip_tags($v));
		
		return $var;
	}
	
	public static function stripTagContent($str, $tagName)
	{
		$ps	= new Pattern('/<'.$tagName.'(\s+[^>]*)?>/i', true, false, true);
		$pe	= new Pattern('/<\/'.$tagName.'>/i', true, false, true);
		
		while (($search = self::retrieve($str, $ps, $pe)) !== false)
			$str = substr($str, 0, $ps->matchIndex).substr($str, $pe->matchIndex + $pe->matchLength);
		
		return $str;
	}
	
	public static function setVar($string, $name, $value)
	{
		return str_replace('{'.$name.'}', $value, $string);
	}
	
	/**************************** private static methods ***************************/

	private static function doLoopRetrieve($str, $p, $pPairs, $limit = null) 
	{
		$p = Pattern::toPattern($p);
		
		if ($p->isPatternEmpty())
			throw new Exception('Primary pointer is empty.');
		
		$p->disableAppend();
		
		if (!is_array($pPairs))
			throw new Exception('pPairs is not array.');
		
		if (($pairCount = count($pPairs)) == 0)
			throw new Exception('pPairs is empty.');
		
		reset($pPairs);
		
		if ($pairCount == 1)
			$pPairs = array($pPairs[key($pPairs)]);
		
		if ($pairCount == 2 && !is_array(current($pPairs)) && !is_array(@next($pPairs[1])))
		{
			$pPairs = array($pPairs);
			$pairCount = 1;
		}
		
		foreach ($pPairs as $key => $pPair)
		{
			if (is_array($pPair) && count($pPair) == 2)
			{
				$pPairs[$key][0] = Pattern::toPattern($pPairs[$key][0]);
				$pPairs[$key][1] = Pattern::toPattern($pPairs[$key][1]);
			}
			else throw new Exception('pPairs is not pair array.');
		}
		
		reset($pPairs);
		
		$limit	= $limit === null ? - 1 : (int) $limit;
		$count = 0;
		
		while ($limit == -1 || $count < $limit)
		{
			$temp = self::retrieve($str, $p);
			
			if ($temp === false)
			{
				if ($count == 0) return false;
				else break;
			}
			
			if ($pairCount == 1)
			{
				$r[$count] = self::retrieve($temp, $pPairs[0][0], $pPairs[0][1]);
			}
			else
			{
				foreach ($pPairs as $key => $pPair)
					$r[$count][$key] = self::retrieve($temp, $pPair[0], $pPair[1]);
			}
			
			$str = $p->reverseEnabled ? substr($str, 0, $p->matchIndex)
				 : substr($str, $p->matchIndex + $p->matchLength);

			$count++;
		}
		
		return $r;
	}

	private static function doLoopRetrieveSimple($str, $ps, $pe, $limit = null)
	{
		$ps = Pattern::toPattern($ps);
		$pe = Pattern::toPattern($pe);
		
		if ($ps->isPatternEmpty() && $pe->isPatternEmpty())
			throw new Exception('Start pointer and end pointer are both empty.');
		
		$limit	= $limit === null ? - 1 : (int) $limit;
		$count = 0;
		
		while ($limit == -1 || $count < $limit)
		{
			$temp = self::retrieve($str, $ps, $pe);
			
			if ($temp === false)
			{
				if ($count == 0) return false;
				else break;
			}
			
			$r[$count++] = $temp;
			$str = $ps->reverseEnabled ? substr($str, 0, $ps->matchIndex)
				 : substr($str, $pe->matchIndex + $pe->matchLength);
		}
		
		return $r;
	}
}
