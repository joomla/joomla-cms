<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Joomla Platform class for interacting with a GitHub server instance.
 *
 * @property-read  JGithubPackageActivity       $activity       GitHub API object for activity.
 * @property-read  JGithubPackageAuthorization  $authorization  GitHub API object for authorizations.
 * @property-read  JGithubPackageData           $data           GitHub API object for data.
 * @property-read  JGithubPackageGists          $gists          GitHub API object for gists.
 * @property-read  JGithubPackageGitignore      $gitignore      GitHub API object for gitignore.
 * @property-read  JGithubPackageIssues         $issues         GitHub API object for issues.
 * @property-read  JGithubPackageMarkdown       $markdown       GitHub API object for markdown.
 * @property-read  JGithubPackageOrgs           $orgs           GitHub API object for orgs.
 * @property-read  JGithubPackagePulls          $pulls          GitHub API object for pulls.
 * @property-read  JGithubPackageRepositories   $repositories   GitHub API object for repositories.
 * @property-read  JGithubPackageSearch         $search         GitHub API object for search.
 * @property-read  JGithubPackageUsers          $users          GitHub API object for users.
 *
 * @property-read  JGithubRefs        $refs        Deprecated GitHub API object for referencess.
 * @property-read  JGithubForks       $forks       Deprecated GitHub API object for forks.
 * @property-read  JGithubCommits     $commits     Deprecated GitHub API object for commits.
 * @property-read  JGithubMilestones  $milestones  Deprecated GitHub API object for commits.
 * @property-read  JGithubStatuses    $statuses    Deprecated GitHub API object for commits.
 * @property-read  JGithubAccount     $account     Deprecated GitHub API object for account references.
 * @property-read  JGithubHooks       $hooks       Deprecated GitHub API object for hooks.
 * @property-read  JGithubMeta        $meta        Deprecated GitHub API object for meta.
 *
 * @since  11.3
 */
class JGithub
{
	/**
	 * @var    Registry  Options for the GitHub object.
	 * @since  11.3
	 */
	protected $options;

	/**
	 * @var    JGithubHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  11.3
	 */
	protected $client;

	/**
	 * @var    array  List of known packages.
	 * @since  3.3 (CMS)
	 */
	protected $packages = array('activity', 'authorization', 'data', 'gists', 'gitignore', 'issues',
		'markdown', 'orgs', 'pulls', 'repositories', 'users');

	/**
	 * @var    array  List of known legacy packages.
	 * @since  3.3 (CMS)
	 */
	protected $legacyPackages = array('refs', 'forks', 'commits', 'milestones', 'statuses', 'account', 'hooks', 'meta');

	/**
	 * Constructor.
	 *
	 * @param   Registry     $options  GitHub options object.
	 * @param   JGithubHttp  $client   The HTTP client object.
	 *
	 * @since   11.3
	 */
	public function __construct(Registry $options = null, JGithubHttp $client = null)
	{
		$this->options = isset($options) ? $options : new Registry;
		$this->client  = isset($client) ? $client : new JGithubHttp($this->options);

		// Setup the default API url if not already set.
		$this->options->def('api.url', 'https://api.github.com');
	}

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @throws RuntimeException
	 *
	 * @since   11.3
	 * @return  JGithubObject  GitHub API object (gists, issues, pulls, etc).
	 */
	public function __get($name)
	{
		if (false == in_array($name, $this->packages))
		{
			// Check for a legacy class
			if (in_array($name, $this->legacyPackages))
			{
				if (false == isset($this->$name))
				{
					$className = 'JGithub' . ucfirst($name);

					$this->$name = new $className($this->options, $this->client);
				}

				return $this->$name;
			}

			throw new RuntimeException(sprintf('%1$s - Unknown package %2$s', __METHOD__, $name));
		}

		if (false == isset($this->$name))
		{
			$className = 'JGithubPackage' . ucfirst($name);

			$this->$name = new $className($this->options, $this->client);
		}

		return $this->$name;
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
