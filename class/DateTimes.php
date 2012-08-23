<?php

class DateTimes
{
	public static function getTimeByFormat($format, $time = null)
	{
		if (is_string($time))
			$time = strtotime($time);
		
		return strtotime(date($format, $time ? $time : time()));
	}
	
	public static function getHourTime($time = null)
	{
		return self::getTimeByFormat('Y-m-d H:00:00', $time);
	}
	
	public static function getDayTime($time = null)
	{
		return self::getTimeByFormat('Y-m-d 00:00:00', $time);
	}
	
	public static function getMonthTime($time = null)
	{
		return self::getTimeByFormat('Y-m-01 00:00:00', $time);
	}
	
	public static function getYearTime($time = null)
	{
		return self::getTimeByFormat('Y-01-01 00:00:00', $time);
	}
}
