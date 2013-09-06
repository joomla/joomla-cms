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
 * Joomla Platform class for interacting with an Rackspace server instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
class JRackspace
{
	/**
	 * @var    JRegistry  Options for the Rackspace object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JRackspaceHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  ??.?
	 */
	protected $client;

	/**
	 * @var    JRackspaceCdn  API Operations for CDN Services
	 * @since  ??.?
	 */
	protected $cdn;

	/**
	 * @var    JRackspacePublic Public Access to the Cloud Files Account
	 * @since  ??.?
	 */
	protected $public;

	/**
	 * @var    JRackspaceStorage  API Operations for Storage Services
	 * @since  ??.?
	 */
	protected $storage;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry       $options  Rackspace options object. Should include
	 *                                    api.accessKeyId and api.secretAccessKey
	 * @param   JRackspaceHttp  $client   The HTTP client object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JRackspaceHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JRackspaceHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('auth.host.us', 'identity.api.rackspacecloud.com');
		$this->options->def('auth.host.uk', 'lon.identity.api.rackspacecloud.com');
		$this->options->def('storage.host', 'storage.clouddrive.com');
		$this->options->def('cdn.host', 'cdn.clouddrive.com');
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve.
	 *
	 * @return  JRackspaceObject  Rackspace API object
	 *
	 * @since   ??.?
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JRackspace' . ucfirst($name);

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
	 * Get an option from the JRackspace instance.
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
	 * Set an option for the JRackspace instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JRackspace  This object for method chaining.
	 *
	 * @since   ??.?
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
