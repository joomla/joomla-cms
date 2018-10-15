<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Router;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Interface defining a factory which can create Router objects
 *
 * @since  4.0.0
 */
interface RouterFactoryInterface
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
	public function createRouter(CMSApplication $app, AbstractMenu $menu = null): Router;
}
