<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Orgs Teams class for the Joomla Platform.
 *
 * All actions against teams require at a minimum an authenticated user who is a member
 * of the owner’s team in the :org being managed. Additionally, OAuth users require “user” scope.
 *
 * @documentation http://developer.github.com/v3/orgs/teams/
 *
 * @since  12.3
 */
class JGithubPackageOrgsTeams extends JGithubPackage
{
	/**
	 * List teams.
	 *
	 * @param   string  $org  The name of the organization.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function getList($org)
	{
		// Build the request path.
		$path = '/orgs/' . $org . '/teams';

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Get team.
	 *
	 * @param   integer  $id  The team id.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function get($id)
	{
		// Build the request path.
		$path = '/teams/' . (int) $id;

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Create team.
	 *
	 * In order to create a team, the authenticated user must be an owner of the organization.
	 *
	 * @param   string  $org         The name of the organization.
	 * @param   string  $name        The name of the team.
	 * @param   array   $repoNames   Repository names.
	 * @param   string  $permission  The permission.
	 *                               pull - team members can pull, but not push to or administer these repositories. Default
	 *                               push - team members can pull and push, but not administer these repositories.
	 *                               admin - team members can pull, push and administer these repositories.
	 *
	 * @throws UnexpectedValueException
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function create($org, $name, array $repoNames = array(), $permission = '')
	{
		// Build the request path.
		$path = '/orgs/' . $org . '/teams';

		$data = array(
			'name' => $name
		);

		if ($repoNames)
		{
			$data['repo_names'] = $repoNames;
		}

		if ($permission)
		{
			if (false == in_array($permission, array('pull', 'push', 'admin')))
			{
				throw new UnexpectedValueException('Permissions must be either "pull", "push", or "admin".');
			}

			$data['permission'] = $permission;
		}

		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), $data),
			201
		);
	}

	/**
	 * Edit team.
	 *
	 * In order to edit a team, the authenticated user must be an owner of the org that the team is associated with.
	 *
	 * @param   integer  $id          The team id.
	 * @param   string   $name        The name of the team.
	 * @param   string   $permission  The permission.
	 *                                pull - team members can pull, but not push to or administer these repositories. Default
	 *                                push - team members can pull and push, but not administer these repositories.
	 *                                admin - team members can pull, push and administer these repositories.
	 *
	 * @throws UnexpectedValueException
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function edit($id, $name, $permission = '')
	{
		// Build the request path.
		$path = '/teams/' . (int) $id;

		$data = array(
			'name' => $name
		);

		if ($permission)
		{
			if (false == in_array($permission, array('pull', 'push', 'admin')))
			{
				throw new UnexpectedValueException('Permissions must be either "pull", "push", or "admin".');
			}

			$data['permission'] = $permission;
		}

		return $this->processResponse(
			$this->client->patch($this->fetchUrl($path), $data)
		);
	}

	/**
	 * Delete team.
	 *
	 * In order to delete a team, the authenticated user must be an owner of the org that the team is associated with.
	 *
	 * @param   integer  $id  The team id.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function delete($id)
	{
		// Build the request path.
		$path = '/teams/' . $id;

		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}

	/**
	 * List team members.
	 *
	 * In order to list members in a team, the authenticated user must be a member of the team.
	 *
	 * @param   integer  $id  The team id.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function getListMembers($id)
	{
		// Build the request path.
		$path = '/teams/' . $id . '/members';

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Get team member.
	 *
	 * In order to get if a user is a member of a team, the authenticated user must be a member of the team.
	 *
	 * @param   integer  $id    The team id.
	 * @param   string   $user  The name of the user.
	 *
	 * @throws UnexpectedValueException
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function isMember($id, $user)
	{
		// Build the request path.
		$path = '/teams/' . $id . '/members/' . $user;

		$response = $this->client->get($this->fetchUrl($path));

		switch ($response->code)
		{
			case 204 :
				// Response if user is a member
				return true;
				break;

			case 404 :
				// Response if user is not a member
				return false;
				break;

			default :
				throw new UnexpectedValueException('Unexpected response code: ' . $response->code);
				break;
		}
	}

	/**
	 * Add team member.
	 *
	 * In order to add a user to a team, the authenticated user must have ‘admin’ permissions
	 * to the team or be an owner of the org that the team is associated with.
	 *
	 * @param   integer  $id    The team id.
	 * @param   string   $user  The name of the user.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function addMember($id, $user)
	{
		// Build the request path.
		$path = '/teams/' . $id . '/members/' . $user;

		return $this->processResponse(
			$this->client->put($this->fetchUrl($path), ''),
			204
		);
	}

	/**
	 * Remove team member.
	 *
	 * In order to remove a user from a team, the authenticated user must have ‘admin’ permissions
	 * to the team or be an owner of the org that the team is associated with.
	 * NOTE: This does not delete the user, it just remove them from the team.
	 *
	 * @param   integer  $id    The team id.
	 * @param   string   $user  The name of the user.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function removeMember($id, $user)
	{
		// Build the request path.
		$path = '/teams/' . $id . '/members/' . $user;

		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}

	/**
	 * List team repos.
	 *
	 * @param   integer  $id  The team id.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function getListRepos($id)
	{
		// Build the request path.
		$path = '/teams/' . $id . '/repos';

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Check if the repo is managed by this team.
	 *
	 * @param   integer  $id    The team id.
	 * @param   string   $repo  The name of the GitHub repository.
	 *
	 * @throws UnexpectedValueException
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function checkRepo($id, $repo)
	{
		// Build the request path.
		$path = '/teams/' . $id . '/repos/' . $repo;

		$response = $this->client->get($this->fetchUrl($path));

		switch ($response->code)
		{
			case 204 :
				// Response if repo is managed by this team.
				return true;
				break;

			case 404 :
				// Response if repo is not managed by this team.
				return false;
				break;

			default :
				throw new UnexpectedValueException('Unexpected response code: ' . $response->code);
				break;
		}
	}

	/**
	 * Add team repo.
	 *
	 * In order to add a repo to a team, the authenticated user must be an owner of the
	 * org that the team is associated with. Also, the repo must be owned by the organization,
	 * or a direct form of a repo owned by the organization.
	 *
	 * If you attempt to add a repo to a team that is not owned by the organization, you get:
	 * Status: 422 Unprocessable Entity
	 *
	 * @param   integer  $id     The team id.
	 * @param   string   $owner  The name of the owner of the GitHub repository.
	 * @param   string   $repo   The name of the GitHub repository.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function addRepo($id, $owner, $repo)
	{
		// Build the request path.
		$path = '/teams/' . $id . '/repos/' . $owner . '/' . $repo;

		return $this->processResponse(
			$this->client->put($this->fetchUrl($path), ''),
			204
		);
	}

	/**
	 * Remove team repo.
	 *
	 * In order to remove a repo from a team, the authenticated user must be an owner
	 * of the org that the team is associated with. NOTE: This does not delete the
	 * repo, it just removes it from the team.
	 *
	 * @param   integer  $id     The team id.
	 * @param   string   $owner  The name of the owner of the GitHub repository.
	 * @param   string   $repo   The name of the GitHub repository.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function removeRepo($id, $owner, $repo)
	{
		// Build the request path.
		$path = '/teams/' . (int) $id . '/repos/' . $owner . '/' . $repo;

		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}
}
