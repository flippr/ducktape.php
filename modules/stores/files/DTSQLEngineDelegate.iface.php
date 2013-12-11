<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";

interface DTSQLEngineDelegate {
	public function createTable($table);
	public function dropTable($table);
	/** @param row - the data for the row to insert */
	public function insertRow($table,$row);
	public function removeRow($table,$idx);
	public function updateRow($table,$idx,$row);
	/** @return returns a row x col representation of the table data */
	public function rowsForTable($table);
}