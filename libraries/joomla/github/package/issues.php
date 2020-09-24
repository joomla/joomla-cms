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
 * GitHub API Issues class for the Joomla Platform.
 *
 * @documentation https://developer.github.com/v3/issues
 *
 * @since       1.7.3
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 *
 * @property-read  JGithubPackageIssuesAssignees   $assignees   GitHub API object for assignees.
 * @property-read  JGithubPackageIssuesComments    $comments    GitHub API object for comments.
 * @property-read  JGithubPackageIssuesEvents      $events      GitHub API object for events.
 * @property-read  JGithubPackageIssuesLabels      $labels      GitHub API object for labels.
 * @property-read  JGithubPackageIssuesMilestones  $milestones  GitHub API object for milestones.
 */
class JGithubPackageIssues extends JGithubPackage
{
	protected $name = 'Issues';

	protected $packages = array('assignees', 'comments', 'events', 'labels', 'milestones');

	/**
	 * Method to create an issue.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   string   $title      The title of the new issue.
	 * @param   string   $body       The body text for the new issue.
	 * @param   string   $assignee   The login for the GitHub user that this issue should be assigned to.
	 * @param   integer  $milestone  The milestone to associate this issue with.
	 * @param   array    $labels     The labels to associate with this issue.
	 *
	 * @throws DomainException
	 * @since   1.7.3
	 *
	 * @return  object
	 */
	public function create($user, $repo, $title, $body = null, $assignee = null, $milestone = null, array $labels = null)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/issues';

		// Ensure that we have a non-associative array.
		if (isset($labels))
		{
			$labels = array_values($labels);
		}

		// Build the request data.
		$data = json_encode(
			array(
				'title'     => $title,
				'assignee'  => $assignee,
				'milestone' => $milestone,
				'labels'    => $labels,
				'body'      => $body,
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
	 * Method to update an issue.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   integer  $issueId    The issue number.
	 * @param   string   $state      The optional new state for the issue. [open, closed]
	 * @param   string   $title      The title of the new issue.
	 * @param   string   $body       The body text for the new issue.
	 * @param   string   $assignee   The login for the GitHub user that this issue should be assigned to.
	 * @param   integer  $milestone  The milestone to associate this issue with.
	 * @param   array    $labels     The labels to associate with this issue.
	 *
	 * @throws DomainException
	 * @since   1.7.3
	 *
	 * @return  object
	 */
	public function edit($user, $repo, $issueId, $state = null, $title = null, $body = null, $assignee = null, $milestone = null, array $labels = null)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/issues/' . (int) $issueId;

		// Create the data object.
		$data = new stdClass;

		// If a title is set add it to the data object.
		if (isset($title))
		{
			$data->title = $title;
		}

		// If a body is set add it to the data object.
		if (isset($body))
		{
			$data->body = $body;
		}

		// If a state is set add it to the data object.
		if (isset($state))
		{
			$data->state = $state;
		}

		// If an assignee is set add it to the data object.
		if (isset($assignee))
		{
			$data->assignee = $assignee;
		}

		// If a milestone is set add it to the data object.
		if (isset($milestone))
		{
			$data->milestone = $milestone;
		}

		// If labels are set add them to the data object.
		if (isset($labels))
		{
			// Ensure that we have a non-associative array.
			if (isset($labels))
			{
				$labels = array_values($labels);
			}

			$data->labels = $labels;
		}

		// Encode the request data.
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
	 * Method to get a single issue.
	 *
	 * @param   string   $user     The name of the owner of the GitHub repository.
	 * @param   string   $repo     The name of the GitHub repository.
	 * @param   integer  $issueId  The issue number.
	 *
	 * @throws DomainException
	 * @since   1.7.3
	 *
	 * @return  object
	 */
	public function get($user, $repo, $issueId)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/issues/' . (int) $issueId;

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
	 * Method to list an authenticated user's issues.
	 *
	 * @param   string   $filter     The filter type: assigned, created, mentioned, subscribed.
	 * @param   string   $state      The optional state to filter requests by. [open, closed]
	 * @param   string   $labels     The list of comma separated Label names. Example: bug,ui,@high.
	 * @param   string   $sort       The sort order: created, updated, comments, default: created.
	 * @param   string   $direction  The list direction: asc or desc, default: desc.
	 * @param   JDate    $since      The date/time since when issues should be returned.
	 * @param   integer  $page       The page number from which to get items.
	 * @param   integer  $limit      The number of items on a page.
	 *
	 * @throws DomainException
	 * @since   1.7.3
	 *
	 * @return  array
	 */
	public function getList($filter = null, $state = null, $labels = null, $sort = null,
		$direction = null, JDate $since = null, $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/issues';

		// TODO Implement the filtering options.

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
	 * Method to list issues.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   string   $milestone  The milestone number, 'none', or *.
	 * @param   string   $state      The optional state to filter requests by. [open, closed]
	 * @param   string   $assignee   The assignee name, 'none', or *.
	 * @param   string   $mentioned  The GitHub user name.
	 * @param   string   $labels     The list of comma separated Label names. Example: bug,ui,@high.
	 * @param   string   $sort       The sort order: created, updated, comments, default: created.
	 * @param   string   $direction  The list direction: asc or desc, default: desc.
	 * @param   JDate    $since      The date/time since when issues should be returned.
	 * @param   integer  $page       The page number from which to get items.
	 * @param   integer  $limit      The number of items on a page.
	 *
	 * @throws DomainException
	 * @since   1.7.3
	 *
	 * @return  array
	 */
	public function getListByRepository($user, $repo, $milestone = null, $state = null, $assignee = null, $mentioned = null, $labels = null,
		$sort = null, $direction = null, JDate $since = null, $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/issues';

		$uri = new JUri($this->fetchUrl($path, $page, $limit));

		if ($milestone)
		{
			$uri->setVar('milestone', $milestone);
		}

		if ($state)
		{
			$uri->setVar('state', $state);
		}

		if ($assignee)
		{
			$uri->setVar('assignee', $assignee);
		}

		if ($mentioned)
		{
			$uri->setVar('mentioned', $mentioned);
		}

		if ($labels)
		{
			$uri->setVar('labels', $labels);
		}

		if ($sort)
		{
			$uri->setVar('sort', $sort);
		}

		if ($direction)
		{
			$uri->setVar('direction', $direction);
		}

		if ($since)
		{
			$uri->setVar('since', $since->toISO8601());
		}

		// Send the request.
		$response = $this->client->get((string) $uri);

		// Validate the response code.
		if ($response->code != 200)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}

	/*
	 * Deprecated methods
	 */

	/**
	 * Method to create a comment on an issue.
	 *
	 * @param   string   $user     The name of the owner of the GitHub repository.
	 * @param   string   $repo     The name of the GitHub repository.
	 * @param   integer  $issueId  The issue number.
	 * @param   string   $body     The comment body text.
	 *
	 * @deprecated use issues->comments->create()
	 *
	 * @return  object
	 *
	 * @since   1.7.3
	 */
	public function createComment($user, $repo, $issueId, $body)
	{
		return $this->comments->create($user, $repo, $issueId, $body);
	}

	/**
	 * Method to create a label on a repo.
	 *
	 * @param   string  $user   The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 * @param   string  $name   The label name.
	 * @param   string  $color  The label color.
	 *
	 * @deprecated use issues->labels->create()
	 *
	 * @return  object
	 *
	 * @since   3.1.4
	 */
	public function createLabel($user, $repo, $name, $color)
	{
		return $this->labels->create($user, $repo, $name, $color);
	}

	/**
	 * Method to delete a comment on an issue.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   integer  $commentId  The id of the comment to delete.
	 *
	 * @deprecated use issues->comments->delete()
	 *
	 * @return  void
	 *
	 * @since   1.7.3
	 */
	public function deleteComment($user, $repo, $commentId)
	{
		$this->comments->delete($user, $repo, $commentId);
	}

	/**
	 * Method to delete a label on a repo.
	 *
	 * @param   string  $user   The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 * @param   string  $label  The label name.
	 *
	 * @deprecated use issues->labels->delete()
	 *
	 * @return  object
	 *
	 * @since   3.1.4
	 */
	public function deleteLabel($user, $repo, $label)
	{
		return $this->labels->delete($user, $repo, $label);
	}

	/**
	 * Method to update a comment on an issue.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   integer  $commentId  The id of the comment to update.
	 * @param   string   $body       The new body text for the comment.
	 *
	 * @deprecated use issues->comments->edit()
	 *
	 * @return  object
	 *
	 * @since   1.7.3
	 */
	public function editComment($user, $repo, $commentId, $body)
	{
		return $this->comments->edit($user, $repo, $commentId, $body);
	}

	/**
	 * Method to update a label on a repo.
	 *
	 * @param   string  $user   The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 * @param   string  $label  The label name.
	 * @param   string  $name   The label name.
	 * @param   string  $color  The label color.
	 *
	 * @deprecated use issues->labels->update()
	 *
	 * @return  object
	 *
	 * @since   3.1.4
	 */
	public function editLabel($user, $repo, $label, $name, $color)
	{
		return $this->labels->update($user, $repo, $label, $name, $color);
	}

	/**
	 * Method to get a specific comment on an issue.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   integer  $commentId  The comment id to get.
	 *
	 * @deprecated use issues->comments->get()
	 *
	 * @return  object
	 *
	 * @since   1.7.3
	 */
	public function getComment($user, $repo, $commentId)
	{
		return $this->comments->get($user, $repo, $commentId);
	}

	/**
	 * Method to get the list of comments on an issue.
	 *
	 * @param   string   $user     The name of the owner of the GitHub repository.
	 * @param   string   $repo     The name of the GitHub repository.
	 * @param   integer  $issueId  The issue number.
	 * @param   integer  $page     The page number from which to get items.
	 * @param   integer  $limit    The number of items on a page.
	 *
	 * @deprecated use issues->comments->getList()
	 *
	 * @return  array
	 *
	 * @since   1.7.3
	 */
	public function getComments($user, $repo, $issueId, $page = 0, $limit = 0)
	{
		return $this->comments->getList($user, $repo, $issueId, $page, $limit);
	}

	/**
	 * Method to get a specific label on a repo.
	 *
	 * @param   string  $user  The name of the owner of the GitHub repository.
	 * @param   string  $repo  The name of the GitHub repository.
	 * @param   string  $name  The label name to get.
	 *
	 * @deprecated use issues->labels->get()
	 *
	 * @return  object
	 *
	 * @since   3.1.4
	 */
	public function getLabel($user, $repo, $name)
	{
		return $this->labels->get($user, $repo, $name);
	}

	/**
	 * Method to get the list of labels on a repo.
	 *
	 * @param   string  $user  The name of the owner of the GitHub repository.
	 * @param   string  $repo  The name of the GitHub repository.
	 *
	 * @deprecated use issues->labels->getList()
	 *
	 * @return  array
	 *
	 * @since   3.1.4
	 */
	public function getLabels($user, $repo)
	{
		return $this->labels->getList($user, $repo);
	}
}
