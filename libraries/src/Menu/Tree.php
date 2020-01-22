<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Menu;

defined('JPATH_PLATFORM') or die;

/**
 * Menu Tree class to represent a menu tree hierarchy
 *
 * @since  3.8.0
 */
class Tree
{
	/**
	 * The root menu node
	 *
	 * @var  Node
	 *
	 * @since   3.8.0
	 */
	protected $root = null;

	/**
	 * The current working menu node
	 *
	 * @var  Node
	 *
	 * @since   3.8.0
	 */
	protected $current = null;

	/**
	 * The CSS style array
	 *
	 * @var  string[]
	 *
	 * @since   3.8.0
	 */
	protected $css = array();

	/**
	 * Constructor
	 *
	 * @since   3.8.0
	 */
	public function __construct()
	{
		$this->root    = new Node;
		$this->current = $this->root;
	}

	/**
	 * Get the root node
	 *
	 * @return  Node
	 *
	 * @since   3.8.0
	 */
	public function getRoot()
	{
		return $this->root;
	}

	/**
	 * Get the current node
	 *
	 * @return  Node
	 *
	 * @since   3.8.0
	 */
	public function getCurrent()
	{
		return $this->current;
	}

	/**
	 * Get the current node
	 *
	 * @param   Node  $node  The node to be set as current
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function setCurrent($node)
	{
		if ($node)
		{
			$this->current = $node;
		}
	}

	/**
	 * Method to get the parent and set it as active optionally
	 *
	 * @param   bool  $setCurrent  Set that parent as the current node for further working
	 *
	 * @return  Node
	 *
	 * @since   3.8.0
	 */
	public function getParent($setCurrent = true)
	{
		$parent = $this->current->getParent();

		if ($setCurrent)
		{
			$this->setCurrent($parent);
		}

		return $parent;
	}

	/**
	 * Method to reset the working pointer to the root node and optionally clear all menu nodes
	 *
	 * @param   bool  $clear  Whether to clear the existing menu items or just reset the pointer to root element
	 *
	 * @return  Node  The root node
	 *
	 * @since   3.8.0
	 */
	public function reset($clear = false)
	{
		if ($clear)
		{
			$this->root = new Node;
			$this->css  = array();
		}

		$this->current = $this->root;

		return  $this->current;
	}

	/**
	 * Method to add a child
	 *
	 * @param   Node  $node        The node to process
	 * @param   bool  $setCurrent  Set this new child as the current node for further working
	 *
	 * @return  Node  The newly added node
	 *
	 * @since   3.8.0
	 */
	public function addChild(Node $node, $setCurrent = false)
	{
		$this->current->addChild($node);

		if ($setCurrent)
		{
			$this->setCurrent($node);
		}

		return $node;
	}

	/**
	 * Method to get the CSS class name for an icon identifier or create one if
	 * a custom image path is passed as the identifier
	 *
	 * @return  string	CSS class name
	 *
	 * @since   3.8.0
	 */
	public function getIconClass()
	{
		static $classes = array();

		$identifier = $this->current->get('class');

		// Top level is special
		if (trim($identifier) == '' || !$this->current->hasParent())
		{
			return null;
		}

		if (!isset($classes[$identifier]))
		{
			// We were passed a class name
			if (substr($identifier, 0, 6) == 'class:')
			{
				$class = substr($identifier, 6);
			}
			// We were passed background icon url. Build the CSS class for the icon
			else
			{
				$class = preg_replace('#\.[^.]*$#', '', basename($identifier));
				$class = preg_replace('#\.\.[^A-Za-z0-9\.\_\- ]#', '', $class);

				if ($class)
				{
					$this->css[] = ".menu-$class {background: url($identifier) no-repeat;}";
				}
			}

			$classes[$identifier] = "menu-$class";
		}

		return $classes[$identifier];
	}

	/**
	 * Get the CSS declarations for this tree
	 *
	 * @return  string[]
	 *
	 * @since   3.8.0
	 */
	public function getCss()
	{
		return $this->css;
	}
}
