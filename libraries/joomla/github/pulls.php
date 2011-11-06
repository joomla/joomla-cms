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
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $state
	 * @param unknown_type $page
	 * @param unknown_type $per_page
	 */
	public function getList($user, $repo, $state = 'open', $page = 0, $per_page = 0)
	{
		$url = '/repos/' . $user . '/' . $repo . '/pulls' . ($state == 'closed' ? '?state=closed' : '');

		return $this->client->get($this->paginate($url, $page, $per_page))->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $pull_id
	 * @return string
	 */
	public function get($user, $repo, $pull_id)
	{
		return $this->client->get('/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pull_id)->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $title
	 * @param unknown_type $base
	 * @param unknown_type $head
	 * @param unknown_type $body
	 * @return string
	 */
	public function create($user, $repo, $title, $base, $head, $body = '')
	{
		$pull = new stdClass();
		$pull->title = $title;
		$pull->base = $base;
		$pull->head = $head;
		$pull->body = $body;

		return $this->client->post('/repos/' . $user . '/' . $repo . '/pulls', $pull)->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $issue
	 * @param unknown_type $base
	 * @param unknown_type $head
	 * @return string
	 */
	public function createFromIssue($user, $repo, $issue, $base, $head)
	{
		$pull = new stdClass();
		$pull->issue = (int) $issue;
		$pull->base = $base;
		$pull->head = $head;

		return $this->client->post('/repos/' . $user . '/' . $repo . '/pulls', $pull)->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $id
	 * @param unknown_type $title
	 * @param unknown_type $body
	 * @param unknown_type $state
	 * @return string
	 */
	public function edit($user, $repo, $id, $title = null, $body = null, $state = null)
	{
		$pull = new stdClass();

		if (isset($title))
		{
			$pull->title = $title;
		}

		if (isset($body))
		{
			$pull->body = $body;
		}

		if (isset($state))
		{
			$pull->state = $state;
		}

		return $this->client->patch('/repos/' . $user . '/' . $repo . '/pulls/' . (int) $id, $pull)->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $pull_id
	 * @param unknown_type $page
	 * @param unknown_type $per_page
	 * @return string
	 */
	public function getCommits($user, $repo, $pull_id, $page = 0, $per_page = 0)
	{
		$url = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pull_id . '/commits';
		return $this->client->get($this->paginate($url, $page, $per_page))->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $pull_id
	 * @param unknown_type $page
	 * @param unknown_type $per_page
	 * @return string
	 */
	public function getFiles($user, $repo, $pull_id, $page = 0, $per_page = 0)
	{
		$url = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pull_id . '/files';
		return $this->client->get($this->paginate($url, $page, $per_page))->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $pull_id
	 * @return boolean
	 */
	public function isMerged($user, $repo, $pull_id)
	{
		$url = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pull_id . '/merge';
		$response = $this->client->get($url);

		if ($response->code == '204')
		{
			return true;
		}
		else
		{ // the code should be 404
			return false;
		}
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $pull_id
	 * @param unknown_type $commit_message
	 * @return string
	 */
	public function merge($user, $repo, $pull_id, $commit_message = '')
	{
		return $this->client->put('/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pull_id . '/merge',
			array('commit_message' => $commit_message))->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $pull_id
	 * @param unknown_type $page
	 * @param unknown_type $per_page
	 * @return string
	 */
	public function getComments($user, $repo, $pull_id, $page = 0, $per_page = 0)
	{
		$url = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pull_id . '/comments';
		return $this->client->get($this->paginate($url, $page, $per_page))->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $comment_id
	 * @return string
	 */
	public function getComment($user, $repo, $comment_id)
	{
		return $this->client->get('/repos/' . $user . '/' . $repo . '/pulls/comments/' . (int) $comment_id)->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $pull_id
	 * @param unknown_type $body
	 * @param unknown_type $commit_id
	 * @param unknown_type $path
	 * @param unknown_type $position
	 * @return string
	 */
	public function createComment($user, $repo, $pull_id, $body, $commit_id, $path, $position)
	{
		$comment = new stdClass();
		$comment->body = $body;
		$comment->commit_id = $commit_id;
		$comment->path = $path;
		$comment->position = $position;

		return $this->client->post('/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pull_id . '/comments', $comment)->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $pull_id
	 * @param unknown_type $body
	 * @param unknown_type $in_reply_to
	 * @return string
	 */
	public function createCommentReply($user, $repo, $pull_id, $body, $in_reply_to)
	{
		$comment = new stdClass();
		$comment->body = $body;
		$comment->in_reply_to = (int) $in_reply_to;

		return $this->client->post('/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pull_id . '/comments', $comment)->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $comment_id
	 * @param unknown_type $body
	 * @return string
	 */
	public function editComment($user, $repo, $comment_id, $body)
	{
		return $this->client->patch('/repos/' . $user . '/' . $repo . '/pulls/comments/' . (int) $comment_id, $body)->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $repo
	 * @param unknown_type $comment_id
	 * @return string
	 */
	public function deleteComment($user, $repo, $comment_id)
	{
		return $this->client->delete('/repos/' . $user . '/' . $repo . '/pulls/comments/' . (int) $comment_id)->body;
	}
}
