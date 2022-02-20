<?php
/**
 * @package     Joomla.Platform
 * @subpackage  MediaWiki
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * MediaWiki API object class for the Joomla Platform.
 *
 * @since  3.1.4
 */
abstract class JMediawikiObject
{
	/**
	 * @var    Registry  Options for the MediaWiki object.
	 * @since  3.1.4
	 */
	protected $options;

	/**
	 * @var    JMediawikiHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  3.1.4
	 */
	protected $client;

	/**
     * Constructor.
     *
     * @param   Registry        $options  Mediawiki options object.
     * @param   JMediawikiHttp  $client   The HTTP client object.
     *
     * @since   3.1.4
     */
	public function __construct(Registry $options = null, JMediawikiHttp $client = null)
	{
		$this->options = isset($options) ? $options : new Registry;
		$this->client = isset($client) ? $client : new JMediawikiHttp($this->options);
	}

	/**
	 * Method to build and return a full request URL for the request.
	 *
	 * @param   string  $path  URL to inflect
	 *
	 * @return  string   The request URL.
	 *
	 * @since   3.1.4
	 */
	protected function fetchUrl($path)
	{
		// Append the path with output format
		$path .= '&format=xml';

		$uri = new JUri($this->options->get('api.url') . '/api.php' . $path);

		if ($this->options->get('api.username', false))
		{
			$uri->setUser($this->options->get('api.username'));
		}

		if ($this->options->get('api.password', false))
		{
			$uri->setPass($this->options->get('api.password'));
		}

		return (string) $uri;
	}

	/**
	 * Method to build request parameters from a string array.
	 *
	 * @param   array  $params  string array that contains the parameters
	 *
	 * @return  string   request parameter
	 *
	 * @since   3.1.4
	 */
	public function buildParameter(array $params)
	{
		$path = '';

		foreach ($params as $param)
		{
			$path .= $param;

			if (next($params) == true)
			{
				$path .= '|';
			}
		}

		return $path;
	}

	/**
	 * Method to validate response for errors
	 *
	 * @param   JHttpresponse  $response  reponse from the mediawiki server
	 *
	 * @return  Object
	 *
	 * @since   3.1.4
	 *
	 * @throws  DomainException
	 */
	public function validateResponse($response)
	{
		$xml = simplexml_load_string($response->body);

		if (isset($xml->warnings))
		{
			throw new DomainException($xml->warnings->info);
		}

		if (isset($xml->error))
		{
			throw new DomainException($xml->error['info']);
		}

		return $xml;
	}
}
