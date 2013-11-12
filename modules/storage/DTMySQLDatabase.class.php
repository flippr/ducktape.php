<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

/**
	An abstract interface using object-oriented MySQLi
*/

class DTMySQLDatabase extends DTDatabase{
	public function connect($db, $user, $pass, $host="localhost"){
		$this->conn = new mysqli($host,$user,$pass,$db);
		if (mysqli_connect_errno())
			DTLog::error("Connect failed: %s\n", mysqli_connect_error());
	}
	
	public function select($query){
		$result = $this->conn->query($query);
		$rows = $result->fetch_all();
		if(!$rows){
			$rows = array();
		}
		return $rows;
	}
	
	public function clean($param){
		return @mysqli_escape_string($param,$this->conn);
	}
	
	public function disconnect(){
		$this->conn->close();
	}
	
	public function query($query){
		$this->conn->query($query);
	}
	
	public function insert($query){
		$this->query($query);
		return $this->last_insert_id();
	}
	
	public function last_insert_id(){
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
		return null;
	}
}