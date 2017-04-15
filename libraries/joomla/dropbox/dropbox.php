<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Dropbox
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform class for interacting with a Google Cloud storage server instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Dropbox
 * @since       ??.?
 */
class JDropbox
{
	/**
	 * @var    JRegistry  Options for the Dropbox object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  ??.?
	 */
	protected $client;

	/**
	 * @var    JDropboxAccounts  Dropbox API object for Accounts.
	 * @since  ??.?
	 */
	protected $accounts;

	/**
	 * @var    JDropboxFiles  Dropbox API object for Files.
	 * @since  ??.?
	 */
	protected $files;

	/**
	 * @var    JDropboxFileops  Dropbox API object for File Operations.
	 * @since  ??.?
	 */
	protected $fileops;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry  $options  Dropbox options object.
	 * @param   JHttp      $client   The HTTP client object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 'api.dropbox.com');
		$this->options->def('api.content', 'api-content.dropbox.com');
		$this->options->def('api.oauth1.request_token', 'https://api.dropbox.com/1/oauth/request_token');
		$this->options->def('api.oauth2.authorize', 'https://www.dropbox.com/1/oauth2/authorize');
		$this->options->def('api.oauth2.access_token', 'https://api.dropbox.com/1/oauth2/token');
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve.
	 *
	 * @return  JDropboxObject  Dropbox API object
	 *
	 * @since   ??.?
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JDropbox' . ucfirst($name);

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
	 * Get an option from the JDropbox instance.
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
	 * Set an option for the JDropbox instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JDropbox  This object for method chaining.
	 *
	 * @since   ??.?
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
