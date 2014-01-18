<?php
/**
	This is the primary include file for the OSProvider library.
	The default directory for settings is 'ducktape/../local'.
	The idea behind this location is to keep the local settings
	outside of the source repository (without relying on .gitignore).
*/
if (version_compare(PHP_VERSION, '5.3') < 0) 
    die ('DuckTape requires PHP version 5.3 or higher.');
	
function dt_load_library($lib){
	require_once dirname(__FILE__)."/lib/{$lib}";
}

/** @param takes a list of module names */
function dt_load_module(){
	$args = func_get_args();
	foreach($args as $mod)
		require_once dirname(__FILE__)."/modules/{$mod}/module.inc.php";
}

dt_load_module("core");
