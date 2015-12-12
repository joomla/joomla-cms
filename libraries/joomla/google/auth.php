<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Google authentication class abstract
 *
 * @since  12.3
 */
abstract class JGoogleAuth
{
	/**
	 * @var    \Joomla\Registry\Registry  Options for the Google authentication object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * Abstract method to authenticate to Google
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.3
	 */
	abstract public function authenticate();

	/**
	 * Verify if the client has been authenticated
	 *
	 * @return  boolean  Is authenticated
	 *
	 * @since   12.3
	 */
	abstract public function isAuthenticated();

	/**
	 * Abstract method to retrieve data from Google
	 *
	 * @param   string  $url      The URL for the request.
	 * @param   mixed   $data     The data to include in the request.
	 * @param   array   $headers  The headers to send with the request.
	 * @param   string  $method   The type of http request to send.
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   12.3
	 */
	abstract public function query($url, $data = null, $headers = null, $method = 'get');

	/**
	 * Get an option from the JGoogleAuth object.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   12.3
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the JGoogleAuth object.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JGoogleAuth  This object for method chaining.
	 *
	 * @since   12.3
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
