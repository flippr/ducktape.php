<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

class DTGeoSQLiteDatabase extends DTSQLiteDatabase{
	function __construct($database=null,$user=null,$password=null,$host=null){
		parent::__construct($database,$user,$password,$host);
		
		$this->conn->createFunction('ST_Distance', 'DTGeoSQLiteDatabase::distance', 2);
		$this->conn->createFunction('ST_DWithin', 'DTGeoSQLiteDatabase::within', 3);
		$this->conn->createFunction('ST_GeomFromText', 'DTGeoSQLiteDatabase::geomFromText', 2);
	}
	
	/**
		@return returns a hash that can be used to retrieve a geoJSON-instantiated object internally
	*/
	public static function geomFromText($wkt,$srid){
		$decoder = new gisconverter\WKT();
		$json = $decoder->geomFromText($wkt)->toGeoJSON();
		return serialize(json_decode($json,true));
	}

	/**
		@return returns the distance (in degrees) between two point geometries
	*/
	public static function distance($geom1,$geom2) {
	    $geom_obj1 = unserialize($geom1);
	    $lng1 = $geom_obj1["coordinates"][0];
	    $lat1 = $geom_obj1["coordinates"][1];
	    $lat1rad = deg2rad($lat1);
	    
	    $geom_obj2 = unserialize($geom2);
	    $lng2 = $geom_obj2["coordinates"][0];
	    $lat2 = $geom_obj2["coordinates"][1];
	    $lat2rad = deg2rad($lat2);
	    
		$R = 3961; //radius of the earth
		$dLat = deg2rad($lat2-$lat1);
		$dLon = deg2rad($lng2-$lng1);
		$a = sin($dLat/2.0) * sin($dLat/2.0) + sin($dLon/2.0) * sin($dLon/2.0) * cos($lat1rad) * cos($lat2rad);
		$c = 2.0 * atan2(sqrt($a),sqrt(1-$a));
		return DTLocation::milesToDegrees($R * $c,$lat1);
	}
	
	public static function within($geom1,$geom2,$dst){
		return static::distance($geom1,$geom2) < $dst;
	}
}