<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Googlecloudstorage API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
abstract class JGooglecloudstorageObject
{
	/**
	 * @var    JRegistry  Options for the Googlecloudstorage object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JGooglecloudstorageHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  ??.?
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry                $options  Googlecloudstorage options object.
	 * @param   JGooglecloudstorageHttp  $client   The HTTP client object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JGooglecloudstorageHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JGooglecloudstorageHttp($this->options);
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
			if ($response->body != null && $response->body[0] == '<')
			{
				// Decode the error response and throw an exception.
				$error = new SimpleXMLElement($response->body);

				// The PUT requests return <Message>[...]</Message> in their bodies
				if ($error->message == "")
				{
					$error->message = $error->Message;
				}

				throw new DomainException($error->message, $response->code);
			}
		}

		if ($response->body != null && $response->body[0] == '<')
		{
			return new SimpleXMLElement($response->body);
		}
		else
		{
			return "Response code: " . $response->code . ".\n";
		}
	}

	/**
	 * Creates the Authorization request header (which handles authentication).
	 *
	 * @param   string  $httpVerb  The HTTP Verb (GET, PUT, etc)
	 * @param   string  $url       The target url of the request
	 * @param   string  $headers   The headers of the request
	 *
	 * @return string The Authorization request header
	 *
	 * @since   ??.?
	 */
	public function createAuthorization($httpVerb, $url, $headers)
	{
		return "";
	}
}
