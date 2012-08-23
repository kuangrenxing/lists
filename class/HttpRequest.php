<?php
Globals::requireClass('HttpSignature');
Globals::requireClass('File');

class HttpRequest extends HttpSignature
{
	const TRIGGER_EXPIRATION = 900;
	const LINELIMIT_NO = -1;
	
	public static $defaultConfig = array(
		'scheme'		=> 'HTTP/1.1',
		'method'		=> 'GET',
		'url'			=> null,
		'headers'		=> null,
		'paramsGet'		=> null,
		'paramsPost'	=> null,
		'tryCount'		=> 3,
		'timeout'		=> 30,
		'directOutput'	=> false,
		'lineLimit'		=> -1,
		'endLinePattern'=> null,
		'auto'			=> false,
		'autoOpen'		=> false,
		'autoSend'		=> false,
		'autoReceive'	=> false,
		'autoClose'		=> false
	);
	
	protected $host;
	protected $port;
	protected $path;
	protected $query;
	protected $headers		= array();
	protected $paramsGet	= array();
	protected $paramsPost	= array();
	protected $socket;
	protected $responseHeaders;
	protected $responseData;
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		
		if ($this->config['auto'] || $this->config['autoOpen'])
			$this->open();
	}
	
	/******************** static trigger ********************/
	public static function trigger($url, $lockFile = null, $expire = self::TRIGGER_EXPIRATION)
	{
		if ($lockFile)
		{
			$time	= file_exists($lockFile) ? intval(file_get_contents($lockFile)) : 0;
			$now	= time();
			
			if ($now <= $time + $expire)
				return false;
			
			File::saveFile($now, $lockFile);
		}
		
		return new HttpRequest(array('url' => $url, 'lineLimit' => 0, 'auto' => true));
	}
	
	/******************** config setters ********************/
	protected function setUrl($url)
	{
		$portions	= parse_url($url);
		$this->host	= isset($portions['host']) ? $portions['host'] : null;
		$this->port	= isset($portions['port']) ? $portions['port'] : 80;
		$this->path	= isset($portions['path']) ? str_replace(' ', '%20', $portions['path']) : '/';
		$this->query= isset($portions['query']) ? $portions['query'] : null;
	}
	
	protected function setHeaders($headers)
	{
		if (is_string($headers))
			$headers = explode("\n", $headers);
		
		if (is_array($headers))
		{
			foreach ($headers as $name => $value)
			{
				if (!is_string($name))
				{
					$pair	= explode(':', $value, 2);
					$name	= $pair[0];
					$value	= isset($pair[1]) ? $pair[1] : null;
				}
				
				$this->setHeader($name, $value);
			}
		}
	}
	
	protected function setParamsGet($params)
	{
		if (is_array($params))
		{
			foreach ($params as $name => $value)
				$this->setParamGet($name, $value);
		}
	}
	
	protected function setParamsPost($params)
	{
		if (is_array($params))
		{
			foreach ($params as $name => $value)
				$this->setParamPost($name, $value);
		}
	}
	
	/******************** setters ********************/
	public function setHeader($name, $value)
	{
		$name	= $this->formatHeaderName($name);
		$value	= trim($value);
		
		$this->headers[$name] = $value;
	}
	
	public function setParamGet($name, $value)
	{
		$this->paramsGet[$name] = $value;
	}
	
	public function setParamPost($name, $value)
	{
		$this->paramsPost[$name] = $value;
	}
	
	/******************** getters ********************/
	public function getResponseHeader($name)
	{
		if (isset($this->responseHeaders[$name]))
			return $this->responseHeaders[$name];
	}
	
	public function getResponseHeaders()
	{
		return $this->responseHeaders;
	}
	
	public function getResponseData()
	{
		return $this->responseData;
	}
	
	/******************** operations ********************/
	public function open($tryCount = null, $timeout = null)
	{
		$tryCount	= $this->getConfig('tryCount', $tryCount);
		$timeout	= $this->getConfig('timeout', $timeout);
		
		if ($tryCount < 1)
			$tryCount = 1;
		
		for ($i = 0; $i < $tryCount; $i++)
		{
			$this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $timeout);
			
			if ($this->socket)
				break;
		}
		
		if (!$this->socket)
			Globals::error('['.$errno.'] '.$errstr, $this);
		
		if ($this->config['auto'] || $this->config['autoSend'])
			return $this->send();
	}
	
	public function send()
	{
		$socket = $this->socket;
		
		if (!$socket)
			return false;
		
		$scheme		= strtoupper($this->config['scheme']);
		$method		= strtoupper($this->config['method']);
		$query		= Url::buildQuery($this->paramsGet);
		$query		= !empty($this->query) && !empty($query) ? $this->query.'&'.$query : $this->query.$query;
		$uri		= $this->path.($query ? '?'.$query : '');
		$postData	= Url::buildQuery($this->paramsPost);
		$uri		= $this->sign($uri, $postData);
		$host		= $this->host;
		$accept		= isset($this->headers['Accept']) ? $this->headers['Accept'] : '*/*';
		
		if (!empty($postData))
			$method = 'POST';
		
		$data = <<<EOF
$method $uri $scheme
Host: $host
Accept: $accept

EOF;
		
		if ($method == 'POST')
		{
			$this->headers['Content-Type']	= 'application/x-www-form-urlencoded';
			$this->headers['Content-Length']= strlen($postData);
		}
		
		if (!isset($this->headers['Cache-Control']))
			$this->headers['Cache-Control'] = 'no-cache';
		
		if (!isset($this->headers['Connection']))
			$this->headers['Connection'] = 'close';
		
		foreach ($this->headers as $name => $value)
		{
			if ($name == 'Host' || $name == 'Accept')
				continue;
			
			$data .= $name.': '.$value."\n";
		}
		
		$data .= "\n".$postData;
		fputs($socket, $data);
		
		if ($this->config['auto'] || $this->config['autoReceive'])
			return $this->receive();
		
		return true;
	}
	
	public function receive($directOutput = null, $lineLimit = null, $endLinePattern = null)
	{
		$socket = $this->socket;
		
		if (!$socket)
			return false;
		
		$r = '';
		$i = 0;
		$headers = array();
		$headersStripped = false;
		
		$directOutput	= $this->getConfig('directOutput', $directOutput);
		$lineLimit		= $this->getConfig('lineLimit', $lineLimit);
		$endLinePattern	= $this->getConfig('endLinePattern', $endLinePattern);
		
		$chunked	= false;
		$chunkSize	= 0;
		
		while (!feof($socket) && ($lineLimit == self::LINELIMIT_NO || $i < $lineLimit))
		{
			$data = fgets($socket);
			
			if (!$headersStripped)
			{
				$data = trim($data);
				
				if (empty($data))
				{
					if (isset($headers['Transfer-Encoding']))
						$chunked = $headers['Transfer-Encoding'] == 'chunked';
					
					$headersStripped = true;
					continue;
				}
				
				$pair	= explode(':', $data, 2);
				$name	= $this->formatHeaderName($pair[0]);
				$value	= isset($pair[1]) ? trim($pair[1]) : null;
				
				$headers[$name] = $value;
				
				if ($directOutput && ($name == 'Content-Type' || $name == 'Content-Length'))
					header($name.': '.$value);
			}
			else
			{
				if ($chunked)
				{
					if ($chunkSize <= 0)
					{
						$chunkSize = hexdec(trim($data));
						continue;
					}
					
					$chunkSize -= strlen($data);
				}
				
				if ($directOutput)
					echo $data;
				else
					$r .= $data;
				
				if ($endLinePattern && preg_match($endLinePattern, $data))
					break;
				
				$i++;
			}
		}
		
		$this->responseHeaders	= $headers;
		$this->responseData		= $r;
		
		if ($this->config['auto'] || $this->config['autoClose'])
			$this->close();
		
		return $r;
	}
	
	public function sendResponseHeader($name)
	{
		if (isset($this->responseHeaders[$name]))
			header($name.': '.$this->responseHeaders[$name]);
	}
	
	public function close()
	{
		if ($this->socket)
			return fclose($this->socket);
		else
			return false;
	}
	
	/******************** misc ********************/
	protected function formatHeaderName($name)
	{
		return preg_replace('/(^|-)([a-z])/e', 'strtoupper("\0")', strtolower(trim($name)));
	}
}

Config::extend('HttpRequest', 'HttpSignature');
