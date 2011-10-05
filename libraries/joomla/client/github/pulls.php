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

	public function star($gist_id)
	{
		return $this->connector->sendRequest('/gists/'.(int)$gist_id.'/star', 'put')->body;
	}

	public function unstar($gist_id)
	{
		return $this->connector->sendRequest('/gists/'.(int)$gist_id.'/star', 'delete')->body;
	}

	public function isStarred($gist_id)
	{
		$response = $this->connector->sendRequest('/gists/'.(int)$gist_id.'/star');

		if ($response->code == '204') {
			return true;
		} else {		// the code should be 404
			return false;
		}
	}

	public function fork($gist_id)
	{
		return $this->connector->sendRequest('/gists/'.(int)$gist_id.'/fork', 'put')->body;
	}

	public function delete($gist_id)
	{
		return $this->connector->sendRequest('/gists/'.(int)$gist_id, 'delete')->body;
	}

	public function getComments($gist_id)
	{
		return $this->connector->sendRequest('/gists/'.(int)$gist_id.'/comments')->body;
	}

	public function getComment($comment_id)
	{
		return $this->connector->sendRequest('/gists/comments/'.(int)$comment_id)->body;
	}

	public function createComment($gist_id, $comment)
	{
		return $this->connector->sendRequest('/gists/'.(int)$gist_id.'/comments', 'post', array('body' => $comment))->body;
	}

	public function editComment($comment_id, $comment)
	{
		return $this->connector->sendRequest('/gists/comments/'.(int)$comment_id, 'patch', array('body' => $comment))->body;
	}

	public function deleteComment($comment_id)
	{
		return $this->connector->sendRequest('/gists/comments/'.(int)$comment_id, 'delete')->body;
	}
}
