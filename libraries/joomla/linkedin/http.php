<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * HTTP client class for connecting to a Linkedin instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 * @since       12.3
 */
class JLinkedinHttp extends JHttp
{
	/**
	 * Constructor.
	 *
	 * @param   JRegistry       &$options   Client options object.
	 * @param   JHttpTransport  $transport  The HTTP transport object.
	 *
	 * @since   12.3
	 */
	public function __construct(JRegistry &$options = null, JHttpTransport $transport = null)
	{
		// Call the JHttp constructor to setup the object.
		parent::__construct($options, $transport);

		// Make sure the user agent string is defined.
		$this->options->def('userAgent', 'JLinkedin');

		// Set the default timeout to 120 seconds.
		$this->options->def('timeout', 120);
	}
}
