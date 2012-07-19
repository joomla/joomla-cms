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
	 * Method to find the groups a member belongs to.
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

		if (empty($response->body))
		{
			return $response->headers['Location'];
		}

		return json_decode($response->body);
	}
}
