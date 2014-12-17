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
 * Advanced component routing class
 *
 * @since  3.4
 */
abstract class JComponentRouterAdvanced extends JComponentRouterBase
{
	/**
	 * Name of the router of the component
	 *
	 * @var string
	 * @since 3.4
	 */
	protected $name;

	/**
	 * Array of rules
	 * 
	 * @var array
	 * @since 3.4
	 */
	protected $rules = array();

	/**
	 * Views of the component
	 * 
	 * @var array
	 * @since 3.4
	 */
	protected $views = array();

	/**
	 * Mapping names with actual views
	 * 
	 * @var array
	 */
	protected $view_map = array();

	/**
	 * Register the views of a component
	 * 
	 * @param   string  $name       Internal name of the view. Has to be unique for the component
	 * @param   string  $view       Identifier of the view
	 * @param   string  $id         Identifier of the ID variable used to identify the primary content item of this view 
	 * @param   string  $parent     Internal name of the parent view
	 * @param   string  $parent_id  Identifier of the ID variable used to identify the content item of the parent view
	 * @param   bool    $nestable   Is this view nestable?
	 * @param   string  $layout     Layout to use for this view by default
	 * 
	 * @return void
	 * 
	 * @since 3.4
	 */
	public function registerView($name, $view, $id = false, $parent = false, $parent_id = false, $nestable = false, $layout = 'default')
	{
		$viewobj = new stdClass;
		$viewobj->view = $view;
		$viewobj->name = $name;
		$viewobj->id = $id;
		if ($parent)
		{
			$viewobj->parent = $this->views[$parent];
			$this->views[$parent]->children[] = &$viewobj;
			$viewobj->path = $this->views[$parent]->path;
		}
		else
		{
			$viewobj->parent = false;
			$viewobj->path = array();
		}
		$viewobj->path[] = $name;
		$viewobj->child_id = false;
		$viewobj->parent_id = $parent_id;
		if ($parent_id)
		{
			$this->views[$parent]->child_id = $parent_id;
		}
		$viewobj->nestable = $nestable;
		$viewobj->layout = $layout;

		$this->views[$name] = $viewobj;
		if (!isset($this->view_map[$view]))
		{
			$this->view_map[$view] = array();
		}
		$this->view_map[$view][] = $name;
	}

	/**
	 * Return an array of registered view objects
	 * 
	 * @return array Array of registered view objects
	 * 
	 * @since 3.4
	 */
	public function getViews()
	{
		return $this->views;
	}

	/**
	 * Return the map of views to names of the registered view objects
	 * 
	 * @param   object  $view  View to return
	 * 
	 * @return array Array of names
	 * 
	 * @since 3.4
	 */
	public function getViewMap($view = false)
	{
		if ($view && isset($this->view_map[$view]))
		{
			return $this->view_map[$view];
		}
		return $this->view_map;
	}

	/**
	 * Get the path of views from target view to root view 
	 * including content items of a nestable view
	 * 
	 * @param   array  $query  Array of query elements
	 * 
	 * @return array List of views including IDs of content items
	 */
	public function getPath($query)
	{
		$views = $this->getViews();
		$result = array();
		$id = false;
		if (isset($query['view']) && $this->view_map[$query['view']])
		{
			$view = $query['view'];
			if (isset($query['layout']))
			{
				$layout = $query['layout'];
			}
			else
			{
				$layout = 'default';
			}
			foreach ($this->view_map[$view] as $name)
			{
				if ($layout == $this->views[$name]->layout)
				{
					$viewobj = $this->views[$name];
					break;
				}
			}
		}
		if (isset($viewobj))
		{
			$path = array_reverse($viewobj->path);

			$view = $views[array_shift($path)];
			$id = $view->id;
			foreach ($path as $element)
			{
				if ($id && isset($query[$id]))
				{
					$result[$view->name] = array($query[$id]);
					if ($view->nestable)
					{
						$nestable = call_user_func_array(array($this, 'get' . ucfirst($view->name)), array($query[$id]));
						if ($nestable)
						{
							$result[$view->name] = array_reverse($nestable->getPath());
						}
					}
				}
				else
				{
					$result[$view->name] = true;
				}
				$view = $views[$element];
				$id = $view->child_id;
			}
		}
		return $result;
	}

	/**
	 * Add a number of router rules to the object
	 * 
	 * @param   array  $rules  Array of JComponentRouterRulesInterface objects
	 * 
	 * @return void
	 * 
	 * @since 3.4
	 */
	public function setRules($rules)
	{
		foreach ($rules as $rule)
		{
			$this->attachRule($rule);
		}
	}

	/**
	 * Attach a build rule
	 *
	 * @param   JComponentRouterRulesInterface  $rule  The function to be called.
	 * 
	 * @return   void
	 */
	public function attachRule(JComponentRouterRulesInterface $rule)
	{
		$this->rules[] = $rule;
	}

	/**
	 * Generic method to preprocess a URL
	 *
	 * @param   array  $query  An associative array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function preprocess($query)
	{
		// Process the parsed variables based on custom defined rules
		foreach ($this->rules as $rule)
		{
			$rule->preprocess($query);
		}
		return $query;
	}

	/**
	 * Build method for URLs
	 * 
	 * @param   array  &$query  Array of query elements
	 * 
	 * @return   array  Array of URL segments
	 */
	public function build(&$query)
	{
		$segments = array();

		// Process the parsed variables based on custom defined rules
		foreach ($this->rules as $rule)
		{
			$rule->build($query, $segments);
		}
		return $segments;
	}

	/**
	 * Parse method for URLs
	 * 
	 * @param   array  &$segments  Array of URL string-segments
	 * 
	 * @return   array  Associative array of query values
	 */
	public function parse(&$segments)
	{
		$vars = array();

		// Process the parsed variables based on custom defined rules
		foreach ($this->rules as $rule)
		{
			$rule->parse($segments, $vars);
		}
		return $vars;
	}

	/**
	 * Method to return the name of the router
	 * 
	 * @return   string  Name of the router
	 * 
	 * @since 3.4
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;
			if (!preg_match('/(.*)Router/i', get_class($this), $r))
			{
				throw new Exception('JLIB_APPLICATION_ERROR_ROUTER_GET_NAME', 500);
			}
			$this->name = strtolower($r[1]);
		}

		return $this->name;
	}

	/**
	 * Get content items of the type category
	 * This is a generic function for all components that use the JCategories
	 * system and can be overriden if necessary.
	 * 
	 * @param   int  $id  ID of the category to load
	 * 
	 * @return   JCategoryNode  Category identified by $id
	 * 
	 * @since 3.4
	 */
	public function getCategory($id)
	{
		$category = JCategories::getInstance($this->getName())->get($id);
		return $category;
	}
}
