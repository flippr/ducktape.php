<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTQueryBuilder{
	public $db = null;
	protected $from_clause = null;
	protected $join_clause = "";
	protected $where_clause = "1=1";
	protected $limit_clause = "";
	protected $order_by = "";
	
	function __construct($db){
		$this->db = $db;
	}
	
	/**
		@return returns itself for chaining
	*/
	function where($where_str){
		$this->where_clause = $where_str;
		return $this;
	}
	
	/**
		@return returns itself for chaining
	*/
	public function from($from_str){
		$this->from_clause = $from_str;
		return $this;
	}
	
	public function limit($limit_count){
		$this->limit_clause = "LIMIT {$limit_count}";
		return $this;
	}
	
	/** @param table - this should be the (escaped) table name */
	public function join($table,$condition){
		$this->join_clause .= " JOIN {$table} ON ({$condition})";
		return $this;
	}
	
	public function orderBy($str){
		$this->order_by = "ORDER BY ".$str;
		return $this;
	}
	
	public function select($cols="*"){
		if(!isset($this->from_clause))
			throw new Exception("No table specified.");
		$stmt = "SELECT {$cols} FROM {$this->from_clause} {$this->join_clause} WHERE {$this->where_clause} {$this->order_by} {$this->limit_clause}";
		return $this->db->select($stmt);
	}
	
	/**
		@return returns a single, matching row or null
	*/
	public function select1($cols="*"){
		$stmt = "SELECT {$cols} FROM {$this->from_clause} {$this->join_clause} WHERE {$this->where_clause} {$this->order_by} LIMIT 1";
		$rows = $this->db->select($stmt);
		if(count($rows)>0){
			return $rows[0];
		}
		return null;
	}
	
	public function selectAs($className,$cols="*"){
		$cols = isset($cols)?$cols:"*";
		$stmt = "SELECT {$cols} FROM {$this->from_clause} {$this->join_clause} WHERE {$this->where_clause} {$this->order_by} {$this->limit_clause}";
		return $this->db->selectAs($stmt,$className);
	}
	
	public function update(array $properties){
		if(count($properties)>0){
			$set_str = implode(",",array_map(function($k,$v) {return "{$k}=".DTQueryBuilder::formatValue($v);},array_keys($properties),$properties));
			$stmt = "UPDATE {$this->from_clause} SET {$set_str} WHERE {$this->where_clause}";
			$this->db->query($stmt);
			return true;
		}
		return false;
	}
	
	public function insert($properties){
		if(count($properties)>0){
			$cols_str = implode(",",array_keys($properties));
			$vals_str = implode(",",array_map(function($v){return DTQueryBuilder::formatValue($v);},array_values($properties)));
			$stmt = "INSERT INTO {$this->from_clause} ({$cols_str}) VALUES ({$vals_str});";
			return  $this->db->insert($stmt);
		}
		return false;
	}
	
	public function delete(){
		try{
			$stmt = "DELETE FROM {$this->from_clause} WHERE {$this->where_clause}";
			$this->db->query($stmt);
			return true;
		}catch(Exception $e){}
		return false;
	}
	
	public static function formatValue($v){
		if(!isset($v)||$v==="NULL")
			return "NULL";
		else if(is_array($v)) //@todo for now we just serialize any second-level objects
			return "'".json_encode(DTResponse::objectAsRenderable($v))."'";
		else if(substr($v,0,11)=="\\DTSQLEXPR\\") //handle expressions as literals, as long as params are cleaned, this should never be possible from users because of the unescaped backslash
			return substr($v,11);
		return "'{$v}'"; // ALWAYS quote other values to avoid 'id=1 OR 1=1' attacks
	}
}