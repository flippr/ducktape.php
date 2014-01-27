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
	
	protected static $_storage_connections = array(); //internal storage for singleton storage connections

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
	
	/** retrieves/creates a singleton connection from storage settings 
		@return returns a valid connection or throws an exception
	*/
	public static function fromStorage($store){
		$storage = static::storage();
		if(!isset(static::$_storage_connections[$store])){
			if(!isset($storage[$store]))
				throw new Exception("connection '{$store}' not found in storage!");
			$connector = $storage[$store]["connector"];
			$dsn = $storage[$store]["dsn"];
			$readonly = isset($storage[$store]["readonly"])?$storage[$store]["readonly"]:false;
			static::$_storage_connections[$store] = new $connector($dsn,$readonly);
		}
		return static::$_storage_connections[$store];
	}
	
	public static function baseURL($suffix=''){
	  $base = isset(DTSettings::$config["base_url"])?DTSettings::$config["base_url"]:$_SERVER['HTTP_HOST'];
	  return sprintf(
	    "%s://%s/%s",
	    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
	    $base,
	    $suffix
	  );
	}
}

DTSettings::config();
DTSettings::storage();