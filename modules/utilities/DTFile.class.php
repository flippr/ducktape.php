<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTFile{
	public static function upload($dst_dir,$dst_name='file',$form_name='file'){
		if(isset($_FILES) && isset($_FILES[$form_name]) && !empty($_FILES[$form_name]["tmp_name"])){
			$path_parts = pathinfo($_FILES[$form_name]['name']);
			$image_ext = $path_parts["extension"];
			$tmpfile = $_FILES[$form_name]['tmp_name'];
			$dst_dir = substr($dst_dir,-1)=="/"?$dst_dir:"/{$dst_dir}";
			$dstfile = "{$dst_dir}{$dst_name}.{$image_ext}";
			if(move_uploaded_file($tmpfile, $dstfile))
				chmod($dstfile,0755);
			return basename($dstfile);
		}
		return null;
	}
	
	public static function baseURL($suffix=''){
	  return sprintf(
	    "%s://%s%s",
	    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
	    $_SERVER['HTTP_HOST'],
	    $suffix
	  );
	}
}