<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

defined('JPATH_PLATFORM') or die;

use Psr\Container\ContainerInterface;

/**
 * Access to component specific services.
 *
 * @since  __DEPLOY_VERSION__
 */
interface BootableExtensionInterface
{
	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function boot(ContainerInterface $container);
}
