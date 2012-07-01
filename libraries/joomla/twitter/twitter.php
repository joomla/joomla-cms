<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Joomla Platform class for interacting with a Twitter API instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
class JTwitter
{
	/**
	 * @var    JRegistry  Options for the GitHub object.
	 * @since  12.1
	 */
	protected $options;

	/**
	 * @var    JTwitterHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  12.1
	 */
	protected $client;

	/**
	 * @var    JTwitterFriends  Twitter API object for friends.
	 * @since  12.1
	 */
	protected $friends;

	/**
	 * @var    JTwitterUsers  Twitter API object for users.
	 * @since  12.1
	 */
	protected $users;

	/**
	 * @var    JTwitterHelp  Twitter API object for help.
	 * @since  12.1
	 */
	protected $help;

	/**
	 * @var    JTwitterStatuses  Twitter API object for statuses.
	 * @since  12.1
	 */
	protected $statuses;

	/**
	 * @var    JTwitterSearch  Twitter API object for search.
	 * @since  12.1
	 */
	protected $search;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry     $options  Twitter options object.
	 * @param   JTwitterHttp  $client   The HTTP client object.
	 *
	 * @since   12.1
	 */
	public function __construct(JRegistry $options = null, JTwitterHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JTwitterHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 'https://api.twitter.com');
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JTwitterObject  Twitter API object (statuses, users, favorites, etc.).
	 *
	 * @since   12.1
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'friends':
				if ($this->friends == null)
				{
					$this->friends = new JTwitterFriends($this->options, $this->client);
				}
				return $this->friends;

			case 'help':
				if ($this->help == null)
				{
					$this->help = new JTwitterHelp($this->options, $this->client);
				}
				return $this->help;

			case 'statuses':
				if ($this->statuses == null)
				{
					$this->statuses = new JTwitterStatuses($this->options, $this->client);
				}
				return $this->statuses;

			case 'users':
				if ($this->users == null)
				{
					$this->users = new JTwitterUsers($this->options, $this->client);
				}
				return $this->users;

			case 'search':
				if ($this->search == null)
				{
					$this->search = new JTwitterSearch($this->options, $this->client);
				}
				return $this->search;
		}
	}

	/**
	 * Get an option from the JTwitter instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   12.1
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the JTwitter instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JTwitter  This object for method chaining.
	 *
	 * @since   12.1
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
