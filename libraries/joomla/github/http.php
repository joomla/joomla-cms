<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * HTTP client class for connecting to a GitHub instance.
 *
 * @since       1.7.3
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubHttp extends JHttp
{
	/**
	 * Use no authentication for HTTP connections.
	 *
	 * @var    integer
	 * @since  1.7.3
	 */
	const AUTHENTICATION_NONE = 0;

	/**
	 * Use basic authentication for HTTP connections.
	 *
	 * @var    integer
	 * @since  1.7.3
	 */
	const AUTHENTICATION_BASIC = 1;

	/**
	 * Use OAuth authentication for HTTP connections.
	 *
	 * @var    integer
	 * @since  1.7.3
	 */
	const AUTHENTICATION_OAUTH = 2;

	/**
	 * Constructor.
	 *
	 * @param   Registry        $options    Client options object.
	 * @param   JHttpTransport  $transport  The HTTP transport object.
	 *
	 * @since   1.7.3
	 */
	public function __construct(Registry $options = null, JHttpTransport $transport = null)
	{
		// Call the JHttp constructor to setup the object.
		parent::__construct($options, $transport);

		// Make sure the user agent string is defined.
		$this->options->def('userAgent', 'JGitHub/2.0');

		// Set the default timeout to 120 seconds.
		$this->options->def('timeout', 120);
	}
}
