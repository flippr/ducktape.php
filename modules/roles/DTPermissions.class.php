<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTPermissions extends DTModel{
	protected static $storage_table = "permissions";
	protected $role_id;
	protected $permission;
	
	/** @return return true if role has permission, otherwise false */
	public static function check($role_id, $permission,$db=null){
		$db = isset($db)?$db:DTSettings::$default_database;
		return count($db->where("role_id='{$role_id}' AND permission='{$permission}'"))>0;
	}
}