<?php
require_once dirname(__FILE__)."/../../ducktape.inc.php";

/**
	DTModel gives php objects some intelligent behaviors, such as simplified key-value coding-style accessors
*/
class DTModel implements arrayaccess {
	/** require properties to be defined in the class, defaults to false */
	protected static $strict_properties = false;
	protected static $storage_table = null;
	
	public $id = 0;

    protected $_properties = array(); /** @internal */
    protected $_bypass_accessors = false; /** @internal a flag used to bypass accessors during construction */
    
    /**
    	@param paramsOrQuery - an assoc. array of default properties or DTQueryBuilder object
    */
    function __construct($paramsOrQuery=null){
   		if(!isset($paramsOrQuery))
    		return; //just create an empty object
		if(is_array($paramsOrQuery)){
			$properties = $paramsOrQuery;
    	}else if($paramsOrQuery instanceof DTQueryBuilder){ //grab the parameters from storage
    		$this->_bypass_accessors = true; //we want direct access to properties
    		if(isset(static::$storage_table))
	    		$properties = $paramsOrQuery->from(static::$storage_table)->select1();
	    	if(!isset($properties))
    			throw new Exception('Failed to find object in storage.',1);
    	}
		if(is_array($properties) && (count($properties)==0 || count(array_filter(array_keys($properties),'is_string')))) // must be an associative array
			foreach($properties as $k=>$v)
				$this[$k] = $v;//make sure we go through the set method
		else
			DTLog::warn("Attempt to instantiate ".get_called_class()." from invalid type (".json_encode($properties).")",1);
			
		$this->_bypass_accessors = false; //make sure we use the accessors now
	}
    
    /**
    	looks for an accessor method (called set+offset+), or uses a basic storage mechanism
    	@return returns the value that was stored
    */
    public function offsetSet($offset, $value) {
    	if (is_null($offset)) {
            $this->_properties[] = $value;
            return $value;
        } else {
	    	$accessor = "set".preg_replace('/[^A-Z^a-z^0-9]+/','',$offset);
			if(!$this->_bypass_accessors && method_exists($this, $accessor)) //use the accessor method
				return $this->$accessor($value);
			else if(property_exists($this, $offset)){ //use the property
				$this->$offset = $value;
				return $value;
			}
			else if(static::$strict_properties==false){ // set object property
				$this->_properties[$offset] = $value;
				return $value;
			}
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
    public function publicProperties(array $defaults=array(),$purpose=null){
		$public_params = array();
		$ref = new ReflectionClass($this);
		$publics = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
		foreach($publics as $p){
			$k = $p->getName();
			$public_params[$k] = $this[$k];
		}
		return array_merge($public_params,$defaults);
	}
	
	public function storageProperties(DTDatabase $db,array $defaults=array(),$purpose=null){
		$storage_params = array();
		$cols = $db->columnsForTable(static::$storage_table);
		if(count($cols)==0)
			DTLog::error("Found 0 columns for table (".static::$storage_table.")");
		foreach($cols as $k){
			if($purpose!="insert"||$k!="id") //don't try to insert the id, assume it's autoincrementing
				$storage_params[$k] = $this[$k];
		}
		return array_merge($storage_params,$defaults);
	}
	
	/**
		convenience method for basic inserts based on storageProperties()
		@return returns the inserted id, or false if nothing was inserted
	*/
	public function insert(DTDatabase $db){
		$qb = new DTQueryBuilder($db);
		return $qb->from(static::$storage_table)->insert($this->storageProperties($db,array(),"insert"));
	}
	
	/**
		convenience method for basic updates based on storageProperties()
		@note uses the object's id property for where-clause
	*/
	public function update(DTDatabase $db){
		$properties = $this->storageProperties($db,array(),"update");
		return $db->where("id={$this->id}")->from(static::$storage_table)->update($properties);
	}
	
	public static function select(DTQueryBuilder $qb){
		return $qb->from(static::$storage_table)->selectAs(get_called_class());
	}
	
	public static function updateRows(DTQueryBuilder $qb,$params){
		return $qb->from(static::$storage_table)->update($params);
	}
}