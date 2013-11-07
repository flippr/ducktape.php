<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

/**
	DTModel gives php objects some intelligent behaviors, such as simplified key-value coding-style accessors
*/
class DTModel implements arrayaccess {
	/** require properties to be defined in the class, defaults to false */
	protected static $strict_properties = false;
	protected static $storage_table = null;
	
	protected $identifier = 0;

    protected $_properties = array(); /** @internal */
    protected $_bypass_accessors = false; /** @internal a flag used to bypass accessors during construction */
    
    /**
    	@param paramsOrQuery - an assoc. array of default properties or DTQueryBuilder object
    */
    function __construct($paramsOrQuery=null){
    	$properties = array();
   		if(!isset($paramsOrQuery))
    		return; //just create an empty object
		if(is_array($paramsOrQuery)){
			$properties = $paramsOrQuery;
    	}else if($paramsOrQuery instanceof DTQueryBuilder){ //grab the parameters from storage
    		$this->_bypass_accessors = true; //we want direct access to properties
    		if(isset(static::$storage_table))
	    		$properties = $paramsOrQuery->from(static::$storage_table)->select1();
    	}
		if(is_array($properties) && count(array_filter(array_keys($properties), 'is_string'))) // must be an associative array
			if(static::$strict_properties==false)
				$this->_properties = $properties;
			else //make sure we go through the strict set method
				foreach($properties as $k=>$v)
					$this[$k] = $v;
		else{
			DTLog::warn("Attempt to instantiate object from invalid type.",1);
			}
			
		$this->_bypass_accessors = false; //make sure we use the accessors now
	}
    
    /**
    	looks for an accessor method (called set+offset+), or uses a basic storage mechanism
    */
    public function offsetSet($offset, $value) {
    	if (is_null($offset)) {
            $this->_properties[] = $value;
        } else {
	    	$accessor = "set".preg_replace('/[^A-Z^a-z^0-9]+/','',$offset);
			if(!$this->_bypass_accessors && method_exists($this, $accessor)) //use the accessor method
				$this->$accessor($value);
			else if(property_exists($this, $offset)) //use the property
				$this->$offset = $value;
			else if(static::$strict_properties==false) // set object property
				$this->_properties[$offset] = $value;
			/*else //it is not an error to fail to set a property
				DTLog::debug("failed to set property ({$offset})");*/
        }
    }
    public function offsetExists($offset) {
        return isset($this->_properties[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->_properties[$offset]);
    }
    /**
    	looks for an accessor method (called +offset+), or uses a basic storage mechanism
    */
    public function offsetGet($offset) {
    	$accessor = preg_replace('/[^A-Z^a-z^0-9]+/','',$offset);
		if(method_exists($this, $accessor)) //use the accessor method
			return $this->$accessor();
		else if(property_exists($this, $offset)) //use the property
			return $this->$offset;
		else if(static::$strict_properties==false)// get object property
			return isset($this->_properties[$offset])?$this->_properties[$offset]:null;
			
		DTLog::warn("property does not exist ({$offset})");
		return null;
    }
    
    /**
    	override this method if you want to compare objects by a subset of properties
    	@return returns true if object is equal to +obj+
    */
    public function isEqual(DTObject $obj){
	    return $this==$obj;
    }
    
//==================
//! Storage Methods
//==================
    
    /**
    	override this method to customize the properties that get stored
    	@return returns an array of key-value pairs that can be used for storage
    	@note values should be properly formatted for storage (including quotes)
    */
    public function storageProperties(array $defaults=array(),$purpose=null){
		return $defaults;
	}
	
	/**
		convenience method for basic inserts based on storageProperties()
		@return returns the inserted id, or false if nothing was inserted
	*/
	public function insert(DTDatabase $db){
		$properties = $this->storageProperties(array(),"insert");
		if(count($properties)>0){
			$cols_str = implode(",",array_keys($properties));
			$vals_str = implode(",",array_values($properties));
			$stmt = "INSERT INTO ".static::$storage_table." ({$cols_str}) VALUES ({$vals_str});";
			return  $db->insert($stmt);
		}
		return false;
	}
	
	/**
		convenience method for basic updates based on storageProperties()
		@note uses the object's identifier property for where-clause
	*/
	public function update(DTDatabase $db){
		$properties = $this->storageProperties(array(),"update");
		if(count($properties)>0 && isset($this->storage_table)){
			$set_str = implode(",",array_map(function($k,$v){return "{$k}={$v}";},array_keys($properties),$properties));
			$stmt = "UPDATE {$this->storage_table} SET {$set_str} WHERE id={$this->identifier}";
			$db->query($stmt);
		}
	}
	
	public function where($where_str){
		$properties = $this->storageProperties(array(),"select");
		if(count($properties)>0 && isset($this->storage_table)){
			$cols_str = implode(",",array_keys($properties));
			$stmt = "SELECT {$col_str} FROM {$this->storage_table} WHERE {$where_str}";
			return $db->select($stmt);
		}
		return null;
	}
}