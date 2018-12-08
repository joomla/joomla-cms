<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Google API object class for the Joomla Platform.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/google` package via Composer instead
 */
abstract class JGoogleEmbed
{
	/**
	 * @var    Registry  Options for the Google data object.
	 * @since  3.1.4
	 */
	protected $options;

	/**
	 * @var    JUri  URI of the page being rendered.
	 * @since  3.1.4
	 */
	protected $uri;

	/**
	 * Constructor.
	 *
	 * @param   Registry  $options  Google options object
	 * @param   JUri      $uri      URL of the page being rendered
	 *
	 * @since   3.1.4
	 */
	public function __construct(Registry $options = null, JUri $uri = null)
	{
		$this->options = $options ? $options : new Registry;
		$this->uri = $uri ? $uri : JUri::getInstance();
	}

	/**
	 * Method to retrieve the javascript header for the embed API
	 *
	 * @return  string  The header
	 *
	 * @since   3.1.4
	 */
	public function isSecure()
	{
		return $this->uri->getScheme() == 'https';
	}

	/**
	 * Method to retrieve the header for the API
	 *
	 * @return  string  The header
	 *
	 * @since   3.1.4
	 */
	abstract public function getHeader();

	/**
	 * Method to retrieve the body for the API
	 *
	 * @return  string  The body
	 *
	 * @since   3.1.4
	 */
	abstract public function getBody();

	/**
	 * Method to output the javascript header for the embed API
	 *
	 * @return  null
	 *
	 * @since   3.1.4
	 */
	public function echoHeader()
	{
		echo $this->getHeader();
	}

	/**
	 * Method to output the body for the API
	 *
	 * @return  null
	 *
	 * @since   3.1.4
	 */
	public function echoBody()
	{
		echo $this->getBody();
	}

	/**
	 * Get an option from the JGoogleEmbed instance.
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
	 * Set an option for the JGoogleEmbed instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JGoogleEmbed  This object for method chaining.
	 *
	 * @since   3.1.4
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
