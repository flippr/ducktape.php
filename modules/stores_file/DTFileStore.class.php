<?php
abstract class DTFileStore extends DTStore {
	public $file_extension = "tab"; //subclasses can override this for custom file extensions
	protected $last_insert_id = 0;
	protected $_filenames;
	
//===============
//! Connection
//===============
	public function connect($dsn){
		$this->dsn = $dsn;
		$this->rollback();
	}
	
	public function disconnect(){
		$this->commit(); //make sure we write any pending changes to file
	}

	/** builds a store from the given string representation */
	public static function initWithString($init_str){
		$store = static::init();
		file_put_contents($store->dsn, $init_str);
		$store->rollback(); //recover from what we just wrote
	}
	
	/** parses a string in storage format into an array of key-value rows
		@return returns the object or null on failure
	*/
	abstract public static function unserialize($str);
	/** produce a string in storage format from key-value rows */
	abstract public static function serialize($obj);
	
	public function columnsForTable($table){
		return array_keys($this->tables[$table][0]);
	}
	
	function rowsForTable($table){
		return $this->tables[$table]; //already in row x col format
	}
	
	public function allTables(){
		return array_keys($this->tables);
	}
	
//===============
//! Queries
//===============	
	/** Default behavior returns the parameter untouched */
	public function clean($param){
		return $param;
	}
	
	public function lastInsertID(){
		return $this->last_insert_id;
	}
	
	public function query($sql){
		$executor = new DTSQLEngine($this);
		$executor->runSQL($sql);
	}
	
	public function select($sql){
		$executor = new DTSQLEngine($this);
		return $executor->runSQL($sql);
	}
	
//===============
//! Transactions
//===============
	public function begin(){
		$this->commit(); //implicitly, save the current state
	}
	
	public function commit(){
		$dsn = $this->dsn;
		foreach($this->tables as $t=>$rows){
			$f = isset($this->_filenames[$t])?$this->_filenames[$t]:$t.".".$this->file_extension;
			$obj = array();
			foreach($rows as $r){
				$id = $r["id"];
				unset($r["id"]);
				$obj[$id] = $r;
			}
			file_put_contents($f, $this->serialize($obj));
		}
	}
	
	public function rollback(){
		$dsn = $this->dsn;
		$files = array();
		if(is_file($dsn))
			$files[] = $dsn;
		else if(is_dir($dsn)){
			$dir = opendir($dsn);
			while (false !== ($entry = readdir($dir)))
				if(substr($entry,0,1)!="." && !is_dir($entry))
			        $files[] = "{$dsn}/".$entry;
		}
		foreach($files as $f){
			$info = pathinfo($f);
			$table = $info["filename"];
			$this->_filenames[$table]=$f;
			$obj = $this->unserialize(file_get_contents("{$f}"));
			if(isset($obj))
				$this->tables[$table] = array_map(function($k,$v){
						return array_merge(array("id"=>$k),$v);
					}, array_keys($obj),array_values($obj));
		}
	}
	
//======================
//! SQL Engine Delegate (under construction)
//======================
/*	public function createTable($table){
		$this->tables[$table] = array(); //add empty table
	}
	
	function dropTable($table){
		unset($this->tables[$table]); //unset table
	}
	
	function insertRow($table,$row){
		$this->tables[$table][] = $row; //append row to table
		$this->last_insert_id = $row["id"];
		return $row["id"];
	}
	
	function removeRow($table,$idx){
		unset($this->tables[$table][$idx]); //unset row
		// the fact that the array does not reindex keeps us
		// from invalidating idx and using a separate row identifier
	}
	
	function updateRow($table,$idx,$row){
		$this->tables[$table][$idx] = //merge new data into row
			array_merge($this->tables[$table][$idx],$row);
	}
*/
}