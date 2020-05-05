<?php

abstract class Model {
	public $info;
	public $dbCon;
	public $tableName;
	public $idField = 'id';
	public $fields;

	public function __construct($dbIndex=''){
		global $INFO;
		$this->info = $INFO;
		if(empty($dbIndex)) {
			$this->dbConnect();
		} else {
			$this->dbConnect($dbIndex);
		}
		array_walk_recursive($_GET, 'trim');
		array_walk_recursive($_POST, 'trim');
	}

	public function dbConnect($dbIndex = 'default'){
		$dbinfo = $this->info['db'][$dbIndex];
		$result = new \mysqli($dbinfo['host'],$dbinfo['user'],$dbinfo['password'],$dbinfo['base'] );
		if (!$result){
			mail('chang@v12software.com,support@v12software.com', 'V12 Software Alert : mysql_pconnect error');
		}
		$result->set_charset("utf8");
		$this->dbCon = $result;
		return TRUE;
	}
/*
	public function query($query, $params = null, $format = null) {

		// The return value.
		$result = null;

		// Log the query for debug.
		$this->info['log']->logDebug($query);

		// Create a prepared statement.
		if ($stmt = $this->dbCon->prepare($query)) {
			// Bind the parameters of the query if there are any.
			if ($params != null) {
				// Merge the format with the params.
				$params = array_merge(array($format), $params);
				$this->info['log']->logDebug($params);
				// bind_params accept only references.
				$refParams = array();
				foreach($params as $key => $value) {
					$refParams[$key] = &$params[$key];
				}

				// Bind the parameters.
				call_user_func_array(array(&$stmt, 'bind_param'), $refParams);
				//$stmt->bind_param($format, $params);
			}

			// Execute the query.
			$stmt->execute();
			$result = $stmt->get_result();
			$stmt->close();
		}

		if ($this->dbCon->error) {
			$this->info['log']->logError($this->dbCon->error);
		}

		// Return the result.
		return $result;
	}
*/
	public function query($query) {
		if(!empty($_GET['debug'])){
			echo $query;
		}
		// Log the information.
		if (isset($this->info['log'])) {
			$this->info['log']->logDebug($query);
		}

		// Run the Query.
		$result = $this->dbCon->query($query);

		// Log some error.
		if ($this->dbCon->error) {
			trigger_error($this->dbCon->error, E_USER_WARNING);
			if (isset($this->info['log'])) {
				$this->info['log']->logError($this->dbCon->error);
			}
		}

		// Return the result.
		return $result;
	}

	/**
	 * Do a query with the pagination.
	 * @param $query to get the current page
	 * @param $query to get the total number of data.
	 * @param $page (optional, default is 1).
	 * @param $item_per_page (optional, default is 20)
	 */
	function queryPagination($query, $query_count, $page = 1, $item_per_page = 20) {
		// Result object.
		$result = (object) array('page' => $page);

		// Do the query and populate the result items.
		$start = ($page - 1) * $item_per_page;
		$query .= " LIMIT $start, $item_per_page";
		$result->items = $this->findAll($query);

		// Get the total count of item.
		$count = $this->findOne($query_count);
		$result->total_items = $count['count'];

		// Pagination meta data.
		$result->page_total_items = count($result->items);
		$result->page_item_start = $start + 1;

		// Count the number of page.
		$result->total_pages = ceil($result->total_items / $item_per_page);

		return $result;
	}

	/**
	 * Find all the model object.
	 * @param $query if null, get all the field.
	 */
	public function findAll($query = null) {
		// Get all the field if the query is not specified.
		if (!isset($query)) {
			$query = "SELECT * FROM $this->tableName;";
		}

		// Execute the query and fetch all rows.
		$result = $this->query($query);
		$rows = array();
		if($result){
			while ($row = $result->fetch_assoc()) {
				$rows[] = $row;
			}
		}
		// Return the rows to the caller.
		return $rows;
	}

	public function findOne($query){
		$result = $this->query($query);
		if(!$result) return FALSE;
		return  $result->fetch_assoc();
	}

	public function findbyID($id){
		$query = "SELECT * FROM $this->tableName WHERE ".$this->idField."=".$id;
		$result = $this->query($query);
		if(!$result) return FALSE;
		return  $result->fetch_assoc();
	}

	/**
	 * Save data in the database. It doesn and insert or an update based in the
	 * presence of the id field in the provided array of data. You can also specify
	 * a custom table to save the data.
	 * @param $data to save
	 * @param $table (optional, defaul null) custom table to save.
	 */
	public function save($data, $table = null) {
		// Get another table
		$table = is_null($table) ? $this->tableName : $table;
		
		if(empty($this->fields)){
			$query = "SHOW COLUMNS FROM ".$table;
			$result = $this->findAll($query);
			$this->fields = array();
			foreach ($result as $row){
				$this->fields[] = $row['Field'];
			}
		}
		
		if (!empty($data[$this->idField])) {
			$query = 'UPDATE ' . $table . ' SET ';
			$i = 0;
			foreach ($data as $key => $v) {
				if(in_array(strtolower($key), $this->fields)){
					$query .= ($i==0 ? '' : ',') . $key . "='" . addslashes($v) . "'";
					$i++;
				}
			}

			$query.= " WHERE $this->idField='{$data[$this->idField]}'";
		} else {
			$query = 'INSERT INTO ' . $table . ' SET ';
			$i = 0;
			foreach ($data as $key => $v) {
				if(in_array(strtolower($key), $this->fields)){
					$query .= ($i==0 ? '' : ',') . $key . "='" . addslashes($v) . "'";
					$i++;
				}
			}
		}

		if($i>0){
			$result = $this->query($query);
		}
		if(!$result) {
			$this->info['log']->logDebug($query);
		}
		return $result;
	}

	/**
	 * Find all the object matching the equals condition given in the array
	 * @param array $conditions - key, value of equals condition.
	 * @param string $table - optional parameter to look to specific table.
	 * @return array of result.
	 */
	public function findbyCon($conditions = array(), $table = null) {
		// Get the table
		$table = ($table) ? $table : $this->tableName;

		// Build the where condition
		$where = '';
		if (count($conditions) > 0) {
			$c = array();
			foreach ($conditions as $key => $value){
				$c[] = $key . "='" . addslashes($value) . "'";
			}

			$where = ' WHERE '.implode(' AND ', $c);
		}

		// Build the query and execute it.
		$query = "SELECT * FROM $table $where";
		return $this->findAll($query);
	}

	public function escaping($data) {
		return mysqli_real_escape_string($this->dbCon, trim($data));
	}
}
