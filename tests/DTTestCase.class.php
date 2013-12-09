<?php
ini_set('display_errors', '1');
require_once dirname(__FILE__)."/../ducktape.inc.php";

class DTTestCase extends PHPUnit_Framework_TestCase{
	protected function initDB($init_sql){
		/*$test_db = DTSettings::fromStorage("test");
		if(file_exists($test_db))
			unlink($test_db);
		$db = new DTSQLiteDatabase("test");
		$db->query($init_sql);
		*/
		return DTSettings::$default_database = DTSQLiteDatabase::init($init_sql); //make sure we use this as the default database
	}
}