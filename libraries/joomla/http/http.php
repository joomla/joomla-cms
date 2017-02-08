<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTTP
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Http\Http;
use Joomla\Http\TransportInterface;

/**
 * HTTP client class.
 *
 * @since  11.3
 */
class JHttp extends Http
{
	/**
	 * Constructor.
	 *
	 * @param   array|ArrayAccess   $options    Client options array. If the registry contains any headers.* elements,
	 *                                          these will be added to the request headers.
	 * @param   TransportInterface  $transport  The HTTP transport object.
	 *
	 * @since   11.3
	 * @throws  InvalidArgumentException
	 */
	public function __construct($options = [], TransportInterface $transport = null)
	{
		if (!is_array($options) && !($options instanceof ArrayAccess))
		{
			throw new \InvalidArgumentException(
				'The options param must be an array or implement the ArrayAccess interface.'
			);
		}

		$this->options = $options;

		if (!isset($transport))
		{
			$transport = JHttpFactory::getAvailableDriver($this->options);
		}

		// Ensure the transport is a TransportInterface instance or bail out
		if (!($transport instanceof TransportInterface))
		{
			throw new InvalidArgumentException('A valid TransportInterface object was not set.');
		}

		$this->transport = $transport;
	}
}
