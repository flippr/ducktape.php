<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";

abstract class DTDatabase extends DTStore {
	public $ilike = "LIKE"; //keyword for case-insensitive search
	public $conn = null;
	
	/** @return returns a prepared statement */
	abstract public function prepare($query);
	/** executes a prepared statement
		@return returns an array of results */
	abstract public function execute($stmt,$params=array(),$fmt=null);
}