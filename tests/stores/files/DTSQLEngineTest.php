<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";
//under construction
/*class DTSQLEngineTest extends DTTestCase{
	protected $store;
	protected $engine;
	
	public function setup(){
		$this->store = new DTYAMLStore("file://".dirname(__FILE__)."/yaml_db/"); //set up a concrete delegate
		$this->engine = new DTSQLEngine($this->store);
	}

	public function testRunSQL(){
	
	}
	
	public function testHandleParsedSQL(){
	
	}
	
	public function testQualifiedTableAndIndex(){
		$index_table = array();
		$left = $this->engine->qualifiedTableAndIndex("table1",$index_table);
		$cols = array_keys($left[0]);
		$this->assertTrue($this->engine->tableFromFQCN($cols[0])=="table1");
		$this->assertEquals(array("table1"=>array(0,1,2)),$index_table);
	}
	
	public function testJoinWith(){
		$index_table = array();
		$left = $this->engine->qualifiedTableAndIndex("table1",$index_table);
		$parsed = <<<END
	{
      "expr_type": "table",
      "table": "table2",
      "no_quotes": "table2",
      "alias": false,
      "join_type": "JOIN",
      "ref_type": "ON",
      "ref_clause": [
        {
          "expr_type": "colref",
          "base_expr": "table2.author_id",
          "no_quotes": "table2.author_id",
          "sub_tree": false
        },
        {
          "expr_type": "operator",
          "base_expr": "=",
          "sub_tree": false
        },
        {
          "expr_type": "colref",
          "base_expr": "table1.id",
          "no_quotes": "table1.id",
          "sub_tree": false
        }
      ],
      "base_expr": "table2 ON (table2.author_id=table1.id)",
      "sub_tree": false
    }
END;
	$results = $this->engine->joinWith($left,json_decode($parsed,true),$index_table);
	//var_dump($results);
		//$sql = "SELECT * FROM table1 JOIN table2 ON (table2.author_id=table1.id)";
		//$parser = new PHPSQLParser($sql);
		//$this->engine->joinWith($left,$parser->parsed["JOIN"][0],$index_table);
	}
	
	public function testBuildWhitelist(){
	
	}
	
	
	public function testSelectColumnsFromResults(){
	
	}
	
	
	public function testLimitResults(){
	
	}
	
	
	public function testSortResults(){
	
	}
	
	public function testfilterResults(){
	
	}

	public function testBuildExpressionTree(){
	
	}
	
	public function testEvaluateExpressionForRow(){
	
	}
	
	public function testFullyQualifiedColumnName(){
	
	}
	
	public function testTableFromFQCN(){
		$tab = "table";
		$this->assertFalse(DTSQLEngine::tableFromFQCN($tab));
		
		$fqcn = "table.column";
		$this->assertEquals("table",DTSQLEngine::tableFromFQCN($fqcn));
	}
	
	public function testColumnFromFQCN(){
		$col = "column";
		$this->assertEquals("column",DTSQLEngine::columnFromFQCN($col));
		
		$fqcn = "table.column";
		$this->assertEquals("column",DTSQLEngine::columnFromFQCN($fqcn));
	}
	
//============
//! SQL Tests
//============
	public function selectBasic(){
		$sql = "SELECT * FROM table1";
		$rows = $this->engine->runSQL($sql);
		var_dump($rows);
	}

}*/