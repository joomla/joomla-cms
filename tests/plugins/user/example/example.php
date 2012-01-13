<?php
/**
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Example User Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	User.example
 * @since		1.5
 */
class plgUserExample extends JPlugin
{
	/**
	 * Example store user method
	 *
	 * Method is called before user data is stored in the database
	 *
	 * @param	array		$user	Holds the old user data.
	 * @param	boolean		$isnew	True if a new user is stored.
	 * @param	array		$new	Holds the new user data.
	 *
	 * @return	void
	 * @since	1.6
	 * @throws	Exception on error.
	 */
	public function onUserBeforeSave($user, $isnew, $new)
	{
		$app = JFactory::getApplication();

		// throw new Exception('Some error occurred. Please do not save me');
	}

	/**
	 * Example store user method
	 *
	 * Method is called after user data is stored in the database
	 *
	 * @param	array		$user		Holds the new user data.
	 * @param	boolean		$isnew		True if a new user is stored.
	 * @param	boolean		$success	True if user was succesfully stored in the database.
	 * @param	string		$msg		Message.
	 *
	 * @return	void
	 * @since	1.6
	 * @throws	Exception on error.
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		$app = JFactory::getApplication();

		// convert the user parameters passed to the event
		// to a format the external application

		$args = array();
		$args['username']	= $user['username'];
		$args['email']		= $user['email'];
		$args['fullname']	= $user['name'];
		$args['password']	= $user['password'];

		if ($isnew) {
			// Call a function in the external app to create the user
			// ThirdPartyApp::createUser($user['id'], $args);
		}
		else {
			// Call a function in the external app to update the user
			// ThirdPartyApp::updateUser($user['id'], $args);
		}
	}

	/**
	 * Example store user method
	 *
	 * Method is called before user data is deleted from the database
	 *
	 * @param	array		$user	Holds the user data.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onUserBeforeDelete($user)
	{
		$app = JFactory::getApplication();
	}

	/**
	 * Example store user method
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user	Holds the user data.
	 * @param	boolean		$succes	True if user was succesfully stored in the database.
	 * @param	string		$msg	Message.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onUserAfterDelete($user, $succes, $msg)
	{
		$app = JFactory::getApplication();

		// only the $user['id'] exists and carries valid information

		// Call a function in the external app to delete the user
		// ThirdPartyApp::deleteUser($user['id']);
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @param	array	$user		Holds the user data.
	 * @param	array	$options	Extra options.
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function onUserLogin($user, $options)
	{
		// Initialise variables.
		$success = false;

		// Here you would do whatever you need for a login routine with the credentials
		//
		// Remember, this is not the authentication routine as that is done separately.
		// The most common use of this routine would be logging the user into a third party
		// application.
		//
		// In this example the boolean variable $success would be set to true
		// if the login routine succeeds

		// ThirdPartyApp::loginUser($user['username'], $user['password']);

		return $success;
	}

	/**
	 * This method should handle any logout logic and report back to the subject
	 *
	 * @param	array	$user	Holds the user data.
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function onUserLogout($user)
	{
		// Initialise variables.
		$success = false;

		// Here you would do whatever you need for a logout routine with the credentials
		//
		// In this example the boolean variable $success would be set to true
		// if the logout routine succeeds

		// ThirdPartyApp::loginUser($user['username'], $user['password']);

		return $success;
	}
}
