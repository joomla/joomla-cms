<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Deprecated class placeholder.  You should use JApplicationWeb instead.
 *
 * @package     Joomla.Legacy
 * @subpackage  Application
 * @since       11.3
 * @deprecated  12.3 Use JApplicationWeb instead.
 */
class JWeb extends JApplicationWeb
{
	/**
	 * Class constructor.
	 *
	 * @param   mixed  $userAgent       The optional user-agent string to parse.
	 * @param   mixed  $acceptEncoding  The optional client accept encoding string to parse.
	 * @param   mixed  $acceptLanguage  The optional client accept language string to parse.
	 *
	 * @since   11.3
	 */
	public function __construct($userAgent = null, $acceptEncoding = null, $acceptLanguage = null)
	{
		JLog::add('JWebClient is deprecated. Use JApplicationWebClient instead.', JLog::WARNING, 'deprecated');
		parent::__construct($userAgent, $acceptEncoding, $acceptLanguage);
	}

	/**
	 * Class constructor.
	 *
	 * @param   mixed  $input   An optional argument to provide dependency injection for the application's
	 *                          input object.  If the argument is a JInput object that object will become
	 *                          the application's input object, otherwise a default input object is created.
	 * @param   mixed  $config  An optional argument to provide dependency injection for the application's
	 *                          config object.  If the argument is a JRegistry object that object will become
	 *                          the application's config object, otherwise a default config object is created.
	 * @param   mixed  $client  An optional argument to provide dependency injection for the application's
	 *                          client object.  If the argument is a JApplicationWebClient object that object will become
	 *                          the application's client object, otherwise a default client object is created.
	 *
	 * @since   11.3
	 * @deprecated  12.3 Use JApplicationWeb instead.
	 */
	public function __construct(JInput $input = null, JRegistry $config = null, JApplicationWebClient $client = null)
	{
		JLog::add('JWeb is deprecated. Use JApplicationWeb instead.', JLog::WARNING, 'deprecated');
		parent::__construct($input, $registry, $client);
	}
}
