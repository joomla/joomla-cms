<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session;

/**
 * Interface defining Joomla! session handlers
 *
 * @since  __DEPLOY_VERSION__
 */
interface HandlerInterface extends \SessionHandlerInterface
{
	/**
	 * Test to see if the HandlerInterface is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported();
}
