<?php
Globals::requireClass('Database');

class Table extends Config
{
	const MODE_ADD		= 0;
	const MODE_MODIFY	= 1;
	
	public static $defaultConfig = array(
		'table'		=> null,
		'primaryKey'=> 'id',
		'autoCreate'=> false
	);
	
	protected $database;
	protected $table;
	protected $mode;
	
	public function __construct($config = null)
	{
		Config::resolveConflict($config, 'table', $this);
		parent::__construct($config);
		
		$this->database = $this->config['database'] instanceof Database ? $this->config['database'] : Database::create($this->config);
		
		if ($this->config['autoCreate'] && method_exists($this, 'create'))
			$this->create();
	}
	
	/******************** config setters ********************/
	protected function setTable($value)
	{
		$this->table = $value;
	}
	
	/******************** getters ********************/
	public function getDatabase()
	{
		return $this->database;
	}
	
	public function getTable()
	{
		return $this->database->getTable($this->table);
	}
	
	/******************** validate ********************/
	public function validate(array &$data)
	{
		return true;
	}
	
	/******************** query methods ********************/
	public function select($fields = null, $where = null, $order = null, $count = null, $offset = null, $group = null)
	{
		return $this->database->select($this->table, $fields, $where, $order, $count, $offset, $group);
	}
	
	public function insertSelect($toTable, $fields = null, $where = null, $order = null, $count = null, $offset = null, $group = null)
	{
		return $this->database->insertSelect($toTable, $this->table, $fields, $where, $order, $count, $offset, $group);
	}
	
	public function listCount($where = null)
	{
		return $this->database->listCount($this->table, $where);
	}
	
	public function listDistinctCount($where = null, $distinct = null)
	{
		return $this->database->listDistinctCount($this->table, $where, $distinct);
	}
	
	public function listRand($where, $count, $weightField = null, $primaryKey = 'id')
	{
		return $this->database->listRand($this->table, $where, $count, $weightField, $primaryKey);
	}
	
	public function listRandWithFields($fields, $where, $count, $weightField = null, $primaryKey = 'id')
	{
		return $this->database->listRandWithFields($this->table, $fields, $where, $count, $weightField, $primaryKey);
	}
	
	public function listAll($where = null, $order = null, $count = null, $offset = null, $group = null)
	{
		return $this->database->listAll($this->table, $where, $order, $count, $offset, $group);
	}
	
	public function listAllWithFields($fields, $where = null, $order = null, $count = null, $offset = null, $group = null)
	{
		return $this->database->listAllWithFields($this->table, $fields, $where, $order, $count, $offset, $group);
	}
	
	public function listPage($where = null, $order = null, $pageSize = null, $pageId = 1, $group = null)
	{
		return $this->database->listPage($this->table, $where, $order, $pageSize, $pageId, $group);
	}
	
	public function listPageWithFields($fields, $where = null, $order = null, $pageSize = null, $pageId = 1, $group = null)
	{
		return $this->database->listPageWithFields($this->table, $fields, $where, $order, $pageSize, $pageId, $group);
	}
	
	public function getRow($where = null, $order = null, $offset = null)
	{
		return $this->database->getRow($this->table, $where, $order, $offset);
	}
	
	public function getRowWithFields($fields, $where = null, $order = null, $offset = null)
	{
		
		return $this->database->getRowWithFields($this->table, $fields, $where, $order, $offset);
	}
	
	public function insert(array $data, $returnInsertId = false)
	{
		return $this->database->insert($this->table, $data, $returnInsertId);
	}
	
	public function add(array $data, $returnInsertId = false)
	{
		$this->mode = self::MODE_ADD;
		
		if (!$this->validate($data))
			Globals::error($this->message);
		
		return $this->insert($data, $returnInsertId);
	}
	
	public function update(array $data, $where = null)
	{
		return $this->database->update($this->table, $data, $where);
	}
	
	public function updateInsert(array $updateData, $where, array $insertData, $merge = false)
	{
		return $this->database->updateInsert($this->table, $updateData, $where, $insertData, $merge);
	}
	
	public function modify(array $data, $where = null)
	{
		$this->mode = self::MODE_MODIFY;
		
		if (!$this->validate($data))
			Globals::error($this->message);
		
		return $this->update($data, $where);
	}
	
	public function delete($where = null)
	{
		return $this->database->delete($this->table, $where);
	}
}

Config::extend('Table', 'Database');
