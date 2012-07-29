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
 * Linkedin API Social Stream class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 * @since       12.3
 */
class JLinkedinStream extends JLinkedinObject
{
	/**
	 * Method to add a new share. Note: post must contain comment and/or (title and url).
	 *
	 * @param   JLinkedinOAuth  $oauth        The JLinkedinOAuth object.
	 * @param   string          $visibility   One of anyone: all members or connections-only: connections only.
	 * @param   string          $comment      Text of member's comment.
	 * @param   string          $title        Title of shared document.
	 * @param   string          $url          URL for shared content.
	 * @param   string          $image         URL for image of shared content.
	 * @param   string          $description  Description of shared content.
	 * @param   boolean         $twitter      True to have LinkedIn pass the status message along to a member's tethered Twitter account.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function share($oauth, $visibility, $comment = null, $title = null, $url = null, $image = null, $description = null, $twitter = false)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/people/~/shares';

		// Check if twitter is true.
		if ($twitter)
		{
			$base .= '?twitter-post=true';
		}

		// Build xml.
		$xml = '<share>
				  <visibility>
					 <code>' . $visibility . '</code>
				  </visibility>';

		// Check if comment specified.
		if ($comment)
		{
			$xml .= '<comment>' . $comment . '</comment>';
		}

		// Check if title and url are specified.
		if ($title && $url)
		{
			$xml .= '<content>
					   <title>' . $title . '</title>
					   <submitted-url>' . $url . '</submitted-url>';

			// Check if image is specified.
			if ($image)
			{
				$xml .= '<submitted-image-url>' . $image . '</submitted-image-url>';
			}

			// Check if descrption id specified.
			if ($description)
			{
				$xml .= '<description>' . $description . '</description>';
			}

			$xml .= '</content>';
		}
		elseif (!$comment)
		{
			throw new RuntimeException('Post must contain comment and/or (title and url).');
		}

		$xml .= '</share>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);
		return $response;
	}

	/**
	 * Method to reshare an existing share.
	 *
	 * @param   JLinkedinOAuth  $oauth       The JLinkedinOAuth object.
	 * @param   string          $visibility  One of anyone: all members or connections-only: connections only.
	 * @param   string          $id          The unique identifier for a share.
	 * @param   string          $comment     Text of member's comment.
	 * @param   boolean         $twitter     True to have LinkedIn pass the status message along to a member's tethered Twitter account.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function reshare($oauth, $visibility, $id, $comment = null, $twitter = false)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/people/~/shares';

		// Check if twitter is true.
		if ($twitter)
		{
			$base .= '?twitter-post=true';
		}

		// Build xml.
		$xml = '<share>
				  <visibility>
					 <code>' . $visibility . '</code>
				  </visibility>';

		// Check if comment specified.
		if ($comment)
		{
			$xml .= '<comment>' . $comment . '</comment>';
		}

		$xml .= '   <attribution>
					   <share>
					   	  <id>' . $id . '</id>
					   </share>
					</attribution>
				 </share>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);
		return $response;
	}

	/**
	 * Method to get a particular member's current share.
	 *
	 * @param   JLinkedinOAuth  $oauth  The JLinkedinOAuth object.
	 * @param   string          $id     Member id of the profile you want.
	 * @param   string          $url    The public profile URL.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getCurrentShare($oauth, $id = null, $url = null)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the API base
		$base = '/v1/people/';

		// Check if a member id is specified.
		if ($id)
		{
			$base .= 'id=' . $id;
		}
		elseif (!$url)
		{
			$base .= '~';
		}

		// Check if profile url is specified.
		if ($url)
		{
			$base .= 'url=' . $oauth->safeEncode($url);
		}

		$base .= ':(current-share)';

		// Set request parameters.
		$data['format'] = 'json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to get a particular member's current share.
	 *
	 * @param   JLinkedinOAuth  $oauth  The JLinkedinOAuth object.
	 * @param   string          $id     Member id of the profile you want.
	 * @param   string          $url    The public profile URL.
	 * @param   boolean         $self   Used to return member's feed. Omitted to return aggregated network feed.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getShareStream($oauth, $id = null, $url = null, $self = true)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the API base
		$base = '/v1/people/';

		// Check if a member id is specified.
		if ($id)
		{
			$base .= $id;
		}
		elseif (!$url)
		{
			$base .= '~';
		}

		// Check if profile url is specified.
		if ($url)
		{
			$base .= 'url=' . $oauth->safeEncode($url);
		}

		$base .= '/network';

		// Set request parameters.
		$data['format'] = 'json';
		$data['type'] = 'SHAR';

		// Check if self is true
		if ($self)
		{
			$data['scope'] = 'self';
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to get the users network updates.
	 *
	 * @param   JLinkedinOAuth  $oauth   The JLinkedinOAuth object.
	 * @param   string          $id      Member id.
	 * @param   boolean         $self    Used to return member's feed. Omitted to return aggregated network feed.
	 * @param   mixed           $type    String containing any valid Network Update Type from the table or an array of strings
	 * 									 to specify more than one Network Update type.
	 * @param   integer         $count   Number of updates to return, with a maximum of 250.
	 * @param   integer         $start   The offset by which to start Network Update pagination.
	 * @param   string          $after   Timestamp after which to retrieve updates.
	 * @param   string          $before  Timestamp before which to retrieve updates.
	 * @param   boolean         $hidden  Whether to display updates from people the member has chosen to "hide" from their update stream.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getNetworkUpdates($oauth, $id = null, $self = true, $type = null, $count = 0, $start = 0, $after = null, $before = null, $hidden = false)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the API base
		$base = '/v1/people/';

		// Check if a member id is specified.
		if ($id)
		{
			$base .= $id;
		}
		else
		{
			$base .= '~';
		}

		$base .= '/network/updates';

		// Set request parameters.
		$data['format'] = 'json';

		// Check if self is true.
		if ($self)
		{
			$data['scope'] = 'self';
		}

		// Check if type is specified.
		if ($type)
		{
			$data['type'] = $type;
		}

		// Check if count is specified.
		if ($count > 0)
		{
			$data['count'] = $count;
		}

		// Check if start is specified.
		if ($start > 0)
		{
			$data['start'] = $start;
		}

		// Check if after is specified.
		if ($after)
		{
			$data['after'] = $after;
		}

		// Check if before is specified.
		if ($before > 0)
		{
			$data['before'] = $before;
		}

		// Check if hidden is true.
		if ($hidden)
		{
			$data['hidden'] = $hidden;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to get information about the current member's network.
	 *
	 * @param   JLinkedinOAuth  $oauth  The JLinkedinOAuth object.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getNetworkStats($oauth)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the API base
		$base = '/v1/people/~/network/network-stats';

		// Set request parameters.
		$data['format'] = 'json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to get the users network updates.
	 *
	 * @param   JLinkedinOAuth  $oauth  The JLinkedinOAuth object.
	 * @param   string          $body   The actual content of the update. You can use HTML to include links to the user name and the content the user created.
	 * 									Other HTML tags are not supported. All body text should be HTML entity escaped and UTF-8 compliant.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function postNetworkUpdate($oauth, $body)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/people/~/person-activities';

		// Build the xml.
		$xml = '<activity locale="en_US">
					<content-type>linkedin-html</content-type>
				    <body>' . $body . '</body>
				</activity>';

		$header['Content-Type'] = 'text/xml';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);
		return $response;
	}

	/**
	 * Method to retrieve all comments for a given network update.
	 *
	 * @param   JLinkedinOAuth  $oauth  The JLinkedinOAuth object.
	 * @param   string          $key    update/update-key representing an update.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getComments($oauth, $key)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the API base
		$base = '/v1/people/~/network/updates/key=' . $key . '/update-comments';

		// Set request parameters.
		$data['format'] = 'json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to post a new comment to an existing update.
	 *
	 * @param   JLinkedinOAuth  $oauth    The JLinkedinOAuth object.
	 * @param   string          $key      update/update-key representing an update.
	 * @param   string          $comment  Maximum length of 700 characters
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function postComment($oauth, $key, $comment)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/people/~/network/updates/key=' . $key . '/update-comments';

		// Build the xml.
		$xml = '<update-comment>
				  <comment>' . $comment . '</comment>
				</update-comment>';

		$header['Content-Type'] = 'text/xml';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);
		return $response;
	}
}
