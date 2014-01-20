<?php
/**
 * @file 01_getting_started.php
 * @description Put this site in your 'site' directory and run it through
 * either visiting it in a browser or PHP-CLI to see it log to your configured
 * error.log.
 * This specific version also loads the tests, stores and SQLite modules from
 * ducktape.php
 * Here we are also adding a Model into the example to output to debug instead
 * of static text
 * And finally, we will add a basic SQLite Database to show Database values
 * being used from the Model
 */
require_once dirname(__FILE__)."/../ducktape.php/ducktape.inc.php";
dt_load_module("tests","stores","stores_sqlite");

$init_sql = <<<END
CREATE TABLE people (
	id int,
	name text,
	occupation text,
	message text
);

INSERT INTO people VALUES (1,"Blake","Software Developer","Hello, World!");
INSERT INTO people VALUES (2,"Robin","Beautiful Wife","Get off the computer!");
END;

$db = DTSQLiteDatabase::init($init_sql);

class Person extends DTModel{
	protected static $storage_table = "people";
//	public function message(){
//		return "Hello, World!";
//	}
}

$p = new Person($db->where("name='Blake'"));
DTLog::debug($p["message"]);
/** [NOTE]
$p['message'] now represents pulling data from the column 'message' after
the new Person() is initialized with a database call.
 **/
//DTLog::debug("Hello, World!");
