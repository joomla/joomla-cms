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
 * Joomla Platform class for interacting with an Amazons3 server instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3
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
	 * @var    JAmazons3OperationsService  Amazons3 API object for Service.
	 * @since  ??.?
	 */
	protected $service;

	/**
	 * @var    JAmazons3OperationsBuckets  Amazons3 API object for Buckets.
	 * @since  ??.?
	 */
	protected $buckets;

	/**
	 * @var    JAmazons3OperationsObjects  Amazons3 API object for Objects.
	 * @since  ??.?
	 */
	protected $objects;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry      $options  Amazons3 options object. Should include
	 *                                    api.accessKeyId and api.secretAccessKey
	 * @param   JAmazons3Http  $client   The HTTP client object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JAmazons3Http $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JAmazons3Http($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 's3.amazonaws.com');
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve.
	 *
	 * @return  JAmazons3Object  Amazons3 API object
	 *
	 * @since   ??.?
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JAmazons3Operations' . ucfirst($name);

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
	 * Get an option from the JAmazons3 instance.
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
	 * Set an option for the JAmazons3 instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JAmazons3  This object for method chaining.
	 *
	 * @since   ??.?
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
