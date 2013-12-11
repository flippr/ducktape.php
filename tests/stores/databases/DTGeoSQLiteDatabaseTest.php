<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";

class DTGeoSQLiteDatabaseTest extends DTGeoTestCase{
	protected $db = null;
	
	public function setUp(){
		$init_sql = <<<END
		CREATE TABLE locations(
			id integer NOT NULL primary key autoincrement,
			latitude double,
			longitude double,
			geom BLOB
		);
END;
	
		$this->db = $this->initDB($init_sql);
	}
	
	public function testGeomFromText(){
		$wkt = "POINT(-97.0 38.0)";
		$geom_blob = DTGeoSQLiteDatabase::geomFromText($wkt,4326);
		$geom = unserialize($geom_blob);
		$lat = $geom["coordinates"][0];
		$lng = $geom["coordinates"][1];
		$this->assertEquals($lat,-97);
		$this->assertEquals($lng,38);
	}
	
	public function testWithin(){
		$wkt1 = "POINT(-97.0 38.0)";
		$wkt2 = "POINT(-96.0 38.0)";
		$geom_blob1 = DTGeoSQLiteDatabase::geomFromText($wkt1,4326);
		$geom_blob2 = DTGeoSQLiteDatabase::geomFromText($wkt2,4326);
		$this->assertTrue(DTGeoSQLiteDatabase::within($geom_blob1,$geom_blob2,10.0));
		$this->assertFalse(DTGeoSQLiteDatabase::within($geom_blob1,$geom_blob2,0.5));
	}
	
	public function testDistance(){
		$wkt1 = "POINT(-97.0 38.0)";
		$wkt2 = "POINT(-96.0 38.0)";
		$geom_blob1 = DTGeoSQLiteDatabase::geomFromText($wkt1,4326);
		$geom_blob2 = DTGeoSQLiteDatabase::geomFromText($wkt2,4326);
		$this->assertEquals(1,DTGeoSQLiteDatabase::distance($geom_blob1,$geom_blob2),"distance not approximately 1 degree.",0.01);
	}
}