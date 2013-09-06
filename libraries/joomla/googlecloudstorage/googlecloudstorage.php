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
 * Joomla Platform class for interacting with an Googlecloudstorage server instance.
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
	 * @var    JGooglecloudstorageHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  ??.?
	 */
	protected $client;

	/**
	 * @var    JGooglecloudstorageService  Googlecloudstorage API object for Service.
	 * @since  ??.?
	 */
	protected $service;

	/**
	 * @var    JGooglecloudstorageBucket  Googlecloudstorage API object for Buckets.
	 * @since  ??.?
	 */
	protected $bucket;

	/**
	 * @var    JGooglecloudstorageObject  Googlecloudstorage API object for Objects.
	 * @since  ??.?
	 */
	protected $object;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry      $options  Googlecloudstorage options object. Should include
	 *                                    api.accessKeyId and api.secretAccessKey
	 * @param   JGooglecloudstorageHttp  $client   The HTTP client object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JGooglecloudstorageHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JGooglecloudstorageHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 's3.amazonaws.com');
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
