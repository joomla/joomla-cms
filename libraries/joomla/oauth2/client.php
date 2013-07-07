<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Oauth
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;
jimport('joomla.environment.response');

/**
 * Joomla Platform class for interacting with an OAuth 2.0 server.
 *
 * @package     Joomla.Platform
 * @subpackage  Oauth
 * @since       12.3
 * @deprecated  4.0  Use JClientOAuth2 instead.
 */
class JOAuth2 extends JClientOAuth2
{

	/**
	 * Constructor.
	 *
	 * @param   JRegistry        $options      JClientOAuth2 options object
	 * @param   JHttp            $http         The HTTP client object
	 * @param   JInput           $input        The input object
	 * @param   mixed            $application  The application object
	 *
	 * @since   12.3
	 */
	public function __construct(JRegistry $options = null, JHttp $http = null, JInput $input = null, $application = null)
	{
		JLog::add('JOAuth2 is deprecated. Use JClientOAuth2 instead.', JLog::WARNING, 'deprecated');
		parent::__construct($options, $http, $input, $application);

	}
}

