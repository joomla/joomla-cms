<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * View-configuration class for the advanced component router
 *
 * @since  3.4
 */
class JComponentRouterViewconfiguration
{
	/**
	 * Views of the component
	 * 
	 * @var array
	 * @since 3.4
	 */
	public $name;

	/**
	 * Views of the component
	 * 
	 * @var array
	 * @since 3.4
	 */
	public $view;

	/**
	 * Views of the component
	 * 
	 * @var array
	 * @since 3.4
	 */
	public $key = false;

	/**
	 * Views of the component
	 * 
	 * @var array
	 * @since 3.4
	 */
	public $parent = false;

	/**
	 * Views of the component
	 * 
	 * @var array
	 * @since 3.4
	 */
	public $parent_key = false;

	/**
	 * Views of the component
	 * 
	 * @var array
	 * @since 3.4
	 */
	public $nestable = false;

	/**
	 * Views of the component
	 * 
	 * @var array
	 * @since 3.4
	 */
	public $layouts = array('default');

	/**
	 * Views of the component
	 * 
	 * @var array
	 * @since 3.4
	 */
	public $children = array();

	/**
	 * Views of the component
	 * 
	 * @var array
	 * @since 3.4
	 */
	public $path = array();

	public function __construct($name)
	{
		$this->name = $name;
		$this->view = $name;
		$this->path[] = $name;
	}

	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function setViewName($name)
	{
		$this->view = $name;

		return $this;
	}

	public function setViewKey($key)
	{
		$this->key = $key;

		return $this;
	}

	public function setParent(JComponentRouterViewconfiguration $parent, $parent_key = false)
	{
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

	public function setNestable($isNestable = true)
	{
		$this->nestable = $isNestable;
		
		return $this;
	}

	public function addLayout($layout)
	{
		$this->layouts[] = $layout;
		
		return $this;
	}
}