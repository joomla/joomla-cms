<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Categories
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * JCategories Class.
 *
 * @since  1.6
 */
class JCategories
{
	/**
	 * Array to hold the object instances
	 *
	 * @var    JCategories[]
	 * @since  1.6
	 */
	public static $instances = array();

	/**
	 * Array of category nodes
	 *
	 * @var    JCategoryNode[]
	 * @since  1.6
	 */
	protected $_nodes;

	/**
	 * Array of checked categories -- used to save values when _nodes are null
	 *
	 * @var    boolean[]
	 * @since  1.6
	 */
	protected $_checkedCategories;

	/**
	 * Name of the extension the categories belong to
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $_extension = null;

	/**
	 * Name of the linked content table to get category content count
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $_table = null;

	/**
	 * Name of the category field
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $_field = null;

	/**
	 * Name of the key field
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $_key = null;

	/**
	 * Name of the items state field
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $_statefield = null;

	/**
	 * Array of options
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $_options = null;

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.6
	 */
	public function __construct($options)
	{
		$this->_extension = $options['extension'];
		$this->_table = $options['table'];
		$this->_field = (isset($options['field']) && $options['field']) ? $options['field'] : 'catid';
		$this->_key = (isset($options['key']) && $options['key']) ? $options['key'] : 'id';
		$this->_statefield = (isset($options['statefield'])) ? $options['statefield'] : 'state';
		$options['access'] = (isset($options['access'])) ? $options['access'] : 'true';
		$options['published'] = (isset($options['published'])) ? $options['published'] : 1;
		$options['countItems'] = (isset($options['countItems'])) ? $options['countItems'] : 0;
		$options['currentlang'] = JLanguageMultilang::isEnabled() ? JFactory::getLanguage()->getTag() : 0;
		$this->_options = $options;

		return true;
	}

	/**
	 * Returns a reference to a JCategories object
	 *
	 * @param   string  $extension  Name of the categories extension
	 * @param   array   $options    An array of options
	 *
	 * @return  JCategories|boolean  JCategories object on success, boolean false if an object does not exist
	 *
	 * @since   1.6
	 */
	public static function getInstance($extension, $options = array())
	{
		$hash = md5($extension . serialize($options));

		if (isset(self::$instances[$hash]))
		{
			return self::$instances[$hash];
		}

		$parts = explode('.', $extension);
		$component = 'com_' . strtolower($parts[0]);
		$section = count($parts) > 1 ? $parts[1] : '';
		$classname = ucfirst(substr($component, 4)) . ucfirst($section) . 'Categories';

		if (!class_exists($classname))
		{
			$path = JPATH_SITE . '/components/' . $component . '/helpers/category.php';

			JLoader::register($classname, $path);

			if (!class_exists($classname))
			{
				return false;
			}
		}

		self::$instances[$hash] = new $classname($options);

		return self::$instances[$hash];
	}

	/**
	 * Loads a specific category and all its children in a JCategoryNode object
	 *
	 * @param   mixed    $id         an optional id integer or equal to 'root'
	 * @param   boolean  $forceload  True to force  the _load method to execute
	 *
	 * @return  JCategoryNode|null|boolean  JCategoryNode object or null if $id is not valid
	 *
	 * @since   1.6
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

		// If this $id has not been processed yet, execute the _load method
		if ((!isset($this->_nodes[$id]) && !isset($this->_checkedCategories[$id])) || $forceload)
		{
			$this->_load($id);
		}

		// If we already have a value in _nodes for this $id, then use it.
		if (isset($this->_nodes[$id]))
		{
			return $this->_nodes[$id];
		}
		// If we processed this $id already and it was not valid, then return null.
		elseif (isset($this->_checkedCategories[$id]))
		{
			return;
		}

		return false;
	}

	/**
	 * Load method
	 *
	 * @param   integer  $id  Id of category to load
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function _load($id)
	{
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$extension = $this->_extension;

		// Record that has this $id has been checked
		$this->_checkedCategories[$id] = true;

		$query = $db->getQuery(true);

		// Right join with c for category
		$query->select('c.id, c.asset_id, c.access, c.alias, c.checked_out, c.checked_out_time,
			c.created_time, c.created_user_id, c.description, c.extension, c.hits, c.language, c.level,
			c.lft, c.metadata, c.metadesc, c.metakey, c.modified_time, c.note, c.params, c.parent_id,
			c.path, c.published, c.rgt, c.title, c.modified_user_id, c.version');
		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('c.alias', '!=', '0');
		$case_when .= ' THEN ';
		$c_id = $query->castAsChar('c.id');
		$case_when .= $query->concatenate(array($c_id, 'c.alias'), ':');
		$case_when .= ' ELSE ';
		$case_when .= $c_id . ' END as slug';
		$query->select($case_when)
			->from('#__categories as c')
			->where('(c.extension=' . $db->quote($extension) . ' OR c.extension=' . $db->quote('system') . ')');

		if ($this->_options['access'])
		{
			$query->where('c.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		}

		if ($this->_options['published'] == 1)
		{
			$query->where('c.published = 1');

			$subQuery = ' (SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ' .
				'ON cat.lft BETWEEN parent.lft AND parent.rgt WHERE parent.extension = ' . $db->quote($extension) .
				' AND parent.published != 1 GROUP BY cat.id) ';
			$query->join('LEFT', $subQuery . 'AS badcats ON badcats.id = c.id')
				->where('badcats.id is null');
		}

		$query->order('c.lft');

		// Note: s for selected id
		if ($id != 'root')
		{
			// Get the selected category
			$query->where('s.id=' . (int) $id);

			if ($app->isClient('site') && JLanguageMultilang::isEnabled())
			{
				$query->join('LEFT', '#__categories AS s ON (s.lft < c.lft AND s.rgt > c.rgt AND c.language in (' . $db->quote(JFactory::getLanguage()->getTag())
					. ',' . $db->quote('*') . ')) OR (s.lft >= c.lft AND s.rgt <= c.rgt)');
			}
			else
			{
				$query->join('LEFT', '#__categories AS s ON (s.lft <= c.lft AND s.rgt >= c.rgt) OR (s.lft > c.lft AND s.rgt < c.rgt)');
			}
		}
		else
		{
			if ($app->isClient('site') && JLanguageMultilang::isEnabled())
			{
				$query->where('c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
			}
		}

		// Note: i for item
		if ($this->_options['countItems'] == 1)
		{
			$queryjoin = $db->quoteName($this->_table) . ' AS i ON i.' . $db->quoteName($this->_field) . ' = c.id';

			if ($this->_options['published'] == 1)
			{
				$queryjoin .= ' AND i.' . $this->_statefield . ' = 1';
			}

			if ($this->_options['currentlang'] !== 0)
			{
				$queryjoin .= ' AND (i.language = ' . $db->quote('*') . ' OR i.language = ' . $db->quote($this->_options['currentlang']) . ')';
			}

			$query->join('LEFT', $queryjoin);
			$query->select('COUNT(i.' . $db->quoteName($this->_key) . ') AS numitems');
		}

		// Group by
		$query->group(
			'c.id, c.asset_id, c.access, c.alias, c.checked_out, c.checked_out_time,
			 c.created_time, c.created_user_id, c.description, c.extension, c.hits, c.language, c.level,
			 c.lft, c.metadata, c.metadesc, c.metakey, c.modified_time, c.note, c.params, c.parent_id,
			 c.path, c.published, c.rgt, c.title, c.modified_user_id, c.version'
		);

		// Get the results
		$db->setQuery($query);
		$results = $db->loadObjectList('id');
		$childrenLoaded = false;

		if (count($results))
		{
			// Foreach categories
			foreach ($results as $result)
			{
				// Deal with root category
				if ($result->id == 1)
				{
					$result->id = 'root';
				}

				// Deal with parent_id
				if ($result->parent_id == 1)
				{
					$result->parent_id = 'root';
				}

				// Create the node
				if (!isset($this->_nodes[$result->id]))
				{
					// Create the JCategoryNode and add to _nodes
					$this->_nodes[$result->id] = new JCategoryNode($result, $this);

					// If this is not root and if the current node's parent is in the list or the current node parent is 0
					if ($result->id != 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id == 1))
					{
						// Compute relationship between node and its parent - set the parent in the _nodes field
						$this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
					}

					// If the node's parent id is not in the _nodes list and the node is not root (doesn't have parent_id == 0),
					// then remove the node from the list
					if (!(isset($this->_nodes[$result->parent_id]) || $result->parent_id == 0))
					{
						unset($this->_nodes[$result->id]);
						continue;
					}

					if ($result->id == $id || $childrenLoaded)
					{
						$this->_nodes[$result->id]->setAllLoaded();
						$childrenLoaded = true;
					}
				}
				elseif ($result->id == $id || $childrenLoaded)
				{
					// Create the JCategoryNode
					$this->_nodes[$result->id] = new JCategoryNode($result, $this);

					if ($result->id != 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id))
					{
						// Compute relationship between node and its parent
						$this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
					}

					// If the node's parent id is not in the _nodes list and the node is not root (doesn't have parent_id == 0),
					// then remove the node from the list
					if (!(isset($this->_nodes[$result->parent_id]) || $result->parent_id == 0))
					{
						unset($this->_nodes[$result->id]);
						continue;
					}

					if ($result->id == $id || $childrenLoaded)
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
 *
 * @since  1.6
 */
class JCategoryNode extends JObject
{
	/**
	 * Primary key
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $id = null;

	/**
	 * The id of the category in the asset table
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $asset_id = null;

	/**
	 * The id of the parent of category in the asset table, 0 for category root
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $parent_id = null;

	/**
	 * The lft value for this category in the category tree
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $lft = null;

	/**
	 * The rgt value for this category in the category tree
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $rgt = null;

	/**
	 * The depth of this category's position in the category tree
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $level = null;

	/**
	 * The extension this category is associated with
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $extension = null;

	/**
	 * The menu title for the category (a short name)
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $title = null;

	/**
	 * The the alias for the category
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $alias = null;

	/**
	 * Description of the category.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $description = null;

	/**
	 * The publication status of the category
	 *
	 * @var    boolean
	 * @since  1.6
	 */
	public $published = null;

	/**
	 * Whether the category is or is not checked out
	 *
	 * @var    boolean
	 * @since  1.6
	 */
	public $checked_out = 0;

	/**
	 * The time at which the category was checked out
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $checked_out_time = 0;

	/**
	 * Access level for the category
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $access = null;

	/**
	 * JSON string of parameters
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $params = null;

	/**
	 * Metadata description
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $metadesc = null;

	/**
	 * Key words for meta data
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $metakey = null;

	/**
	 * JSON string of other meta data
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $metadata = null;

	/**
	 * The ID of the user who created the category
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $created_user_id = null;

	/**
	 * The time at which the category was created
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $created_time = null;

	/**
	 * The ID of the user who last modified the category
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $modified_user_id = null;

	/**
	 * The time at which the category was modified
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $modified_time = null;

	/**
	 * Nmber of times the category has been viewed
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $hits = null;

	/**
	 * The language for the category in xx-XX format
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $language = null;

	/**
	 * Number of items in this category or descendants of this category
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $numitems = null;

	/**
	 * Number of children items
	 *
	 * @var    integer
	 * @since  1.6
	 */
	public $childrennumitems = null;

	/**
	 * Slug fo the category (used in URL)
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $slug = null;

	/**
	 * Array of  assets
	 *
	 * @var    array
	 * @since  1.6
	 */
	public $assets = null;

	/**
	 * Parent Category object
	 *
	 * @var    JCategoryNode
	 * @since  1.6
	 */
	protected $_parent = null;

	/**
	 * Array of Children
	 *
	 * @var    JCategoryNode[]
	 * @since  1.6
	 */
	protected $_children = array();

	/**
	 * Path from root to this category
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $_path = array();

	/**
	 * Category left of this one
	 *
	 * @var    JCategoryNode
	 * @since  1.6
	 */
	protected $_leftSibling = null;

	/**
	 * Category right of this one
	 *
	 * @var    JCategoryNode
	 * @since  1.6
	 */
	protected $_rightSibling = null;

	/**
	 * Flag if all children have been loaded
	 *
	 * @var    boolean
	 * @since  1.6
	 */
	protected $_allChildrenloaded = false;

	/**
	 * Constructor of this tree
	 *
	 * @var    JCategoryNode
	 * @since  1.6
	 */
	protected $_constructor = null;

	/**
	 * Class constructor
	 *
	 * @param   array          $category     The category data.
	 * @param   JCategoryNode  $constructor  The tree constructor.
	 *
	 * @since   1.6
	 */
	public function __construct($category = null, $constructor = null)
	{
		if ($category)
		{
			$this->setProperties($category);

			if ($constructor)
			{
				$this->_constructor = $constructor;
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
	 * @param   JCategoryNode|null  $parent  JCategoryNode for the parent to be set or null
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function setParent($parent)
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

			$this->_parent = $parent;

			if ($this->id != 'root')
			{
				if ($this->parent_id != 1)
				{
					$this->_path = $parent->getPath();
				}

				$this->_path[$this->id] = $this->id . ':' . $this->alias;
			}

			if (count($parent->_children) > 1)
			{
				end($parent->_children);
				$this->_leftSibling = prev($parent->_children);
				$this->_leftSibling->_rightsibling = & $this;
			}
		}
	}

	/**
	 * Add child to this node
	 *
	 * If the child already has a parent, the link is unset
	 *
	 * @param   JCategoryNode  $child  The child to be added.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function addChild($child)
	{
		if ($child instanceof JCategoryNode)
		{
			$child->setParent($this);
		}
	}

	/**
	 * Remove a specific child
	 *
	 * @param   integer  $id  ID of a category
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function removeChild($id)
	{
		$key = array_search($this, $this->_parent->_children);
		unset($this->_parent->_children[$key]);
	}

	/**
	 * Get the children of this node
	 *
	 * @param   boolean  $recursive  False by default
	 *
	 * @return  JCategoryNode[]  The children
	 *
	 * @since   1.6
	 */
	public function &getChildren($recursive = false)
	{
		if (!$this->_allChildrenloaded)
		{
			$temp = $this->_constructor->get($this->id, true);

			if ($temp)
			{
				$this->_children = $temp->getChildren();
				$this->_leftSibling = $temp->getSibling(false);
				$this->_rightSibling = $temp->getSibling(true);
				$this->setAllLoaded();
			}
		}

		if ($recursive)
		{
			$items = array();

			foreach ($this->_children as $child)
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
	 * @return  JCategoryNode
	 *
	 * @since   1.6
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return  boolean  True if there is a child
	 *
	 * @since   1.6
	 */
	public function hasChildren()
	{
		return count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  boolean  True if there is a parent
	 *
	 * @since   1.6
	 */
	public function hasParent()
	{
		return $this->getParent() != null;
	}

	/**
	 * Function to set the left or right sibling of a category
	 *
	 * @param   JCategoryNode  $sibling  JCategoryNode object for the sibling
	 * @param   boolean        $right    If set to false, the sibling is the left one
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function setSibling($sibling, $right = true)
	{
		if ($right)
		{
			$this->_rightSibling = $sibling;
		}
		else
		{
			$this->_leftSibling = $sibling;
		}
	}

	/**
	 * Returns the right or left sibling of a category
	 *
	 * @param   boolean  $right  If set to false, returns the left sibling
	 *
	 * @return  JCategoryNode|null  JCategoryNode object with the sibling information or null if there is no sibling on that side.
	 *
	 * @since   1.6
	 */
	public function getSibling($right = true)
	{
		if (!$this->_allChildrenloaded)
		{
			$temp = $this->_constructor->get($this->id, true);
			$this->_children = $temp->getChildren();
			$this->_leftSibling = $temp->getSibling(false);
			$this->_rightSibling = $temp->getSibling(true);
			$this->setAllLoaded();
		}

		if ($right)
		{
			return $this->_rightSibling;
		}
		else
		{
			return $this->_leftSibling;
		}
	}

	/**
	 * Returns the category parameters
	 *
	 * @return  Registry
	 *
	 * @since   1.6
	 */
	public function getParams()
	{
		if (!($this->params instanceof Registry))
		{
			$this->params = new Registry($this->params);
		}

		return $this->params;
	}

	/**
	 * Returns the category metadata
	 *
	 * @return  Registry  A Registry object containing the metadata
	 *
	 * @since   1.6
	 */
	public function getMetadata()
	{
		if (!($this->metadata instanceof Registry))
		{
			$this->metadata = new Registry($this->metadata);
		}

		return $this->metadata;
	}

	/**
	 * Returns the category path to the root category
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * Returns the user that created the category
	 *
	 * @param   boolean  $modified_user  Returns the modified_user when set to true
	 *
	 * @return  JUser  A JUser object containing a userid
	 *
	 * @since   1.6
	 */
	public function getAuthor($modified_user = false)
	{
		if ($modified_user)
		{
			return JFactory::getUser($this->modified_user_id);
		}

		return JFactory::getUser($this->created_user_id);
	}

	/**
	 * Set to load all children
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function setAllLoaded()
	{
		$this->_allChildrenloaded = true;

		foreach ($this->_children as $child)
		{
			$child->setAllLoaded();
		}
	}

	/**
	 * Returns the number of items.
	 *
	 * @param   boolean  $recursive  If false number of children, if true number of descendants
	 *
	 * @return  integer  Number of children or descendants
	 *
	 * @since   1.6
	 */
	public function getNumItems($recursive = false)
	{
		if ($recursive)
		{
			$count = $this->numitems;

			foreach ($this->getChildren() as $child)
			{
				$count = $count + $child->getNumItems(true);
			}

			return $count;
		}

		return $this->numitems;
	}
}
