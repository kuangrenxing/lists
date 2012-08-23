<?php
Globals::requireClass('Database');

class DatabaseMysql extends Database
{
	protected function error()
	{
		Globals::error('Database error ['.mysql_errno().']: '.mysql_error());
	}
	
	public function connect($server = null, $username = null, $password = null)
	{
		$this->link = mysql_connect(
			$this->getConfig('server', $server),
			$this->getConfig('username', $username),
			$this->getConfig('password', $password)
		)
		or $this->error();
	}
	
	/******************** config setters ********************/
	protected function setDatabase($value)
	{
		if (!empty($value))
		{
			if (!$this->link && $this->config['autoConnect'])
				$this->connect();
			
			mysql_select_db($value, $this->link) or $this->error();
		}
	}
	
	protected function setCharset($value)
	{
		if (!empty($value))
			return $this->query('SET NAMES '.$value);
	}
	
	/******************** query methods ********************/
	public function escape($string)
	{
		return mysql_real_escape_string($string, $this->link);
	}
	
	public function query($query)
	{
		$this->queryString = $query;
		$this->result = mysql_query($query, $this->link) or $this->error();
		
		return $this->result;
	}
	
	public function seek($result = null, $offset = 0)
	{
		@mysql_data_seek($result ? $result : $this->result, $offset);
	}
	
	public function result($result = null , $offset = 0)
	{
		$res = @mysql_result($result ? $result : $this->result, $offset);
		
		return $res;
	}
	
	public function fetch($result = null, $numMode = false)
	{
		if (!$result)
			$result = $this->result;
		
		return $numMode ? mysql_fetch_row($result) : mysql_fetch_assoc($result);
	}
	
	public function getInsertId()
	{
		return mysql_insert_id($this->link);
	}
	
	public function getAffectedRows()
	{
		return mysql_affected_rows($this->link);
	}
}
