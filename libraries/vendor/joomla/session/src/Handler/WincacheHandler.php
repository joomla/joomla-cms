<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Handler;

use Joomla\Session\HandlerInterface;

/**
 * Wincache session storage handler
 *
 * @since  __DEPLOY_VERSION__
 */
class WincacheHandler extends \SessionHandler implements HandlerInterface
{
	/**
	 * Constructor
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		ini_set('session.save_handler', 'wincache');
	}

	/**
	 * Test to see if the HandlerInterface is available
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		return extension_loaded('wincache') && function_exists('wincache_ucache_get') && !strcmp(ini_get('wincache.ucenabled'), "1");
	}
}
