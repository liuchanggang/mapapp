<?php
class Simple extends Model{
	public function __construct($tableName) {
		parent::__construct();
		$this->tableName = $tableName;
	}
}