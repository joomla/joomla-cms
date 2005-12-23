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

jimport('joomla.plugin');


/**
 * Example JAuth Plugin
 *
 * @author Louis Landry <louis@webimagery.net>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
class JAuthExample extends JPlugin {

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
	function JAuthExample(& $subject) {
		parent::__construct($subject);
	}

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access public
	 * @param array Authentication credentials
	 * @return mixed Integer userid or boolean false
	 * @since 1.1
	 */
	function auth(& $credentials) {
		// Initialize variables
		$return = false;

		/*
		 * Here you would do whatever you need for an authentication routine with the credentials
		 *
		 * In this example the mixed variable $return would be set to false
		 * if the authentication routine fails or an integer userid of the authenticated
		 * user if the routine passes
		 */

		return $return;
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @access public
	 * @param array Authentication credentials
	 * @return boolean True on success
	 * @since 1.1
	 */
	function login(& $credentials) {
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
	function logout(& $credentials) {
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
