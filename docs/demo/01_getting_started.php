<?php
/**
 * @file 01_getting_started.php
 * @description Put this site in your 'site' directory and run it through
 * either visiting it in a browser or PHP-CLI to see it log to your configured
 * error.log.
 */
require_once dirname(__FILE__)."/../ducktape.php/ducktape.inc.php";

DTLog::debug("Hello, World!");
