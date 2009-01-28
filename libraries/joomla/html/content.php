<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// No direct access
defined('_JEXEC') or die();

/**
 * JContent class
 * This class serves to provide a common API for different types of content
 *
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.6
 */
 class JContent extends JObject
 {
 	
 	/**
 	 * A Unique identifier for the content item
 	 * @var mixed
 	 */
 	public $id = '';

 	/**
 	 * The title for the content item if appropriate
 	 * @var string
 	 */
 	public $title = '';

 	/**
 	 * The most basic url to get the content item
 	 * Ex: index.php?option=com_content&view=article&id=7
 	 * @var string
 	 */
 	public $url = '';

 	/**
 	 * The configured url to get the content item (pre-sef'd)
 	 * Ex: index.php?option=com_content&view=article&id=7:foobar&catid=12:mycat&Itemid=42
 	 * @var string
 	 */
 	public $route = '';

 	/**
 	 * Summary data about the content item (intro-text for example)
 	 * @var string
 	 */
 	public $summary = '';

 	/**
 	 * The full textual content of the item (fulltext for example)
 	 * @var string
 	 */
 	public $body = '';

 	/**
 	 * An associative array of $metadata_type=>$metatdata_value
 	 * This is used for HTML or Document
 	 * @var array
 	 */
 	public $metadata = array();

 	/**
 	 * String date the item was created
 	 * @var string
 	 */
 	public $created_date = '';

 	/**
 	 * String date the item was modified
 	 * @var string
 	 */
 	public $modified_date = '';

 	/**
 	 * String date for display purposes (such as date published)
 	 * @var string
 	 */
 	public $display_date = '';

 	/**
 	 * Name of the author of the content item
 	 * @var string
 	 */
 	public $author_name = '';

 	/**
 	 * Internal user id of the author (0 if not exists)
 	 * @var string
 	 */
 	public $author_id = 0;

 	/**
 	 * Publishing status of the content item
 	 * @var bool
 	 */
 	public $published = false;

 	/**
 	 * Authorization status for the content item
 	 * @var bool
 	 */
 	public $authorized = false;

 	/**
 	 * Language of the content item
 	 * @var string
 	 */
 	public $language = '';

	/**
	 * Constructor
	 *
	 * @param	mixed	Object/Array to initialize JContent object with
	 */
	public function __construct($object = null) {
		if (!empty($object)) {
			$this->bind($object);
		}
	}
	
	public static function &getInstance($name = '', $include_path = '') {
		static $cache = array();
		if(!isset($cache[$name])) {
			$type = $name;
			$scope = '';
			if(strpos($name, '.') !== false) {
				$parts = explode('.', $name, 2);
				if(isset($parts[1])) {
					$type = $parts[1];
					$scope = preg_replace('#[^A-Z0-9_-]#i', '', $parts[0]);
				}
			}
			$type = preg_replace('#[^A-Z0-9_-]#i', '', $type);
			$class = 'JContent'.ucfirst($scope).ucfirst($type);
			if(!class_exists($class)) {
				//we must find the class
				$base = empty($include_path) ? $include_path : JPATH_ROOT;
				if(file_exists($base.DS.$type.'.php')) {
					require_once($base.DS.$type.'.php');
				} elseif(file_exists($base.DS.'content'.DS.$type.'.php')) {
					require_once($base.DS.'content'.DS.$type.'.php');
				} elseif(file_exists($base.DS.'components'.DS.'com_'.$scope.DS.'content'.DS.$type.'.php')) {
					require_once($base.DS.'components'.DS.'com_'.$scope.DS.'content'.DS.$type.'.php');
				} else {
					throw new JException('Could not find JContent include file for Scope: '.$scope.', Type: '.$type);
				}
			}
			$cache[$name] = new $class();
		}
		return clone $cache[$name];
	}
	
 	/**
 	 * Set a property on the object
 	 * We need to overload the parent set, so that we can force the types
 	 *
	 * @param	string	The key for the internal variable
	 * @param	mixed	The value to set the internal variable
 	 */
 	public function set($key, $value) {
 		//proxy to magic set method __set
 		return $this->bind(array($key=>$value));
 	}

 	/**
 	 * Bind an object or array to the instance
 	 *
 	 * @param	mixed	Object/Array to initialize JContent object with
 	 */
 	public function bind($object) {
 		$array = array();
 		if (is_array($object)) {
 			$array = $object;
 		} elseif (is_object($object)) {
 			if ($object INSTANCEOF JObject) {
 				$array = $object->getProperties();
 			} else {
 				$array = get_object_vars($object);
 			}
 		} else {
 			throw new JException('Attempt to bind an incorrect value to JContent');
 		}

 		foreach($array AS $key => $value) {
 			if(isset($this->$key)) {
 				//force types...
 				switch(getType($this->$key)) {
					case 'array':
					 	$value = (array) $value;
					 	break;
					 case 'boolean':
					 	$value = (bool) $value;
					 	break;
					case 'integer':
						$value = (int) $value;
						break;
				}
 			}
 			$this->$key = $value;
 		}
 	}
 	
 	public function prepare() {
 		$app = JFactory::getApplication();
 		$app->triggerEvent('onContentPrepare', array(&$this));
 	}
 	
 	public function getDisplay($defaults = array()) {
 		if(is_object($defaults)) {
 			$positions = $defaults;
 		} elseif(is_array($defaults)) {
 			$positions = new stdclass();
 			foreach($defaults AS $value) {
 				$positions->$value = '';
 			}
 		} else {
 			$positions = new stdclass();
 		}
 		$app = JFactory::getApplication();
 		$app->triggerEvent('onContentDisplay', array($this, &$positions));
 		return $positions;
 	}
 }