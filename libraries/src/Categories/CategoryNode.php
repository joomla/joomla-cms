<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Helper class to load Categorytree
 *
 * @since  1.6
 */
class CategoryNode extends \JObject
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
	 * Key words for metadata
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $metakey = null;

	/**
	 * JSON string of other metadata
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
	 * @var    CategoryNode
	 * @since  1.6
	 */
	protected $_parent = null;

	/**
	 * Array of Children
	 *
	 * @var    CategoryNode[]
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
	 * @var    CategoryNode
	 * @since  1.6
	 */
	protected $_leftSibling = null;

	/**
	 * Category right of this one
	 *
	 * @var    CategoryNode
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
	 * @var    CategoryNode
	 * @since  1.6
	 */
	protected $_constructor = null;

	/**
	 * Class constructor
	 *
	 * @param   array         $category     The category data.
	 * @param   CategoryNode  $constructor  The tree constructor.
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
	 * @param   CategoryNode|null  $parent  CategoryNode for the parent to be set or null
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function setParent($parent)
	{
		if ($parent instanceof CategoryNode || is_null($parent))
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
	 * @param   CategoryNode  $child  The child to be added.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function addChild($child)
	{
		if ($child instanceof CategoryNode)
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
	 * @return  CategoryNode[]  The children
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
	 * @return  CategoryNode
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
	 * @param   CategoryNode  $sibling  CategoryNode object for the sibling
	 * @param   boolean       $right    If set to false, the sibling is the left one
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
	 * @return  CategoryNode|null  CategoryNode object with the sibling information or null if there is no sibling on that side.
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
	 * @return  \JUser  A \JUser object containing a userid
	 *
	 * @since   1.6
	 */
	public function getAuthor($modified_user = false)
	{
		if ($modified_user)
		{
			return \JFactory::getUser($this->modified_user_id);
		}

		return \JFactory::getUser($this->created_user_id);
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
