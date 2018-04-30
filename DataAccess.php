<?php

class DataAccess {

	private $defaultDB = null;
	private $link = null;
	private $sql = null;
	private $host_info = null;
	private $bindValue = null;
	private $last_result_set = null;
	public $num_rows = 0;
	public $affected_rows = 0;
	public $insert_id = 0;
	public $queries = 0;

	public function __construct() {

		if(func_num_args()) {
			$argv = func_get_arg(0);
			if(!empty($argv) && is_array($argv)) { 
				$this->connect($argv);
				$argv['charset'] = isset($argv['charset']) ? $argv['charset'] : 'utf8';
				$this->setCharset($argv['charset']);
			}
		}

	}

	public function connect($argv, $charset = null) {

		if($this->link) return false;
		$argv = func_get_arg(0);
		$argv['port'] = isset($argv['port']) ? $argv['port'] : 3306;
		$this->link = mysqli_connect( $argv['host'], $argv['user'], $argv['password'], $argv['database'], $argv['port']);
		if(mysqli_connect_errno()) {
			echo mysqli_connect_error(); 
			exit(0);
		}

		$this->defaultDB = $argv['database'];
		$this->host_info = (object) $argv;

		if($charset) $this->setCharset($charset);
	}

	public function selectDB($database){

		$int = mysqli_select_db($this->link, $database);
		if($int) $this->defaultDB = $database;
		return $int;

	}

	public function query($sql) {

		$result = mysqli_query($this->link, $sql);
		if(mysqli_errno($this->link)) {
			echo mysqli_error($this->link);
			exit(0);
		}

		$this->queries++;

		if(preg_match('/^use\s+(\w+)/', $sql, $matches))
			list($range, $this->defaultDB) = $matches;

		if(!preg_match('/^select(.+)$/i', $sql)) {
			$this->affected_rows = mysqli_affected_rows($this->link);
		}else{
			$this->num_rows = mysqli_num_rows($result);
		}

		if(preg_match('/^insert(.+)$/i', $sql))
			$this->insert_id = mysqli_insert_id($this->link);

		return $result;

	}
	public function insert_id() {
		return mysqli_insert_id($this->link);
	}
	public function find($sql) {

		$collection = array();
		$result = $this->query($sql);
		while($rows = mysqli_fetch_assoc($result))
			array_push($collection, $rows);
		mysqli_free_result($result);
		$last_result_set = $collection;
		return $collection;

	}

	public function findOne($sql) {

		$result = $this->query($sql);
		$rows = mysqli_fetch_assoc($result);
		mysqli_free_result($result);
		return $rows;

	}

	public function setCharset($charset) {

		return mysqli_set_charset($this->link, $charset);

	}

	public function prepare($sql) {

		$this->sql = $sql;

	}

	public function bindValue($search, $value) {
		$value = str_replace("'","''",$value);
		#$this->bindValue = array();
		$this->bindValue[$search] = $value;

	}

	public function execute() {

		if(func_num_args()) {
			$argv = func_get_arg(0);
			if(!empty($argv) && is_array($argv)) {
				if(!is_array($this->bindValue)) $this->bindValue = array();
				$this->bindValue = array_merge($this->bindValue, $argv);
			}
		}

		if($this->bindValue) {
			foreach($this->bindValue as $search => $value) {
				$this->sql = str_replace($search, "'".$this->escape($value)."'", $this->sql);
			}
			$this->bindValue = array();
		}
		#echo "SQL:".$this->sql."\n";
		$result = $this->query($this->sql);
		//$this->sql = null;
		if(!$result) 
			return false;
		else if($result===true)
			return true;
		else {
			$this->last_result_set = array();
                	while($rows = mysqli_fetch_assoc($result)) {
                        	array_push($this->last_result_set, $rows);
			}
			mysqli_free_result($result);
			return true;
		}
		//return (boolean)$result;
		//return $result;
	}

	public function resultSet() {
		return $this->last_result_set;
	}

	public function escape($string) {

		return mysqli_real_escape_string($this->link, $string);

	}

	public function close() {

		return mysqli_close($this->link);

	}

	public function ping() {

		return mysqli_ping($this->link);

	}

	public function beginTransaction($boolean) {

		return mysqli_autocommit($this->link, $boolean);

	}

	public function commit() {

		return mysqli_commit($this->link);

	}

	public function rollback() {

		return mysqli_rollback($this->link);

	}

	public function __destruct() {

		if($this->link) $this->close();
		unset($this->link, $this->defaultDB, $this->bindValue, $this->sql, $this->result, $this->num_rows, $this->affected_rows, $this->insert_id, $this->host_info);

	}

}
/*
   $argv = array(
   'host' => 'localhost',
   'user' => 'root',
   'password' => 'meteorshower.',
   'port' => 3306,
   'database' => 'test',
   'charset'=> 'utf8');


// Using the "mysql::__construct" method to connect MySQL database

$mysql = new mysql($argv);
var_dump($mysql->find('select version()'));
var_dump($mysql->queries);


// Using the "mysql::connect" method to connect MySQL database

$mysql = new mysql();
$mysql->connect($argv);
var_dump($mysql->find('select version()'));
var_dump($mysql->queries);


$mysql = new mysql();
$mysql->connect($argv);
$mysql->setCharset($argv['charset']);
var_dump($mysql->find('select version()'));
var_dump($mysql->queries);
 */


?>

