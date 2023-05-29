<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Tree\NodeInterface;
use Joomla\CMS\Tree\NodeTrait;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper class to load Categorytree
 *
 * @since  1.6
 */
class CategoryNode extends CMSObject implements NodeInterface
{
    use NodeTrait;

    /**
     * Primary key
     *
     * @var    integer
     * @since  1.6
     */
    public $id;

    /**
     * The id of the category in the asset table
     *
     * @var    integer
     * @since  1.6
     */
    public $asset_id;

    /**
     * The id of the parent of category in the asset table, 0 for category root
     *
     * @var    integer
     * @since  1.6
     */
    public $parent_id;

    /**
     * The lft value for this category in the category tree
     *
     * @var    integer
     * @since  1.6
     */
    public $lft;

    /**
     * The rgt value for this category in the category tree
     *
     * @var    integer
     * @since  1.6
     */
    public $rgt;

    /**
     * The depth of this category's position in the category tree
     *
     * @var    integer
     * @since  1.6
     */
    public $level;

    /**
     * The extension this category is associated with
     *
     * @var    integer
     * @since  1.6
     */
    public $extension;

    /**
     * The menu title for the category (a short name)
     *
     * @var    string
     * @since  1.6
     */
    public $title;

    /**
     * The alias for the category
     *
     * @var    string
     * @since  1.6
     */
    public $alias;

    /**
     * Description of the category.
     *
     * @var    string
     * @since  1.6
     */
    public $description;

    /**
     * The publication status of the category
     *
     * @var    boolean
     * @since  1.6
     */
    public $published;

    /**
     * Whether the category is or is not checked out
     *
     * @var    boolean
     * @since  1.6
     */
    public $checked_out;

    /**
     * The time at which the category was checked out
     *
     * @var    string
     * @since  1.6
     */
    public $checked_out_time;

    /**
     * Access level for the category
     *
     * @var    integer
     * @since  1.6
     */
    public $access;

    /**
     * JSON string of parameters
     *
     * @var    string
     * @since  1.6
     */
    public $params;

    /**
     * Metadata description
     *
     * @var    string
     * @since  1.6
     */
    public $metadesc;

    /**
     * Keywords for metadata
     *
     * @var    string
     * @since  1.6
     */
    public $metakey;

    /**
     * JSON string of other metadata
     *
     * @var    string
     * @since  1.6
     */
    public $metadata;

    /**
     * The ID of the user who created the category
     *
     * @var    integer
     * @since  1.6
     */
    public $created_user_id;

    /**
     * The time at which the category was created
     *
     * @var    string
     * @since  1.6
     */
    public $created_time;

    /**
     * The ID of the user who last modified the category
     *
     * @var    integer
     * @since  1.6
     */
    public $modified_user_id;

    /**
     * The time at which the category was modified
     *
     * @var    string
     * @since  1.6
     */
    public $modified_time;

    /**
     * Number of times the category has been viewed
     *
     * @var    integer
     * @since  1.6
     */
    public $hits;

    /**
     * The language for the category in xx-XX format
     *
     * @var    string
     * @since  1.6
     */
    public $language;

    /**
     * Number of items in this category or descendants of this category
     *
     * @var    integer
     * @since  1.6
     */
    public $numitems;

    /**
     * Slug for the category (used in URL)
     *
     * @var    string
     * @since  1.6
     */
    public $slug;

    /**
     * Array of  assets
     *
     * @var    array
     * @since  1.6
     */
    public $assets;

    /**
     * Path from root to this category
     *
     * @var    array
     * @since  1.6
     */
    protected $_path = [];

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
     * @var    Categories
     * @since  1.6
     */
    protected $_constructor;

    /**
     * Class constructor
     *
     * @param   array       $category     The category data.
     * @param   Categories  $constructor  The tree constructor.
     *
     * @since   1.6
     */
    public function __construct($category = null, $constructor = null)
    {
        if ($category) {
            $this->setProperties($category);

            if ($constructor) {
                $this->_constructor = $constructor;
            }
        }
    }

    /**
     * Set the parent of this category
     *
     * If the category already has a parent, the link is unset
     *
     * @param   NodeInterface  $parent  CategoryNode for the parent to be set or null
     *
     * @return  void
     *
     * @since   1.6
     */
    public function setParent(NodeInterface $parent)
    {
        if (!\is_null($this->_parent)) {
            $key = array_search($this, $this->_parent->_children);
            unset($this->_parent->_children[$key]);
        }

        $this->_parent = $parent;

        $this->_parent->_children[] = &$this;

        if (\count($this->_parent->_children) > 1) {
            end($this->_parent->_children);
            $this->_leftSibling                = prev($this->_parent->_children);
            $this->_leftSibling->_rightsibling = &$this;
        }

        if ($this->parent_id != 1) {
            $this->_path = $parent->getPath();
        }

        $this->_path[$this->id] = $this->id . ':' . $this->alias;
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
        if (!$this->_allChildrenloaded) {
            $temp = $this->_constructor->get($this->id, true);

            if ($temp) {
                $this->_children     = $temp->getChildren();
                $this->_leftSibling  = $temp->getSibling(false);
                $this->_rightSibling = $temp->getSibling(true);
                $this->setAllLoaded();
            }
        }

        if ($recursive) {
            $items = [];

            foreach ($this->_children as $child) {
                $items[] = $child;
                $items   = array_merge($items, $child->getChildren(true));
            }

            return $items;
        }

        return $this->_children;
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
        if (!$this->_allChildrenloaded) {
            $temp                = $this->_constructor->get($this->id, true);
            $this->_children     = $temp->getChildren();
            $this->_leftSibling  = $temp->getSibling(false);
            $this->_rightSibling = $temp->getSibling(true);
            $this->setAllLoaded();
        }

        if ($right) {
            return $this->_rightSibling;
        }

        return $this->_leftSibling;
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
        if (!($this->params instanceof Registry)) {
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
        if (!($this->metadata instanceof Registry)) {
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
     * @param   boolean  $modifiedUser  Returns the modified_user when set to true
     *
     * @return  \Joomla\CMS\User\User  A User object containing a userid
     *
     * @since   1.6
     */
    public function getAuthor($modifiedUser = false)
    {
        if ($modifiedUser) {
            return Factory::getUser($this->modified_user_id);
        }

        return Factory::getUser($this->created_user_id);
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

        foreach ($this->_children as $child) {
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
        if ($recursive) {
            $count = $this->numitems;

            foreach ($this->getChildren() as $child) {
                $count += $child->getNumItems(true);
            }

            return $count;
        }

        return $this->numitems;
    }

    /**
     * Serialize the node.
     *
     * @since   4.3.2
     */
    public function __serialize()
    {
        $vars = get_object_vars($this);

        // Store constructor as array of options.
        if ($this->_constructor) {
            $vars['_constructor'] = $this->_constructor->getOptions();
        }

        return $vars;
    }

    /**
     * Unserialize the node.
     *
     * @param   array  $data
     *
     * @since   4.3.2
     */
    public function __unserialize($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        // Restore constructor from array of options.
        if ($this->_constructor) {
            $this->_constructor = Categories::getInstance($this->_constructor['extension'], $this->_constructor);
        }
    }
}
