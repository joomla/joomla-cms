<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Openstreetmap
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

use Joomla\Registry\Registry;

/**
 * Joomla Platform class for generating Openstreetmap API access token.
 *
 * @since       3.2.0
 * @deprecated  4.0  Use the `joomla/openstreetmap` package via Composer instead
 */
class JOpenstreetmapOauth extends JOAuth1Client
{
	/**
	 * Options for the JOpenstreetmapOauth object.
	 *
	 * @var    Registry
	 * @since  3.2.0
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * @param   Registry  $options  JOpenstreetmapOauth options object.
	 * @param   JHttp     $client   The HTTP client object.
	 * @param   JInput    $input    The input object
	 *
	 * @since   3.2.0
	 */
	public function __construct(Registry $options = null, JHttp $client = null, JInput $input = null)
	{
		$this->options = isset($options) ? $options : new Registry;

		$this->options->def('accessTokenURL', 'https://www.openstreetmap.org/oauth/access_token');
		$this->options->def('authoriseURL', 'https://www.openstreetmap.org/oauth/authorize');
		$this->options->def('requestTokenURL', 'https://www.openstreetmap.org/oauth/request_token');

		/*
		$this->options->def('accessTokenURL', 'https://api06.dev.openstreetmap.org/oauth/access_token');
		$this->options->def('authoriseURL', 'https://api06.dev.openstreetmap.org/oauth/authorize');
		$this->options->def('requestTokenURL', 'https://api06.dev.openstreetmap.org/oauth/request_token');
		*/

		// Call the JOauth1Client constructor to setup the object.
		parent::__construct($this->options, $client, $input, null, '1.0');
	}

	/**
	 * Method to verify if the access token is valid by making a request to an API endpoint.
	 *
	 * @return  boolean  Returns true if the access token is valid and false otherwise.
	 *
	 * @since   3.2.0
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
	 * @since   3.2.0
	 * @throws  DomainException
	 */
	public function validateResponse($url, $response)
	{
		if ($response->code != 200)
		{
			$error = htmlspecialchars($response->body, ENT_COMPAT, 'UTF-8');

			throw new DomainException($error, $response->code);
		}
	}
}
