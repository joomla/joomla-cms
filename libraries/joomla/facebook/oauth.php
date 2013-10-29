<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();


/**
 * Joomla Platform class for generating Facebook API access token.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @since       13.1
 */
class JFacebookOAuth extends JOAuth2Client
{
	/**
	 * @var JRegistry Options for the JFacebookOAuth object.
	 * @since 13.1
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry  $options  JFacebookOauth options object.
	 * @param   JHttp      $client   The HTTP client object.
	 * @param   JInput     $input    The input object.
	 *
	 * @since   13.1
	 */
	public function __construct(JRegistry $options = null, JHttp $client = null, JInput $input = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;

		// Setup the authentication and token urls if not already set.
		$this->options->def('authurl', 'http://www.facebook.com/dialog/oauth');
		$this->options->def('tokenurl', 'https://graph.facebook.com/oauth/access_token');

		// Call the JOauthOauth2client constructor to setup the object.
		parent::__construct($this->options, $client, $input);
	}

	/**
	 * Method used to set permissions.
	 *
	 * @param   string  $scope  Comma separated list of permissions.
	 *
	 * @return  JFacebookOauth  This object for method chaining
	 *
	 * @since   13.1
	 */
	public function setScope($scope)
	{
		$this->setOption('scope', $scope);

		return $this;
	}

	/**
	 * Method to get the current scope
	 *
	 * @return  string Comma separated list of permissions.
	 *
	 * @since   13.1
	 */
	public function getScope()
	{
		return $this->getOption('scope');
	}
}
