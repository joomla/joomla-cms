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
	 * @var JComponentRouterView
	 * @since 3.4
	 */
	protected $router;

	/**
	 * Class constructor.
	 *
	 * @param   JComponentRouterView  $router  Router this rule belongs to
	 *
	 * @since   3.4
	 */
	public function __construct(JComponentRouterView $router)
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
	 * @codeCoverageIgnore
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
			$views = $this->router->getViews();

			if (isset($views[$segments[0]]))
			{
				$vars['view'] = array_shift($segments);

				if (isset($views[$vars['view']]->key) && isset($segments[0]))
				{
					$vars[$views[$vars['view']]->key] = preg_replace('/-/', ':', array_shift($segments), 1);
				}
			}
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
		if (!isset($query['Itemid']) && isset($query['view']))
		{
			$views = $this->router->getViews();
			if (isset($views[$query['view']]))
			{
				$segments[] = $query['view'];

				if ($views[$query['view']]->key)
				{
					$key = $views[$query['view']]->key;
					$segments[] = str_replace(':', '-', $query[$key]);
					unset($query[$views[$query['view']]->key]);
				}
				unset($query['view']);
			}
		}
	}
}
