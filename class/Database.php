<?php

abstract class Database extends Config
{
	const ORDER_RANDOM = 0;
	
	public static $defaultConfig = array
	(
		'type'				=> 'mysql',
		'autoConnect'		=> true,
		'server'			=> 'localhost',
		'username'			=> 'root',
		'password'			=> null,
		'database'			=> null,
		'tablePrefixEnabled'=> false,
		'tablePrefix'		=> null,
		'charset'			=> 'utf8',
		'pageSize'			=> 20
	);
	
	protected $link;
	protected $queryString;
	protected $result;
	
	/******************** abstract methods ********************/
	abstract protected function error();
	abstract public function connect($server = null, $username = null, $password = null);
	/******************** abstract config setters ********************/
	abstract protected function setDatabase($value);
	abstract protected function setCharset($value);
	/******************** abstract query methods ********************/
	abstract public function escape($string);
	abstract public function query($query);
	abstract public function seek($result = null, $offset = 0);
	abstract public function fetch($result = null, $numMode = false);
	abstract public function getInsertId();
	abstract public function getAffectedRows();
	
	/******************** static creator ********************/
	public static function create($config = null)
	{
		if ($config instanceof Database)
			return $config;
		
		$type = is_array($config) && isset($config['type']) ? $config['type'] : self::$defaultConfig['type'];
		$type = Globals::firstToUpper($type);
		
		eval('
			Globals::requireClass("Database'.$type.'");
			$database = new Database'.$type.'($config);
		');
		
		return $database;
	}
	
	/******************** config setters ********************/
	protected function setTablePrefix($value)
	{
		$this->config['tablePrefixEnabled'] = !empty($value);
	}
	
	/******************** set tablePrefixEnabled ********************/
	public function enableTablePrefix()
	{
		$this->config['tablePrefixEnabled'] = true;
	}
	
	public function disableTablePrefix()
	{
		$this->config['tablePrefixEnabled'] = false;
	}
	
	/******************** getters ********************/
	public function getTable($table)
	{
		if ($this->config['tablePrefixEnabled'])
			return $this->config['tablePrefix'].$table;
		else
			return $table;
	}
	
	public function getLink()
	{
		return $this->link;
	}
	
	public function getQuery()
	{
		return $this->queryString;
	}
	
	public function getResult()
	{
		return $this->result;
	}
	
	public function getData(array $data, $pairMode = false)
	{
		if (!count($data))
			$this->error('Data array can not be empty.');
		
		if ($pairMode)
		{
			foreach ($data as $k => $v)
			{
				if (is_numeric($v))
					continue;
				else if (is_null($v))
					$data[$k] = 'NULL';
				else
					$data[$k] = "'".$this->escape($v)."'";
			}
			
			$keys	= implode(', ', array_keys($data));
			$values	= implode(', ', array_values($data));
			
			return '('.$keys.') VALUES ('.$values.')';
		}
		else
		{
			$dataList = array();
			
			foreach ($data as $k => $v)
			{
				if (is_int($k))
					$dataList[] = $v;
				else
				{
					if (is_numeric($v))
						$dataList[] = $k.' = '.$v;
					else if (is_bool($v))
						$dataList[] = $k.' = '.intval($v);
					else if (is_null($v))
						$dataList[] = $k.' = NULL';
					else
						$dataList[] = $k." = '".$this->escape($v)."'";
				}
			}
			
			return implode(', ', $dataList);
		}
	}
	
	public function getWhere($where, $orMode = false)
	{
		
		if (is_array($where))
		{
			$arr = array();
			
			foreach ($where as $k => $v)
			{
				if (is_int($k))
					$arr[] = $v;
				else
				{
					if (strpos($k, '?') !== false)
					{
						if (is_numeric($v))
							;
						else if (is_bool($v))
							$v = intval($v);
						else if (is_null($v))
							$v = 'NULL';
						else
							$v = "'".$this->escape($v)."'";
						
						$arr[] = str_replace('?', $v, $k);
					}
					else
					{
						if (is_numeric($v))
							$v = ' = '.$v;
						else if (is_bool($v))
							$v = ' = '.intval($v);
						else if (is_null($v))
							$v = ' IS NULL';
						else
							$v = " = '".$this->escape($v)."'";
						
						$arr[] = $k.$v;
					}
				}
			}
			
			$where = count($arr) ? implode($orMode ? ' OR ' : ' AND ', $arr) : '1';
		}
		else if (is_numeric($where))
			$where = 'id = '.$where;
		else if (empty($where))
			$where = '1';
		else
			$where = strval($where);
		
		return $where;
	}
	
	public function getOrder($order)
	{
		if (is_array($order))
			$order = implode(', ', $order);
		else
			$order = strval($order);
		
		return $order;
	}
	
	public function getList($result = null)
	{
		if (!$result)
			$result = $this->result;
		
		$list = array();
		
		while ($row = $this->fetch($result))
			$list[] = $row;
		
		$this->seek($result);
		
		return $list;
	}
	
	/******************** query methods ********************/
	public function select($table, $fields = null, $where = null, $order = null, $count = null, $offset = null, $group = null, $toTable = null)
	{
		
		$table	= $this->getTable($table);
		$fields	= $fields ? $fields : '*';
		$where	= $this->getWhere($where);
		$group	= $group ? ' GROUP BY '.$group : null;
		$order	= $order ? ' ORDER BY '.$this->getOrder($order) : null;
		$limit	= is_numeric($count) ? ' LIMIT '.($offset ? $offset.', '.$count : $count) : null;
		$query	= ($toTable ? 'INSERT INTO `'.$toTable.'` ' : null).'SELECT '.$fields.' FROM `'.$table.'` WHERE '.$where.$group.$order.$limit;
		
		return $this->query($query);
	}
	
	public function insertSelect($toTable, $table, $fields = null, $where = null, $order = null, $count = null, $offset = null, $group = null)
	{
		return $this->select($table, $fields, $where, $order, $count, $offset, $group, $toTable);
	}
	
	public function listCount($table, $where = null)
	{
		$result	= $this->select($table, 'COUNT(*) AS total', $where);
		$row	= $this->fetch($result);
		
		return intval($row['total']);
	}
	
	public function listDistinctCount($table, $where = null, $distinct = null)
	{
		$result	= $this->select($table, 'COUNT(distinct('.$distinct.')) AS total', $where, null, null, null);
		$row	= $this->fetch($result);
		
		return intval($row['total']);
	}
	
	public function listRand($table, $where, $count, $weightField = null, $primaryKey = 'id')
	{
		return $this->listRandWithFields($table, null, $where, $count, $weightField, $primaryKey);
	}
	
	public function listRandWithFields($table, $fields, $where, $count, $weightField = null, $primaryKey = 'id')
	{
		$ids	= array();
		$list	= array();
		
		if ($weightField)
		{
			$data	= $this->getList($this->select($table, $primaryKey.','.$weightField, $where));
			$total	= count($data);
			
			if (!$total)
				return $list;
			
			$allWeight	= 0;
			$weightMap	= array();
			
			foreach ($data as $row)
			{
				$allWeight += $row[$weightField];
				$weightMap[$row[$primaryKey]] = $allWeight;
			}
			
			for ($i = 0; $i < $count && $i < $total; $i++)
			{
				while (true)
				{
					$randWeight = mt_rand(0, $allWeight - 1);
					
					foreach ($weightMap as $id => $weight)
					{
						if ($randWeight < $weight)
							break;
					}
					
					if (array_search($id, $ids) === false)
					{
						$ids[]	= $id;
						$list[]	= $this->getRowWithFields($table, $fields, $id);
						break;
					}
				}
			}
		}
		else
		{
			$allIds	= $this->getIds($table, $where, $primaryKey, true);
			$total	= count($allIds);
			
			for ($i = 0; $i < $count && $i < $total; $i++)
			{
				while (true)
				{
					$id = $allIds[mt_rand(0, $total - 1)];
					
					if (array_search($id, $ids) === false)
					{
						$ids[]	= $id;
						$list[]	= $this->getRowWithFields($table, $fields, $id);
						break;
					}
				}
			}
		}
		
		return $list;
	}
	
	public function listAll($table, $where = null, $order = null, $count = null, $offset = null, $group = null)
	{
		return $this->listAllWithFields($table, null, $where, $order, $count, $offset, $group);
	}
	
	public function listAllWithFields($table, $fields, $where = null, $order = null, $count = null, $offset = null, $group = null)
	{
		if ($order === self::ORDER_RANDOM)
			return $this->listRandWithFields($table, $fields, $where, $count);
		else
		{
			$result = $this->select($table, $fields, $where, $order, $count, $offset, $group);
			return $this->getList($result);
		}
	}
	
	public function listPage($table, $where = null, $order = null, $pageSize = null, $pageId = 1, $group = null)
	{
		return $this->listPageWithFields($table, null, $where, $order, $pageSize, $pageId, $group);
	}
	
	public function listPageWithFields($table, $fields, $where = null, $order = null, $pageSize = null, $pageId = 1, $group = null)
	{
		$pageSize = $this->getConfig('pageSize', $pageSize);
		return $this->listAllWithFields($table, $fields, $where, $order, $pageSize, ($pageId - 1) * $pageSize, $group);
	}
	
	public function getRow($table, $where = null, $order = null, $offset = null)
	{
		return $this->getRowWithFields($table, null, $where, $order, $offset);
	}
	
	public function getRowWithFields($table, $fields, $where = null, $order = null, $offset = null)
	{
		
		$result = $this->select($table, $fields, $where, $order, 1, $offset);
		return $this->fetch($result);
	}
	
	public function getIds($table, $where = null, $field = 'id', $returnArray = false, $order = null, $count = null, $offset = null)
	{
		$result	= $this->select($table, $field, $where, $order, $count, $offset);
		$ids	= array();
		
		while ($row = $this->fetch($result))
			$ids[] = $row[$field];
		
		return $returnArray ? $ids : implode(',', $ids);
	}
	
	public function insert($table, array $data, $returnInsertId = false)
	{
		$table	= $this->getTable($table);
		$data	= $this->getData($data, true);
		$query	= 'INSERT INTO `'.$table.'` '.$data;
		$result	= $this->query($query);
		
		return $returnInsertId && $result ? $this->getInsertId() : $result;
	}
	
	public function update($table, array $data, $where = null)
	{
		$table	= $this->getTable($table);
		$data	= $this->getData($data);
		$where	= $this->getWhere($where);
		$query	= 'UPDATE `'.$table.'` SET '.$data.' WHERE '.$where;
		
		return $this->query($query);
	}
	
	// update on fail insert
	public function updateInsert($table, array $updateData, $where, array $insertData, $merge = false)
	{
		$result = $this->update($table, $updateData, $where);
		
		if ($result && $this->getAffectedRows())
			return $result;
		else
		{
			if ($merge)
			{
				if (!is_array($where))
					$where = array($where);
				
				$insertData = array_merge($where, $insertData);
			}
			
			return $this->insert($table, $insertData);
		}
	}
	
	public function delete($table, $where = null)
	{
		$table	= $this->getTable($table);
		$where	= $this->getWhere($where);
		$query	= 'DELETE FROM `'.$table.'` WHERE '.$where;
		
		return $this->query($query);
	}
}
