<?php
/**
 * @package     Joomla.Platform
 * @subpackage  MediaWiki
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Joomla Platform class for interacting with a Mediawiki server instance.
 *
 * @property-read  JMediawikiSites          $sites          MediaWiki API object for sites.
 * @property-read  JMediawikiPages          $pages          MediaWiki API object for pages.
 * @property-read  JMediawikiUsers          $users          MediaWiki API object for users.
 * @property-read  JMediawikiLinks          $links          MediaWiki API object for links.
 * @property-read  JMediawikiCategories     $categories     MediaWiki API object for categories.
 * @property-read  JMediawikiImages         $images         MediaWiki API object for images.
 * @property-read  JMediawikiSearch         $search         MediaWiki API object for search.
 *
 * @since  12.3
 */
class JMediawiki
{
	/**
	 * @var    Registry  Options for the MediaWiki object.
	 * @since  12.1
	 */
	protected $options;

	/**
	 * @var    JMediawikiHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  12.3
	 */
	protected $client;

	/**
	 * @var    JMediawikiSites  MediaWiki API object for Site.
	 * @since  12.3
	 */
	protected $sites;

	/**
	 * @var    JMediawikiPages  MediaWiki API object for pages.
	 * @since  12.1
	 */
	protected $pages;

	/**
	 * @var    JMediawikiUsers  MediaWiki API object for users.
	 * @since  12.3
	 */
	protected $users;

	/**
	 * @var    JMediawikiLinks  MediaWiki API object for links.
	 * @since  12.3
	 */
	protected $links;

	/**
	 * @var    JMediawikiCategories  MediaWiki API object for categories.
	 * @since  12.3
	 */
	protected $categories;

	/**
	 * @var    JMediawikiImages  MediaWiki API object for images.
	 * @since  12.3
	 */
	protected $images;

	/**
	 * @var    JMediawikiSearch  MediaWiki API object for search.
	 * @since  12.1
	 */
	protected $search;

	/**
     * Constructor.
     *
     * @param   Registry        $options  MediaWiki options object.
     * @param   JMediawikiHttp  $client   The HTTP client object.
     *
     * @since   12.3
     */
	public function __construct(Registry $options = null, JMediawikiHttp $client = null)
	{
		$this->options = isset($options) ? $options : new Registry;
		$this->client = isset($client) ? $client : new JMediawikiHttp($this->options);
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JMediaWikiObject  MediaWiki API object (users, reviews, etc).
	 *
	 * @since   12.3
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$name = strtolower($name);
		$class = 'JMediawiki' . ucfirst($name);
		$accessible = array(
			'categories',
			'images',
			'links',
			'pages',
			'search',
			'sites',
			'users',
		);

		if (class_exists($class) && in_array($name, $accessible))
		{
			if (!isset($this->$name))
			{
				$this->$name = new $class($this->options, $this->client);
			}

			return $this->$name;
		}

		throw new InvalidArgumentException(sprintf('Property %s is not accessible.', $name));
	}

	/**
	 * Get an option from the JMediawiki instance.
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
	 * Set an option for the JMediawiki instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JMediawiki  This object for method chaining.
	 *
	 * @since   12.3
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
