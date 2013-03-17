<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('JPATH_PLATFORM') or die();


/**
 * Facebook API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @since       13.1
 */
abstract class JFacebookObject
{
	/**
	 * @var    JRegistry  Options for the Facebook object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var    JFacebookOAuth  The OAuth client.
	 * @since  13.1
	 */
	protected $oauth;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry       $options  Facebook options object.
	 * @param   JHttp           $client   The HTTP client object.
	 * @param   JFacebookOAuth  $oauth    The OAuth client.
	 *
	 * @since   13.1
	 */
	public function __construct(JRegistry $options = null, JHttp $client = null, JFacebookOAuth $oauth = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JHttp($this->options);
		$this->oauth = $oauth;
	}

	/**
	 * Method to build and return a full request URL for the request.  This method will
	 * add appropriate pagination details if necessary and also prepend the API url
	 * to have a complete URL for the request.
	 *
	 * @param   string     $path    URL to inflect.
	 * @param   integer    $limit   The number of objects per page.
	 * @param   integer    $offset  The object's number on the page.
	 * @param   timestamp  $until   A unix timestamp or any date accepted by strtotime.
	 * @param   timestamp  $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  string  The request URL.
	 *
	 * @since   13.1
	 */
	protected function fetchUrl($path, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		// Get a new JUri object fousing the api url and given path.
		$uri = new JUri($this->options->get('api.url') . $path);

		if ($limit > 0)
		{
			$uri->setVar('limit', (int) $limit);
		}

		if ($offset > 0)
		{
			$uri->setVar('offset', (int) $offset);
		}

		if ($until != null)
		{
			$uri->setVar('until', $until);
		}

		if ($since != null)
		{
			$uri->setVar('since', $since);
		}

		return (string) $uri;
	}

	/**
	 * Method to send the request.
	 *
	 * @param   string   $path     The path of the request to make.
	 * @param   mixed    $data     Either an associative array or a string to be sent with the post request.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request
	 * @param   integer  $limit    The number of objects per page.
	 * @param   integer  $offset   The object's number on the page.
	 * @param   string   $until    A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since    A unix timestamp or any date accepted by strtotime.
	 *
	 * @return   mixed  The request response.
	 *
	 * @since    13.1
	 * @throws   DomainException
	 */
	public function sendRequest($path, $data = '', array $headers = null, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		// Send the request.
		$response = $this->client->get($this->fetchUrl($path, $limit, $offset, $until, $since), $headers);

		$response = json_decode($response->body);

		// Validate the response.
		if (property_exists($response, 'error'))
		{
			throw new RuntimeException($response->error->message);
		}

		return $response;
	}

	/**
	 * Method to get an object.
	 *
	 * @param   string  $object  The object id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function get($object)
	{
		if ($this->oauth != null)
		{
			if ($this->oauth->isAuthenticated())
			{
				$response = $this->oauth->query($this->fetchUrl($object));

				return json_decode($response->body);
			}
			else
			{
				return false;
			}
		}

		// Send the request.
		return $this->sendRequest($object);
	}

	/**
	 * Method to get object's connection.
	 *
	 * @param   string   $object        The object id.
	 * @param   string   $connection    The object's connection name.
	 * @param   string   $extra_fields  URL fields.
	 * @param   integer  $limit         The number of objects per page.
	 * @param   integer  $offset        The object's number on the page.
	 * @param   string   $until         A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since         A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getConnection($object, $connection = null, $extra_fields = '', $limit = 0, $offset = 0, $until = null, $since = null)
	{
		$path = $object . '/' . $connection . $extra_fields;

		if ($this->oauth != null)
		{
			if ($this->oauth->isAuthenticated())
			{
				$response = $this->oauth->query($this->fetchUrl($path, $limit, $offset, $until, $since));

				if (strcmp($response->body, ''))
				{
					return json_decode($response->body);
				}
				else
				{
					return $response->headers['Location'];
				}
			}
			else
			{
				return false;
			}
		}

		// Send the request.
		return $this->sendRequest($path, '', null, $limit, $offset, $until, $since);
	}

	/**
	 * Method to create a connection.
	 *
	 * @param   string  $object      The object id.
	 * @param   string  $connection  The object's connection name.
	 * @param   array   $parameters  The POST request parameters.
	 * @param   array   $headers     An array of name-value pairs to include in the header of the request
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createConnection($object, $connection = null, $parameters = null, array $headers = null)
	{
		if ($this->oauth->isAuthenticated())
		{
			// Build the request path.
			if ($connection != null)
			{
				$path = $object . '/' . $connection;
			}
			else
			{
				$path = $object;
			}

			// Send the post request.
			$response = $this->oauth->query($this->fetchUrl($path), $parameters, $headers, 'post');

			return json_decode($response->body);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to delete a connection.
	 *
	 * @param   string  $object        The object id.
	 * @param   string  $connection    The object's connection name.
	 * @param   string  $extra_fields  URL fields.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function deleteConnection($object, $connection = null, $extra_fields = '')
	{
		if ($this->oauth->isAuthenticated())
		{
			// Build the request path.
			if ($connection != null)
			{
				$path = $object . '/' . $connection . $extra_fields;
			}
			else
			{
				$path = $object . $extra_fields;
			}

			// Send the delete request.
			$response = $this->oauth->query($this->fetchUrl($path), null, array(), 'delete');

			return json_decode($response->body);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method used to set the OAuth client.
	 *
	 * @param   JFacebookOAuth  $oauth  The OAuth client object.
	 *
	 * @return  JFacebookObject  This object for method chaining.
	 *
	 * @since   13.1
	 */
	public function setOAuth($oauth)
	{
		$this->oauth = $oauth;

		return $this;
	}

	/**
	 * Method used to get the OAuth client.
	 *
	 * @return  JFacebookOAuth  The OAuth client
	 *
	 * @since   13.1
	 */
	public function getOAuth()
	{
		return $this->oauth;
	}
}
