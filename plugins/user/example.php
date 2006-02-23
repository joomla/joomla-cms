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

/*
 * Here we register the plugin with the JApplication class by passing an empty
 * string for event and the class name for the handler. Function based plugins
 * cannot register this way as they also have to pass the event to be called on.
 */
$mainframe->registerEvent( '', 'JUserExample' );

/**
 * Example User Plugin
 *
 * @author		Louis Landry <louis@webimagery.net>
 * @package		Joomla
 * @subpackage	JFramework
 * @since 		1.1
 */
class JUserExample extends JPlugin {

	/**
	* Class constructor
	*
	* @param object $subject The object to observe
	* @acces protected
	*/
	function JUserExample(& $subject) {
		parent::__construct($subject);
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
	function onLogin(& $credentials) {
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
	function onLogout(& $credentials) {
		// Initialize variables
		$success = false;

		/*
		 * Here you would do whatever you need for a logout routine with the credentials
		 *
		 * In this example the boolean variable $success would be set to true
		 * if the logout routine succeeds
		 */

		return $success;
	}
}
?>