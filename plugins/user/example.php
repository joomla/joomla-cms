<?php
/**
* @version $Id$
* @package Joomla
* @subpackage JFramework
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.application.extension.plugin');

/**
 * Attach the plugin to the event dispatcher
 */  
$dispatcher =& JEventDispatcher::getInstance();
$dispatcher->attach('JUserExample');

/**
 * Example User Plugin
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	JFramework
 * @since 		1.1
 */
class JUserExample extends JPlugin {

	/**
	 * Constructor
	 * 
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 * 
	 * @param object $subject The object to observe
	 * @since 1.1
	 */
	function JUserExample(& $subject) {
		parent::__construct($subject);
	}
	
	/**
	 * Example store user method
	 * Method is called before user data is stored in the database
	 * 
	 * @param 	array	  	holds the user data
	 * @param 	boolean		true if a new user is stored
	 */
	function onBeforeStoreUser($user, $isnew)
	{
		global $mainframe;
		
		//Make sure
		mysql_select_db($mainframe->getCfg('db'));
	}
	
	/**
	 * Example store user method
	 * Method is called after user data is stored in the database
	 * 
	 * @param 	array	  	holds the user data
	 * @param 	boolean		true if a new user is stored
	 * @param	boolean		true if user was succesfully stored in the database
	 * @param	string		message
	 */
	function onAfterStoreUser($user, $isnew, $succes, $msg)
	{
		global $mainframe;

		/*
	 	 * convert the user parameters passed to the event to a format the
	 	 * external appliction
	 	 */

		$args = array();
		$args['username'] = $user['username'];
		$args['email'] 	  = $user['email'];
		$args['fullname'] = $user['name'];
		$args['password'] = $user['password'];

		if($isnew) {
			// Call a function in the external app to create the user
			// ThirdPartyApp::createUser($user['id'], $args);
		} else {
			// Call a function in the external app to update the user
			// ThirdPartyApp::updateUser($user['id'], $args);
		}

		//Make sure
		mysql_select_db($mainframe->getCfg('db'));
	}
	
	/**
	 * Example store user method
	 * 
	 * Method is called before user data is deleted from the database
	 * @param 	array	  	holds the user data
	 */
	function onBeforeDeleteUser($user)
	{
		global $mainframe;

		//Make sure
		mysql_select_db($mainframe->getCfg('db'));
	}

	/**
	 * Example store user method
	 * Method is called after user data is deleted from the database
	 * @param 	array	  	holds the user data
	 * @param	boolean		true if user was succesfully stored in the database
	 * @param	string		message
	 */
	function onAfterDeleteUser($user, $succes, $msg)
	{
		global $mainframe;

		/*
	 	 * only the $user['id'] exists and carries valid information
	 	 */

		// Call a function in the external app to delete the user
		// ThirdPartyApp::deleteUser($user['id']);

		//Make sure
		mysql_select_db($mainframe->getCfg('db'));
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @access	public
	 * @param	string	$username	Username for authentication
	 * @param	string	$password	Password for authentication
	 * @return	boolean	True on success
	 * @since	1.1
	 */
	function onLogin(& $credentials) 
	{
		// Initialize variables
		$success = false;

		/*
		 * Here you would do whatever you need for a login routine with the credentials
		 *
		 * Remember, this is not the authentication routine as that is done separately.
		 * The most common use of this routine would be logging the user into a third party
		 * application.
		 *
		 * In this example the boolean variable $success would be set to true
		 * if the login routine succeeds
		 */
		 
		 //ThirdPartyApp::loginUser($username, $password);

		return $success;
	}

	/**
	 * This method should handle any logout logic and report back to the subject
	 *
	 * @access public
	 * @param array Authentication credentials
	 * @return boolean True on success
	 * @since 1.1
	 */
	function onLogout(& $credentials) 
	{
		// Initialize variables
		$success = false;

		/*
		 * Here you would do whatever you need for a logout routine with the credentials
		 *
		 * In this example the boolean variable $success would be set to true
		 * if the logout routine succeeds
		 */
		 
		 // ThirdPartyApp::logoutUser($user->username, $user->password);

		return $success;
	}
}
?>