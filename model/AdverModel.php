<?php
Globals::requireTable('Adver');

class AdverModel extends Config
{
	protected $table;
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->table = new AdverTable($config);
	}
}
