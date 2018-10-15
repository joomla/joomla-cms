<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Router;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Menu\AbstractMenu;

defined('_JEXEC') or die;

/**
 * Default factory for creating Form objects
 *
 * @since  4.0.0
 */
class RouterFactory implements RouterFactoryInterface
{
	/**
	 * Method to get an instance of a router.
	 *
	 * @param   CMSApplication  $app   CMSApplication Object
	 * @param   AbstractMenu    $menu  AbstractMenu object
	 *
	 * @return  Router
	 *
	 * @since   4.0.0
	 */
	public function createRouter(CMSApplication $app, AbstractMenu $menu = null): Router
	{
		if ($app->isClient('administrator'))
		{
			return new AdministratorRouter;
		}

		if ($app->isClient('site'))
		{
			return new SiteRouter($app, $menu);
		}

		throw new \RuntimeException(Text::sprintf('JLIB_APPLICATION_ERROR_ROUTER_LOAD', $app->getName()), 500);
	}
}
