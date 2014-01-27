<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

abstract class DTStore{
	public $tables = array(); //internal storage for loaded data
	public $dsn=null;
	public $readonly;
	
	/** @param dsnOrTables - either a Data Source Name (DSN) or an array of tables in storage format */
	function __construct($dsnOrTables=array(),$readonly=false){
		$this->readonly = $readonly;
		if(is_array($dsnOrTables)) // we were given the represented store
			$this->tables = $dsnOrTables;
		else // create the store from the specified DSN
			$this->connect($dsnOrTables);
	}
	
	public static function init($init_sql=""){
		$dsn = "file://".tempnam(sys_get_temp_dir(),"dt.store.");
		$store = new static($dsn);
		$store->connect($dsn);
		$store->query($init_sql);
		return $store;
	}
	
	public function shareTables(&$tables){
		$this->tables = $tables;
	}
	
	/** creates a new storage by duplicating +store+ */
	public static function copy(DTStore $store){
		return new static($store->tables);
	}
	
	/** creates a new storage sharing the internal tables of +store+ */
	public static function share(DTStore $store){
		$new = new static();
		$new->shareTables($store->tables);
		return $new;
	}
	
//===============
//! Connection
//===============
	/** connects to a data store via data source name */
	abstract public function connect($dsn);
	/** disconnects from data store, saving any ongoing transactions */
	abstract public function disconnect();
	/** initializes a temporary database with optional SQL to populate
		@return returns a DTStore object */
		
	/** pushes internal storage to permanent storage
		@warn will not modify existing tables
	 */
	public function pushTables(){
		$permanent_tables = $this->allTables();
		foreach($this->tables as $table=>$t){
			if(!in_array($table,$permanent_tables)){ //make sure we skip existing tables
				$insert_vals = array(); $all_cols = array(); $insert_cols = array();
				foreach($t as $row){
					$vals = array(); $cols = array();
					foreach($row as $c=>$v){
						$cols[] = $c;
						if(!is_array($v)) //do our best to clean what's going in
							$v = $this->clean($v);
						$vals[] = DTQueryBuilder::formatValue($v); //collect values
						if(!in_array($c,$all_cols))
							$all_cols[] = $c; //merge into all_cols (we could store type info)
					}
					$insert_vals[] = implode(",",$vals);
					$insert_cols[] = implode(",",$cols);
				}
				//  create the table (for now, all columns are text)
				$create_cols = implode(",",array_map(function($c){ return "{$c} text"; },$all_cols));
				$stmt = "CREATE TABLE \"{$table}\" ({$create_cols});";
				$this->query($stmt);
				//  insert all rows (can't use prepared, cause we don't know how many cols)
				foreach($t as $i=>$row){
					$stmt = "INSERT INTO \"{$table}\" ({$insert_cols[$i]}) VALUES ({$insert_vals[$i]});";
					$this->insert($stmt);
				}
			}
		}
	}
	
	/** pulls all tables to internal storage
		@return returns false if internal storage is already set
	 */
	public function pullTables(){
		if(isset($this->tables))
			return false;
		//for each table in the database
		foreach($this->allTables() as $table){
			$stmt = "SELECT * FROM {$table}";
			$this->tables = $this->select($stmt);
		}
	}
	
//===============
//! Queries
//===============
	/** prepares a parameter for safe storage. */
	abstract public function clean($param);
	/** executes a given query without expecting a result */
	abstract public function query($stmt);
	/** @return returns an array with the results of a query */
	abstract public function select($stmt);
	/** @return returns the id of the last row inserted */
	abstract public function lastInsertID();
	/** @return returns an array column names */
	abstract public function columnsForTable($table);
	/** @return returns an array of table names */
	abstract public function allTables();
	
	
	/** @return returns a single object matching query */
	public function select1($stmt){
		$rows = $this->select($stmt);
		return (count($rows)>0?$rows[0]:null);
	}
	/** @return returns an array of objects of type +class_name+ */
	public function selectAs($stmt,$class_name){
		$list = array();
		$rows = $this->select($stmt);
		foreach($rows as $r){
			$obj = $list[] = new $class_name($r);
			$obj->setStore($this); //keep track of where we came from
		}
		return $list;
	}
	/** pairs the first 2 columns (key,val) in an assoc array
	@returns the key-value paired query results */
	public function selectKV($stmt){
		$list = array();
		$rows = $this->select($stmt);
		$cols = array_keys($rows[0]);
		$key_col = $cols[0];
		$val_col = $cols[1];
		foreach($rows as $r){ //pair keys and values
			$list[$r[$key_col]] = $r[$val_col];
		}
		return $list;
	}
	/** @return returns the id of the new row */
	public function insert($stmt){
		$this->query($stmt);
		return $this->lastInsertID();
	}
	
//================
//! Query Builder
//================
	/** @return returns a DTQueryBuilder with the appropriate where clause */
	public function where($where_str){
		$qb = new DTQueryBuilder($this);
		return $qb->where($where_str);
	}
	
	public function qb(){
		return new DTQueryBuilder($this);
	}
	
//===============
//! Transactions
//===============
	/** initiates an atomic transaction */
	abstract public function begin();
	/** saves the changes of the current transaction */
	abstract public function commit();
	/** cancels current transaction and reverts to pre-transaction state */
	abstract public function rollback();
	
//===============
//! Date Methods
//===============
	/** @return returns a storage-formatted string representing the current UTC timestamp */
	public static function now(){
		return static::gmdate();
	}
	
	/** @return returns a storage-formatted string representing the given timestamp */
	public static function date($timestamp=null){
		$timestamp = isset($timestamp)?$timestamp:time();
		return date("Y-m-d H:i:s",$timestamp);
	}
	
	/** @return returns a storage-formatted string representing the given (local) timestamp convered to UTC */
	public static function gmdate($timestamp=null){
		$timestamp = isset($timestamp)?$timestamp:time();
		return gmdate("Y-m-d H:i:s",$timestamp);
	}
	
	/** @return returns the conversion of a local time to gmtime */
	public static function gmtime($timestamp=null){
		$timestamp = isset($timestamp)?$timestamp:time();
		//return (int)gmdate('U',$timestamp);
		return $timestamp - (int)substr(date('O'),0,3)*60*60;
	}
	
	function __destruct() {
      $this->disconnect();
   }
}