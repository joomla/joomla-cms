<?php
/**
 * @package     Joomla.Platform
 * @subpackage  AmazonS3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform class for interacting with a AmazonS3 server instance.
 *
 * @property-read  JAmazonS3Meta  $meta  AmazonS3 API object for meta.
 *
 * @package     Joomla.Platform
 * @subpackage  AmazonS3
 * @since       11.3
 */
class JAmazonS3
{
	/**
	 * @var    JRegistry  Options for the AmazonS3 object.
	 * @since  11.3
	 */
	protected $options;

	/**
	 * @var    JAmazonS3Http  The HTTP client object to use in sending HTTP requests.
	 * @since  11.3
	 */
	protected $client;

	/**
	 * @var    JAmazonS3Meta  AmazonS3 API object for meta.
	 * @since  13.1
	 */
	protected $meta;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry    $options  AmazonS3 options object.
	 * @param   JAmazonS3Http  $client   The HTTP client object.
	 *
	 * @since   11.3
	 */
	public function __construct(JRegistry $options = null, JAmazonS3Http $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JAmazonS3Http($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 's3.amazonaws.com');
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JAmazonS3Object  AmazonS3 API object (gists, issues, pulls, etc).
	 *
	 * @since   11.3
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JAmazonS3' . ucfirst($name);

		if (class_exists($class))
		{
			if (false == isset($this->$name))
			{
				$this->$name = new $class($this->options, $this->client);
			}

			return $this->$name;
		}

		throw new InvalidArgumentException(sprintf('Argument %s produced an invalid class name: %s', $name, $class));
	}

	/**
	 * Get an option from the JAmazonS3 instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   11.3
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the JAmazonS3 instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JAmazonS3  This object for method chaining.
	 *
	 * @since   11.3
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
