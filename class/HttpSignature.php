<?php
Globals::requireClass('Url');

class HttpSignature extends Config
{
	public static $defaultConfig = array(
		'signEnabled'	=> false,
		'signKey'		=> null,
		'signName'		=> Url::SIGNATURE_NAME
	);
	
	protected function setSignKey($value)
	{
		$this->config['signEnabled'] = !empty($value);
	}
	
	public function sign($uri, $postData)
	{
		if ($this->config['signEnabled'])
		{
			$name	= $this->config['signName'];
			$value	= Url::getSignature(Url::appendQuery($uri, $postData), $this->config['signKey'], $name);
			$uri	= Url::appendQuery($uri, $name.'='.$value);
		}
		
		return $uri;
	}
	
	public function validateSignature($uri = null, $params = null)
	{
		if (!$this->config['signEnabled'])
			return true;
		
		if (!isset($uri))
			$uri = $_SERVER['REQUEST_URI'];
		
		if (!isset($params))
			$params = $_POST;
		
		$url = Url::appendQuery($uri, Url::buildQuery($params));
		return Url::validateSignature($url, $this->config['signKey'], $this->config['signName']);
	}
}
