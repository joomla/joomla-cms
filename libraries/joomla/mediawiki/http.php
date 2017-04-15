<?php
/**
 * @package     Joomla.Platform
 * @subpackage  MediaWiki
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Http\TransportInterface;

/**
 * HTTP client class for connecting to a MediaWiki instance.
 *
 * @since  12.3
 */
class JMediawikiHttp extends JHttp
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
			throw new InvalidArgumentException(
				'The options param must be an array or implement the ArrayAccess interface.'
			);
		}

		$this->options = $options;

		// Override the JHttp contructor to use JHttpTransportStream.
		if (!isset($transport))
		{
			$transport = JHttpFactory::getAvailableDriver($this->options, 'stream');
		}

		// Ensure the transport is a TransportInterface instance or bail out
		if (!($transport instanceof TransportInterface))
		{
			throw new InvalidArgumentException('A valid TransportInterface object was not set.');
		}

		$this->transport = $transport;

		// Make sure the user agent string is defined.
		$this->setOption('api.useragent', 'JMediawiki/1.0');

		// Set the default timeout to 120 seconds.
		if (!$this->getOption('api.timeout'))
		{
			$this->setOption('api.timeout', 120);
		}
	}
}
