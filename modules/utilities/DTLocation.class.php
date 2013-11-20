<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTLocation{
	public static function locationForString($str){
		$location = null;
		$url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=true";
		$geocoded = json_decode(DTHTTPRequest::makeGETRequest($url,array("address"=>$str)));
		if(count($geocoded->results)>0){
			$location = array();
			$location["lat"] = $geocoded->results[0]->geometry->location->lat;
			$location["lng"] = $geocoded->results[0]->geometry->location->lng;
		}
		return $location;
	}
	
	/**
		@return returns the (approximate) degrees of longitude represented by a mile
	*/
	public static function milesToDegrees($miles,$latitude){
		return $miles/(69.046767 * cos($latitude * pi() / 180.0));
	}
}