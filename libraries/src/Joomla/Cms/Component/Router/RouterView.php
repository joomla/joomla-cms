<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Cms\Component\Router;

defined('JPATH_PLATFORM') or die;

use Joomla\Cms\Component\Router\Rules\RulesInterface;

/**
 * View-based component routing class
 *
 * @since  3.5
 */
abstract class RouterView extends RouterBase
{
	/**
	 * Name of the router of the component
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $name;

	/**
	 * Array of rules
	 *
	 * @var    RulesInterface[]
	 * @since  3.5
	 */
	protected $rules = array();

	/**
	 * Views of the component
	 *
	 * @var    RouterViewConfiguration[]
	 * @since  3.5
	 */
	protected $views = array();

	/**
	 * Register the views of a component
	 *
	 * @param   RouterViewConfiguration  $view  View configuration object
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function registerView(RouterViewConfiguration $view)
	{
		$this->views[$view->name] = $view;
	}

	/**
	 * Return an array of registered view objects
	 *
	 * @return  RouterViewConfiguration[] Array of registered view objects
	 *
	 * @since   3.5
	 */
	public function getViews()
	{
		return $this->views;
	}

	/**
	 * Get the path of views from target view to root view
	 * including content items of a nestable view
	 *
	 * @param   array  $query  Array of query elements
	 *
	 * @return  array List of views including IDs of content items
	 *
	 * @since   3.5
	 */
	public function getPath($query)
	{
		$views  = $this->getViews();
		$result = array();

		// Get the right view object
		if (isset($query['view']) && isset($views[$query['view']]))
		{
			$viewobj = $views[$query['view']];
		}

		// Get the path from the current item to the root view with all IDs
		if (isset($viewobj))
		{
			$path     = array_reverse($viewobj->path);
			$start    = true;
			$childkey = false;

			foreach ($path as $element)
			{
				$view = $views[$element];

				if ($start)
				{
					$key   = $view->key;
					$start = false;
				}
				else
				{
					$key = $childkey;
				}

				$childkey = $view->parent_key;

				if (($key || $view->key) && is_callable(array($this, 'get' . ucfirst($view->name) . 'Segment')))
				{
					if (isset($query[$key]))
					{
						$result[$view->name] = call_user_func_array(array($this, 'get' . ucfirst($view->name) . 'Segment'), array($query[$key], $query));
					}
					elseif (isset($query[$view->key]))
					{
						$result[$view->name] = call_user_func_array(array($this, 'get' . ucfirst($view->name) . 'Segment'), array($query[$view->key], $query));
					}
					else
					{
						$result[$view->name] = array();
					}
				}
				else
				{
					$result[$view->name] = true;
				}
			}
		}

		return $result;
	}

	/**
	 * Get all currently attached rules
	 *
	 * @return  RulesInterface[]  All currently attached rules in an array
	 *
	 * @since   3.5
	 */
	public function getRules()
	{
		return $this->rules;
	}

	/**
	 * Add a number of router rules to the object
	 *
	 * @param   RulesInterface[]  $rules  Array of JComponentRouterRulesInterface objects
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function attachRules($rules)
	{
		foreach ($rules as $rule)
		{
			$this->attachRule($rule);
		}
	}

	/**
	 * Attach a build rule
	 *
	 * @param   RulesInterface  $rule  The function to be called.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function attachRule(RulesInterface $rule)
	{
		$this->rules[] = $rule;
	}

	/**
	 * Remove a build rule
	 *
	 * @param   RulesInterface  $rule  The rule to be removed.
	 *
	 * @return   boolean  Was a rule removed?
	 *
	 * @since   3.5
	 */
	public function detachRule(RulesInterface $rule)
	{
		foreach ($this->rules as $id => $r)
		{
			if ($r == $rule)
			{
				unset($this->rules[$id]);

				return true;
			}
		}

		return false;
	}

	/**
	 * Generic method to preprocess a URL
	 *
	 * @param   array  $query  An associative array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.5
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
	 * @return  array  Array of URL segments
	 *
	 * @since   3.5
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
	 * @return  array  Associative array of query values
	 *
	 * @since   3.5
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
	 * @return  string  Name of the router
	 *
	 * @since   3.5
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;

			if (!preg_match('/(.*)Router/i', get_class($this), $r))
			{
				throw new \Exception('JLIB_APPLICATION_ERROR_ROUTER_GET_NAME', 500);
			}

			$this->name = strtolower($r[1]);
		}

		return $this->name;
	}
}
