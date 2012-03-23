<?php

/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 */


defined('JPATH_PLATFORM') or die();


/**
 * Facebook API Friends class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 */

class JFacebookFriends extends JFacebookObject
{
	/**
	 * Method to get the friendlist for the specified user.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 *
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.	
	 */
	public function getFriendList($user, $access_token)
	{
		$username = '/' . $user;
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $username . '/friends' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

}
