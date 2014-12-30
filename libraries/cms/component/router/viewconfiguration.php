<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * View-configuration class for the advanced component router
 *
 * @since  3.4
 */
class JComponentRouterViewconfiguration
{
	/**
	 * Name of the view
	 * 
	 * @var string
	 * @since 3.4
	 */
	public $name;

	/**
	 * Identifier of the view
	 * 
	 * @var string
	 * @since 3.4
	 */
	public $view;

	/**
	 * Key of the view
	 * 
	 * @var string
	 * @since 3.4
	 */
	public $key = false;

	/**
	 * Parentview of this one
	 * 
	 * @var JComponentRouterViewconfiguration
	 * @since 3.4
	 */
	public $parent = false;

	/**
	 * Key of the parentview
	 * 
	 * @var string
	 * @since 3.4
	 */
	public $parent_key = false;

	/**
	 * Is this view nestable?
	 * 
	 * @var bool
	 * @since 3.4
	 */
	public $nestable = false;

	/**
	 * Layouts that are supported by this view
	 * 
	 * @var array
	 * @since 3.4
	 */
	public $layouts = array('default');

	/**
	 * Child-views of this view
	 * 
	 * @var array
	 * @since 3.4
	 */
	public $children = array();

	/**
	 * Path of views from this one to the root view
	 * 
	 * @var array
	 * @since 3.4
	 */
	public $path = array();

	/**
	 * Constructor for the View-configuration class
	 * 
	 * @param   string  $name  Name of the view
	 * 
	 * @since 3.4
	 */
	public function __construct($name)
	{
		$this->name = $name;
		$this->view = $name;
		$this->path[] = $name;
	}

	/**
	 * Set the name of the view
	 * The name is different to the view-name. One view can be injected with
	 * several names and the same view-name into the routing system
	 * 
	 * @param   string  $name  Name of the view
	 * 
	 * @return  JComponentRouterViewconfiguration  This object for chaining
	 * @since 3.4
	 */
	public function setName($name)
	{
		$this->name = $name;

		array_pop($this->path);
		$this->path[] = $name;

		return $this;
	}

	/**
	 * Set the view-name of the view
	 * 
	 * @param   string  $name  Name of the view-name
	 * 
	 * @return  JComponentRouterViewconfiguration  This object for chaining
	 * @since 3.4
	 */
	public function setViewName($name)
	{
		$this->view = $name;

		return $this;
	}

	/**
	 * Set the key-identifier for the view
	 * 
	 * @param   string  $key  Key of the view
	 * 
	 * @return  JComponentRouterViewconfiguration  This object for chaining
	 * @since 3.4
	 */
	public function setViewKey($key)
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
	 * @since 3.4
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

			unset($this->parent->child_key);
		}

		$this->parent = $parent;
		$parent->children[] = $this;

		$this->path = $parent->path;
		$this->path[] = $this->name;

		$this->parent_key = $parent_key;

		if ($parent_key)
		{
			$parent->child_key = $parent_key;
		}

		return $this;
	}

	/**
	 * Set if this view is nestable or not
	 * 
	 * @param   bool  $isNestable  If set to true, the view is nestable
	 * 
	 * @return  JComponentRouterViewconfiguration  This object for chaining
	 * @since 3.4
	 */
	public function setNestable($isNestable = true)
	{
		$this->nestable = $isNestable;

		return $this;
	}

	/**
	 * Add a layout to this view
	 * 
	 * @param   string  $layout  Layouts that this view supports
	 * 
	 * @return  JComponentRouterViewconfiguration  This object for chaining
	 * @since 3.4
	 */
	public function addLayout($layout)
	{
		array_push($this->layouts, $layout);
		$this->layouts = array_unique($this->layouts);

		return $this;
	}
	
	/**
	 * Remove a layout from this view
	 * 
	 * @param   string  $layout  Layouts that this view supports
	 * 
	 * @return  JComponentRouterViewconfiguration  This object for chaining
	 * @since 3.4
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
