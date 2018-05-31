<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\ModuleDispatcherInterface;

/**
 * Access to module specific services.
 *
 * @since  __DEPLOY_VERSION__
 */
interface ModuleInterface
{
	/**
	 * Returns the dispatcher for the given application, null if none exists.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 *
	 * @return  ModuleDispatcherInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDispatcher(CMSApplicationInterface $application): ModuleDispatcherInterface;
}
