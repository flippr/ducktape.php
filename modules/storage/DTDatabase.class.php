<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

abstract class DTDatabase {
	public $conn = null;

	function __construct($database=null,$user=null,$password=null,$host=null){
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
}