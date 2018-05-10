<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Router factory interface
 *
 * @since  __DEPLOY_VERSION__
 */
interface RouterFactoryInterface
{
	/**
	 * Creates a router.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 *
	 * @return  RouterInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createRouter(CMSApplicationInterface $application, AbstractMenu $menu): RouterInterface;
}
