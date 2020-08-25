<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

\defined('JPATH_PLATFORM') or die;

use Joomla\Event\DispatcherAwareInterface;

/**
 * Access to plugin specific services.
 *
 * @since  4.0.0
 */
interface PluginInterface extends DispatcherAwareInterface
{
	/**
	 * Registers its listeners.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function registerListeners();
}
