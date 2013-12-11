<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";

/**
	An abstract interface using object-oriented MySQLi
*/

class DTMySQLDatabase extends DTDatabase{
	public function connect($dsn){
		$parts = parse_url($dsn);
		$user = $parts["user"];
		$pass = $parts["pass"];
		$host = $parts["host"];
		$db = substr($parts["path"],1); //omit starting slash
		$this->conn = new mysqli($host,$user,$pass,$db);
		if (mysqli_connect_errno()){
			unset($this->conn);
			DTLog::error("Connect failed: ".mysqli_connect_error());
		}
	}
	
	public function select($query){
		$result = $this->conn->query($query);
		if(!$result)
			DTLog::error("Query failed: ".$this->conn->error."\n".$query);
		$rows = $result->fetch_all(MYSQLI_ASSOC);
		if(!$rows){
			$rows = array();
		}
		return $rows;
	}
	
	public function clean($param){
		return @mysqli_escape_string($this->conn,$param);
	}
	
	public function disconnect(){
		if(isset($this->conn))
			$this->conn->close();
	}
	
	public function query($query){
		$this->conn->query($query);
	}
	
	public function insert($query){
		$this->query($query);
		return $this->last_insert_id();
	}
	
	public function lastInsertID(){
		$query = "SELECT LAST_INSERT_ID() as id";
		$rows = $this->query($query);
		return $rows[0]["id"];
	}
	
	public function prepare($query){
		return $this->conn->prepare($query);
	}
	
	public function execute($stmt,$params=array(),$fmt=null){
		$stmt->bind_param($fmt,$params);
		$object = array();
		$stmt->execute();
		$res = $stmt->get_result();
		for ($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--){
			$res->data_seek($row_no);
			$object[] = $res->fetch_assoc();
		}
		$res->close();
	}
	
	public function columnsForTable($table){
		return array_reduce( $this->select("SHOW columns FROM {$table}"),
			function($rV,$cV) { $rV[]=$cV['Field']; return $rV; },array());
		return null;
	}
	
	public function allTables(){
		return $this->select("show tables");
	}
	
	public function begin(){
	// @todo implement this method
	}
	
	public function commit(){
	// @todo implement this method
	}
	
	public function rollback(){
	// @todo implement this method
	}
}