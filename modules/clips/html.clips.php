<?php

function dt_anchor($url,$content){
	if(preg_match("/^http/",$url))//this is an absolute url
		return "<a href='{$url}' target='_blank'>{$content}</a>";
	return "<a href='".DTFile::baseURL($url)."'>{$content}</a>";
}