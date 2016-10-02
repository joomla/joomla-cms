<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Joomla Platform class for interacting with the Google APIs.
 *
 * @property-read  JGoogleData    $data    Google API object for data.
 * @property-read  JGoogleEmbed   $embed   Google API object for embed generation.
 *
 * @since       12.3
 * @deprecated  4.0  Use the `joomla/google` package via Composer instead
 */
class JGoogle
{
	/**
	 * @var    Registry  Options for the Google object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * @var    JGoogleAuth  The authentication client object to use in sending authenticated HTTP requests.
	 * @since  12.3
	 */
	protected $auth;

	/**
	 * @var    JGoogleData  Google API object for data request.
	 * @since  12.3
	 */
	protected $data;

	/**
	 * @var    JGoogleEmbed  Google API object for embed generation.
	 * @since  12.3
	 */
	protected $embed;

	/**
	 * Constructor.
	 *
	 * @param   Registry     $options  Google options object.
	 * @param   JGoogleAuth  $auth     The authentication client object.
	 *
	 * @since   12.3
	 */
	public function __construct(Registry $options = null, JGoogleAuth $auth = null)
	{
		$this->options = isset($options) ? $options : new Registry;
		$this->auth  = isset($auth) ? $auth : new JGoogleAuthOauth2($this->options);
	}

	/**
	 * Method to create JGoogleData objects
	 *
	 * @param   string       $name     Name of property to retrieve
	 * @param   Registry     $options  Google options object.
	 * @param   JGoogleAuth  $auth     The authentication client object.
	 *
	 * @return  JGoogleData  Google data API object.
	 *
	 * @since   12.3
	 */
	public function data($name, $options = null, $auth = null)
	{
		if ($this->options && !$options)
		{
			$options = $this->options;
		}

		if ($this->auth && !$auth)
		{
			$auth = $this->auth;
		}

		switch ($name)
		{
			case 'plus':
			case 'Plus':
				return new JGoogleDataPlus($options, $auth);
			case 'picasa':
			case 'Picasa':
				return new JGoogleDataPicasa($options, $auth);
			case 'adsense':
			case 'Adsense':
				return new JGoogleDataAdsense($options, $auth);
			case 'calendar':
			case 'Calendar':
				return new JGoogleDataCalendar($options, $auth);
			default:
				return;
		}
	}

	/**
	 * Method to create JGoogleEmbed objects
	 *
	 * @param   string    $name     Name of property to retrieve
	 * @param   Registry  $options  Google options object.
	 *
	 * @return  JGoogleEmbed  Google embed API object.
	 *
	 * @since   12.3
	 */
	public function embed($name, $options = null)
	{
		if ($this->options && !$options)
		{
			$options = $this->options;
		}

		switch ($name)
		{
			case 'maps':
			case 'Maps':
				return new JGoogleEmbedMaps($options);
			case 'analytics':
			case 'Analytics':
				return new JGoogleEmbedAnalytics($options);
			default:
				return;
		}
	}

	/**
	 * Get an option from the JGoogle instance.
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
	 * Set an option for the JGoogle instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JGoogle  This object for method chaining.
	 *
	 * @since   12.3
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
