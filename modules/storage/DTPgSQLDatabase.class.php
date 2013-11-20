<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTPgSQLDatabase extends DTDatabase{
	public $ilike = "ILIKE";

	public function connect($db, $user, $pass, $host="localhost"){
		$this->conn = @pg_connect("host={$host} dbname={$db} user={$user} password={$pass}");
		if(!$this->conn)
			DTLog::error("DTPgSQL:connect_error:host={$host} dbname={$db} user={$user}");
		@pg_set_client_encoding($this->conn, "UNICODE");
	}
	
	public function select($query){
		$result = @pg_query($this->conn,$query);
		if(!$result)
			DTLog::error("Query failed:".pg_last_error().":{$query}");
		$rows = @pg_fetch_all($result);
		if(!$rows){
			$rows = array();
		}
		return $rows;
	}
	
	public function query($query){
		if(@pg_query($this->conn,$query)===false)
			DTLog::error("Failed query: ".pg_last_error());
	}
	
	public function clean($param){
		return @pg_escape_string($this->conn,$param);
	}
	
	public function disconnect(){
		@pg_close($this->conn);
	}
	
	public function last_insert_id(){
		$query = "SELECT LASTVAL() as id";
		$rows = $this->select($query); //get the id
		if(count($rows)==0) return 0;
		return $rows[0]["id"];
	}
	
	public function insert($query){
		$this->query($query);
		return $this->last_insert_id();
	}
	
	public function prepare($query){
		$name = "HS_prepared_".rand();
		@pg_prepare($this->conn, $name, $query);
		return $name;
	}
	
	public function execute($stmt,$params=array(),$fmt=null){
		if(@pg_execute($this->conn,$stmt,$params)===false)
			throw new Exception("Failed to insert: ".pg_last_error());
	}
	
	public function execute_insert($name,$params){
		$this->execute($name,$params);
		return $this->last_insert_id();
	}
	
	public function columnsForTable($table){
		return array_reduce( $this->select("select column_name from information_schema.columns where table_name='{$table}'"),
			function($rV,$cV) { $rV[]=$cV['column_name']; return $rV; },array());
	}
}