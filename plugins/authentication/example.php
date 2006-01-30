<?php
/**
* @version $Id: example.php 2034 2006-01-28 20:45:57Z webImagery $
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
 * Example JAuthenticate Plugin
 *
 * @author Louis Landry <louis@webimagery.net>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
class JAuthenticateExample extends JPlugin {

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
	function JAuthenticateExample(& $subject) {
		parent::__construct($subject);
	}

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param	string	$username	Username for authentication
	 * @param	string	$password	Password for authentication
	 * @return	object	JAuthenticateResponse
	 * @since 1.1
	 */
	function onAuthenticate( $username, $password ) {
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
}
?>