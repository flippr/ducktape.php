<?php
/** factory for posts using a table lookup */
class DTPostBuilder{
	public function classFor($base,$role,$db=null){
		$db = isset($db)?$db:DTSettings::$default_database;
		$row = $db->select1("SELECT class FROM role_builders WHERE base='{$base}' AND role_id='{$role_id}'");
		return isset($row)?$row["class"]:$base;
	}
}