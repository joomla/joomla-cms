<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Linkedin API Groups class for the Joomla Platform.
 *
 * @since  13.1
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
	 * @since   13.1
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
	 * @param   string   $id                The unique identifier for a user.
	 * @param   string   $fields            Request fields beyond the default ones.
	 * @param   integer  $start             Starting location within the result set for paginated returns.
	 * @param   integer  $count             The number of results returned.
	 * @param   string   $membership_state  The state of the caller’s membership to the specified group.
	 * 										Values are: non-member, awaiting-confirmation, awaiting-parent-group-confirmation, member, moderator, manager, owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getMemberships($id = null, $fields = null, $start = 0, $count = 5, $membership_state = null)
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
		if ($membership_state)
		{
			$data['membership-state'] = $membership_state;
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
	 * @param   string   $person_id  The unique identifier for a user.
	 * @param   string   $group_id   The unique identifier for a group.
	 * @param   string   $fields     Request fields beyond the default ones.
	 * @param   integer  $start      Starting location within the result set for paginated returns.
	 * @param   integer  $count      The number of results returned.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getSettings($person_id = null, $group_id = null, $fields = null, $start = 0, $count = 5)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/people/';

		// Check if person_id is specified.
		if ($person_id)
		{
			$base .= $person_id . '/group-memberships';
		}
		else
		{
			$base .= '~/group-memberships';
		}

		// Check if group_id is specified.
		if ($group_id)
		{
			$base .= '/' . $group_id;
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
	 * @param   string   $group_id          The unique identifier for a group.
	 * @param   boolean  $show_logo         Show group logo in profile.
	 * @param   string   $digest_frequency  Email digest frequency.
	 * @param   boolean  $announcements     Email announcements from managers.
	 * @param   boolean  $allow_messages    Allow messages from members.
	 * @param   boolean  $new_post          Email for every new post.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function changeSettings($group_id, $show_logo = null, $digest_frequency = null, $announcements = null,
		$allow_messages = null, $new_post = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/people/~/group-memberships/' . $group_id;

		// Build xml.
		$xml = '<group-membership>';

		if (!is_null($show_logo))
		{
			$xml .= '<show-group-logo-in-profile>' . $this->booleanToString($show_logo) . '</show-group-logo-in-profile>';
		}

		if ($digest_frequency)
		{
			$xml .= '<email-digest-frequency><code>' . $digest_frequency . '</code></email-digest-frequency>';
		}

		if (!is_null($announcements))
		{
			$xml .= '<email-announcements-from-managers>' . $this->booleanToString($announcements) . '</email-announcements-from-managers>';
		}

		if (!is_null($allow_messages))
		{
			$xml .= '<allow-messages-from-members>' . $this->booleanToString($allow_messages) . '</allow-messages-from-members>';
		}

		if (!is_null($new_post))
		{
			$xml .= '<email-for-every-new-post>' . $this->booleanToString($new_post) . '</email-for-every-new-post>';
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
	 * @param   string   $group_id          The unique identifier for a group.
	 * @param   boolean  $show_logo         Show group logo in profile.
	 * @param   string   $digest_frequency  Email digest frequency.
	 * @param   boolean  $announcements     Email announcements from managers.
	 * @param   boolean  $allow_messages    Allow messages from members.
	 * @param   boolean  $new_post          Email for every new post.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function joinGroup($group_id, $show_logo = null, $digest_frequency = null, $announcements = null,
		$allow_messages = null, $new_post = null)
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
		$xml = '<group-membership><group><id>' . $group_id . '</id></group>';

		if (!is_null($show_logo))
		{
			$xml .= '<show-group-logo-in-profile>' . $this->booleanToString($show_logo) . '</show-group-logo-in-profile>';
		}

		if ($digest_frequency)
		{
			$xml .= '<email-digest-frequency><code>' . $digest_frequency . '</code></email-digest-frequency>';
		}

		if (!is_null($announcements))
		{
			$xml .= '<email-announcements-from-managers>' . $this->booleanToString($announcements) . '</email-announcements-from-managers>';
		}

		if (!is_null($allow_messages))
		{
			$xml .= '<allow-messages-from-members>' . $this->booleanToString($allow_messages) . '</allow-messages-from-members>';
		}

		if (!is_null($new_post))
		{
			$xml .= '<email-for-every-new-post>' . $this->booleanToString($new_post) . '</email-for-every-new-post>';
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
	 * @param   string  $group_id  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function leaveGroup($group_id)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/people/~/group-memberships/' . $group_id;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'DELETE', $parameters);

		return $response;
	}

	/**
	 * Method to get dicussions for a group.
	 *
	 * @param   string   $id              The unique identifier for a group.
	 * @param   string   $fields          Request fields beyond the default ones.
	 * @param   integer  $start           Starting location within the result set for paginated returns.
	 * @param   integer  $count           The number of results returned.
	 * @param   string   $order           Sort order for posts. Valid for: recency, popularity.
	 * @param   string   $category        Category of posts. Valid for: discussion
	 * @param   string   $modified_since  Timestamp filter for posts created after the specified value.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getDiscussions($id, $fields = null, $start = 0, $count = 0, $order = null, $category = 'discussion', $modified_since = null)
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
		if ($modified_since)
		{
			$data['modified-since'] = $modified_since;
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
	 * @param   string   $group_id        The unique identifier for a group.
	 * @param   string   $role            Filter for posts related to the caller. Valid for: creator, commenter, follower.
	 * @param   string   $person_id       The unique identifier for a user.
	 * @param   string   $fields          Request fields beyond the default ones.
	 * @param   integer  $start           Starting location within the result set for paginated returns.
	 * @param   integer  $count           The number of results returned.
	 * @param   string   $order           Sort order for posts. Valid for: recency, popularity.
	 * @param   string   $category        Category of posts. Valid for: discussion
	 * @param   string   $modified_since  Timestamp filter for posts created after the specified value.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getUserPosts($group_id, $role, $person_id = null, $fields = null, $start = 0, $count = 0,
		$order = null, $category = 'discussion', $modified_since = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/people/';

		// Check if person_id is specified.
		if ($person_id)
		{
			$base .= $person_id;
		}
		else
		{
			$base .= '~';
		}

		$base .= '/group-memberships/' . $group_id . '/posts';

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
		if ($modified_since)
		{
			$data['modified-since'] = $modified_since;
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
	 * @param   string  $post_id  The unique identifier for a post.
	 * @param   string  $fields   Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getPost($post_id, $fields = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/posts/' . $post_id;

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
	 * @param   string   $post_id  The unique identifier for a post.
	 * @param   string   $fields   Request fields beyond the default ones.
	 * @param   integer  $start    Starting location within the result set for paginated returns.
	 * @param   integer  $count    The number of results returned.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getPostComments($post_id, $fields = null, $start = 0, $count = 0)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/posts/' . $post_id . '/comments';

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
	 * @param   string  $group_id  The unique identifier for a group.
	 * @param   string  $title     Post title.
	 * @param   string  $summary   Post summary.
	 *
	 * @return  string  The created post's id.
	 *
	 * @since   13.1
	 */
	public function createPost($group_id, $title, $summary)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/groups/' . $group_id . '/posts';

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
	 * @param   string   $post_id  The unique identifier for a group.
	 * @param   boolean  $like     True to like post, false otherwise.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	private function _likeUnlike($post_id, $like)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/posts/' . $post_id . '/relation-to-viewer/is-liked';

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
	 * @param   string  $post_id  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function likePost($post_id)
	{
		return $this->_likeUnlike($post_id, true);
	}

	/**
	 * Method used to unlike a post.
	 *
	 * @param   string  $post_id  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function unlikePost($post_id)
	{
		return $this->_likeUnlike($post_id, false);
	}

	/**
	 * Method to follow or unfollow a post.
	 *
	 * @param   string   $post_id  The unique identifier for a group.
	 * @param   boolean  $follow   True to like post, false otherwise.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	private function _followUnfollow($post_id, $follow)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/posts/' . $post_id . '/relation-to-viewer/is-following';

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
	 * @param   string  $post_id  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function followPost($post_id)
	{
		return $this->_followUnfollow($post_id, true);
	}

	/**
	 * Method used to unfollow a post.
	 *
	 * @param   string  $post_id  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function unfollowPost($post_id)
	{
		return $this->_followUnfollow($post_id, false);
	}

	/**
	 * Method to flag a post as a Promotion or Job.
	 *
	 * @param   string  $post_id  The unique identifier for a group.
	 * @param   string  $flag     Flag as a 'promotion' or 'job'.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function flagPost($post_id, $flag)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/posts/' . $post_id . '/category/code';

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
	 * @param   string  $post_id  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function deletePost($post_id)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/posts/' . $post_id;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'DELETE', $parameters);

		return $response;
	}

	/**
	 * Method to access the comments resource.
	 *
	 * @param   string  $comment_id  The unique identifier for a comment.
	 * @param   string  $fields      Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getComment($comment_id, $fields = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/comments/' . $comment_id;

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
	 * @param   string  $post_id  The unique identifier for a group.
	 * @param   string  $comment  The post comment's text.
	 *
	 * @return  string   The created comment's id.
	 *
	 * @since   13.1
	 */
	public function addComment($post_id, $comment)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/posts/' . $post_id . '/comments';

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
	 * @param   string  $comment_id  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function deleteComment($comment_id)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/comments/' . $comment_id;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'DELETE', $parameters);

		return $response;
	}

	/**
	 * Method to get suggested groups for a user.
	 *
	 * @param   string  $person_id  The unique identifier for a user.
	 * @param   string  $fields     Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getSuggested($person_id = null, $fields = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/people/';

		// Check if person_id is specified.
		if ($person_id)
		{
			$base .= $person_id . '/suggestions/groups';
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
	 * @param   string  $suggestion_id  The unique identifier for a suggestion.
	 * @param   string  $person_id      The unique identifier for a user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function deleteSuggestion($suggestion_id, $person_id = null)
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
		if ($person_id)
		{
			$base .= $person_id . '/suggestions/groups/' . $suggestion_id;
		}
		else
		{
			$base .= '~/suggestions/groups/' . $suggestion_id;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'DELETE', $parameters);

		return $response;
	}
}
