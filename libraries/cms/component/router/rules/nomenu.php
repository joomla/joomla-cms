<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Rule to process URLs without a menu item
 *
 * @since  3.4
 */
class JComponentRouterRulesNomenu implements JComponentRouterRulesInterface
{
	/**
	 * Router this rule belongs to
	 *
	 * @var JComponentRouterAdvanced
	 * @since 3.4
	 */
	protected $router;

	/**
	 * Class constructor.
	 *
	 * @param   JComponentRouterAdvanced  $router  Router this rule belongs to
	 *
	 * @since   3.4
	 */
	public function __construct(JComponentRouterAdvanced $router)
	{
		$this->router = $router;
	}

	/**
	 * Dummymethod to fullfill the interface requirements
	 * 
	 * @param   array  &$query  The query array to process
	 * 
	 * @return  void
	 * 
	 * @since   3.4
	 */
	public function preprocess(&$query)
	{
	}

	/**
	 * Parse a menu-less URL
	 *
	 * @param   array  &$segments  The URL segments to parse
	 * @param   array  &$vars      The vars that result from the segments
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function parse(&$segments, &$vars)
	{
		$active = $this->router->menu->getActive();

		if (!is_object($active))
		{
			$vars['view'] = $segments[0];
			$views = $this->router->getViews();
			if (isset($views[$vars['view']]->id) && isset($segments[1]))
			{
				$vars[$views[$vars['view']]->id] = $segments[1];
				unset($segments[1]);
			}
			unset($segments[0]);
		}
	}

	/**
	 * Build a menu-less URL
	 *
	 * @param   array  &$query     The vars that should be converted
	 * @param   array  &$segments  The URL segments to create
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function build(&$query, &$segments)
	{
		if (!isset($query['Itemid']))
		{
			$segments[] = $query['view'];
			$views = $this->router->getViews();
			if (isset($views[$query['view']]->id))
			{
				$segments[] = $query[$views[$query['view']]->id];
				unset($query[$views[$query['view']]->id]);
			}
			unset($query['view']);
		}
	}
}
