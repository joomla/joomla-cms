<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.client.githubobject');

/**
 * Github Pull Request Class
 *
 * @package     Joomla.Platform
 * @subpackage  Client
 * @since       11.1
 */
class JGithubPulls extends JGithubObject
{
	public function getAll($user, $repo, $state = 'open', $page = 0, $per_page = 0)
	{
		$url = '/repos/'.$user.'/'.$repo.'/pulls'.($state == 'closed' ? '?state=closed' : '');
		return $this->connector->sendRequest($this->paginate($url, $page, $per_page))->body;
	}

	public function get($user, $repo, $pull_id)
	{
		return $this->connector->sendRequest('/repos/'.$user.'/'.$repo.'/pulls/'.(int)$pull_id)->body;
	}

	public function create($user, $repo, $title, $base, $head, $body = '')
	{
		$pull = new stdClass;
		$pull->title = $title;
		$pull->base = $base;
		$pull->head = $head;
		$pull->body = $body;

		return $this->connector->sendRequest('/repos/'.$user.'/'.$repo.'/pulls', 'post', $pull)->body;
	}

	public function createFromIssue($user, $repo, $issue, $base, $head)
	{
		$pull = new stdClass;
		$pull->issue = (int)$issue;
		$pull->base = $base;
		$pull->head = $head;

		return $this->connector->sendRequest('/repos/'.$user.'/'.$repo.'/pulls', 'post', $pull)->body;
	}

	public function edit($user, $repo, $id, $title = null, $body = null, $state = null)
	{
		$pull = new stdClass;

		if (isset($title)) {
			$pull->title = $title;
		}

		if (isset($body)) {
			$pull->body = $body;
		}

		if (isset($state)) {
			$pull->state = $state;
		}

		return $this->connector->sendRequest('/repos/'.$user.'/'.$repo.'/pulls/'.(int)$id, 'patch', $pull)->body;
	}

	public function getCommits($user, $repo, $pull_id, $page = 0, $per_page = 0)
	{
		$url = '/repos/'.$user.'/'.$repo.'/pulls/'.(int)$pull_id.'/commits';
		return $this->connector->sendRequest($this->paginate($url, $page, $per_page))->body;
	}

	public function getFiles($user, $repo, $pull_id, $page = 0, $per_page = 0)
	{
		$url = '/repos/'.$user.'/'.$repo.'/pulls/'.(int)$pull_id.'/files';
		return $this->connector->sendRequest($this->paginate($url, $page, $per_page))->body;
	}

	public function isMerged($user, $repo, $pull_id)
	{
		$url = '/repos/'.$user.'/'.$repo.'/pulls/'.(int)$pull_id.'/merge';
		$response = $this->connector->sendRequest($url);

		if ($response->code == '204') {
			return true;
		} else {		// the code should be 404
			return false;
		}
	}

	public function merge($user, $repo, $pull_id, $commit_message = '')
	{
		return $this->connector->sendRequest('/repos/'.$user.'/'.$repo.'/pulls/'.(int)$pull_id.'/merge', 'put', array('commit_message' => $commit_message))->body;
	}

	public function getComments($user, $repo, $pull_id, $page = 0, $per_page = 0)
	{
		$url = '/repos/'.$user.'/'.$repo.'/pulls/'.(int)$pull_id.'/comments';
		return $this->connector->sendRequest($this->paginate($url, $page, $per_page))->body;
	}

	public function getComment($user, $repo, $comment_id)
	{
		return $this->connector->sendRequest('/repos/'.$user.'/'.$repo.'/pulls/comments/'.(int)$comment_id)->body;
	}

	public function createComment($user, $repo, $pull_id, $body, $commit_id, $path, $position)
	{
		$comment = new stdClass;
		$comment->body = $body;
		$comment->commit_id = $commit_id;
		$comment->path = $path;
		$comment->position = $position;

		return $this->connector->sendRequest('/repos/'.$user.'/'.$repo.'/pulls/'.(int)$pull_id.'/comments', 'post', $comment)->body;
	}

	public function createCommentReply($user, $repo, $pull_id, $body, $in_reply_to)
	{
		$comment = new stdClass;
		$comment->body = $body;
		$comment->in_reply_to = (int)$in_reply_to;

		return $this->connector->sendRequest('/repos/'.$user.'/'.$repo.'/pulls/'.(int)$pull_id.'/comments', 'post', $comment)->body;
	}

	public function editComment($user, $repo, $comment_id, $body)
	{
		return $this->connector->sendRequest('/repos/'.$user.'/'.$repo.'/pulls/comments/'.(int)$comment_id, 'patch', array('body' => $body))->body;
	}

	public function deleteComment($user, $repo, $comment_id)
	{
		return $this->connector->sendRequest('/repos/'.$user.'/'.$repo.'/pulls/comments/'.(int)$comment_id, 'delete')->body;
	}

}
