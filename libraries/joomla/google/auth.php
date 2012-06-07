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
 * Google authentication class abstract
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       1234
 */
abstract class JGoogleAuth
{
	/**
	 * @var    JRegistry  Options for the Google authentication object.
	 * @since  1234
	 */
	protected $options;

	/**
	 * Abstract method to authenticate to Google
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1234
	 */
	abstract public function auth();

	/**
	 * Abstract method to retrieve data from Google
	 *
	 * @param   string  $url      The URL for the request.
	 * @param   mixed   $data     The data to include in the request.
	 * @param   array   $headers  The headers to send with the request.
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   1234
	 */
	abstract public function query($url, $data = null, $headers = null);

	/**
	 * Get an option from the JGoogleAuth object.
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
	 * Set an option for the JGoogleAuth object.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JGoogleAuth  This object for method chaining.
	 *
	 * @since   1234
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
