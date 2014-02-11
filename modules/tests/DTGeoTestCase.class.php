<?php
class DTGeoTestCase extends DTTestCase{
	public function initDB($init_sql){
		return DTSettings::$default_database = DTGeoSQLiteDatabase::init($init_sql);
	}
}