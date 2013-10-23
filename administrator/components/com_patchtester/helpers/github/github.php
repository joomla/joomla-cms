<?php
/**
 * @package    PatchTester
 *
 * @copyright  Copyright (C) 2011 - 2012 Ian MacLennan, Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Extended JGithub class allowing additional JGithubObject instances to be used
 *
 * @property-read  PTGithubRepos  $repos  GitHub API object for repos.
 *
 * @package  PatchTester
 * @since    2.0
 */
class PTGithub extends JGithub
{
	/**
	 * @var    PTGithubRepos
	 * @since  2.0
	 */
	protected $repos;

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JGithubObject  GitHub API object (gists, issues, pulls, etc).
	 *
	 * @since   2.0
	 */
	public function __get($name)
	{
		if ($name == 'repos')
		{
			if ($this->repos == null)
			{
				$this->repos = new PTGithubRepos($this->options, $this->client);
			}

			return $this->repos;
		}

		return parent::__get($name);
	}
}
