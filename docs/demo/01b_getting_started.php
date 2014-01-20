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
 */
require_once dirname(__FILE__)."/../ducktape.php/ducktape.inc.php";
dt_load_module("tests","stores","stores_sqlite");

class Person extends DTModel{
	public function message(){
		return "Hello, World!";
	}
}

$p = new Person();
DTLog::debug($p["message"]);
//DTLog::debug("Hello, World!");
