<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Joomla Platform class for interacting with a Linkedin API instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 * @since       13.1
 */
class JLinkedin
{
	/**
	 * @var    JRegistry  Options for the Linkedin object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var JLinkedinOAuth The OAuth client.
	 * @since 13.1
	 */
	protected $oauth;

	/**
	 * @var    JLinkedinPeople  Linkedin API object for people.
	 * @since  13.1
	 */
	protected $people;

	/**
	 * @var    JLinkedinGroups  Linkedin API object for groups.
	 * @since  13.1
	 */
	protected $groups;

	/**
	 * @var    JLinkedinCompanies  Linkedin API object for companies.
	 * @since  13.1
	 */
	protected $companies;

	/**
	 * @var    JLinkedinJobs  Linkedin API object for jobs.
	 * @since  13.1
	 */
	protected $jobs;

	/**
	 * @var    JLinkedinStream  Linkedin API object for social stream.
	 * @since  13.1
	 */
	protected $stream;

	/**
	 * @var    JLinkedinCommunications  Linkedin API object for communications.
	 * @since  13.1
	 */
	protected $communications;

	/**
	 * Constructor.
	 *
	 * @param   JLinkedinOauth  $oauth    OAuth object
	 * @param   JRegistry       $options  Linkedin options object.
	 * @param   JHttp           $client   The HTTP client object.
	 *
	 * @since   13.1
	 */
	public function __construct(JLinkedinOauth $oauth = null, JRegistry $options = null, JHttp $client = null)
	{
		$this->oauth = $oauth;
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 'https://api.linkedin.com');
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JLinkedinObject  Linkedin API object (statuses, users, favorites, etc.).
	 *
	 * @since   13.1
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JLinkedin' . ucfirst($name);

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
	 * Get an option from the JLinkedin instance.
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
	 * Set an option for the Linkedin instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JLinkedin  This object for method chaining.
	 *
	 * @since   13.1
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
