/**
 * @package     Joomla.Platform
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();
jimport('joomla.environment.response');

/**
 * Joomla Platform class for interacting with an OAuth 1.0 and 1.0a server.
 *
 * @package     Joomla.Platform
 * @subpackage  Client
 *
 * @since       13.1
 * @deprecated  4.0  Use JClientOAuth1 instead.
 */
abstract class JOAuth1 extends JClientOAuth1
{
	/**
	 * Constructor.
	 *
	 * @param   JRegistry        $options      OAuth1Client options object.
	 * @param   JHttp            $client       The HTTP client object.
	 * @param   JInput           $input        The input object
	 * @param   JApplicationWeb  $application  The application object
	 * @param   string           $version      Specify the OAuth version. By default we are using 1.0a.
	 *
	 * @since 13.1
	 * @deprecated  4.0  Use JClientOAuth1 instead.
	 */
	public function __construct(JRegistry $options = null, JHttp $client = null, JInput $input = null, $application = null,
		$version = null)
	{
		JLog::add('JOAuth1 is deprecated. Use JClientOAuth1 instead.', JLog::WARNING, 'deprecated');
		parent::__construct($options, $http, $input, $application, $version);
	}
}