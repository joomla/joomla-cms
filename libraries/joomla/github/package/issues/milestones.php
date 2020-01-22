<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Milestones class for the Joomla Platform.
 *
 * @documentation https://developer.github.com/v3/issues/milestones/
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageIssuesMilestones extends JGithubPackage
{
	/**
	 * Method to get the list of milestones for a repo.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   string   $state      The milestone state to retrieved.  Open (default) or closed.
	 * @param   string   $sort       Sort can be due_date (default) or completeness.
	 * @param   string   $direction  Direction is asc or desc (default).
	 * @param   integer  $page       The page number from which to get items.
	 * @param   integer  $limit      The number of items on a page.
	 *
	 * @throws DomainException
	 * @since   3.1.4
	 *
	 * @return  array
	 */
	public function getList($user, $repo, $state = 'open', $sort = 'due_date', $direction = 'desc', $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/milestones?';

		$path .= 'state=' . $state;
		$path .= '&sort=' . $sort;
		$path .= '&direction=' . $direction;

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path, $page, $limit));

		// Validate the response code.
		if ($response->code != 200)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}

	/**
	 * Method to get a specific milestone.
	 *
	 * @param   string   $user         The name of the owner of the GitHub repository.
	 * @param   string   $repo         The name of the GitHub repository.
	 * @param   integer  $milestoneId  The milestone id to get.
	 *
	 * @throws DomainException
	 * @return  object
	 *
	 * @since   3.1.4
	 */
	public function get($user, $repo, $milestoneId)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/milestones/' . (int) $milestoneId;

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		// Validate the response code.
		if ($response->code != 200)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}

	/**
	 * Method to create a milestone for a repository.
	 *
	 * @param   string   $user         The name of the owner of the GitHub repository.
	 * @param   string   $repo         The name of the GitHub repository.
	 * @param   integer  $title        The title of the milestone.
	 * @param   string   $state        Can be open (default) or closed.
	 * @param   string   $description  Optional description for milestone.
	 * @param   string   $due_on       Optional ISO 8601 time.
	 *
	 * @throws DomainException
	 * @return  object
	 *
	 * @since   3.1.4
	 */
	public function create($user, $repo, $title, $state = null, $description = null, $due_on = null)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/milestones';

		// Build the request data.
		$data = array(
			'title' => $title,
		);

		if (!is_null($state))
		{
			$data['state'] = $state;
		}

		if (!is_null($description))
		{
			$data['description'] = $description;
		}

		if (!is_null($due_on))
		{
			$data['due_on'] = $due_on;
		}

		$data = json_encode($data);

		// Send the request.
		$response = $this->client->post($this->fetchUrl($path), $data);

		// Validate the response code.
		if ($response->code != 201)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}

	/**
	 * Method to update a milestone.
	 *
	 * @param   string   $user         The name of the owner of the GitHub repository.
	 * @param   string   $repo         The name of the GitHub repository.
	 * @param   integer  $milestoneId  The id of the comment to update.
	 * @param   integer  $title        Optional title of the milestone.
	 * @param   string   $state        Can be open (default) or closed.
	 * @param   string   $description  Optional description for milestone.
	 * @param   string   $due_on       Optional ISO 8601 time.
	 *
	 * @throws DomainException
	 * @return  object
	 *
	 * @since   3.1.4
	 */
	public function edit($user, $repo, $milestoneId, $title = null, $state = null, $description = null, $due_on = null)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/milestones/' . (int) $milestoneId;

		// Build the request data.
		$data = array();

		if (!is_null($title))
		{
			$data['title'] = $title;
		}

		if (!is_null($state))
		{
			$data['state'] = $state;
		}

		if (!is_null($description))
		{
			$data['description'] = $description;
		}

		if (!is_null($due_on))
		{
			$data['due_on'] = $due_on;
		}

		$data = json_encode($data);

		// Send the request.
		$response = $this->client->patch($this->fetchUrl($path), $data);

		// Validate the response code.
		if ($response->code != 200)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}

	/**
	 * Method to delete a milestone.
	 *
	 * @param   string   $user         The name of the owner of the GitHub repository.
	 * @param   string   $repo         The name of the GitHub repository.
	 * @param   integer  $milestoneId  The id of the milestone to delete.
	 *
	 * @throws DomainException
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function delete($user, $repo, $milestoneId)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/milestones/' . (int) $milestoneId;

		// Send the request.
		$response = $this->client->delete($this->fetchUrl($path));

		// Validate the response code.
		if ($response->code != 204)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->message, $response->code);
		}
	}
}
