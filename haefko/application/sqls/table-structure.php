<?php


class TableStructure extends Object
{

	protected $updated = false;
	protected $structure = array();


	public function __construct()
	{
		$this->structure = Cache::get('sql', 'tables');
	}


	public function __destruct()
	{
		if ($this->updated)
			Cache::put('sql', 'tables', $this->structure, 10);
	}


	public function getType($table, $column)
	{
		if (!isset($this->structure[$table]))
			$this->getTable($table);

		if (!isset($this->structure[$table][$column]))
			return 'default';

		return $this->structure[$table][$column]['type'];
	}


	protected function getTable($table)
	{
		$structure = db::query('DESCRIBE ' . $table)->fetchAll();

		foreach ($structure as $row) {
			$this->structure[$table][$row->Field] = array();

			$type = $row->Type;
			$length = null;
			if (preg_match('#^(.*)\((\d+)\)$#', $row->Type, $match)) {
				$type = $match[1];
				$length = $match[2];
			}

			$this->structure[$table][$row->Field]['type'] = $type;
			$this->structure[$table][$row->Field]['length'] = $length;
			$this->structure[$table][$row->Field]['primary'] = $row->Key === 'PRI';
		}

		$this->updated = true;
	}


}