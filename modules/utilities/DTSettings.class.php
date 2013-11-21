<?php
require_once(dirname(__FILE__)."/../../ducktape.inc.php");

/**
	DTSettings.php
	Loads the local environment settings by parsing specified yaml files
*/

class DTSettings{
	public static $config = null;
	public static $storage = null;
	protected static $api = null;
	public static $default_database = null;

	public static function config(){
		$yaml = dirname(__FILE__)."/../../../local/config.yml";
		if(!isset(static::$config) && file_exists($yaml))
			static::$config = yaml_parse_file($yaml);
		return static::$config;
	}
	
	public static function storage(){
		$yaml = dirname(__FILE__)."/../../../local/storage.yml";
		if(!isset(static::$storage) && file_exists($yaml))
			static::$storage = yaml_parse_file($yaml);
		return static::$storage;
	}
	
	public static function api(){
		$yaml = dirname(__FILE__)."/../../../local/api.yml";
		if(!isset(static::$api) && file_exists($yaml))
			static::$api = yaml_parse_file($yaml);
		return static::$api;
	}
}

DTSettings::config();
DTSettings::storage();