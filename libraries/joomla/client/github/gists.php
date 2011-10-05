<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTTP client class.
 *
 * @package     Joomla.Platform
 * @subpackage  Client
 * @since       11.1
 */
class JGithubGists
{
	/**
	 * Github Connector
	 *
	 * @var    JGithub
	 * @since  11.3
	 */
	protected $connector = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Array of configuration options for the client.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function __construct($connector, $options = array())
	{
		$this->connector = $connector;
	}

	protected function paginate($url, $page = 0, $per_page = 0)
	{
		//TODO: Make a new base class and move paginate into it
		$query_string = array();
		
		if ($page > 0) {
			$query_string[] = 'page='.(int)$page;
		}

		if ($per_page > 0) {
			$query_string[] = 'per_page='.(int)$per_page;
		}

		if (isset($query_string[0])) {
			$query = implode('&', $query_string);
		} else {
			$query = '';
		}

		if (strlen($query) > 0) {
			if (strpos($url, '?') === false) {
				$url .= '?'.$query;
			} else {
				$url .= '&'.$query;
			}
		}

		return $url;
	}

	public function getAll($page = 0, $per_page = 0)
	{
		$url = '/gists';
		return $this->connector->sendRequest($this->paginate($url, $page, $per_page))->body;
	}

	public function getByUser($user, $page = 0, $per_page = 0)
	{
		$url = '/users/'.$user.'/gists';
		return $this->connector->sendRequest($this->paginate($url, $page, $per_page))->body;
	}

	public function getPublic($page = 0, $per_page = 0)
	{
		$url = '/gists/public';
		return $this->connector->sendRequest($this->paginate($url, $page, $per_page))->body;
	}

	public function getStarred($page = 0, $per_page = 0)
	{
		$url = '/gists/starred';
		return $this->connector->sendRequest($this->paginate($url, $page, $per_page))->body;
	}

	public function get($gist_id)
	{
		return $this->connector->sendRequest('/gists/'.(int)$gist_id)->body;
	}

	public function create($files, $public = false, $description = null)
	{
		$gist = new stdClass;
		$gist->public = $public;
		$gist->files = $files;

		if (!empty($description)) {
			$gist->description = $description;
		}

		return $this->connector->sendRequest('/gists', 'post', $gist)->body;
	}

	public function edit($gist_id, $files, $description = null)
	{
		$gist = new stdClass;
		$gist->files = $files;

		if (!empty($description)) {
			$gist->description = $description;
		}

		return $this->connector->sendRequest('/gists/'.(int)$gist_id, 'patch', $gist)->body;
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
