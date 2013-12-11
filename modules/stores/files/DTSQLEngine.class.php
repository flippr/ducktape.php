<?php
require_once dirname(__FILE__)."/../../../ducktape.inc.php";


/** @todo turns out the 3rd-party parser is really more of a tokenizer,
	Since we would have to handle our own operation precedence, etc.
	it makes more sense to implement our own SQL Parser at some point.
	Until then, this class is not operational. */

class DTSQLEngine{
	protected $delegate;

	function __construct(DTSQLEngineDelegate $delegate){
		$this->delegate = $delegate;
	}
	
	/** runs a series of SQL statements
		@return returns the result of the last statement */
	public function runSQL($sql){
		$result = null;
		preg_match_all('/(?:\\\\.|[^;\\\\]++)+/',$sql,$stmts); //split on unescaped ;
		foreach($stmts[0] as $stmt){
			$parser = new PHPSQLParser($stmt);
			$index_table = array(); // we don't really care about this at the top level...
			$result = $this->handleParsedSQL($parser->parsed,$index_table);
		}
		return $result;
	}
	
	
	/** @param - index_table on return, this contains the index table for the results */
	protected function handleParsedSQL($parsed,&$index_table){
		if(isset($parsed["CREATE"]))
			return $this->delegate->createTable($parsed["TABLE"]["name"]);
		
		if(isset($parsed["DROP"]))
			return $this->delegate->dropTable($parsed["DROP"]["object_list"][0]["base_expr"]);
		
		if(isset($parsed["INSERT"])){ //INSERT means adding a row
			$table = $parsed["INSERT"][0]["no_quotes"];
			$row = array();
			for($i=0;$i<count($parsed["VALUES"][0]["data"]);$i++){ //step through columns
				$col = $parsed["INSERT"][0]["columns"][$i]["no_quotes"];
				$val = $parsed["VALUES"][0]["data"][$i]["base_expr"];
				$row[$col] = $trim($val,"' \t");
			}
			$this->delegate->insertRow($table,$row);
			return null;
		}
		
		//collect all of our result sets (FROM/JOIN)
		$results = array();
		foreach($parsed["FROM"] as $f) //step through all of the tables (FROM, JOIN, etc)
			$results = $this->joinWith($results,$f,$index_table);
		
		//filter our results (WHERE)
		$expr_tree = $this->buildExpressionTree($parsed["WHERE"]);
		$filtered = array();
		$filtered_index_table = array();
		for($i=0;$i<count($results);$i++){
			if($this->evaluateExpressionForRow($expr_tree,$results[$i]))
				$filtered[] = $result[$i]; // copy the row
			foreach($index_table as $t) // copy the index table entries
				$filtered_index_table[$t][] = $index_table[$t][$i];
		}
		$results = $filtered;
		$index_table = $filtered_index_table;
		
		//@todo perform GROUP BY -- for now, we do not support aggregate functions
		
		//select the subset of columns from the result set
		if(isset($parsed["SELECT"])){
			$whitelisted = array(); // a list of output columns as 'alias'=>'fqcn'
			foreach($parsed["SELECT"] as $c)
				$whitelisted = buildWhitelist($whitelisted,$c,$results);
			$final = $this->selectColumnsFromResults($whitelisted,$results);
			
			//filter our results one last time (HAVING)
			if(isset($parsed["HAVING"]))
				$this->filterResults($final,$parsed["HAVING"]);
		
			//sort the results
			if(isset($parsed["ORDER"]))
				$this->sortResults($final,$parsed["ORDER"]);
			
			//limit our results
			if(isset($parsed["LIMIT"]))
				$this->limitResults($final,$parsed["LIMIT"]);
			
			return $final;
		}
		
		if(isset($parsed["UPDATE"])){
			//$update = 
			for($i=0;$i<count($results);$i++){
				foreach(array_keys($index_table) as $t){
					$this->delegate->updateRow($t,$index_table[$t][$i],$update);
				}
			}
		}
		
		if(isset($parsed["DELETE"])){ //we should delete the rows for all results
			for($i=0;$i<count($results);$i++){
				foreach(array_keys($index_table) as $t){
					$this->delegate->removeRow($t,$index_table[$t][$i]);
				}
			}
		}
	}
	
	/** @return returns the rows from the table named +table+ and populates index_table */
	public function qualifiedTableAndIndex($table,&$index_table){
		$rows = $this->delegate->rowsForTable($table);
		// fully-qualify all columns from table and build index_table
		foreach($rows as $idx=>$row){
			$new_row = array();
			foreach($row as $k=>$v){
				$new_row[$table.".".$k] = $v;
			}
			$rows[$idx] = $new_row;
			$index_table[$table][] = $idx;
		}
		return $rows;
	}
	
	/** produces a row x col result set of the combination of the left and right tables
		@param index_table - on return, this contains the index table for the results
		@return returns a 2D result set of the joined tables
	*/
	public function joinWith($left,$parsed_join,&$index_table){
		$right_index_table = array();
		if($parsed_join["expr_type"]=="table") //either get rows from a table
			$right = $this->qualifiedTableAndIndex($parsed_join["no_quotes"],$right_index_table);
		else if($parsed_join["expr_type"]=="subquery") //or from a subquery
			$right = $this->handleParsedSQL($join["sub_tree"],$right_index_table);
						
		$join_type = $parsed_join["join_type"]; //parser doesn't not seem to work with OUTER
		
		//add table and alias to table_lookup
		/*$alias = isset($parsed_join["alias"])?$parsed_join["alias"]["no_quotes"]:""; 
		$table = isset($parsed_join["table"])?$parsed_join["no_quotes"]:$alias;
		$table_lookup[$alias] = $table; $table_lookup[$table] = $table;*/
		
		$left_tables = array_keys($index_table);
		$right_tables = array_keys($right_index_table);
		$output_index_table = array();
		
		$expr_tree = $this->buildExpressionTree($parsed_join["ref_clause"]);
		//perform a simple nested-loop join
		$output = array();
		foreach($left as $lidx=>$lrow){
			$matched = false;
			foreach($right as $ridx=>$rrow){
				$urow = array_merge($lrow,$rrow);
				if($this->evaluateExpressionForRow($expr_tree,$urow)){
					$output[] = $urow;
					$matched = true;
					foreach($left_tables as $t)
						$output_index_table[$t][] = $lidx;
					foreach($right_tables as $t)
						$output_index_table[$t][] = $ridx;
				}else if($join_type=="RIGHT" || $join_type=="OUTER"){
					$output[] = $rrow;
					$matched = true;
					foreach($left_tables as $t)
						$output_index_table[$t][] = null;
					foreach($right_tables as $t)
						$output_index_table[$t][] = $ridx;
				}
			}
			if(!$matched && ($join_type=="LEFT" || $join_type=="OUTER")){
				$output[] = $lrow;
				foreach($left_tables as $t)
						$output_index_table[$t][] = $lidx;
					foreach($right_tables as $t)
						$output_index_table[$t][] = null;
			}
		}
		$index_table = $output_index_table; //set the output index table
		return $output;
	}
	
	public function buildWhitelist(array $whitelisted,$parsed_select,&$results){
		//first, if we have a ...*, we need to copy every matching column to final
		if(substr($parsed_select["base_expr"],-1)=="*"){
			if($parsed_select["base_expr"]=="*"){ //whitelist everything
				foreach($results as $table){
					$cols = array_keys($results[$table][0]); //get the columns from the table
					$fqcns = array_map(function($c)use($table){return "{$table}.{$c}";},$cols); //make table.col the fqcn
					$whitelisted = array_merge($whitelisted,array_combine($cols,$fqcns));
				}
			}else{ //whitelist the columns from this table
				$table = strstr($parsed_select["base_expr"],".*",true);
				$cols = array_keys($results[$table][0]); //get the columns from the table
				$fqcns = array_map(function($c)use($table){return "{$table}.{$c}";},$cols); //make table.col the fqcn
				$whitelisted = array_merge($whitelisted,array_combine($cols,$fqcns));
			}
		}else{ //otherwise, set this is just a column name or possibly an alias
			$dst_col = $src_col = $this->fullyQualifiedColumnName($parsed_select["no_quotes"]);
			if(isset($parsed_select["alias"]))
				$dst_col = $this->fullyQualifiedColumnName($parsed_select["alias"]["no_quotes"]);
			$whitelisted = array_merge($whitelisted,array($dst_col=>$src_col));
		}
		return $whitelisted;
	}
	
	function selectColumnsFromResults($whitelisted,&$results){
		$final = array();
		foreach($whitelisted as $alias=>$fqcn){
				$table = $this->tableFromFQCN($fqcn);
				$src_col = $this->columnFromFQCN($fqcn);
				$src_table = $results[$table];
				$dst_col = $this->columnFromFQCN($alias);
				for($i=0;$i<count($src_col);$i++){ //for each row in the src column
					$final[$i][$dst_col] = $src_table[$i][$src_col];
				}
		}
		return $final;		
	}
	
	function limitResults(&$final,$parsed_limit){
		$offset = intval($parsed_limit["offset"]);
		$length = intval($parsed_limit["rowcount"]);
		$final = array_slice($final,$offset,$length);
	}
	
	function sortResults(&$final,$parsed_order){
		$sort_args = array();
		foreach($parsed_order as $o){ //build a set of sort args
			$col = $this->fullyQualifiedColumnName($o["no_quotes"]);
			$vals = array();
			foreach($final[$this->tableFromFQCN($col)] as $row){
				$vals[] = $row[$this->columnFromFQCN($col)];
			}
			$sort_args[] = $vals; //push column
			$sort_args[] = ($o["direction"]=="DESC")?SORT_DESC:SORT_ASC; //push order
		}
		$sort_args[] = $final; //last arg is our target
		call_user_func("array_multisort",$sort_args);
	}
	
	/**
		@param results - the current result sets
		@param parsed_expr - a parsed SQL expression to filter via evaluation
	*/
	protected function filterResults(&$results,$parsed_expr){
		//first, build a real tree using operands as branching nodes
		$expr_tree = $this->buildExpressionTree($parsed_expr);
		//traverse the tree, reducing the result set
		
	}
	
	/** generates a tree-structure from a parsed expression */
	protected function buildExpressionTree($parsed_expr){
		if(!isset($parsed_expr))
			return null;
	}
	
	/** evaluates the expression tree for a given row */
	public function evaluateExpressionForRow($expr_tree, $row){
		
	}
	
	protected static function fullyQualifiedColumnName($results,$col){
		$found_table = null;
		if(strpos($col,".")===false){ //we need to get the table
			foreach($results as $tableOrAlias=>$rows){
				$table_columns = array_keys($rows[0]);
				if(in_array($col,$table_columns)){
					if(isset($found_table)) // crap, we already found this before
						throw new Exception("Ambiguous column name ({$col})");
					$found_table = $tableOrAlias;
				}
				return "{$found_table}.{$col}";
			}
		}
		return $col;
	}
	
	/** @returns the table name, or false */
	public static function tableFromFQCN($col){
		return strstr($col,".",true);
	}
	
	/** @return returns only the column name after any table-name qualifiers */
	public static function columnFromFQCN($col){
		$sub = strstr($col,".");
		if($sub===false)
			return $col; //this is just the column
		return substr($sub,1);
	}
}