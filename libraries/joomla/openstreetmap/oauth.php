<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Openstreetmap
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Joomla Platform class for generating Openstreetmap API access token.
 *
 * @package     Joomla.Platform
 * @subpackage  Openstreetmap
 * @since       13.1
 */
class JOpenstreetmapOauth extends JOAuth1Client
{
	/**
	 * Options for the JOpenstreetmapOauth object.
	 *
	 * @var    JRegistry
	 * @since  13.1
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry  $options  JOpenstreetmapOauth options object.
	 * @param   JHttp      $client   The HTTP client object.
	 * @param   JInput     $input    The input object
	 *
	 * @since   13.1
	 */
	public function __construct(JRegistry $options = null, JHttp $client = null, JInput $input = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;

		$this->options->def('accessTokenURL', 'http://www.openstreetmap.org/oauth/access_token');
		$this->options->def('authoriseURL', 'http://www.openstreetmap.org/oauth/authorize');
		$this->options->def('requestTokenURL', 'http://www.openstreetmap.org/oauth/request_token');

		/*
		$this->options->def('accessTokenURL', 'http://api06.dev.openstreetmap.org/oauth/access_token');
		$this->options->def('authoriseURL', 'http://api06.dev.openstreetmap.org/oauth/authorize');
		$this->options->def('requestTokenURL', 'http://api06.dev.openstreetmap.org/oauth/request_token');
		*/

		// Call the JOauth1Client constructor to setup the object.
		parent::__construct($this->options, $client, $input, null, '1.0');
	}

	/**
	 * Method to verify if the access token is valid by making a request to an API endpoint.
	 *
	 * @return  boolean  Returns true if the access token is valid and false otherwise.
	 *
	 * @since   13.1
	 */
	public function verifyCredentials()
	{
		return true;
	}

	/**
	 * Method to validate a response.
	 *
	 * @param   string         $url       The request URL.
	 * @param   JHttpResponse  $response  The response to validate.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @throws  DomainException
	 */
	public function validateResponse($url, $response)
	{
		if ($response->code != 200)
		{
			$error = htmlspecialchars($response->body);

			throw new DomainException($error, $response->code);
		}
	}
}
