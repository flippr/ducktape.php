<?php
require_once(dirname(__FILE__)."/../../ducktape.inc.php");

/**
	DTSettings.php
	Loads the local environment settings by parsing specified yaml files
*/

class DTSettings{
	public static $config = null;
	public static $storage = null;
	public static $oauth = null;
	public static $default_database = null;
}

DTSettings::$config = yaml_parse_file("{$local_dir}/config.yml");
DTSettings::$storage = yaml_parse_file("{$local_dir}/storage.yml");
DTSettings::$oauth = yaml_parse_file("{$local_dir}/oauth.yml");