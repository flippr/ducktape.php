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
	public static function error($msg){
		if(!isset(DTLog::$error_fp)){
			$error_log = dirname(__FILE__)."/../../".DTSettings::$config["logs"]["path"].DTSettings::$config["logs"]["error_log"];
			if(!file_exists($error_log)){
				touch($error_log);
				chmod($error_log,DTSettings::$config["logs"]["permissions"]);
			}
			DTLog::$error_fp = fopen($error_log,"a");
		}
		DTLog::write(DTLog::$error_fp,$msg);
	}
	
	/** currently an alias for info **/
	public static function warn($msg,$bt_offset=1){
		DTLog::info($msg,$bt_offset);
	}
	
	/** emit warnings/information */
	public static function info($msg,$bt_offset=1){
		if(!isset(DTLog::$info_fp)){
			$info_log = dirname(__FILE__)."/../../".DTSettings::$config["logs"]["path"].DTSettings::$config["logs"]["info_log"];
			if(!file_exists($info_log)){
				touch($info_log);
				chmod($info_log,DTSettings::$config["logs"]["permissions"]);
			}
			DTLog::$info_fp = fopen($info_log,"a");
		}
	
		DTLog::write(DTLog::$info_fp,$msg,$bt_offset);
	}
	
	/** only emits message if debug */
	public static function debug($msg,$full_backtrace=false){
		if(DTSettings::$config["logs"]["debug"]==true){
			if(!isset(DTLog::$debug_fp)){
				$debug_log = dirname(__FILE__)."/../../".DTSettings::$config["logs"]["path"].DTSettings::$config["logs"]["debug_log"];
				if(!file_exists($debug_log)){
					touch($debug_log);
					chmod($debug_log,DTSettings::$config["logs"]["permissions"]);
				}
				DTLog::$debug_fp = fopen($debug_log,"a");
			}
			DTLog::write(DTLog::$debug_fp,$msg);
			if($full_backtrace)
				debug_print_backtrace();
		}
	}
	
	/** private method for writing to a log file */
	protected static function write($fp,$msg,$bt_offset=0){
		$bt = debug_backtrace();
		$file = basename($bt[1+$bt_offset]["file"]);
		$line = $bt[1+$bt_offset]["line"];
		$timestamp = date("D M d H:i:s Y");
		$msg = is_string($msg)?$msg:json_encode($msg);
		if(flock($fp,LOCK_EX)===false
		|| fwrite($fp,"[{$timestamp}] {$file}:{$line}:{$msg}\n")===false)
			error_log("DTLog:write:Could not write to log!");
		else
			flock($fp,LOCK_UN);
	}
}





