<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
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
 	protected $id = '';
 	
 	/**
 	 * The title for the content item if appropriate
 	 * @var string
 	 */
 	protected $title = '';
 	
 	/**
 	 * The most basic url to get the content item 
 	 * Ex: index.php?option=com_content&view=article&id=7
 	 * @var string
 	 */
 	protected $url = '';
 	
 	/**
 	 * The configured url to get the content item (pre-sef'd) 
 	 * Ex: index.php?option=com_content&view=article&id=7:foobar&catid=12:mycat&Itemid=42
 	 * @var string
 	 */	
 	protected $route = '';
 	
 	/**
 	 * Summary data about the content item (intro-text for example)
 	 * @var string
 	 */
 	protected $summary = '';
 	
 	/**
 	 * The full textual content of the item (fulltext for example)
 	 * @var string
 	 */
 	protected $body = '';
 	
 	/**
 	 * An associative array of $metadata_type=>$metatdata_value
 	 * This is used for HTML or Document 
 	 * @var array
 	 */
 	protected $metadata = array();
 	
 	/**
 	 * String date the item was created 
 	 * @var string
 	 */
 	protected $created_date = '';
 	
 	/**
 	 * String date the item was modified 
 	 * @var string
 	 */ 	
 	protected $modified_date = '';
 	
 	/**
 	 * String date for display purposes (such as date published) 
 	 * @var string
 	 */ 	
 	protected $display_date = '';
 	
 	/**
 	 * Name of the author of the content item
 	 * @var string
 	 */
 	protected $author_name = '';
 	
 	/**
 	 * Internal user id of the author (0 if not exists)
 	 * @var string
 	 */
 	protected $author_id = 0;
 	
 	/**
 	 * Publishing status of the content item
 	 * @var bool
 	 */
 	protected $published = false;
 	
 	/**
 	 * Authorization status for the content item
 	 * @var bool
 	 */
 	protected $authorized = false;
 	
 	/**
 	 * Language of the content item
 	 * @var string
 	 */
 	protected $language = '';

	/**
	 * Constructor
	 * 
	 * @param	mixed	Object/Array to initialize JContent object with
	 */
	public function __construct($object = null) {
		if(!empty($object)) {
			$this->bind($object);
		}
	}
	
	/**
	 * Set Magic Method
	 * 
	 * @param	string	The key for the internal variable
	 * @param	mixed	The value to set the internal variable to
	 */
	public function __set($key, $value) {
		if(isset($this->$key)) {
			if(is_array($this->$key)) {
				$this->$key = (array) $value;
			} elseif(is_bool($this->$key)) {
				$this->$key = (bool) $value;
			} elseif(is_int($this->$key)) {
				$this->$key = (int) $value;
			} else {
				$this->$key = $value;
			}
		} else {
			$this->$key = $value;
		}
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
 		return $this->__set($key, $value);
 	}
 	
 	/**
 	 * Bind an object or array to the instance
 	 * 
 	 * @param	mixed	Object/Array to initialize JContent object with
 	 */
 	public function bind($object) {
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
 			throw new JException('Attempt to bind an incorrect value to JContent');
 		}
 		
 		foreach($array AS $key => $value) {
 			//Used to enforce types
 			$this->__set($key, $value);
 		}
 	}
 }