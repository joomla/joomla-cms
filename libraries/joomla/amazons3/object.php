<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Amazons3 API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
abstract class JAmazons3Object
{
	/**
	 * @var    JRegistry  Options for the Amazons3 object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JAmazons3Http  The HTTP client object to use in sending HTTP requests.
	 * @since  ??.?
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry      $options  Amazons3 options object.
	 * @param   JAmazons3Http  $client   The HTTP client object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JAmazons3Http $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JAmazons3Http($this->options);
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
	protected function processResponse(JHttpResponse $response, $expectedCode = 200)
	{
		// TODO - xml

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
