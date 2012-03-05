<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub
 * @since       11.3
 */
abstract class JGithubObject
{
	/**
	 * @var    JRegistry  Options for the GitHub object.
	 * @since  11.3
	 */
	protected $options;

	/**
	 * @var    JGithubHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  11.3
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry    $options  GitHub options object.
	 * @param   JGithubHttp  $client   The HTTP client object.
	 *
	 * @since   11.3
	 */
	public function __construct(JRegistry $options = null, JGithubHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JGithubHttp($this->options);
	}

	/**
	 * Method to build and return a full request URL for the request.  This method will
	 * add appropriate pagination details if necessary and also prepend the API url
	 * to have a complete URL for the request.
	 *
	 * @param   string   $path   URL to inflect
	 * @param   integer  $page   Page to request
	 * @param   integer  $limit  Number of results to return per page
	 *
	 * @return  string   The request URL.
	 *
	 * @since   11.3
	 */
	protected function fetchUrl($path, $page = 0, $limit = 0)
	{
		// Get a new JUri object fousing the api url and given path.
		$uri = new JUri($this->options->get('api.url') . $path);

		if ($this->options->get('api.username', false))
		{
			$uri->setUser($this->options->get('api.username'));
		}

		if ($this->options->get('api.password', false))
		{
			$uri->setPass($this->options->get('api.password'));
		}

		// If we have a defined page number add it to the JUri object.
		if ($page > 0)
		{
			$uri->setVar('page', (int) $page);
		}

		// If we have a defined items per page add it to the JUri object.
		if ($limit > 0)
		{
			$uri->setVar('per_page', (int) $limit);
		}

		return (string) $uri;
	}
}
