<?php
/**
* @version $Id: ldap.php 2034 2006-01-28 20:45:57Z webImagery $
* @package Joomla
* @subpackage JFramework
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.application.extension.plugin');
jimport('joomla.connector.ldap');

/**
 * LDAP JAuthenticate Plugin
 *
 * @author Louis Landry <louis.landry@joomla.org>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
class JAuthenticateLdap extends JPlugin {

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
	function JAuthenticateLdap(& $subject) {
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
	function onAuthenticate( $username, $password ) 
	{
		global $mainframe;

		// Initialize variables
		$conditions = '';

		// Get a database connector
		$db = $mainframe->getDBO();

		// If we are in the admin panel, make sure we have access to it
		if ($mainframe->isAdmin()) {
			$conditions = "AND gid > 22";
		}

		// load plugin parameters
	 	$plugin =& JPluginHelper::getPlugin('authentication', 'ldap'); 
	 	$pluginParams = new JParameter( $plugin->params );

		$ldap = new JLDAP($pluginParams);
		
		$return = new JAuthenticateResponse('LDAP');
		if (!$ldap->connect()) {
			$return->error_type = 'error';
			$return->error_message = 'Connection to LDAP server failed';
			return $return;
		}
		$success = $ldap->bind($username, $password);
	
		/*
			// just a test, please leave
			$search_filters = array( '(objectclass=*)' );
			$attributes = $ldap->search( $search_filters );
			print_r($attributes);
		*/
		
		$ldap->close();

		return $success;
	}
}
?>