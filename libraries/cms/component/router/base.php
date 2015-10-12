<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base component routing class
 *
 * @since  3.3
 */
abstract class JComponentRouterBase implements JComponentRouterInterface
{
	/**
	 * Application object to use in the router
	 *
	 * @var    JApplicationCms
	 * @since  3.4
	 */
	public $app;

	/**
	 * Menu object to use in the router
	 *
	 * @var    JMenu
	 * @since  3.4
	 */
	public $menu;

	/**
	 * Class constructor.
	 *
	 * @param   JApplicationCms  $app   Application-object that the router should use
	 * @param   JMenu            $menu  Menu-object that the router should use
	 *
	 * @since   3.4
	 */
	public function __construct($app = null, $menu = null)
	{
		if ($app)
		{
			$this->app = $app;
		}
		else
		{
			$this->app = JFactory::getApplication();
		}

		if ($menu)
		{
			$this->menu = $menu;
		}
		else
		{
			$this->menu = $this->app->getMenu();
		}
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
		return $query;
	}
}
