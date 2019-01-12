<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

use Joomla\Registry\Registry;

/**
 * Joomla Platform class for interacting with a Twitter API instance.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/twitter` package via Composer instead
 */
class JTwitter
{
	/**
	 * @var    Registry  Options for the JTwitter object.
	 * @since  3.1.4
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  3.1.4
	 */
	protected $client;

	/**
	 * @var    JTwitterOAuth The OAuth client.
	 * @since  3.1.4
	 */
	protected $oauth;

	/**
	 * @var    JTwitterFriends  Twitter API object for friends.
	 * @since  3.1.4
	 */
	protected $friends;

	/**
	 * @var    JTwitterUsers  Twitter API object for users.
	 * @since  3.1.4
	 */
	protected $users;

	/**
	 * @var    JTwitterHelp  Twitter API object for help.
	 * @since  3.1.4
	 */
	protected $help;

	/**
	 * @var    JTwitterStatuses  Twitter API object for statuses.
	 * @since  3.1.4
	 */
	protected $statuses;

	/**
	 * @var    JTwitterSearch  Twitter API object for search.
	 * @since  3.1.4
	 */
	protected $search;

	/**
	 * @var    JTwitterFavorites  Twitter API object for favorites.
	 * @since  3.1.4
	 */
	protected $favorites;

	/**
	 * @var    JTwitterDirectMessages  Twitter API object for direct messages.
	 * @since  3.1.4
	 */
	protected $directMessages;

	/**
	 * @var    JTwitterLists  Twitter API object for lists.
	 * @since  3.1.4
	 */
	protected $lists;

	/**
	 * @var    JTwitterPlaces  Twitter API object for places & geo.
	 * @since  3.1.4
	 */
	protected $places;

	/**
	 * @var    JTwitterTrends  Twitter API object for trends.
	 * @since  3.1.4
	 */
	protected $trends;

	/**
	 * @var    JTwitterBlock  Twitter API object for block.
	 * @since  3.1.4
	 */
	protected $block;

	/**
	 * @var    JTwitterProfile  Twitter API object for profile.
	 * @since  3.1.4
	 */
	protected $profile;

	/**
	 * Constructor.
	 *
	 * @param   JTwitterOauth  $oauth    The oauth client.
	 * @param   Registry       $options  Twitter options object.
	 * @param   JHttp          $client   The HTTP client object.
	 *
	 * @since   3.1.4
	 */
	public function __construct(JTwitterOAuth $oauth = null, Registry $options = null, JHttp $client = null)
	{
		$this->oauth = $oauth;
		$this->options = isset($options) ? $options : new Registry;
		$this->client  = isset($client) ? $client : new JHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 'https://api.twitter.com/1.1');
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JTwitterObject  Twitter API object (statuses, users, favorites, etc.).
	 *
	 * @since   3.1.4
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JTwitter' . ucfirst($name);

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
	 * Get an option from the JTwitter instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   3.1.4
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
	 * @since   3.1.4
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
