<?php

/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 */

defined('JPATH_PLATFORM') or die();

/**
 * Joomla Platform class for interacting with a Facebook API instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 */

class JFacebook
{
	/**
	 * @var    JRegistry  Options for the Facebook object.
	 */
	protected $options;

	/**
	 * @var    JFacebookHttp  The HTTP client object to use in sending HTTP requests.
	 */
	protected $client;

	/**
	 * @var    JFacebookFriends  Facebook API object for friends.
	 */
	protected $friends;

	/**
	 * @var    JFacebookUser  Facebook API object for user.
	 */
	protected $user;

	/**
	* @var    JFacebookStatus  Facebook API object for status.
	*/
	protected $status;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry      $options  Facebook options object.
	 * @param   JFacebookHttp  $client   The HTTP client object.
	 */
	public function __construct(JRegistry $options = null, JFacebookHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JFacebookHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 'https://graph.facebook.com/');
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JFacebookObject  Facebook API object (status, user, friends etc).
	 */

	public function __get($name)
	{
		if ($name == 'friends')
		{
			$this->friends = new JFacebookFriends($this->options, $this->client);
			return $this->friends;
		}

		if ($name == 'user')
		{
			if ($this->user == null)
			{
				$this->user = new JFacebookUser($this->options, $this->client);
			}
			return $this->user;
		}

		if ($name == 'status')
		{
			if ($this->status == null)
			{
				$this->status = new JFacebookStatus($this->options, $this->client);
			}
			return $this->status;
		}
	}

	/**
	 * Get an option from the JFacebook instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 */

	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the JFacebook instance.
	 *
	* @param   string  $key    The name of the option to set.
	* @param   mixed   $value  The option value to set.
	*
	* @return  JFacebook  This object for method chaining.
	*/

	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
