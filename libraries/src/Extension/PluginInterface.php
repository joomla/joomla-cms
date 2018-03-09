<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\Event\DispatcherAwareInterface;

/**
 * Access to plugin specific services.
 *
 * @since  __DEPLOY_VERSION__
 */
interface PluginInterface extends DispatcherAwareInterface
{
	/**
	 * Registers it's listeners.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function registerListeners();
}
