<?php
class DTRoleBuilder{
	/**
		@param base - the base class used for lookup in table and default class
	*/
	public static function classFor($base,$role_id,$db=null){
		$db = isset($db)?$db:DTSettings::$default_database;
		$row = $db->select1("SELECT class FROM role_builders WHERE base='{$base}' AND role_id='{$role_id}'");
		return isset($row)?$row["class"]:$base;
	}
}
?>