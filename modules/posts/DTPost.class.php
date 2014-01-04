<?php
class DTPost extends DTModel{
	protected static $storage_table = "posts";
	
	public $name;
	public $publish_at;
	public $publish_at_date;
	public $short;
	
	public function publishAtDate(){
		return date("m/d/Y",strtotime($this["publish_at"]));
	}
}