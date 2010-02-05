<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.base.node');

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
	protected $_nodes = null;

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
	 * Array of options
	 *
	 * @var array
	 */
	protected $_options = null;

	/**
	 * Save the information, if a tree is loaded
	 *
	 * @var boolean
	 */
	protected $_treeloaded = false;


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
		if (isset(self::$instances[$extension]))
		{
			return self::$instances[$extension];
		}
		$parts = explode('.',$extension);
		$component = $parts[0];
		$section = (count($parts)>1)?$parts[1]:'';
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
		self::$instances[$extension] = new $classname($options);
		return self::$instances[$extension];
	}

	/**
	 * Loads a specific category and all its children in a JCategoryNode object
	 * @param an optional id integer or equal to 'root'
	 * @param an optional array of boolean options (all set to true by default).
	 *   'load' to force loading
	 *   'children' to get its direct children,
	 *   'parent' to get its direct parent,
	 *   'siblings' to get its siblings
	 *   'ascendants' to get its ascendants
	 *   'descentants' to get its descendants
	 *   'level-min' to get nodes from this level
	 *   'level-max' to get nodes until this level 
	 * @return JCategoryNode|null
	 */
	public function get($id='root',$options=array())
	{
		if ($id != 'root')
		{
			$id = (int) $id;
			if ($id == 0)
			{
				return null;
			}
		}
		if (!isset($this->_nodes[$id]) || !isset($options['load']) || $options['load'])
		{
			$this->_load($id,$options);
		}
		
		return $this->node($id);
	}
	/**
	 * Return a node
	 * @param an optional id
	 * @return JCategoryNode|null
	 */
	public function node($id='root')
	{
		if (isset($this->_nodes[$id]) && $this->_nodes[$id] instanceof JCategoryNode)
		{
			return $this->_nodes[$id];
		}
		else
		{
			return null;
		}
	}
	/**
	 * Return the root or null
	 *
	 * @return JCategoryNode|null
	 */
	public function root()
	{
		return $this->node('root');
	}

	protected function _load($id,$options)
	{
		$db	= JFactory::getDbo();
		$user = JFactory::getUser();
		$extension = $this->_extension;
		
		$children		= !isset($options['children'])		|| $options['children'];
		$parent			= !isset($options['parent'])		|| $options['parent'];
		$siblings		= !isset($options['siblings'])		|| $options['siblings'];
		$ascendants		= !isset($options['ascendants'])	|| $options['ascendants'];
		$descendants	= !isset($options['descendants'])	|| $options['descendants'];
		
		$query = new JDatabaseQuery;
		
		// right join with c for category
		$query->select('c.*');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as slug');
		$query->from('#__categories as c');
		$query->where('(c.extension='.$db->Quote($extension).' OR c.extension='.$db->Quote('system').')');
		$query->where('c.access IN ('.implode(',', $user->authorisedLevels()).')');		
		$query->order('c.lft');

		// s for selected id
		if ($id!='root')
		{
			// Get the selected category
			$test = 					'(s.lft = c.lft AND s.rgt = c.rgt)';
			// Get the parent
			$test.=$parent?			' OR (s.parent_id = c.id)':''; 
			// Get the children
			$test.=$children ?		' OR (c.parent_id = s.id)':'';
			// Get the siblings
			$test.=$siblings ?		' OR (s.parent_id = c.parent_id)':'';
			// Get the ascendants
			$test.=$ascendants?		' OR (s.lft <= c.lft AND s.rgt >= c.rgt)':''; 
			// Get the descendants
			$test.=$descendants ?	' OR (s.lft >= c.lft AND s.rgt <= c.rgt)':'';
			
			$query->leftJoin('#__categories AS s ON ' . $test);
			$query->where('s.id='.(int)$id);
		}
		
		// Deal with level min and max
		if (isset($options['level-min']))
		{
			$query->where('c.level >='.(int)$options['level-min']);
		}
		if (isset($options['level-max']))
		{
			$query->where('c.level <='.(int)$options['level-max']);
		}

		// i for item
		$query->leftJoin($db->nameQuote($this->_table).' AS i ON i.'.$db->nameQuote($this->_field).' = c.id ');
		$query->select('COUNT(i.'.$db->nameQuote($this->_key).') AS numitems');
		
		// Group by
		$query->group('c.id');

		// Get the results
		$db->setQuery($query);
		$results = $db->loadObjectList('id');
		
		if (count($results))
		{
			// foreach categories
			foreach($results as $result)
			{
				// Deal with parent_id
				if (empty($result->parent_id))
				{
					$result->id = 'root';
				}
				elseif($result->parent_id == 1)
				{
					$result->parent_id = 'root';
				}
				
				// Create the node
				if (!isset($this->_nodes[$result->id]))
				{
					// Convert the params field to an array.
					$registry = new JRegistry();
					$registry->loadJSON($result->params);
					$result->params = $registry->toArray();

					// Convert the metadata field to an array.
					$registry = new JRegistry();
					$registry->loadJSON($result->metadata);
					$result->metadata = $registry->toArray();
					
					// Create the JCategoryNode
					$this->_nodes[$result->id] = new JCategoryNode($result);
				}
				
				// Compute relationship between node and its parent
				if (!$this->_nodes[$result->id]->hasParent())
				{
					if (isset($this->_nodes[$result->parent_id]))
					{
						$this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
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
class JCategoryNode extends JNode
{
	/** @var int Primary key */
	public $id					= null;
	public $lft					= null;
	public $rgt					= null;
	public $ref_id				= null;
	public $parent_id			= null;
	/** @var int */
	public $extension			= null;
	public $lang				= null;
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
	/** @var int */
	public $numitems			= null;
	/** @var string */
	public $slug				= null;

	/**
	 * Class constructor
	 * @param $category
	 * @return unknown_type
	 */
	public function __construct($category = null)
	{
		if ($category)
		{
			$this->setProperties($category);
			return true;
		}
		return false;
	}
	/**
	 * Test if this node is the system node
	 *
	 * @return bool
	 */
	public function isSystem()
	{
		return $this->id=='root';
	}
}
