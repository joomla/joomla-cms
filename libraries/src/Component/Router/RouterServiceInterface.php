<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * The component router service.
 *
 * @since  __DEPLOY_VERSION__
 */
interface RouterServiceInterface
{
	/**
	 * Returns the router.
	 *
	 * @param   CMSApplication  $application  The application object
	 * @param   AbstractMenu    $menu         The menu object to work with
	 *
	 * @return  RouterInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function createRouter(CMSApplication $application, AbstractMenu $menu): RouterInterface;
}
