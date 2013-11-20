<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

abstract class DTDatabase {
	public $ilike = "LIKE"; //keyword for case-insensitive search
	public $conn = null;

	function __construct($database=null,$user=null,$password=null,$host="localhost"){
		if($database==null){
			$host = DTSettings::$storage["default"]["host"];
			$user = DTSettings::$storage["default"]["user"];
			$password = DTSettings::$storage["default"]["password"];
			$database = DTSettings::$storage["default"]["database"];
		}
		
		$this->connect($database,$user,$password,$host);
	}
	
	/**
		@return returns an OSQueryBuilder with the appropriate where clause
	*/
	public function where($where_str){
		$qb = new DTQueryBuilder($this);
		return $qb->where($where_str);
	}
	
	abstract public function connect($db, $user, $pass, $host="localhost");
	abstract public function disconnect();
	abstract public function clean($param);
	/** @return returns an array with the results of a query */
	abstract public function select($query);
	
	public function select1($query){
		$rows = $this->select($query);
		return (count($rows)>0?$rows[0]:null);
	}
	
	/** @return returns an array of objects of type +class_name+ */
	public function selectAs($query,$class_name){
		$list = array();
		$rows = $this->select($query);
		foreach($rows as $r)
			$list[] = new $class_name($r);
		return $list;
	}
	/** excecutes a statement without retrieving the result */
	abstract public function query($query);
	/** @return returns a prepared statement */
	abstract public function prepare($query);
	/** bubds abd executes a prepared statement
		@return returns an array of results */
	abstract public function execute($stmt,$params=array(),$fmt=null);
	abstract public function last_insert_id();
	/** @return returns the id of the new row */
	abstract public function insert($query);
	
	/** @return returns an array of name=>type pairs */
	abstract public function columnsForTable($table);
}