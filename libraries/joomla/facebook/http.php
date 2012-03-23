<?php

/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 */

defined('JPATH_PLATFORM') or die();


/**
 * HTTP client class for connecting to a Facebook instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 */

class JFacebookHttp extends JHttp
{
	/**
	 * Constructor.
	 *
	 * @param   JRegistry       &$options   Client options object.
	 * @param   JHttpTransport  $transport  The HTTP transport object.
	 */
	public function __construct(JRegistry &$options = null, JHttpTransport $transport = null)
	{
		// Call the JHttp constructor to setup the object.
		parent::__construct($options, $transport);

		// Make sure the user agent string is defined.
		$this->options->def('userAgent', 'JFacebook');

		// Set the default timeout to 120 seconds.
		$this->options->def('timeout', 120);
	}
}
