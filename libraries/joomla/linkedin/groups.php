<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Linkedin API Groups class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 * @since       12.3
 */
class JLinkedinGroups extends JLinkedinObject
{
	/**
	 * Method to get a group.
	 *
	 * @param   JLinkedinOAuth  $oauth   The JLinkedinOAuth object.
	 * @param   string          $id      The unique identifier for a group.
	 * @param   string          $fields  Request fields beyond the default ones.
	 * @param   integer         $start   Starting location within the result set for paginated returns.
	 * @param   integer         $count   The number of results returned.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getGroup($oauth, $id, $fields = null, $start = 0, $count = 5)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
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
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to find the groups a member belongs to.
	 *
	 * @param   JLinkedinOAuth  $oauth             The JLinkedinOAuth object.
	 * @param   string          $id                The unique identifier for a user.
	 * @param   string          $fields            Request fields beyond the default ones.
	 * @param   integer         $start             Starting location within the result set for paginated returns.
	 * @param   integer         $count             The number of results returned.
	 * @param   string          $membership_state  The state of the caller’s membership to the specified group.
	 * 											   Values are: non-member, awaiting-confirmation, awaiting-parent-group-confirmation, member, moderator, manager, owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getMemberships($oauth, $id = null, $fields = null, $start = 0, $count = 5, $membership_state = null)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
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
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to find the groups a member belongs to.
	 *
	 * @param   JLinkedinOAuth  $oauth      The JLinkedinOAuth object.
	 * @param   string          $person_id  The unique identifier for a user.
	 * @param   string          $group_id   The unique identifier for a group.
	 * @param   string          $fields     Request fields beyond the default ones.
	 * @param   integer         $start      Starting location within the result set for paginated returns.
	 * @param   integer         $count      The number of results returned.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getSettings($oauth, $person_id = null, $group_id = null, $fields = null, $start = 0, $count = 5)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
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
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to change a groups settings.
	 *
	 * @param   JLinkedinOAuth  $oauth             The JLinkedinOAuth object.
	 * @param   string          $group_id          The unique identifier for a group.
	 * @param   boolean         $show_logo         Show group logo in profile.
	 * @param   string          $digest_frequency  E-mail digest frequency.
	 * @param   boolean         $announcements     E-mail announcements from managers.
	 * @param   boolean         $allow_messages    Allow messages from members.
	 * @param   boolean         $new_post          E-mail for every new post.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function changeSettings($oauth, $group_id, $show_logo = null, $digest_frequency = null, $announcements = null, $allow_messages = null, $new_post = null)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the API base
		$base = '/v1/people/~/group-memberships/' . $group_id;

		// Build xml.
		$xml = '<group-membership>';

		if (!is_null($show_logo))
		{
			$xml .= '<show-group-logo-in-profile>' . $this->boolean_to_string($show_logo) . '</show-group-logo-in-profile>';
		}

		if ($digest_frequency)
		{
			$xml .= '<email-digest-frequency><code>' . $digest_frequency . '</code></email-digest-frequency>';
		}

		if (!is_null($announcements))
		{
			$xml .= '<email-announcements-from-managers>' . $this->boolean_to_string($announcements) . '</email-announcements-from-managers>';
		}

		if (!is_null($allow_messages))
		{
			$xml .= '<allow-messages-from-members>' . $this->boolean_to_string($allow_messages) . '</allow-messages-from-members>';
		}

		if (!is_null($new_post))
		{
			$xml .= '<email-for-every-new-post>' . $this->boolean_to_string($new_post) . '</email-for-every-new-post>';
		}

		$xml .= '</group-membership>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $oauth->oauthRequest($path, 'PUT', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method to join a group.
	 *
	 * @param   JLinkedinOAuth  $oauth             The JLinkedinOAuth object.
	 * @param   string          $group_id          The unique identifier for a group.
	 * @param   boolean         $show_logo         Show group logo in profile.
	 * @param   string          $digest_frequency  E-mail digest frequency.
	 * @param   boolean         $announcements     E-mail announcements from managers.
	 * @param   boolean         $allow_messages    Allow messages from members.
	 * @param   boolean         $new_post          E-mail for every new post.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function joinGroup($oauth, $group_id, $show_logo = null, $digest_frequency = null, $announcements = null, $allow_messages = null, $new_post = null)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/people/~/group-memberships';

		// Build xml.
		$xml = '<group-membership><group><id>' . $group_id . '</id></group>';

		if (!is_null($show_logo))
		{
			$xml .= '<show-group-logo-in-profile>' . $this->boolean_to_string($show_logo) . '</show-group-logo-in-profile>';
		}

		if ($digest_frequency)
		{
			$xml .= '<email-digest-frequency><code>' . $digest_frequency . '</code></email-digest-frequency>';
		}

		if (!is_null($announcements))
		{
			$xml .= '<email-announcements-from-managers>' . $this->boolean_to_string($announcements) . '</email-announcements-from-managers>';
		}

		if (!is_null($allow_messages))
		{
			$xml .= '<allow-messages-from-members>' . $this->boolean_to_string($allow_messages) . '</allow-messages-from-members>';
		}

		if (!is_null($new_post))
		{
			$xml .= '<email-for-every-new-post>' . $this->boolean_to_string($new_post) . '</email-for-every-new-post>';
		}

		$xml .= '<membership-state><code>member</code></membership-state></group-membership>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method to leave a group.
	 *
	 * @param   JLinkedinOAuth  $oauth             The JLinkedinOAuth object.
	 * @param   string          $group_id          The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function leaveGroup($oauth, $group_id)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/people/~/group-memberships/' . $group_id;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'DELETE', $parameters);

		return $response;
	}

	/**
	 * Method to get dicussions for a group.
	 *
	 * @param   JLinkedinOAuth  $oauth           The JLinkedinOAuth object.
	 * @param   string          $id              The unique identifier for a group.
	 * @param   string          $fields          Request fields beyond the default ones.
	 * @param   integer         $start           Starting location within the result set for paginated returns.
	 * @param   integer         $count           The number of results returned.
	 * @param   string          $order           Sort order for posts. Valid for: recency, popularity.
	 * @param   string          $category        Category of posts. Valid for: discussion
	 * @param   string          $modified_since  Timestamp filter for posts created after the specified value.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getDiscussions($oauth, $id, $fields = null, $start = 0, $count = 0, $order = null, $category = 'discussion', $modified_since = null)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
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
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to get posts a user started / participated in / follows for a group.
	 *
	 * @param   JLinkedinOAuth  $oauth           The JLinkedinOAuth object.
	 * @param   string          $group_id        The unique identifier for a group.
	 * @param   string          $role            Filter for posts related to the caller. Valid for: creator, commenter, follower.
	 * @param   string          $person_id       The unique identifier for a user.
	 * @param   string          $fields          Request fields beyond the default ones.
	 * @param   integer         $start           Starting location within the result set for paginated returns.
	 * @param   integer         $count           The number of results returned.
	 * @param   string          $order           Sort order for posts. Valid for: recency, popularity.
	 * @param   string          $category        Category of posts. Valid for: discussion
	 * @param   string          $modified_since  Timestamp filter for posts created after the specified value.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getUserPosts($oauth, $group_id, $role, $person_id = null, $fields = null, $start = 0, $count = 0, $order = null, $category = 'discussion', $modified_since = null)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
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
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to retrieve details about a post.
	 *
	 * @param   JLinkedinOAuth  $oauth    The JLinkedinOAuth object.
	 * @param   string          $post_id  The unique identifier for a post.
	 * @param   string          $fields   Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getPost($oauth, $post_id, $fields = null)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
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
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to retrieve all comments of a post.
	 *
	 * @param   JLinkedinOAuth  $oauth    The JLinkedinOAuth object.
	 * @param   string          $post_id  The unique identifier for a post.
	 * @param   string          $fields   Request fields beyond the default ones.
	 * @param   integer         $start    Starting location within the result set for paginated returns.
	 * @param   integer         $count    The number of results returned.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getPostComments($oauth, $post_id, $fields = null, $start = 0, $count = 0)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
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
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to retrieve all comments of a post.
	 *
	 * @param   JLinkedinOAuth  $oauth     The JLinkedinOAuth object.
	 * @param   string          $group_id  The unique identifier for a group.
	 * @param   string          $title     Post title.
	 * @param   string          $summary   Post summary.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function createPost($oauth, $group_id, $title, $summary)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/groups/' . $group_id . '/posts';

		// Build xml.
		$xml = '<post><title>' . $title . '</title><summary>' . $summary . '</summary></post>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method to like or unlike a post.
	 *
	 * @param   JLinkedinOAuth  $oauth    The JLinkedinOAuth object.
	 * @param   string          $post_id  The unique identifier for a group.
	 * @param   boolean         $like     True to like post, false otherwise.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	private function _likeUnlike($oauth, $post_id, $like)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/posts/' . $post_id . '/relation-to-viewer/is-liked';

		// Build xml.
		$xml = '<is-liked>' . $this->boolean_to_string($like) . '</is-liked>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $oauth->oauthRequest($path, 'PUT', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method used to like a post.
	 *
	 * @param   JLinkedinOAuth  $oauth    The JLinkedinOAuth object.
	 * @param   string          $post_id  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function likePost($oauth, $post_id)
	{
		return $this->_likeUnlike($oauth, $post_id, true);
	}

	/**
	 * Method used to unlike a post.
	 *
	 * @param   JLinkedinOAuth  $oauth    The JLinkedinOAuth object.
	 * @param   string          $post_id  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function unlikePost($oauth, $post_id)
	{
		return $this->_likeUnlike($oauth, $post_id, false);
	}

	/**
	 * Method to follow or unfollow a post.
	 *
	 * @param   JLinkedinOAuth  $oauth    The JLinkedinOAuth object.
	 * @param   string          $post_id  The unique identifier for a group.
	 * @param   boolean         $follow   True to like post, false otherwise.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	private function _followUnfollow($oauth, $post_id, $follow)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/posts/' . $post_id . '/relation-to-viewer/is-following';

		// Build xml.
		$xml = '<is-following>' . $this->boolean_to_string($follow) . '</is-following>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $oauth->oauthRequest($path, 'PUT', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method used to follow a post.
	 *
	 * @param   JLinkedinOAuth  $oauth    The JLinkedinOAuth object.
	 * @param   string          $post_id  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function followPost($oauth, $post_id)
	{
		return $this->_followUnfollow($oauth, $post_id, true);
	}

	/**
	 * Method used to unfollow a post.
	 *
	 * @param   JLinkedinOAuth  $oauth    The JLinkedinOAuth object.
	 * @param   string          $post_id  The unique identifier for a group.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function unfollowPost($oauth, $post_id)
	{
		return $this->_followUnfollow($oauth, $post_id, false);
	}

	/**
	 * Method to flag a post as a Promotion or Job.
	 *
	 * @param   JLinkedinOAuth  $oauth    The JLinkedinOAuth object.
	 * @param   string          $post_id  The unique identifier for a group.
	 * @param   string          $flag     Flag as a 'promotion' or 'job'.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function flagPost($oauth, $post_id, $flag)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/posts/' . $post_id . '/category/code';

		// Build xml.
		$xml = '<code>' . $flag . '</code>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $oauth->oauthRequest($path, 'PUT', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method to delete a post if the current user is the creator or flag it as inappropriate otherwise.
	 *
	 * @param   JLinkedinOAuth  $oauth    The JLinkedinOAuth object.
	 * @param   string          $post_id  The unique identifier for a group.
	 * @param   string          $flag     Flag as a 'promotion' or 'job'.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function deletePost($oauth, $post_id)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/posts/' . $post_id;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'DELETE', $parameters);

		return $response;
	}

	/**
	 * Method to access the comments resource.
	 *
	 * @param   JLinkedinOAuth  $oauth       The JLinkedinOAuth object.
	 * @param   string          $comment_id  The unique identifier for a comment.
	 * @param   string          $fields      Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getComment($oauth, $comment_id, $fields = null)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
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
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}
}
