<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTQueryBuilder{
	public $db = null;
	protected $from_clause = null;
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
	
	public function select($cols="*"){
		$stmt = "SELECT {$cols} FROM {$this->from_clause} WHERE {$this->where_clause} {$this->order_by}";
		return $this->db->select($stmt);
	}
	
	/**
		@return returns a single, matching row or null
	*/
	public function select1($cols="*"){
		$stmt = "SELECT {$cols} FROM {$this->from_clause} WHERE {$this->where_clause} {$this->order_by} LIMIT 1";
		$rows = $this->db->select($stmt);
		if(count($rows)>0){
			return $rows[0];
		}
		return null;
	}
	
	public function selectAs($className,$cols="*"){
		$stmt = "SELECT {$cols} FROM {$this->from_clause} WHERE {$this->where_clause} {$this->order_by}";
		return $this->db->selectAs($stmt,$className);
	}
	
	public function update(array $properties){
		$set_str = implode(",",array_map(function($k,$v){return ($v=="NULL"||!isset($v))?"{$k}=NULL":"{$k}='{$v}'";},array_keys($properties),$properties));
		$stmt = "UPDATE {$this->from_clause} SET {$set_str} WHERE {$this->where_clause}";
		return $this->db->query($stmt);
	}
	
	public function insert($properties){
		if(count($properties)>0){
			$cols_str = implode(",",array_keys($properties));
			$vals_str = implode(",",array_map(function($v){return ($v=="NULL"||!isset($v))?"NULL":"'{$v}'";},array_values($properties)));
			$stmt = "INSERT INTO {$this->from_clause} ({$cols_str}) VALUES ({$vals_str});";
			return  $this->db->insert($stmt);
		}
		return false;
	}
}