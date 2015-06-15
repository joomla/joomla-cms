<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Storage;

use Joomla\Session\Storage;

/**
 * WINCACHE session storage handler for PHP
 *
 * @since  1.0
 * @deprecated  The joomla/session package is deprecated
 */
class Wincache extends Storage
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function __construct($options = array())
	{
		if (!self::isSupported())
		{
			throw new \RuntimeException('Wincache Extension is not available', 404);
		}

		parent::__construct($options);
	}

	/**
	 * Register the functions of this class with PHP's session handler
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function register()
	{
		ini_set('session.save_handler', 'wincache');
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	static public function isSupported()
	{
		return (extension_loaded('wincache') && function_exists('wincache_ucache_get') && !strcmp(ini_get('wincache.ucenabled'), "1"));
	}
}
