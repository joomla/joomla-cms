<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Linkedin API Groups class for the Joomla Platform.
 *
 * @since  3.2.0
 */
class JLinkedinGroups extends JLinkedinObject
{
	/**
	 * Method to get a group.
	 *
	 * @param   string   $id      The unique identifier for a group.
	 * @param   string   $fields  Request fields beyond the default ones.
	 * @param   integer  $start   Starting location within the result set for paginated returns.
	 * @param   integer  $count   The number of results returned.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function getGroup($id, $fields = null, $start = 0, $count = 5)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/groups/' . $id;

		$data['format'] = 'json';

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Check if start is specified.
		if ($start > 0)
		{
			$data['start'] = $start;
		}

		// Check if count is specified.
		if ($count != 5)
		{
			$data['count'] = $count;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to find the groups a member belongs to.
	 *
	 * @param   string   $id               The unique identifier for a user.
	 * @param   string   $fields           Request fields beyond the default ones.
	 * @param   integer  $start            Starting location within the result set for paginated returns.
	 * @param   integer  $count            The number of results returned.
	 * @param   string   $membershipState  The state of the caller’s membership to the specified group.
	 *                                     Values are: non-member, awaiting-confirmation, awaiting-parent-group-confirmation, member, moderator,
	 *                                     manager, owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function getMemberships($id = null, $fields = null, $start = 0, $count = 5, $membershipState = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/people/';

		// Check if id is specified.
		if ($id)
		{
			$base .= $id . '/group-memberships';
		}
		else
		{
			$base .= '~/group-memberships';
		}

		$data['format'] = 'json';

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Check if start is specified.
		if ($start > 0)
		{
			$data['start'] = $start;
		}

		// Check if count is specified.
		if ($count != 5)
		{
			$data['count'] = $count;
		}

		// Check if membership_state is specified.
		if ($membershipState)
		{
			$data['membership-state'] = $membershipState;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to find the groups a member belongs to.
	 *
	 * @param   string   $personId  The unique identifier for a user.
	 * @param   string   $groupId   The unique identifier for a group.
	 * @param   string   $fields    Request fields beyond the default ones.
	 * @param   integer  $start     Starting location within the result set for paginated returns.
	 * @param   integer  $count     The number of results returned.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function getSettings($personId = null, $groupId = null, $fields = null, $start = 0, $count = 5)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/people/';

		// Check if person_id is specified.
		if ($personId)
		{
			$base .= $personId . '/group-memberships';
		}
		else
		{
			$base .= '~/group-memberships';
		}

		// Check if group_id is specified.
		if ($groupId)
		{
			$base .= '/' . $groupId;
		}

		$data['format'] = 'json';

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Check if start is specified.
		if ($start > 0)
		{
			$data['start'] = $start;
		}

		// Check if count is specified.
		if ($count != 5)
		{
			$data['count'] = $count;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to change a groups settings.
	 *
	 * @param   string   $groupId          The unique identifier for a group.
	 * @param   boolean  $showLogo         Show group logo in profile.
	 * @param   string   $digestFrequency  Email digest frequency.
	 * @param   boolean  $announcements    Email announcements from managers.
	 * @param   boolean  $allowMessages    Allow messages from members.
	 * @param   boolean  $newPost          Email for every new post.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function changeSettings($groupId, $showLogo = null, $digestFrequency = null, $announcements = null,
		$allowMessages = null, $newPost = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/people/~/group-memberships/' . $groupId;

		// Build xml.
		$xml = '<group-membership>';

		if (!is_null($showLogo))
		{
			$xml .= '<show-group-logo-in-profile>' . $this->booleanToString($showLogo) . '</show-group-logo-in-profile>';
		}

		if ($digestFrequency)
		{
			$xml .= '<email-digest-frequency><code>' . $digestFrequency . '</code></email-digest-frequency>';
		}

		if (!is_null($announcements))
		{
			$xml .= '<email-announcements-from-managers>' . $this->booleanToString($announcements) . '</email-announcements-from-managers>';
		}

		if (!is_null($allowMessages))
		{
			$xml .= '<allow-messages-from-members>' . $this->booleanToString($allowMessages) . '</allow-messages-from-members>';
		}

		if (!is_null($newPost))
		{
			$xml .= '<email-for-every-new-post>' . $this->booleanToString($newPost) . '</email-for-every-new-post>';
		}

		$xml .= '</group-membership>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'PUT', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method to join a group.
	 *
	 * @param   string   $groupId          The unique identifier for a group.
	 * @param   boolean  $showLogo         Show group logo in profile.
	 * @param   string   $digestFrequency  Email digest frequency.
	 * @param   boolean  $announcements    Email announcements from managers.
	 * @param   boolean  $allowMessages    Allow messages from members.
	 * @param   boolean  $newPost          Email for every new post.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function joinGroup($groupId, $showLogo = null, $digestFrequency = null, $announcements = null,
		$allowMessages = null, $newPost = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/people/~/group-memberships';

		// Build xml.
		$xml = '<group-membership><group><id>' . $groupId . '</id></group>';

		if (!is_null($showLogo))
		{
			$xml .= '<show-group-logo-in-profile>' . $this->booleanToString($showLogo) . '</show-group-logo-in-profile>';
		}

		if ($digestFrequency)
		{
			$xml .= '<email-digest-frequency><code>' . $digestFrequency . '</code></email-digest-frequency>';
		}

		if (!is_null($announcements))
		{
			$xml .= '<email-announcements-from-managers>' . $this->booleanToString($announcements) . '</email-announcements-from-managers>';
		}

		if (!is_null($allowMessages))
		{
			$xml .= '<allow-messages-from-members>' . $this->booleanToString($allowMessages) . '</allow-messages-from-members>';
		}

		if (!is_null($newPost))
		{
			$xml .= '<email-for-every-new-post>' . $this->booleanToString($newPost) . '</email-for-every-new-post>';
		}

		$xml .= '<membership-state><code>member</code></membership-state></group-membership>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method to leave a group.
	 *
	 * @param   string  $groupId  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function leaveGroup($groupId)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/people/~/group-memberships/' . $groupId;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'DELETE', $parameters);

		return $response;
	}

	/**
	 * Method to get dicussions for a group.
	 *
	 * @param   string   $id             The unique identifier for a group.
	 * @param   string   $fields         Request fields beyond the default ones.
	 * @param   integer  $start          Starting location within the result set for paginated returns.
	 * @param   integer  $count          The number of results returned.
	 * @param   string   $order          Sort order for posts. Valid for: recency, popularity.
	 * @param   string   $category       Category of posts. Valid for: discussion
	 * @param   string   $modifiedSince  Timestamp filter for posts created after the specified value.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function getDiscussions($id, $fields = null, $start = 0, $count = 0, $order = null, $category = 'discussion', $modifiedSince = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/groups/' . $id . '/posts';

		$data['format'] = 'json';

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Check if start is specified.
		if ($start > 0)
		{
			$data['start'] = $start;
		}

		// Check if count is specified.
		if ($count > 0)
		{
			$data['count'] = $count;
		}

		// Check if order is specified.
		if ($order)
		{
			$data['order'] = $order;
		}

		// Check if category is specified.
		if ($category)
		{
			$data['category'] = $category;
		}

		// Check if modified_since is specified.
		if ($modifiedSince)
		{
			$data['modified-since'] = $modifiedSince;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to get posts a user started / participated in / follows for a group.
	 *
	 * @param   string   $groupId        The unique identifier for a group.
	 * @param   string   $role           Filter for posts related to the caller. Valid for: creator, commenter, follower.
	 * @param   string   $personId       The unique identifier for a user.
	 * @param   string   $fields         Request fields beyond the default ones.
	 * @param   integer  $start          Starting location within the result set for paginated returns.
	 * @param   integer  $count          The number of results returned.
	 * @param   string   $order          Sort order for posts. Valid for: recency, popularity.
	 * @param   string   $category       Category of posts. Valid for: discussion
	 * @param   string   $modifiedSince  Timestamp filter for posts created after the specified value.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function getUserPosts($groupId, $role, $personId = null, $fields = null, $start = 0, $count = 0,
		$order = null, $category = 'discussion', $modifiedSince = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/people/';

		// Check if person_id is specified.
		if ($personId)
		{
			$base .= $personId;
		}
		else
		{
			$base .= '~';
		}

		$base .= '/group-memberships/' . $groupId . '/posts';

		$data['format'] = 'json';
		$data['role'] = $role;

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Check if start is specified.
		if ($start > 0)
		{
			$data['start'] = $start;
		}

		// Check if count is specified.
		if ($count > 0)
		{
			$data['count'] = $count;
		}

		// Check if order is specified.
		if ($order)
		{
			$data['order'] = $order;
		}

		// Check if category is specified.
		if ($category)
		{
			$data['category'] = $category;
		}

		// Check if modified_since is specified.
		if ($modifiedSince)
		{
			$data['modified-since'] = $modifiedSince;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to retrieve details about a post.
	 *
	 * @param   string  $postId  The unique identifier for a post.
	 * @param   string  $fields  Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function getPost($postId, $fields = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/posts/' . $postId;

		$data['format'] = 'json';

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to retrieve all comments of a post.
	 *
	 * @param   string   $postId  The unique identifier for a post.
	 * @param   string   $fields  Request fields beyond the default ones.
	 * @param   integer  $start   Starting location within the result set for paginated returns.
	 * @param   integer  $count   The number of results returned.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function getPostComments($postId, $fields = null, $start = 0, $count = 0)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/posts/' . $postId . '/comments';

		$data['format'] = 'json';

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Check if start is specified.
		if ($start > 0)
		{
			$data['start'] = $start;
		}

		// Check if count is specified.
		if ($count > 0)
		{
			$data['count'] = $count;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to retrieve all comments of a post.
	 *
	 * @param   string  $groupId  The unique identifier for a group.
	 * @param   string  $title    Post title.
	 * @param   string  $summary  Post summary.
	 *
	 * @return  string  The created post's id.
	 *
	 * @since   3.2.0
	 */
	public function createPost($groupId, $title, $summary)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/groups/' . $groupId . '/posts';

		// Build xml.
		$xml = '<post><title>' . $title . '</title><summary>' . $summary . '</summary></post>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);

		// Return the post id.
		$response = explode('posts/', $response->headers['Location']);

		return $response[1];
	}

	/**
	 * Method to like or unlike a post.
	 *
	 * @param   string   $postId  The unique identifier for a group.
	 * @param   boolean  $like    True to like post, false otherwise.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	private function _likeUnlike($postId, $like)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/posts/' . $postId . '/relation-to-viewer/is-liked';

		// Build xml.
		$xml = '<is-liked>' . $this->booleanToString($like) . '</is-liked>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'PUT', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method used to like a post.
	 *
	 * @param   string  $postId  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function likePost($postId)
	{
		return $this->_likeUnlike($postId, true);
	}

	/**
	 * Method used to unlike a post.
	 *
	 * @param   string  $postId  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function unlikePost($postId)
	{
		return $this->_likeUnlike($postId, false);
	}

	/**
	 * Method to follow or unfollow a post.
	 *
	 * @param   string   $postId  The unique identifier for a group.
	 * @param   boolean  $follow  True to like post, false otherwise.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	private function _followUnfollow($postId, $follow)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/posts/' . $postId . '/relation-to-viewer/is-following';

		// Build xml.
		$xml = '<is-following>' . $this->booleanToString($follow) . '</is-following>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'PUT', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method used to follow a post.
	 *
	 * @param   string  $postId  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function followPost($postId)
	{
		return $this->_followUnfollow($postId, true);
	}

	/**
	 * Method used to unfollow a post.
	 *
	 * @param   string  $postId  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function unfollowPost($postId)
	{
		return $this->_followUnfollow($postId, false);
	}

	/**
	 * Method to flag a post as a Promotion or Job.
	 *
	 * @param   string  $postId  The unique identifier for a group.
	 * @param   string  $flag    Flag as a 'promotion' or 'job'.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function flagPost($postId, $flag)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/posts/' . $postId . '/category/code';

		// Build xml.
		$xml = '<code>' . $flag . '</code>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'PUT', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method to delete a post if the current user is the creator or flag it as inappropriate otherwise.
	 *
	 * @param   string  $postId  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function deletePost($postId)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/posts/' . $postId;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'DELETE', $parameters);

		return $response;
	}

	/**
	 * Method to access the comments resource.
	 *
	 * @param   string  $commentId  The unique identifier for a comment.
	 * @param   string  $fields     Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function getComment($commentId, $fields = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/comments/' . $commentId;

		$data['format'] = 'json';

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to add a comment to a post
	 *
	 * @param   string  $postId   The unique identifier for a group.
	 * @param   string  $comment  The post comment's text.
	 *
	 * @return  string   The created comment's id.
	 *
	 * @since   3.2.0
	 */
	public function addComment($postId, $comment)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/posts/' . $postId . '/comments';

		// Build xml.
		$xml = '<comment><text>' . $comment . '</text></comment>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);

		// Return the comment id.
		$response = explode('comments/', $response->headers['Location']);

		return $response[1];
	}

	/**
	 * Method to delete a comment if the current user is the creator or flag it as inappropriate otherwise.
	 *
	 * @param   string  $commentId  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function deleteComment($commentId)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/comments/' . $commentId;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'DELETE', $parameters);

		return $response;
	}

	/**
	 * Method to get suggested groups for a user.
	 *
	 * @param   string  $personId  The unique identifier for a user.
	 * @param   string  $fields    Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function getSuggested($personId = null, $fields = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/people/';

		// Check if person_id is specified.
		if ($personId)
		{
			$base .= $personId . '/suggestions/groups';
		}
		else
		{
			$base .= '~/suggestions/groups';
		}

		$data['format'] = 'json';

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to delete a group suggestion for a user.
	 *
	 * @param   string  $suggestionId  The unique identifier for a suggestion.
	 * @param   string  $personId      The unique identifier for a user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function deleteSuggestion($suggestionId, $personId = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/people/';

		// Check if person_id is specified.
		if ($personId)
		{
			$base .= $personId . '/suggestions/groups/' . $suggestionId;
		}
		else
		{
			$base .= '~/suggestions/groups/' . $suggestionId;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'DELETE', $parameters);

		return $response;
	}
}
