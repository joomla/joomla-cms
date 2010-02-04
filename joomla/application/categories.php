<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

//jimport('joomla.base.tree');

require_once JPATH_SITE.DS.'libraries'.DS.'joomla'.DS.'base'.DS.'tree.php';

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
		$this->_treeloaded  = false;
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
		$classname = ucfirst(substr($extension,4)).'Categories';
		if (!class_exists($classname))
		{
			$path = JPATH_SITE.DS.'components'.DS.$extension.DS.'helpers'.DS.'category.php';
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
	 * @param $id
	 * @return JCategoryNode
	 */
	public function get($id)
	{
		$id = (int) $id;
		if ($id == 0)
		{
			return false;
		}
		if (!isset($this->_nodes[$id]))
		{
			if ($this->_load($id) === false)
			{
				throw new JException('Unable to load category: '.$id, 0000, E_ERROR, null, true);;
			}
		}
		if ($this->_nodes[$id] instanceof JCategoryNode)
		{
			return $this->_nodes[$id];
		} else {
			throw new JException('Unable to load category: '.$id, 0000, E_ERROR, null, true);
		}
	}

	protected function _load($id)
	{
		if ($this->_treeloaded)
		{
			return true;
		}
		/*
		 * TODO: should be made with JDatabaseQuery but I guess subqueries aren't available atm
		 */
		$db	= &JFactory::getDbo();
		$user = &JFactory::getUser();
		$query = 'SELECT c.*, COUNT(b.id) AS numitems, ' .
			' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as slug'.
			' FROM #__categories AS c' .
			' LEFT JOIN '.$this->_table.' AS b ON b.catid = c.id AND b.access IN ('.implode(',', $user->authorisedLevels()).')'.
			' WHERE c.id IN  ('.
					'SELECT distinct n.id' .
					' FROM `#__categories` AS n, `#__categories` AS p' .
					' WHERE n.lft BETWEEN p.lft AND p.rgt' .
					' AND n.extension =' .$db->Quote($this->_extension) .
					' AND n.access IN ('.implode(',', $user->authorisedLevels()).')' .
			')'.
			' GROUP BY c.id'.
			' ORDER BY c.lft';
		$db->setQuery($query);
		$results = $db->loadObjectList('id');

		if (count($results))
		{
			foreach($results as $result)
			{
				$this->_nodes[$result->id] = new JCategoryNode($result);
			}
		} else {
			$this->_nodes[$id] = false;
		}

		$this->_treeloaded = true;
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
	 * Adds a child to the current element of the Categorytree
	 * @param $node
	 * @return void
	 */
	public function addChild(&$node)
	{
		$node->setParent($this);
		$this->_children[] = & $node;
	}

	/**
	 * Returns the parent category of the current category
	 * @return JCategoryNode
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * Sets the parent for the current category
	 * @param $node
	 * @return void
	 */
	public function setParent(&$node)
	{
		$this->_parent = & $node;
	}

	/**
	 * Returns true if the category has children
	 * @return boolean
	 */
	public function hasChildren()
	{
		return count($this->_children);
	}

	/**
	 * Returns the children of the Category
	 * @return array
	 */
	public function getChildren()
	{
		return $this->_children;
	}
}