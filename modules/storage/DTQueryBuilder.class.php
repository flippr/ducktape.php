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
}