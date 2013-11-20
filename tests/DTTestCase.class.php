<?php
ini_set('display_errors', '1');
require_once dirname(__FILE__)."/../ducktape.inc.php";

define("DT_TEST_DB", "/tmp/dt.test.sqlite");

class DTTestCase extends PHPUnit_Framework_TestCase{
	protected function initDB($init_sql){
		if(file_exists(DT_TEST_DB))
			unlink(DT_TEST_DB);
		$db = new DTSQLiteDatabase(DT_TEST_DB);
		$db->query($init_sql);
		return $db;
	}
}