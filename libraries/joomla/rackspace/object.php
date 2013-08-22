<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Rackspace API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
abstract class JRackspaceObject
{
	/**
	 * @var    JRegistry  Options for the Rackspace object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  ??.?
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry  $options  Rackspace options object.
	 * @param   JHttp      $client   The HTTP client object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JHttp($this->options);
	}

	/**
	 * Process the response and decode it.
	 *
	 * @param   JHttpResponse  $response      The response.
	 * @param   integer        $expectedCode  The expected "good" code.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 */
	public function processResponse(JHttpResponse $response, $expectedCode = 200)
	{
		// Validate the response code.
		if ($response->code != $expectedCode)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}
}
