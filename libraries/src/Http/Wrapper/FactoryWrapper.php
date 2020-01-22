<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Http\Wrapper;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Http\TransportInterface;

/**
 * Wrapper class for HttpFactory
 *
 * @since  3.4
 */
class FactoryWrapper
{
	/**
	 * Helper wrapper method for getHttp
	 *
	 * @param   Registry  $options   Client options object.
	 * @param   mixed     $adapters  Adapter (string) or queue of adapters (array) to use for communication.
	 *
	 * @return  Http      Joomla Http class
	 *
	 * @see     HttpFactory::getHttp()
	 * @since   3.4
	 * @throws  \RuntimeException
	 */
	public function getHttp(Registry $options = null, $adapters = null)
	{
		return HttpFactory::getHttp($options, $adapters);
	}

	/**
	 * Helper wrapper method for getAvailableDriver
	 *
	 * @param   Registry  $options  Option for creating http transport object.
	 * @param   mixed     $default  Adapter (string) or queue of adapters (array) to use.
	 *
	 * @return  TransportInterface  Interface sub-class
	 *
	 * @see     HttpFactory::getAvailableDriver()
	 * @since   3.4
	 */
	public function getAvailableDriver(Registry $options, $default = null)
	{
		return HttpFactory::getAvailableDriver($options, $default);
	}

	/**
	 * Helper wrapper method for getHttpTransports
	 *
	 * @return array  An array of available transport handlers
	 *
	 * @see     HttpFactory::getHttpTransports()
	 * @since   3.4
	 */
	public function getHttpTransports()
	{
		return HttpFactory::getHttpTransports();
	}
}
