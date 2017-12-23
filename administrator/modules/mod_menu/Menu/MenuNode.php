<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Module\Menu\Administrator\Menu;

use Joomla\CMS\Uri\Uri;
use Joomla\Filter\OutputFilter;

defined('_JEXEC') or die;

/**
 * A Node for JAdminCssMenu
 *
 * @see    JAdminCssMenu
 * @since  1.5
 */
class MenuNode
{
	/**
	 * Node Title
	 *
	 * @var  string
	 */
	public $title = null;

	/**
	 * Node Id
	 *
	 * @var  string
	 */
	public $id = null;

	/**
	 * Node Link
	 *
	 * @var  string
	 */
	public $link = null;

	/**
	 * Link Target
	 *
	 * @var  string
	 */
	public $target = null;

	/**
	 * CSS Class for node
	 *
	 * @var  string
	 */
	public $class = null;

	/**
	 * Active Node?
	 *
	 * @var  boolean
	 */
	public $active = false;

	/**
	 * Parent node
	 *
	 * @var  MenuNode
	 */
	protected $_parent = null;

	/**
	 * Array of Children
	 *
	 * @var  array
	 */
	protected $_children = array();

	/**
	 * Constructor for the class.
	 *
	 * @param   string   $title      The title of the node
	 * @param   string   $link       The node link
	 * @param   string   $class      The CSS class for the node
	 * @param   boolean  $active     True if node is active, false otherwise
	 * @param   string   $target     The link target
	 * @param   string   $titleicon  The title icon for the node
	 */
	public function __construct($title, $link = null, $class = null, $active = false, $target = null, $titleicon = null)
	{
		$this->title  = $titleicon ? $title . $titleicon : $title;
		$this->link   = OutputFilter::ampReplace($link);
		$this->class  = $class;
		$this->active = $active;

		$this->id = null;

		if (!empty($link) && $link !== '#')
		{
			$uri    = new Uri($link);
			$params = $uri->getQuery(true);
			$parts  = array();

			foreach ($params as $value)
			{
				$parts[] = str_replace(array('.', '_'), '-', $value);
			}

			$this->id = implode('-', $parts);
		}

		$this->target = $target;
	}

	/**
	 * Add child to this node
	 *
	 * If the child already has a parent, the link is unset
	 *
	 * @param   MenuNode  &$child  The child to be added
	 *
	 * @return  void
	 */
	public function addChild(MenuNode &$child)
	{
		$child->setParent($this);
	}

	/**
	 * Set the parent of a this node
	 *
	 * If the node already has a parent, the link is unset
	 *
	 * @param   JMenuNode  &$parent  The JMenuNode for parent to be set or null
	 *
	 * @return  void
	 */
	public function setParent(MenuNode &$parent = null)
	{
		$hash = spl_object_hash($this);

		if (!is_null($this->_parent))
		{
			unset($this->_parent->_children[$hash]);
		}

		if (!is_null($parent))
		{
			$parent->_children[$hash] = &$this;
		}

		$this->_parent = &$parent;
	}

	/**
	 * Get the children of this node
	 *
	 * @return  array  The children
	 */
	public function &getChildren()
	{
		return $this->_children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  mixed  JMenuNode object with the parent or null for no parent
	 */
	public function &getParent()
	{
		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return  boolean  True if there are children
	 */
	public function hasChildren()
	{
		return (bool) count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  boolean  True if there is a parent
	 */
	public function hasParent()
	{
		return $this->getParent() != null;
	}
}
