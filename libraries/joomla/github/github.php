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
 * Joomla Platform class for interacting with a GitHub server instance.
 *
 * @property-read  JGithubGists    $gists    GitHub API object for gists.
 * @property-read  JGithubIssues   $issues   GitHub API object for issues.
 * @property-read  JGithubPulls    $pulls    GitHub API object for pulls.
 * @property-read  JGithubRefs     $refs     GitHub API object for referencess.
 * @property-read  JGithubForks    $forks    GitHub API object for forks.
 * @property-read  JGithubCommits  $commits  GitHub API object for commits.
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub
 * @since       11.3
 */
class JGithub
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
	 * @var    JGithubGists  GitHub API object for gists.
	 * @since  11.3
	 */
	protected $gists;

	/**
	 * @var    JGithubIssues  GitHub API object for issues.
	 * @since  11.3
	 */
	protected $issues;

	/**
	 * @var    JGithubPulls  GitHub API object for pulls.
	 * @since  11.3
	 */
	protected $pulls;

	/**
	 * @var    JGithubRefs  GitHub API object for referencess.
	 * @since  11.3
	 */
	protected $refs;

	/**
	 * @var    JGithubForks  GitHub API object for forks.
	 * @since  11.3
	 */
	protected $forks;

	/**
	 * @var    JGithubCommits  GitHub API object for commits.
	 * @since  12.1
	 */
	protected $commits;

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
		$this->client  = isset($client) ? $client : new JGithubHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 'https://api.github.com');
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JGithubObject  GitHub API object (gists, issues, pulls, etc).
	 *
	 * @since   11.3
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

		if ($name == 'refs')
		{
			if ($this->refs == null)
			{
				$this->refs = new JGithubRefs($this->options, $this->client);
			}
			return $this->refs;
		}

		if ($name == 'forks')
		{
			if ($this->forks == null)
			{
				$this->forks = new JGithubForks($this->options, $this->client);
			}
			return $this->forks;
		}

		if ($name == 'commits')
		{
			if ($this->commits == null)
			{
				$this->commits = new JGithubCommits($this->options, $this->client);
			}
			return $this->commits;
		}
	}

	/**
	 * Get an option from the JGitHub instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   11.3
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the JGitHub instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JGitHub  This object for method chaining.
	 *
	 * @since   11.3
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
