<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Linkedin API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 * @since       13.1
 */
abstract class JLinkedinObject
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
	 * @var   JLinkedinOAuth The OAuth client.
	 * @since  13.1
	 */
	protected $oauth;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry       $options  Linkedin options object.
	 * @param   JHttp           $client   The HTTP client object.
	 * @param   JLinkedinOAuth  $oauth    The OAuth client.
	 *
	 * @since   13.1
	 */
	public function __construct(JRegistry $options = null, JHttp $client = null, JLinkedinOAuth $oauth = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JHttp($this->options);
		$this->oauth = $oauth;
	}

	/**
	 * Method to convert boolean to string.
	 *
	 * @param   boolean  $bool  The boolean value to convert.
	 *
	 * @return  string  String with the converted boolean.
	 *
	 * @since 13.1
	 */
	public function booleanToString($bool)
	{
		if ($bool)
		{
			return 'true';
		}
		else
		{
			return 'false';
		}
	}

	/**
	 * Get an option from the JLinkedinObject instance.
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
	 * Set an option for the JLinkedinObject instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JLinkedinObject  This object for method chaining.
	 *
	 * @since   13.1
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
