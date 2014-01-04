<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTPermissions extends DTModel{
	protected static $storage_table = "permissions";
	protected $role_id;
	public $permission;
	public $context;
	
	/** @return return true if role has permission, otherwise false */
	public static function check($role_id, $permission,$db=null){
		$db = isset($db)?$db:DTSettings::$default_database;
		return count(static::select($db->where("role_id='{$role_id}' AND permission='{$permission}'")))>0;
	}
	
	public static function forContext($role_id,$context,$db=null){
		$db = isset($db)?$db:DTSettings::$default_database;
		$list = (static::select($db->where("role_id='{$role_id}' AND context LIKE '{$context}'")));
		return array_map(function($r){return $r["permission"];},$list);
	}
}