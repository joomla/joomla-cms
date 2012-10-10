<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform class for interacting with the Google APIs.
 *
 * @property-read  JGoogleDate    $gists   Google API object for gists.
 * @property-read  JGoogleEmbed   $embed   Google API object for embed generation.
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       1234
 */
class JGoogle
{
	/**
	 * @var    JRegistry  Options for the Google object.
	 * @since  1234
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  1234
	 */
	protected $client;

	/**
	 * @var    JGoogleData  Google API object for data request.
	 * @since  1234
	 */
	protected $data;

	/**
	 * @var    JGoogleEmbed  Google API object for embed generation.
	 * @since  1234
	 */
	protected $embed;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry  $options  Google options object.
	 * @param   JHttp      $client   The HTTP client object.
	 *
	 * @since   1234
	 */
	public function __construct(JRegistry $options = null, JHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JHttp($this->options);
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JGoogleObject  Google API object (data, embed).
	 *
	 * @since   1234
	 */
	public function __get($name)
	{
		if ($name == 'data')
		{
			if ($this->data == null)
			{
				$this->data = new JGoogleData($this->data, $this->client);
			}
			return $this->data;
		}

		if ($name == 'embed')
		{
			if ($this->embed == null)
			{
				$this->embed = new JGoogleData($this->options);
			}
			return $this->embed;
		}
	}

	/**
	 * Get an option from the JGoogle instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   1234
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the JGoogle instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JGoogle  This object for method chaining.
	 *
	 * @since   1234
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
