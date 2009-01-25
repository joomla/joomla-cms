<?php

final class JConfig {
	protected static $variables = array();

	//prevent instantiation	
	private function __construct() {
		
	}
	
	//get value
	public static function _($key) {
		return isset(self::$variables[$key]) ? self::$variables[$key] : null; 
	}
	
	//set a value
	public static function set($key, $value) {
		self::$variables[$key] = $value;
	}
	
	//Get all vars
	public static function fetch() {
		return self::$variables;
	}
	
	//bind array/object
	public static function bind($object) {
		$array = array();
		if(is_array($object)) {
			$array = $object;
		} elseif(is_object($object)) {
			if($object INSTANCEOF JObject) {
				$array = $object->getProperties();
			} else {
				$array = get_object_vars($object);
			}
		} else {
			throw new JException('Attempting to bind an incorrect value');
		}
		self::$variables = array_merge(self::$variables, $array);
	}
	
}