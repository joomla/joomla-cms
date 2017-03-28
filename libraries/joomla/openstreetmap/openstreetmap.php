<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Openstreetmap
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

use Joomla\Registry\Registry;

/**
 * Joomla Platform class for interact with Openstreetmap API.
 *
 * @since       13.1
 * @deprecated  4.0  Use the `joomla/openstreetmap` package via Composer instead
 */
class JOpenstreetmap
{
	/**
	 * Options for the Openstreetmap object.
	 *
	 * @var    Registry
	 * @since  13.1
	 */
	protected $options;

	/**
	 * The HTTP client object to use in sending HTTP requests.
	 *
	 * @var    JHttp
	 * @since  13.1
	 */
	protected $client;

	/**
	 * The OAuth client.
	 *
	 * @var   JOpenstreetmapOauth
	 * @since 13.1
	 */
	protected $oauth;

	/**
	 * Openstreetmap API object for changesets.
	 *
	 * @var    JOpenstreetmapChangesets
	 * @since  13.1
	 */
	protected $changesets;

	/**
	 * Openstreetmap API object for elements.
	 *
	 * @var    JOpenstreetmapElements
	 * @since  13.1
	 */
	protected $elements;

	/**
	 * Openstreetmap API object for GPS.
	 *
	 * @var    JOpenstreetmapGps
	 * @since  13.1
	 */
	protected $gps;

	/**
	 * Openstreetmap API object for info.
	 *
	 * @var    JOpenstreetmapInfo
	 * @since  13.1
	 */
	protected $info;

	/**
	 * Openstreetmap API object for user.
	 *
	 * @var    JOpenstreetmapUser
	 * @since  13.1
	 */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param   JOpenstreetmapOauth  $oauth    Openstreetmap oauth client
	 * @param   Registry             $options  Openstreetmap options object
	 * @param   JHttp                $client   The HTTP client object
	 *
	 * @since   13.1
	 */
	public function __construct(JOpenstreetmapOauth $oauth = null, Registry $options = null, JHttp $client = null)
	{
		$this->oauth = $oauth;
		$this->options = isset($options) ? $options : new Registry;
		$this->client  = isset($client) ? $client : new JHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 'http://api.openstreetmap.org/api/0.6/');

		// $this->options->def('api.url', 'http://api06.dev.openstreetmap.org/api/0.6/');
	}

	/**
	 * Method to get object instances
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JOpenstreetmapObject  Openstreetmap API object
	 *
	 * @since   13.1
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JOpenstreetmap' . ucfirst($name);

		if (class_exists($class))
		{
			if (false == isset($this->$name))
			{
				$this->$name = new $class($this->options, $this->client, $this->oauth);
			}

			return $this->$name;
		}

		throw new InvalidArgumentException(sprintf('Argument %s produced an invalid class name: %s', $name, $class));
	}

	/**
	 * Get an option from the JOpenstreetmap instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   13.1
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the Openstreetmap instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JOpenstreetmap  This object for method chaining.
	 *
	 * @since   13.1
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
