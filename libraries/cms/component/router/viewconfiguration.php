<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * View-configuration class for the view-based component router
 *
 * @since  3.5
 */
class JComponentRouterViewconfiguration
{
	/**
	 * Name of the view
	 *
	 * @var    string
	 * @since  3.5
	 */
	public $name;

	/**
	 * Key of the view
	 *
	 * @var    string
	 * @since  3.5
	 */
	public $key = false;

	/**
	 * Parentview of this one
	 *
	 * @var    JComponentRouterViewconfiguration
	 * @since  3.5
	 */
	public $parent = false;

	/**
	 * Key of the parentview
	 *
	 * @var    string
	 * @since  3.5
	 */
	public $parent_key = false;

	/**
	 * Is this view nestable?
	 *
	 * @var    bool
	 * @since  3.5
	 */
	public $nestable = false;

	/**
	 * Layouts that are supported by this view
	 *
	 * @var    array
	 * @since  3.5
	 */
	public $layouts = array('default');

	/**
	 * Child-views of this view
	 *
	 * @var    JComponentRouterViewconfiguration[]
	 * @since  3.5
	 */
	public $children = array();

	/**
	 * Keys used for this parent view by the child views
	 *
	 * @var    array
	 * @since  3.5
	 */
	public $child_keys = array();

	/**
	 * Path of views from this one to the root view
	 *
	 * @var    array
	 * @since  3.5
	 */
	public $path = array();

	/**
	 * Constructor for the View-configuration class
	 *
	 * @param   string  $name  Name of the view
	 *
	 * @since   3.5
	 */
	public function __construct($name)
	{
		$this->name   = $name;
		$this->path[] = $name;
	}

	/**
	 * Set the name of the view
	 *
	 * @param   string  $name  Name of the view
	 *
	 * @return  JComponentRouterViewconfiguration  This object for chaining
	 *
	 * @since   3.5
	 */
	public function setName($name)
	{
		$this->name = $name;

		array_pop($this->path);
		$this->path[] = $name;

		return $this;
	}

	/**
	 * Set the key-identifier for the view
	 *
	 * @param   string  $key  Key of the view
	 *
	 * @return  JComponentRouterViewconfiguration  This object for chaining
	 *
	 * @since   3.5
	 */
	public function setKey($key)
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * Set the parent view of this view
	 *
	 * @param   JComponentRouterViewconfiguration  $parent      Parent view object
	 * @param   string                             $parent_key  Key of the parent view in this context
	 *
	 * @return  JComponentRouterViewconfiguration  This object for chaining
	 *
	 * @since   3.5
	 */
	public function setParent(JComponentRouterViewconfiguration $parent, $parent_key = false)
	{
		if ($this->parent)
		{
			$key = array_search($this, $this->parent->children);

			if ($key !== false)
			{
				unset($this->parent->children[$key]);
			}

			if ($this->parent_key)
			{
				$child_key = array_search($this->parent_key, $this->parent->child_keys);
				unset($this->parent->child_keys[$child_key]);
			}
		}

		$this->parent       = $parent;
		$parent->children[] = $this;

		$this->path   = $parent->path;
		$this->path[] = $this->name;

		$this->parent_key = $parent_key;

		if ($parent_key)
		{
			$parent->child_keys[] = $parent_key;
		}

		return $this;
	}

	/**
	 * Set if this view is nestable or not
	 *
	 * @param   bool  $isNestable  If set to true, the view is nestable
	 *
	 * @return  JComponentRouterViewconfiguration  This object for chaining
	 *
	 * @since   3.5
	 */
	public function setNestable($isNestable = true)
	{
		$this->nestable = (bool) $isNestable;

		return $this;
	}

	/**
	 * Add a layout to this view
	 *
	 * @param   string  $layout  Layouts that this view supports
	 *
	 * @return  JComponentRouterViewconfiguration  This object for chaining
	 *
	 * @since   3.5
	 */
	public function addLayout($layout)
	{
		$this->layouts[] = $layout;
		$this->layouts   = array_unique($this->layouts);

		return $this;
	}

	/**
	 * Remove a layout from this view
	 *
	 * @param   string  $layout  Layouts that this view supports
	 *
	 * @return  JComponentRouterViewconfiguration  This object for chaining
	 *
	 * @since   3.5
	 */
	public function removeLayout($layout)
	{
		$key = array_search($layout, $this->layouts);

		if ($key !== false)
		{
			unset($this->layouts[$key]);
		}

		return $this;
	}
}
