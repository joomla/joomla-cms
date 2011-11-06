<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Joomla Platform class for interacting with a GitHub server instance.
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub
 * @since       11.4
 */
class JGithub
{
	/**
	 * @var    JRegistry  Options for the GitHub object.
	 * @since  11.4
	 */
	protected $options;

	/**
	 * @var    JGithubHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  11.4
	 */
	protected $client;

	/**
	 * @var    JGithubGists  GitHub API object for gists.
	 * @since  11.4
	 */
	protected $gists;

	/**
	 * @var    JGithubIssues  GitHub API object for issues.
	 * @since  11.4
	 */
	protected $issues;

	/**
	 * @var    JGithubPulls  GitHub API object for pulls.
	 * @since  11.4
	 */
	protected $pulls;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry    $options  GitHub options object.
	 * @param   JGithubHttp  $client   The HTTP client object.
	 *
	 * @since   11.4
	 */
	public function __construct(JRegistry $options = null, JGithubHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry();
		$this->client = isset($client) ? $client : new JGithubHttp();
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JGithubObject  GitHub API object (gists, issues, pulls, etc).
	 *
	 * @since   11.4
	 */
	public function __get($name)
	{
		if ($name == 'gists')
		{
			if ($this->gists == null)
			{
				$this->gists = new JGithubGists($this->options, $this->client);
			}
			return $this->gists;
		}

		if ($name == 'issues')
		{
			if ($this->issues == null)
			{
				$this->issues = new JGithubIssues($this->options, $this->client);
			}
			return $this->issues;
		}

		if ($name == 'pulls')
		{
			if ($this->pulls == null)
			{
				$this->pulls = new JGithubPulls($this->options, $this->client);
			}
			return $this->pulls;
		}
	}
}
