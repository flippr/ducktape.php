<?php
require_once(dirname(__FILE__)."/../../ducktape.inc.php");

/**
	DTSettings.php
	Loads the local environment settings by parsing specified yaml files
	The default directory for settings is ../local/. The idea behind this location is to keep the local settings outside of the source repository (without relying on .gitignore).
*/

$local_dir = "../local";

class DTSettings{
	public static $config = null;
	public static $storage = null;
	public static $default_database = null;
}

DTSettings::$config = yaml_parse_file("{$local_dir}/config.yml");
DTSettings::$storage = yaml_parse_file("{$local_dir}/storage.yml");