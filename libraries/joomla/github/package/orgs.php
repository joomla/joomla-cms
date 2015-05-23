<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Activity class for the Joomla Platform.
 *
 * @since  3.3 (CMS)
 *
 * @documentation  http://developer.github.com/v3/orgs/
 *
 * @property-read  JGithubPackageOrgsMembers  $members  GitHub API object for members.
 * @property-read  JGithubPackageOrgsTeams    $teams    GitHub API object for teams.
 */
class JGithubPackageOrgs extends JGithubPackage
{
	protected $name = 'Orgs';

	protected $packages = array(
		'members', 'teams'
	);

	/**
	 * List User Organizations.
	 *
	 * If a user name is given, public and private organizations for the authenticated user will be listed.
	 *
	 * @param   string  $user  The user name.
	 *
	 * @since   3.3 (CMS)
	 *
	 * @return  object
	 */
	public function getList($user = '')
	{
		// Build the request path.
		$path = ($user)
			? '/users/' . $user . '/orgs'
			: '/user/orgs';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Get an Organization.
	 *
	 * @param   string  $org  The organization name.
	 *
	 * @since   3.3 (CMS)
	 *
	 * @return  object
	 */
	public function get($org)
	{
		// Build the request path.
		$path = '/orgs/' . $org;

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Edit an Organization.
	 *
	 * @param   string  $org           The organization name.
	 * @param   string  $billingEmail  Billing email address. This address is not publicized.
	 * @param   string  $company       The company name.
	 * @param   string  $email         The email address.
	 * @param   string  $location      The location name.
	 * @param   string  $name          The name.
	 *
	 * @since   3.3 (CMS)
	 *
	 * @return  object
	 */
	public function edit($org, $billingEmail = '', $company = '', $email = '', $location = '', $name = '')
	{
		// Build the request path.
		$path = '/orgs/' . $org;

		$args = array('billing_email', 'company', 'email', 'location', 'name');

		$data = array();

		$fArgs = func_get_args();

		foreach ($args as $i => $arg)
		{
			if (array_key_exists($i + 1, $fArgs) && $fArgs[$i + 1])
			{
				$data[$arg] = $fArgs[$i + 1];
			}
		}

		// Send the request.
		return $this->processResponse(
			$this->client->patch($this->fetchUrl($path), $data)
		);
	}
}
