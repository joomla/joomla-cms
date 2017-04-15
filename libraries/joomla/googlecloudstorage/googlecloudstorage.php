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
 * Joomla Platform class for interacting with a Google Cloud storage server instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
class JGooglecloudstorage
{
	/**
	 * @var    JRegistry  Options for the Googlecloudstorage object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  ??.?
	 */
	protected $client;

	/**
	 * @var    JGooglecloudstorageService  Googlecloudstorage API object for Service.
	 * @since  ??.?
	 */
	protected $service;

	/**
	 * @var    JGooglecloudstorageBuckets  Googlecloudstorage API object for Buckets.
	 * @since  ??.?
	 */
	protected $buckets;

	/**
	 * @var    JGooglecloudstorageObjects  Googlecloudstorage API object for Objects.
	 * @since  ??.?
	 */
	protected $objects;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry  $options  Googlecloudstorage options object.
	 * @param   JHttp      $client   The HTTP client object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 'storage.googleapis.com');
		$this->options->def('api.host', 'accounts.google.com');
		$this->options->def('api.oauth.privateKeyPassword', 'notasecret');
		$this->options->def('api.oauth.assertionTarget', 'https://accounts.google.com/o/oauth2/token');
		$this->options->def('api.oauth.scope.read-only', 'https://www.googleapis.com/auth/devstorage.read_only');
		$this->options->def('api.oauth.scope.read-write', 'https://www.googleapis.com/auth/devstorage.read_write');
		$this->options->def('api.oauth.scope.full-control', 'https://www.googleapis.com/auth/devstorage.full_control');
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve.
	 *
	 * @return  JGooglecloudstorageObject  Googlecloudstorage API object
	 *
	 * @since   ??.?
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JGooglecloudstorage' . ucfirst($name);

		if (class_exists($class))
		{
			if (false == isset($this->$name))
			{
				$this->$name = new $class($this->options, $this->client);
			}

			return $this->$name;
		}

		throw new InvalidArgumentException(
			sprintf('Argument %s produced an invalid class name: %s', $name, $class)
		);
	}

	/**
	 * Get an option from the JGooglecloudstorage instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   ??.?
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the JGooglecloudstorage instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JGooglecloudstorage  This object for method chaining.
	 *
	 * @since   ??.?
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
