<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Http
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Wrapper class for JHttpFactory
 *
 * @package     Joomla.Platform
 * @subpackage  Http
 * @since       3.4
 */
class JHttpWrapperFactory
{
	/**
	 * Helper wrapper method for getHttp
	 *
	 * @param   Registry  $options   Client options object.
	 * @param   mixed     $adapters  Adapter (string) or queue of adapters (array) to use for communication.
	 *
	 * @return JHttp      Joomla Http class
	 *
	 * @see     JHttpFactory::getHttp()
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function getHttp(Registry $options = null, $adapters = null)
	{
		return JHttpFactory::getHttp($options, $adapters);
	}

	/**
	 * Helper wrapper method for getAvailableDriver
	 *
	 * @param   Registry  $options  Option for creating http transport object.
	 * @param   mixed     $default  Adapter (string) or queue of adapters (array) to use.
	 *
	 * @return JHttpTransport Interface sub-class
	 *
	 * @see     JHttpFactory::getAvailableDriver()
	 * @since   3.4
	 */
	public function getAvailableDriver(Registry $options, $default = null)
	{
		return JHttpFactory::getAvailableDriver($options, $default);
	}

	/**
	 * Helper wrapper method for getHttpTransports
	 *
	 * @return array  An array of available transport handlers
	 *
	 * @see     JHttpFactory::getHttpTransports()
	 * @since   3.4
	 */
	public function getHttpTransports()
	{
		return JHttpFactory::getHttpTransports();
	}
}
