<?php
ini_set('display_errors', '1');
dt_load_module("stores_mysql","stores_pgsql","stores_sqlite","stores_file"); //we need to load these to handle production database connections... 

class DTTestCase extends PHPUnit_Framework_TestCase{
	protected $db=null;
	protected $production_store = "default"; /** used to verify production schema */

	public function setup(){
		$this->db = DTSettings::$default_database = DTSQLiteDatabase::init($this->initSQL());
	}

	/* @return returns +sql+ after adding initialization steps  */
	protected function initSQL($sql=""){
		return $sql;
	}
	
	/** test defined for all cases to verify that production (minimally) matches test schema */
	public function testProductionSchema(){
		if(isset($this->db))
			try {
				$productiondb = DTSettings::fromStorage($this->production_store);

				$test_tables = $this->db->allTables();
				$prod_tables = $productiondb->allTables();
				foreach($test_tables as $t){
					if(in_array($t, $prod_tables)){ //make sure this table exists in production
						$test_cols = $this->db->columnsForTable($t);
						$prod_cols = $productiondb->columnsForTable($t);
						foreach($test_cols as $c)
							if(!in_array($c,$prod_cols)) //make sure all columns exist in production
								echo "WARNING: '".get_class($this)."' is not compatible with production schema (table '{$t}' missing column '{$c}')\n";
					}else
						echo "WARNING: '".get_class($this)."' is not compatible with production schema (missing table '{$t}')\n";
				}
			} catch(Exception $e){} // database is not set, so there's nothing to verify
	}
	
//=============
// !Deprecated
//=============
	protected function initDB($init_sql){
		return DTSettings::$default_database = DTSQLiteDatabase::init($init_sql); //make sure we use this as the default database
	}
}