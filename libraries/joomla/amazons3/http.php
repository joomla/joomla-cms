<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTTP client class for connecting to an Amazons3 instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3Http extends JHttp
{
	/**
	 * @const  integer  Use no authentication for HTTP connections.
	 * @since  ??.?
	 */
	const AUTHENTICATION_NONE = 0;

	/**
	 * @const  integer  Use the standard HTTP Authorization header to pass
	 *		    authentication information.
	 * @since  ??.?
	 */
	const AUTHENTICATION_HEADER = 1;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry       $options    Client options object.
	 * @param   JHttpTransport  $transport  The HTTP transport object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JHttpTransport $transport = null)
	{
		// Call the JHttp constructor to setup the object.
		parent::__construct($options, $transport);

		// Make sure the user agent string is defined.
		$this->options->def('userAgent', 'JAmazons3');

		// Set the default timeout to 120 seconds.
		$this->options->def('timeout', 120);
	}
}
