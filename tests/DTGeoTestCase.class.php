<?php
require_once dirname(__FILE__)."/../ducktape.inc.php";

class DTGeoTestCase extends DTTestCase{
	public function initDB($init_sql){
		/*if(file_exists(DT_TEST_DB))
			unlink(DT_TEST_DB);
		$db = new DTGeoSQLiteDatabase(DT_TEST_DB);
		$db->query($init_sql);*/
		return DTSettings::$default_database = DTGeoSQLiteDatabase::init($init_sql);
	}
}