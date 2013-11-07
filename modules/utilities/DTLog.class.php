<?php
require_once(dirname(__FILE__)."/../../ducktape.inc.php");
/**
	DTLog
	Controls log functionality. DTLog bypasses standard error_log(), because ini_set('error_log',...) is inconsistent.
*/

class DTLog{
	public static $error_fp = null;
	public static $info_fp = null;
	public static $debug_fp = null;
	
	/** emit major failure message */
	public function error($msg){
		DTLog::write(DTLog::$error_fp,$msg);
	}
	
	/** currently an alias for info **/
	public function warn($msg,$bt_offset=0){
		DTLog::info($msg,$bt_offset);
	}
	
	/** emit warnings/information */
	public function info($msg,$bt_offset=0){
		DTLog::write(DTLog::$info_fp,$msg,$bt_offset);
	}
	
	/** only emits message if debug */
	public function debug($msg,$full_backtrace=false){
		if(DTSettings::$config["logs"]["debug"]){
			DTLog::write(DTLog::$debug_fp,$msg);
			if($full_backtrace)
				debug_print_backtrace();
		}
	}
	
	/** private method for writing to a log file */
	protected function write($fp,$msg,$bt_offset=0){
		$bt = debug_backtrace();
		$file = basename($bt[1+$bt_offset]["file"]);
		$line = $bt[1+$bt_offset]["line"];
		$timestamp = date("D M d H:i:s Y");
		if(flock(DTLog::$debug_fp,LOCK_EX)===false
		|| fwrite(DTLog::$debug_fp,"[{$timestamp}] {$file}:{$line}:{$msg}\n")===false)
			error_log("DTLog:write:Could not write to log!");
		else
			flock(DTLog::$debug_fp,LOCK_UN);
	}
}

DTLog::$error_fp = fopen(DTSettings::$config["logs"]["path"].DTSettings::$config["logs"]["error_log"],"a");
DTLog::$info_fp = fopen(DTSettings::$config["logs"]["path"].DTSettings::$config["logs"]["info_log"],"a");
DTLog::$debug_fp = fopen(DTSettings::$config["logs"]["path"].DTSettings::$config["logs"]["debug_log"],"a");

chmod(DTSettings::$config["logs"]["path"].DTSettings::$config["logs"]["error_log"],DTSettings::$config["logs"]["permissions"]);
chmod(DTSettings::$config["logs"]["path"].DTSettings::$config["logs"]["info_log"],DTSettings::$config["logs"]["permissions"]);
chmod(DTSettings::$config["logs"]["path"].DTSettings::$config["logs"]["debug_log"],DTSettings::$config["logs"]["permissions"]);