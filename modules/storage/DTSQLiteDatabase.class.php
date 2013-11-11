<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTSQLiteDatabase extends DTDatabase{
	public function connect($database, $user, $pass, $host="localhost"){
		$this->conn = new SQLite3($database);
	}
	
	public function select($query){
		$object = array();
		if(($result=$this->conn->query($query))===false){
			OSLog::error($this->conn->lastErrorMsg());
			return $object;
		}
		while($result!==false && $row=$result->fetchArray()){
			$object[] = $row;
		}
		return $object;
	}
	
	public function query($query){
		$result = $this->conn->exec($query);
	}
	
	public function clean($param){
		return $this->conn->escapeString($param);
	}
	
	public function disconnect(){
		$this->conn->close();
	}
	
	public function last_insert_id(){
		return $this->conn->lastInsertRowID();
	}
	
	public function insert($query){
		$this->query($query);
		return $this->last_insert_id();
	}
	
	public function prepare($query){
		$name = "DT_prepared_".rand();
		@pg_prepare($this->conn, $name, $query);
		return $name;
	}
	
	public function execute($stmt,$params=array(),$fmt=null){
		@pg_execute($this->conn,$name,$params);
	}
	
	public function execute_insert($name,$params){
		$this->execute($name,$params);
		return $this->last_insert_id();
	}
}