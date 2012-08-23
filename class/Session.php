<?php
/*********************************
 *  session处理类
 ********************************/
class Session
{	
	var $sessionId = "";
	var $member = array();
	
	function __construct()
	{
		session_start();
		$this->sessionId = session_id();
		if (isset($_SESSION[session_id()]))
		{
			$this->member = $_SESSION[session_id()];
		}
	}
	
	function getSessionId()
	{
		return $this->sessionId;
	}
	
	function addMember($name, $value)
	{
		$this->member[$name] = serialize($value);
		$this->storeSession();
	}
	
	function setMember($name, $value)
	{
		$this->member[$name] = serialize($value);
		$this->storeSession();
	}
		
	function deleteSession($name)
	{
		unset($this->member[$name]);
		$this->storeSession();
	}
	
	function deleteMember($name)
	{
		unset($this->member[$name]);
		$this->storeSession();
	}
	
	function getMember($name)
	{
		if (isset($this->member[$name]))
			return unserialize($this->member[$name]);
		else 
			return false;
	}

	function getSession($name)
	{
		if (isset($this->member[$name]))
			return unserialize($this->member[$name]);
		else 
			return false;
	}

	function storeSession()
	{
		$_SESSION[$this->sessionId] = $this->member;
	}
	
	function clearSession()
	{
		$this->member = array();
	}
	
	function unregisterSession()
	{
		session_unset();
	}
}
?>