<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Storage;

use Joomla\Session\Storage;

/**
 * WINCACHE session storage handler for PHP
 *
 * @since       1.0
 * @deprecated  2.0  The Storage class chain will be removed
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
	 * @deprecated  2.0
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
	 * @deprecated  2.0
	 */
	public function register()
	{
		if (!headers_sent())
		{
			ini_set('session.save_handler', 'wincache');
		}
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 * @deprecated  2.0
	 */
	public static function isSupported()
	{
		return \extension_loaded('wincache') && \function_exists('wincache_ucache_get') && !strcmp(ini_get('wincache.ucenabled'), '1');
	}
}
