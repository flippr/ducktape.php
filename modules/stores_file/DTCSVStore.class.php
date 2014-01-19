<?php
class DTCSVStore extends DTBackedFileStore{
	public $file_extension = "csv";
	public $retain_id_attr = true;

	public static function unserialize($str){
		try{
			return parseCSV($str);
		}catch(Exception $e){}
		return null;
	}
	
	public static function serialize($obj){
		$o = $obj[0];
		$lines = array(implode(",",array_keys($o)));
		foreach($obj as $o){
			$lines[] = arrayToCSV($o,",");
		}
		return implode("\n",$lines);
	}
}

/** alternative CSV parsing compliant with RDF 
	(heavily modified, but adapted from http://www.php.net/manual/en/function.str-getcsv.php) */
function parseCSV($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true){
	$lines = preg_split(
        $skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s',
        preg_replace_callback('/"(.*?)"/s',function ($field) {return urlencode(utf8_encode($field[1]));},
            $enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string)
        )
    );
    $headers = parse_fields($lines[0],$delimiter,$trim_fields);
    unset($lines[0]);
    return array_map(
    	function($line) use ($delimiter,$trim_fields,$headers){
	        return array_combine($headers,parse_fields($line,$delimiter,$trim_fields));
		},
		$lines
	);
}

function parse_fields($line,$delimiter,$trim_fields){
	return array_map(
        	function ($field) { return str_replace('!!Q!!', '"', utf8_decode(urldecode($field))); },
            $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line)
    );
}

/** Formats a line (passed as a fields  array) as CSV and returns the CSV as a string.
	(adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120) */
function arrayToCSV( array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false ) {
    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');
    $output = array();
    foreach ($fields as $field)
        if ($field===null&&$nullToMysqlNull) 
            $output[] = 'NULL';
        else // Enclose fields containing $delimiter, $enclosure or whitespace
		$output[] = ($encloseAll||preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/",$field))
			? $enclosure.str_replace($enclosure, $enclosure . $enclosure, $field).$enclosure
			:$field;
    return implode($delimiter,$output);
}