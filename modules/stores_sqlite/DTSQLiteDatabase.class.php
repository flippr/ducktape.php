<?php
class DTSQLiteDatabase extends DTDatabase{
	public function connect($dsn){
		$parts = parse_url($dsn);
		$database = $parts["path"];
		$this->conn = new SQLite3($database);
	}
	
	public function select($query){
		$object = array();
		if(($result=$this->conn->query($query))===false){
			DTLog::error($this->conn->lastErrorMsg()."\n".$query);
			return $object;
		}
		while($result!==false && $row=$result->fetchArray(SQLITE3_ASSOC)){
			$object[] = $row;
		}
		return $object;
	}
	
	public function query($query){
		try{
			$this->conn->exec($query);
		}catch(Exception $e){
			DTLog::error("SQLite error! ".$this->conn->lastErrorMsg()."\n".$query);
		}
	}
	
	public function clean($param){
		return $this->conn->escapeString($param);
	}
	
	public function disconnect(){
		$this->conn->close();
	}
	
	public function lastInsertID(){
		return $this->conn->lastInsertRowID();
	}
	
	public function insert($query){
		$this->query($query);
		return $this->lastInsertID();
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
		return $this->lastInsertID();
	}
	
	public function columnsForTable($table){
		return array_reduce($this->select("PRAGMA table_info(`{$table}`)"),
			function($rV,$cV) { $rV[]=$cV['name']; return $rV; },array());
	}
	
	public function allTables(){
		return array_reduce($this->select("SELECT name FROM sqlite_master WHERE type='table';"),
			function($row,$item) { if($item['name']!="sqlite_sequence") $row[] = $item['name']; return $row; },array());
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