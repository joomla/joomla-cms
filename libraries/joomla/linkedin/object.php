<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

use Joomla\Registry\Registry;

/**
 * Linkedin API object class for the Joomla Platform.
 *
 * @since  3.2.0
 */
abstract class JLinkedinObject
{
	/**
	 * @var    Registry  Options for the Linkedin object.
	 * @since  3.2.0
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  3.2.0
	 */
	protected $client;

	/**
	 * @var   JLinkedinOAuth The OAuth client.
	 * @since  3.2.0
	 */
	protected $oauth;

	/**
	 * Constructor.
	 *
	 * @param   Registry        $options  Linkedin options object.
	 * @param   JHttp           $client   The HTTP client object.
	 * @param   JLinkedinOAuth  $oauth    The OAuth client.
	 *
	 * @since   3.2.0
	 */
	public function __construct(Registry $options = null, JHttp $client = null, JLinkedinOAuth $oauth = null)
	{
		$this->options = isset($options) ? $options : new Registry;
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
	 * @since 3.2.0
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
	 * @since   3.2.0
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
	 * @since   3.2.0
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
