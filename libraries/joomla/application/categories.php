<?php
/**
 * @version		$Id:categorytree.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.base.tree');
/**
 * JCategories Class.
 *
 * @package 	Joomla.Framework
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
	 * Class constructor
	 *
	 * @access public
	 * @return boolean True on success
	 */
	public function __construct($options)
	{
		$this->_extension 	= $options['extension'];
		$this->_table		= $options['table'];
		$this->_options		= $options;
		return true;
	}

	/**
	 * Returns a reference to a JCategories object
	 *
	 * This method must be invoked as:
	 * 		<pre>  $categories = &JCategories::getInstance();</pre>
	 *
	 * @param $extension Name of the categories extension
	 * @param $options An array of options
	 * @return object
	 */
	public static function &getInstance($extension, $options = array())
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
				require_once($path);
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
			$this->_load($id);
		}
		if ($this->_nodes[$id] instanceof JCategoryNode)
		{
			return $this->_nodes[$id];
		} else {
			throw new JException('Unable to load category: '.$id, 0000, E_ERROR, $info, true);
		}
	}

	protected function _load($id)
	{
		$db	= &JFactory::getDbo();
		$user = &JFactory::getUser();
		$subquery = 'SELECT c.id, c.lft, c.rgt'.
			' FROM #__categories AS c'.
			' JOIN #__categories AS cp ON cp.lft >= c.lft AND c.rgt >= cp.rgt'.
			' WHERE c.extension = '.$db->Quote($this->_extension).
			' AND cp.id = '.$id.' AND c.parent_id = 0';

		$query = 'SELECT c.*, COUNT(b.id) AS numitems, ' .
			' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as slug'.
			' FROM #__categories AS c' .
			' LEFT JOIN '.$this->_table.' AS b ON b.catid = c.id ';
		if ($id != 0)
		{
			$query .= ' JOIN ('.$subquery.') AS cp ON c.lft >= cp.lft AND c.rgt <= cp.rgt';
		}
		$query .= ' WHERE c.extension = '.$db->Quote($this->_extension).
			' AND c.access IN ('.implode(',', $user->authorisedLevels()).')'.
			' GROUP BY c.id'.
			' ORDER BY c.lft';
		$db->setQuery($query);
		$results = $db->loadObjectList();

		if (count($results))
		{
			foreach($results as $result)
			{
				$this->_nodes[$result->id] = new JCategoryNode($result);
			}
		} else {
			$this->_nodes[$id] = false;
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
	public $lft					= null;
	public $rgt					= null;
	public $ref_id				= null;
	public $parent_id			= null;
	/** @var int */
	public $extension			= null;
	public $lang					= null;
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
	public $checked_out_time		= 0;
	/** @var int */
	public $access				= null;
	/** @var string */
	public $params				= null;
	/** @var int */
	public $numitems				= null;
	/** @var string */
	public $slug					= null;

	protected $_parent				= null;

	protected $_children			= array();

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
			if ($this->parent_id > 0)
			{
				$categoryTree = JCategories::getInstance($this->extension);
				$parentNode = &$categoryTree->get($this->parent_id);
				$parentNode->addChild(&$this);
			}
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
	public function &getParent()
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
	public function &getChildren()
	{
		return $this->_children;
	}
}