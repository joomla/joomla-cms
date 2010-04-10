<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * JCategories Class.
 *
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.6
 */
class JCategories
{
	/**
	 * Array to hold the object instances
	 *
	 * @param array
	 */
	static $instances = array();

	/**
	 * Array of category nodes
	 *
	 * @var mixed
	 */
	protected $_nodes;

	/**
	 * Name of the extension the categories belong to
	 *
	 * @var string
	 */
	protected $_extension = null;

	/**
	 * Name of the linked content table to get category content count
	 *
	 * @var string
	 */
	protected $_table = null;

	/**
	 * Name of the category field
	 *
	 * @var string
	 */
	protected $_field = null;

	/**
	 * Name of the key field
	 *
	 * @var string
	 */
	protected $_key = null;

	/**
	 * Name of the items state field
	 */
	protected $_statefield = null;
	
	/**
	 * Array of options
	 *
	 * @var array
	 */
	protected $_options = null;

	/**
	 * Class constructor
	 *
	 * @access public
	 * @return boolean True on success
	 */
	public function __construct($options)
	{
		$this->_extension	= $options['extension'];
		$this->_table		= $options['table'];
		$this->_field		= (isset($options['field'])&&$options['field'])?$options['field']:'catid';
		$this->_key			= (isset($options['key'])&&$options['key'])?$options['key']:'id';
		$this->_statefield 	= (isset($options['statefield'])) ? $options['statefield'] : 'state'; 
		$options['access']	= (isset($options['access'])) ? $options['access'] : 'true';
		$options['published']	= (isset($options['published'])) ? $options['published'] : 1;
		$this->_options		= $options;
		return true;
	}

	/**
	 * Returns a reference to a JCategories object
	 *
	 * @param $extension Name of the categories extension
	 * @param $options An array of options
	 * @return object
	 */
	public static function getInstance($extension, $options = array())
	{
		$hash = md5($extension.serialize($options));
		if (isset(self::$instances[$hash]))
		{
			return self::$instances[$hash];
		}
		$parts = explode('.',$extension);
		$component = 'com_'.strtolower($parts[0]);
		$section = count($parts) > 1 ? $parts[1] : '';
		$classname = ucfirst(substr($component,4)).ucfirst($section).'Categories';
		if (!class_exists($classname))
		{
			$path = JPATH_SITE.DS.'components'.DS.$component.DS.'helpers'.DS.'category.php';
			if (is_file($path))
			{
				require_once $path;
			} else {
				return false;
			}
		}
		
		self::$instances[$hash] = new $classname($options);
		return self::$instances[$hash];
	}

	/**
	 * Loads a specific category and all its children in a JCategoryNode object
	 * 
	 * @param an optional id integer or equal to 'root'
	 * @return JCategoryNode|null
	 */
	public function get($id = 'root', $forceload = false)
	{
		if ($id !== 'root')
		{
			$id = (int) $id;
			if ($id == 0)
			{
				$id = 'root';
			}
		}
		if (!isset($this->_nodes[$id]) || $forceload)
		{
			$this->_load($id);
		}
		
		if(isset($this->_nodes[$id]))
		{
			return $this->_nodes[$id];
		}
		return false;
	}

	protected function _load($id)
	{
		$db	= JFactory::getDbo();
		$user = JFactory::getUser();
		$extension = $this->_extension;
	
		$query = new JDatabaseQuery;
		
		// right join with c for category
		$query->select('c.*');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as slug');
		$query->from('#__categories as c');
		$query->where('(c.extension='.$db->Quote($extension).' OR c.extension='.$db->Quote('system').')');
		if($this->_options['access'])
		{
			$query->where('c.access IN ('.implode(',', $user->authorisedLevels()).')');
		}
		if($this->_options['published'] == 1)
		{
			$query->where('c.published = 1');
		}		
		$query->order('c.lft');
		

		// s for selected id
		if ($id!='root')
		{
			// Get the selected category
			$query->leftJoin('#__categories AS s ON (s.lft <= c.lft AND s.rgt >= c.rgt) OR (s.lft > c.lft AND s.rgt < c.rgt)');
			$query->where('s.id='.(int)$id);
		}
		
		// i for item
		if(isset($this->_options['countItems']) && $this->_options['countItems'] == 1)
		{
			if($this->_options['published'] == 1)
			{
				$query->leftJoin($db->nameQuote($this->_table).' AS i ON i.'.$db->nameQuote($this->_field).' = c.id AND i.'.$this->_statefield.' = 1');
			} else {
				$query->leftJoin($db->nameQuote($this->_table).' AS i ON i.'.$db->nameQuote($this->_field).' = c.id');
			}
			$query->select('COUNT(i.'.$db->nameQuote($this->_key).') AS numitems');
		}
		
		// Group by
		$query->group('c.id');

		// Get the results
		$db->setQuery($query);
		$results = $db->loadObjectList('id');
		$childrenLoaded = false;
		if (count($results))
		{
			// foreach categories
			foreach($results as $result)
			{
				// Deal with root category
				if($result->id == 1)
				{
					$result->id = 'root';	
				}
				// Deal with parent_id
				if($result->parent_id == 1)
				{
					$result->parent_id = 'root';
				}
				// Create the node
				if (!isset($this->_nodes[$result->id]))
				{
					// Create the JCategoryNode
					$this->_nodes[$result->id] = new JCategoryNode($result, &$this);
					if($result->id != 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id == 0))
					{
						// Compute relationship between node and its parent
						$this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
					}
					if(!(isset($this->_nodes[$result->parent_id]) || $result->parent_id == 0))
					{
						unset($this->_nodes[$result->id]);
						break;
					}
					
					if($result->id == $id || $childrenLoaded)
					{
						$this->_nodes[$result->id]->setAllLoaded();
						$childrenLoaded = true;
					}
				} elseif($result->id == $id || $childrenLoaded) {
					// Create the JCategoryNode
					$this->_nodes[$result->id] = new JCategoryNode($result, &$this);
					if($result->id != 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id))
					{
						// Compute relationship between node and its parent
						$this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
					}
					if(!isset($this->_nodes[$result->parent_id]))
					{
						unset($this->_nodes[$result->id]);
						break;
					}
					if($result->id == $id || $childrenLoaded)
					{
						$this->_nodes[$result->id]->setAllLoaded();
						$childrenLoaded = true;
					}
					
				}
			}
		}
		else
		{
			$this->_nodes[$id] = null;
		}
	}
}

/**
 * Helper class to load Categorytree
 * @author Hannes
 * @since 1.6
 */
class JCategoryNode extends JObject
{
	/** @var int Primary key */
	public $id					= null;
	public $asset_id			= null;
	public $parent_id			= null;
	public $lft					= null;
	public $rgt					= null;
	public $level				= null;
	public $extension			= null;
	/** @var string The menu title for the category (a short name)*/
	public $title				= null;
	/** @var string The the alias for the category*/
	public $alias				= null;
	/** @var string */
	public $description			= null;
	/** @var boolean */
	public $published			= null;
	/** @var boolean */
	public $checked_out			= 0;
	/** @var time */
	public $checked_out_time	= 0;
	/** @var int */
	public $access				= null;
	/** @var string */
	public $params				= null;
	public $metadesc			= null;
	public $metakey				= null;
	public $metadata			= null;
	public $created_user_id		= null;
	public $created_time		= null;
	public $modified_user_id	= null;
	public $modified_time		= null;
	public $hits				= null;
	public $language			= null;
	public $numitems			= null;
	public $childrennumitems	= null;
	public $slug				= null;
	public $assets				= null;
	
	/**
	 * @var Parent Category
	 */
	protected $_parent = null;

	/**
	 * @var Array of Children
	 */
	protected $_children = array();
	
	/**
	 * @var Path from root to this category
	 */
	protected $_path = array();
	
	/**
	 * @var Category left of this one
	 */
	protected $_leftSibling = null;
	
	/**
	 * @var Category right of this one
	 */
	protected $_rightSibling = null;
	
	/**
	 * @var boolean true if all children have been loaded
	 */
	protected $_allChildrenloaded = false;

	/**
	 * @var Constructor of this tree
	 */
	protected $_constructor = null;
	
	/**
	 * Class constructor
	 * @param $category
	 * @return unknown_type
	 */
	public function __construct($category = null, &$constructor = null)
	{
		if ($category)
		{
			$this->setProperties($category);
			if($constructor)
			{
				$this->_constructor = &$constructor;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * Set the parent of this category
	 *
	 * If the category already has a parent, the link is unset
	 *
	 * @param JCategoryNode|null the parent to be setted
	 */
	function setParent(&$parent) 
	{
		if ($parent instanceof JCategoryNode || is_null($parent)) 
		{
			if (!is_null($this->_parent)) 
			{
				$key = array_search($this, $this->_parent->_children);
				unset($this->_parent->_children[$key]);
			}
			if (!is_null($parent)) 
			{
				$parent->_children[] = & $this;
			}
			$this->_parent = & $parent;
			if($this->id != 'root')
			{
				$this->_path = $parent->getPath();
				$this->_path[] = $this->id.':'.$this->alias;
			} 
			
			if(count($parent->_children) > 1)
			{
				end($parent->_children);
				$this->_leftSibling = &prev($parent->_children);
				$this->_leftSibling->_rightsibling = &$this;
			}
		}
	}
	
	/**
	 * Add child to this node
	 *
	 * If the child already has a parent, the link is unset
	 *
	 * @param JNode the child to be added
	 */
	function addChild(&$child) 
	{
		if ($child instanceof JCategoryNode) 
		{
			$child->setParent($this);
		}
	}

	/**
	 * Remove a specific child
	 * 
	 * @param int	ID of a category 
	 */
	function removeChild($id)
	{
		$key = array_search($this, $this->_parent->_children);
		unset($this->_parent->_children[$key]);
	}
	
	/**
	 * Get the children of this node
	 *
	 * @return array the children
	 */
	function &getChildren($recursive = false) 
	{
		if(!$this->_allChildrenloaded)
		{
			$temp = $this->_constructor->get($this->id, true);
			$this->_children = $temp->getChildren();
			$this->_leftSibling = $temp->getSibling(false);
			$this->_rightSibling = $temp->getSibling(true);
			$this->setAllLoaded();
		}
		if($recursive)
		{
			$items = array();
			foreach($this->_children as $child)
			{
				$items[] = $child;
				$items = array_merge($items, $child->getChildren(true));
			}
			return $items;
		}
		return $this->_children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return JNode|null the parent
	 */
	function &getParent() 
	{
		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return bool
	 */
	function hasChildren() 
	{
		return count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return bool
	 */
	function hasParent() 
	{
		return $this->getParent() != null;
	}
	
	/**
	 * Function to set the left or right sibling
	 * of a category
	 * 
	 * @param JCategoryNode $sibling sibling
	 * @param boolean $right if set to false, the sibling is the left one
	 * @return void
	 */
	function setSibling($sibling, $right = true)
	{
		if($right)
		{
			$this->_rightSibling = $sibling;
		} else {
			$this->_leftSibling = $sibling;
		}
	}
	
	/**
	 * Returns the right or left sibling of a category
	 * 
	 * @param boolean $right if set to false, returns the left sibling
	 * @return JCategoryNode|null
	 */
	function getSibling($right = true)
	{
		if(!$this->_allChildrenloaded)
		{
			$temp = $this->_constructor->get($this->id, true);
			$this->_children = $temp->getChildren();
			$this->_leftSibling = $temp->getSibling(false);
			$this->_rightSibling = $temp->getSibling(true);
			$this->setAllLoaded();
		}
		if($right)
		{
			return $this->_rightSibling;
		} else {
			return $this->_leftSibling;
		}
	}

	/**
	 * Returns the category parameters
	 * 
	 * @return JRegistry
	 */
	function getParams()
	{
		if(!$this->params instanceof JRegistry)
		{
			$temp = new JRegistry();
			$temp->loadJSON($this->params);
			$this->params = $temp;
		}
		return $this->params;
	}
	
	/**
	 * Returns the category metadata
	 * 
	 * @return JRegistry
	 */
	function getMetadata()
	{
		if(!($this->metadata instanceof JParameter))
		{
			$this->metadata = new JParameter($this->metadata);
			//$this->metadata->loadJSON($this->metadata);
		}
		return $this->metadata;
	}
	
	/**
	 * Returns the category path to the root category
	 * 
	 * @return array
	 */
	function getPath()
	{
		return $this->_path;
	}
	
	/**
	 * Returns the user that authored the category 
	 * 
	 * @param boolean $modified_user Returns the modified_user when set to true
	 * @return JUser
	 */
	function getAuthor($modified_user = false)
	{
		if($modified_user)
		{
			return JFactory::getUser($this->modified_user_id);
		}
		return JFactory::getUser($this->created_user_id);
	}
	
	function setAllLoaded()
	{
		$this->_allChildrenloaded = true;
		foreach($this->_children as $child)
		{
			$child->setAllLoaded();
		}
	}
	
	function getNumItems($recursive = false)
	{
		if($recursive)
		{
			$count = $this->numitems;
			foreach($this->getChildren() as $child)
			{
				$count = $count + $child->getNumItems(true);
			}
			return $count;
		}
		return $this->numitems;
	}
}
