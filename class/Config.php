<?php
class Config
{
	public static $defaultConfig = array();
	protected $config = array();
	protected $params = array();
	protected $message;
	
	/******************** static methods ********************/
	public static function extend($childClassName, $parentClassNames)
	{
		if (is_string($parentClassNames))
			$parentClassNames = array($parentClassNames);
		
		if (is_array($parentClassNames))
		{
			foreach ($parentClassNames as $parentClassName)
				eval($childClassName.'::$defaultConfig = array_merge('.$parentClassName.'::$defaultConfig, '.$childClassName.'::$defaultConfig);');
		}
	}
	
	public static function exclude(&$config, $namesToExclude)
	{
		if (is_array($config))
		{
			if (is_string($namesToExclude))
				$namesToExclude = array($namesToExclude);
			
			if (is_array($namesToExclude))
			{
				foreach ($namesToExclude as $nameToExclude)
					unset($config[$nameToExclude]);
			}
		}
	}
	
	public static function resolveConflict(&$config, $search, $replace)
	{
		if (is_array($config))
		{
			if (is_object($replace))
			{
				$object		= $replace;
				$className	= get_class($object);
				$replace	= Globals::firstToLower($className);
				
				if (isset($config[$replace]) && $object instanceof Config)
				{
					eval('$configName = isset('.$className.'::$configName) ? '.$className.'::$configName : Globals::firstToLower("'.$className.'");');
					
					if ($replace == $configName)
						return;
				}
			}
			
			if (isset($config[$replace]))
			{
				$config[$search] = $config[$replace];
				unset($config[$replace]);
			}
		}
	}
	
	/******************** constructor ********************/
	public function __construct($config = null)
	{
		$className = get_class($this);
		eval('$this->config = isset('.$className.'::$defaultConfig) ? '.$className.'::$defaultConfig : array();');
		
		if (is_array($config))
		{
			eval('$configName = isset('.$className.'::$configName) ? '.$className.'::$configName : Globals::firstToLower("'.$className.'");');
			
			if (isset($config[$configName]) && is_array($config[$configName]))
				$config = $config[$configName];
			
			$this->config = array_merge($this->config, $config);
		}
		
		foreach ($this->config as $name => $value)
			$this->callSetter($name, $value);
	}
	
	/******************** params methods ********************/
	public function __set($name, $value)
	{
		$this->params[$name] = $value;
	}
	
	public function __get($name)
	{
		if (isset($this->params[$name]))
			return $this->params[$name];
	}
	
	public function __isset($name)
	{
		return isset($this->params[$name]);
	}
	
	public function __unset($name)
	{
		unset($this->params[$name]);
	}
	
	public function setParams(array $params)
	{
		$this->params = array_merge($this->params, $params);
	}
	
	/******************** config methods ********************/
	public function setConfig($name, $value)
	{
		$this->config[$name] = $value;
		return $this->callSetter($name, $value);
	}
	
	public function getConfig($name, $priorValue = null)
	{
		if (isset($priorValue))
			return $priorValue;
		
		if (isset($this->config[$name]))
			return $this->config[$name];
	}
	
	public function issetConfig($name)
	{
		return isset($this->config[$name]);
	}
	
	public function unsetConfig($name)
	{
		unset($this->config[$name]);
	}
	
	protected function callSetter($name, $value)
	{
		$setter = 'set'.Globals::firstToUpper($name);
		
		if (method_exists($this, $setter))
			return $this->$setter($value);
		else
			return false;
	}
	
	/******************** message ********************/
	public function getMessage()
	{
		return $this->message;
	}
}
