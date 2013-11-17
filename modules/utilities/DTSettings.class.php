<?php
require_once(dirname(__FILE__)."/../../ducktape.inc.php");

/**
	DTSettings.php
	Loads the local environment settings by parsing specified yaml files
*/

class DTSettings{
	public static $config = null;
	public static $storage = null;
	protected static $oauth = null;
	public static $default_database = null;

	public static function config(){
		global $dt_local_dir;
		if(!isset(static::$config))
			static::$config = yaml_parse_file("{$dt_local_dir}/config.yml");
		return static::$config;
	}
	
	public static function storage(){
		global $dt_local_dir;
		if(!isset(static::$storage))
			static::$storage = yaml_parse_file("{$dt_local_dir}/storage.yml");
		return static::$storage;
	}
	
	public static function oauth(){
		global $dt_local_dir;
		if(!isset(static::$oauth))
			static::$oauth = yaml_parse_file("{$dt_local_dir}/oauth.yml");
		return static::$oauth;
	}
}

DTSettings::$config = yaml_parse_file("{$dt_local_dir}/config.yml");
DTSettings::$storage = yaml_parse_file("{$dt_local_dir}/storage.yml");