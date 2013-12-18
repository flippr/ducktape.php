<?php
/**
	DTBackedFileStore is an extension of the DTFileStore using SQLite as the SQLEngine.
	SQLite is intended to be a temporary solution until we implement our own native SQL Engine.
*/
abstract class DTBackedFileStore extends DTFileStore{
	protected $engine;
	
//===============
//! Connection
//===============
	public function connect($dsn){
		parent::connect($dsn); //connect before we have an engine to pull from file (see rollback)
		$this->engine = DTSQLiteDatabase::init(); //create a temp database to respond to queries
		$this->pushTables();
	}

	public function disconnect(){
		if(isset($this->engine)){
			parent::disconnect(); //must commit before we disconnect
			$this->engine->disconnect();
		}
	}
	
	public function columnsForTable($table){
		return $this->engine->columnsForTable();
	}
	
	public function allTables(){
		return $this->engine->allTables();
	}
	
//===============
//! Queries
//===============	
	/** Default behavior returns the parameter untouched */
	public function clean($param){
		return $this->engine->clean($param);
	}
	
	public function lastInsertID(){
		return $this->engine->lastInsertID();
	}
	
	public function query($sql){
		return $this->engine->query($sql);
	}
	
	public function select($sql){
		return $this->engine->select($sql);
	}
	
//===============
//! Transactions
//===============
	public function begin(){
		$this->engine->begin();
	}
	
	public function commit(){
		if(!isset($this->engine))
			throw new Exception("No SQL engine defined.");
		$this->engine->commit();
		$this->pullTables();
		parent::commit(); //save to file
	}
	
	public function rollback(){
		if(isset($this->engine))
			$this->engine->rollback();
		else //if we don't have an engine, this means pull from file
			parent::rollback();
	}
}