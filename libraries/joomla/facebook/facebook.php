<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * 
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Joomla Platform class for interacting with a Facebook API instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * @since       12.1
 */
class JFacebook
{
	/**
	 * @var    JRegistry  Options for the Facebook object.
	 * @since  12.1
	 */
	protected $options;

	/**
	 * @var    JFacebookHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  12.1
	 */
	protected $client;

	/**
	 * @var    JFacebookUser  Facebook API object for user.
	 * @since  12.1
	 */
	protected $user;

	/**
	* @var    JFacebookStatus  Facebook API object for status.
	* @since  12.1
	*/
	protected $status;

	/**
	* @var    JFacebookCheckin  Facebook API object for checkin.
	* @since  12.1
	*/
	protected $checkin;

	/**
	* @var    JFacebookEvent  Facebook API object for event.
	* @since  12.1
	*/
	protected $event;

	/**
	* @var    JFacebookGroup  Facebook API object for group.
	* @since  12.1
	*/
	protected $group;

	/**
	* @var    JFacebookLink  Facebook API object for link.
	* @since  12.1
	*/
	protected $link;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry      $options  Facebook options object.
	 * @param   JFacebookHttp  $client   The HTTP client object.
	 * 
	 * @since   12.1
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
	 * 
	 * @since   12.1
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'user':
				if ($this->user == null)
				{
					$this->user = new JFacebookUser($this->options, $this->client);
				}
				return $this->user;

			case 'status':
				if ($this->status == null)
				{
					$this->status = new JFacebookStatus($this->options, $this->client);
				}
				return $this->status;

			case 'checkin':
				if ($this->checkin == null)
				{
					$this->checkin = new JFacebookCheckin($this->options, $this->client);
				}
				return $this->checkin;

			case 'event':
				if ($this->event == null)
				{
					$this->event = new JFacebookEvent($this->options, $this->client);
				}
				return $this->event;

			case 'group':
				if ($this->group == null)
				{
					$this->group = new JFacebookGroup($this->options, $this->client);
				}
				return $this->group;

			case 'link':
				if ($this->link == null)
				{
					$this->link = new JFacebookLink($this->options, $this->client);
				}
				return $this->link;
		}
	}

	/**
	 * Get an option from the JFacebook instance.
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
	 * Set an option for the JFacebook instance.
	 *
	* @param   string  $key    The name of the option to set.
	* @param   mixed   $value  The option value to set.
	*
	* @return  JFacebook  This object for method chaining.
	* 
	* @since   12.1
	*/
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
