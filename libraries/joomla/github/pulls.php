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
 * GitHub API Pull Requests class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub
 * @since       11.4
 */
class JGithubPulls extends JGithubObject
{
	/**
	 * @param   string  $user
	 * @param   string  $repo
	 * @param   string  $title
	 * @param   string  $base
	 * @param   string  $head
	 * @param   string  $body
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function create($user, $repo, $title, $base, $head, $body = '')
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls';

		// Build the request data.
		$data = json_encode(
			array(
				'title' => $title,
				'base' => $base,
				'head' => $head,
				'body' => $body
			)
		);

		return $this->client->post($this->fetchUrl($path), $data)->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $pullId
	 * @param   string   $body
	 * @param   string   $commitId
	 * @param   string   $filePath
	 * @param   string   $position
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function createComment($user, $repo, $pullId, $body, $commitId, $filePath, $position)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pullId . '/comments';

		// Build the request data.
		$data = json_encode(
			array(
				'body' => $body,
				'commit_id' => $commitId,
				'path' => $filePath,
				'position' => $position
			)
		);

		return $this->client->post($this->fetchUrl($path), $data)->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $pullId
	 * @param   string   $body
	 * @param   integer  $inReplyTo
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function createCommentReply($user, $repo, $pullId, $body, $inReplyTo)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pullId . '/comments';

		// Build the request data.
		$data = json_encode(
			array(
				'body' => $body,
				'in_reply_to' => (int) $inReplyTo
			)
		);

		return $this->client->post($this->fetchUrl($path), $data)->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $issueId
	 * @param   string   $base
	 * @param   string   $head
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function createFromIssue($user, $repo, $issueId, $base, $head)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls';

		// Build the request data.
		$data = json_encode(
			array(
				'issue' => (int) $issueId,
				'base' => $base,
				'head' => $head
			)
		);

		return $this->client->post($this->fetchUrl($path), $data)->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $commentId
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function deleteComment($user, $repo, $commentId)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/comments/' . (int) $commentId;

		return $this->client->delete($this->fetchUrl($path))->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $pullId
	 * @param   string   $title
	 * @param   string   $body
	 * @param   string   $state
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function edit($user, $repo, $pullId, $title = null, $body = null, $state = null)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pullId;

		// Craete the data object.
		$data = new stdClass;

		// If a title is set add it to the data object.
		if (isset($title))
		{
			$pull->title = $title;
		}

		// If a body is set add it to the data object.
		if (isset($body))
		{
			$pull->body = $body;
		}

		// If a state is set add it to the data object.
		if (isset($state))
		{
			$pull->state = $state;
		}

		// Encode the request data.
		$data = json_encode($data);

		return $this->client->patch($this->fetchUrl($path), $data)->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $commentId
	 * @param   string   $body
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function editComment($user, $repo, $commentId, $body)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/comments/' . (int) $commentId;

		return $this->client->patch($this->fetchUrl($path), $body)->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $pullId
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function get($user, $repo, $pullId)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pullId;

		return $this->client->get($this->fetchUrl($path))->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $commentId
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function getComment($user, $repo, $commentId)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/comments/' . (int) $commentId;

		return $this->client->get($this->fetchUrl($path))->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $pullId
	 * @param   integer  $page
	 * @param   integer  $limit
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function getComments($user, $repo, $pullId, $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pullId . '/comments';

		return $this->client->get($this->fetchUrl($path, $page, $limit))->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $pullId
	 * @param   integer  $page
	 * @param   integer  $limit
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function getCommits($user, $repo, $pullId, $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pullId . '/commits';

		return $this->client->get($this->fetchUrl($path, $page, $limit))->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $pullId
	 * @param   integer  $page
	 * @param   integer  $limit
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function getFiles($user, $repo, $pullId, $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pullId . '/files';

		return $this->client->get($this->fetchUrl($path, $page, $limit))->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   string   $state
	 * @param   integer  $page
	 * @param   integer  $limit
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function getList($user, $repo, $state = 'open', $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls';

		// If a state exists append it as an option.
		if ($state != 'open')
		{
			$path .= '?state=' . $state;
		}

		return $this->client->get($this->fetchUrl($path, $page, $limit))->body;
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $pullId
	 *
	 * @return  boolean
	 *
	 * @since   11.4
	 */
	public function isMerged($user, $repo, $pullId)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pullId . '/merge';

		$response = $this->client->get($this->fetchUrl($path));

		if ($response->code == '204')
		{
			return true;
		}
		else
		{
			// the code should be 404
			return false;
		}
	}

	/**
	 * @param   string   $user
	 * @param   string   $repo
	 * @param   integer  $pullId
	 * @param   string   $commitMessage
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	public function merge($user, $repo, $pullId, $commitMessage = '')
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pullId . '/merge';

		// Build the request data.
		$data = json_encode(
			array(
				'commit_message' => $commitMessage
			)
		);

		return $this->client->put($this->fetchUrl($path), $data)->body;
	}
}
