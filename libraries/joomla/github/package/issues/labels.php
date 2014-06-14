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
 * GitHub API Milestones class for the Joomla Platform.
 *
 * @documentation http://developer.github.com/v3/issues/labels/
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub.Issues
 * @since       12.3
 */
class JGithubPackageIssuesLabels extends JGithubPackage
{
	/**
	 * Method to get the list of labels on a repo.
	 *
	 * @param   string  $owner  The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 *
	 * @throws DomainException
	 * @since   12.3
	 *
	 * @return  array
	 */
	public function getList($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/labels';

		// Send the request.
		return $this->processResponse(
			$response = $this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Method to get a specific label on a repo.
	 *
	 * @param   string  $user  The name of the owner of the GitHub repository.
	 * @param   string  $repo  The name of the GitHub repository.
	 * @param   string  $name  The label name to get.
	 *
	 * @throws DomainException
	 * @since   12.3
	 *
	 * @return  object
	 */
	public function get($user, $repo, $name)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/labels/' . $name;

		// Send the request.
		return $this->processResponse(
			$response = $this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Method to create a label on a repo.
	 *
	 * @param   string  $owner  The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 * @param   string  $name   The label name.
	 * @param   string  $color  The label color.
	 *
	 * @throws DomainException
	 * @since   12.3
	 *
	 * @return  object
	 */
	public function create($owner, $repo, $name, $color)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/labels';

		// Build the request data.
		$data = json_encode(
			array(
				'name'  => $name,
				'color' => $color
			)
		);

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
	 * Method to update a label on a repo.
	 *
	 * @param   string  $user   The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 * @param   string  $label  The label name.
	 * @param   string  $name   The new label name.
	 * @param   string  $color  The new label color.
	 *
	 * @throws DomainException
	 * @since   12.3
	 *
	 * @return  object
	 */
	public function update($user, $repo, $label, $name, $color)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/labels/' . $label;

		// Build the request data.
		$data = json_encode(
			array(
				'name'  => $name,
				'color' => $color
			)
		);

		// Send the request.
		return $this->processResponse(
			$this->client->patch($this->fetchUrl($path), $data)
		);
	}

	/**
	 * Method to delete a label on a repo.
	 *
	 * @param   string  $owner  The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 * @param   string  $name   The label name.
	 *
	 * @throws DomainException
	 * @return  object
	 *
	 * @since   12.3
	 */
	public function delete($owner, $repo, $name)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/labels/' . $name;

		// Send the request.
		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}

	/**
	 * List labels on an issue.
	 *
	 * @param   string   $owner   The name of the owner of the GitHub repository.
	 * @param   string   $repo    The name of the GitHub repository.
	 * @param   integer  $number  The issue number.
	 *
	 * @since   3.3 (CMS)
	 *
	 * @return  object
	 */
	public function getListByIssue($owner, $repo, $number)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/issues/' . $number . '/labels';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Add labels to an issue.
	 *
	 * @param   string  $owner   The name of the owner of the GitHub repository.
	 * @param   string  $repo    The name of the GitHub repository.
	 * @param   string  $number  The issue number.
	 * @param   array   $labels  An array of labels to add.
	 *
	 * @since   3.3 (CMS)
	 *
	 * @return  object
	 */
	public function add($owner, $repo, $number, array $labels)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/issues/' . $number . '/labels';

		// Send the request.
		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), json_encode($labels))
		);
	}

	/**
	 * Remove a label from an issue.
	 *
	 * @param   string  $owner   The name of the owner of the GitHub repository.
	 * @param   string  $repo    The name of the GitHub repository.
	 * @param   string  $number  The issue number.
	 * @param   string  $name    The name of the label to remove.
	 *
	 * @since   3.3 (CMS)
	 *
	 * @return  object
	 */
	public function removeFromIssue($owner, $repo, $number, $name)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/issues/' . $number . '/labels/' . $name;

		// Send the request.
		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path))
		);
	}

	/** Replace all labels for an issue.
	 *
	 * Sending an empty array ([]) will remove all Labels from the Issue.
	 *
	 * @param   string  $owner   The name of the owner of the GitHub repository.
	 * @param   string  $repo    The name of the GitHub repository.
	 * @param   string  $number  The issue number.
	 * @param   array   $labels  New labels
	 *
	 * @since   3.3 (CMS)
	 *
	 * @return  object
	 */
	public function replace($owner, $repo, $number, array $labels)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/issues/' . $number . '/labels';

		// Send the request.
		return $this->processResponse(
			$this->client->put($this->fetchUrl($path), json_encode($labels))
		);
	}

	/**
	.* Remove all labels from an issue.
	 *
	 * @param   string  $owner   The name of the owner of the GitHub repository.
	 * @param   string  $repo    The name of the GitHub repository.
	 * @param   string  $number  The issue number.
	 *
	 * @since   3.3 (CMS)
	 *
	 * @return  object
	 */
	public function removeAllFromIssue($owner, $repo, $number)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/issues/' . $number . '/labels';

		// Send the request.
		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}

	/**
	 * Get labels for every issue in a milestone.
	 *
	 * @param   string  $owner   The name of the owner of the GitHub repository.
	 * @param   string  $repo    The name of the GitHub repository.
	 * @param   string  $number  The issue number.
	 *
	 * @since   3.3 (CMS)
	 *
	 * @return  object
	 */
	public function getListByMilestone($owner, $repo, $number)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/milestones/' . $number . '/labels';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}
}
