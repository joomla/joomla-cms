<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Openstreetmap
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Joomla Platform class for interact with Openstreetmap API.
 *
 * @package     Joomla.Platform
 * @subpackage  Openstreetmap
 *
 * @since       13.1
 */
class JOpenstreetmap
{
	/**
	 * @var    JRegistry  Options for the Openstreetmap object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp      The HTTP client object to use in sending HTTP requests.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var   JOpenstreetmapOauth  The OAuth client.
	 * @since 13.1
	 */
	protected $oauth;

	/**
	 * @var    JOpenstreetmapChangesets  Openstreetmap API object for changesets.
	 * @since  13.1
	 */
	protected $changesets;

	/**
	 * @var    JOpenstreetmapElements  Openstreetmap API object for elements.
	 * @since  13.1
	 */
	protected $elements;

	/**
	 * @var   JOpenstreetmapGps  Openstreetmap API object for gps.
	 * @since  13.1
	 */
	protected $gps;

	/**
	 * @var    JOpenstreetmapInfo  Openstreetmap API object for info.
	 * @since  13.1
	 */
	protected $info;

	/**
	 * @var    JOpenstreetmapUser  Openstreetmap API object for user.
	 * @since  13.1
	 */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param   JOpenstreetmapOauth  $oauth    Openstreetmap oauth client.
	 * @param   JRegistry            $options  Openstreetmap options object.
	 * @param   JOpenstreetmapHttp   $client   The HTTP client object.
	 *
	 * @since   13.1
	 */
	public function __construct(JOpenstreetmapOauth $oauth = null, JRegistry $options = null, JHttp $client = null)
	{
		$this->oauth = $oauth;
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 'http://api.openstreetmap.org/api/0.6/');

		// $this->options->def('api.url', 'http://api06.dev.openstreetmap.org/api/0.6/');
	}

	/**	
	 * Method to get object instances
	 * 
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JOpenstreetmapObject  Openstreetmap API object .
	 *
	 * @since   13.1
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'changesets':
				if ($this->changesets == null)
				{
					$this->changesets = new JOpenstreetmapChangesets($this->options, $this->client, $this->oauth);
				}
				return $this->changesets;

			case 'elements':
				if ($this->elements == null)
				{
					$this->elements = new JOpenstreetmapElements($this->options, $this->client, $this->oauth);
				}
				return $this->elements;

			case 'gps':
				if ($this->gps == null)
				{
					$this->gps = new JOpenstreetmapGps($this->options, $this->client, $this->oauth);
				}
				return $this->gps;

			case 'info':
				if ($this->info == null)
				{
					$this->info = new JOpenstreetmapInfo($this->options, $this->client, $this->oauth);
				}
				return $this->info;

			case 'user':
				if ($this->user == null)
				{
					$this->user = new JOpenstreetmapUser($this->options, $this->client, $this->oauth);
				}
				return $this->user;
		}
	}

	/**
	 * Get an option from the JOpenstreetmap instance.
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
	 * Set an option for the Openstreetmap instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JOpenstreetmap  This object for method chaining.
	 *
	 * @since   13.1
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
