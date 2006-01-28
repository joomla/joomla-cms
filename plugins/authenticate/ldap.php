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

jimport('joomla.application.extensions.plugin');
jimport('joomla.connector.ldap');

/**
 * LDAP JAuthenticate Plugin
 *
 * @author Louis Landry <louis@webimagery.net>
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
	 * @param	array	$credentials	Authentication credentials
	 * @return	object	JAuthenticateResponse
	 * @since 1.1
	 */
	function onAuthenticate(& $credentials) {
		global $mainframe;

		// Initialize variables
		$return = new JAuthenticateResponse('LDAP');
		$conditions = '';
		$userID = 0;

		// Get a database connector
		$db = $mainframe->getDBO();

		// If we are in the admin panel, make sure we have access to it
		if ($mainframe->isAdmin()) {
			$conditions = "AND gid > 22";
		}

		// load plugin parameters
	 	$plugin =& JPluginHelper::getPlugin('auth', 'ldap'); 
	 	$pluginParams = new JParameters( $plugin->params );

		$ldap = new JLDAP($pluginParams);
		//print_r($ldap);
		if (!$ldap->connect()) {
			$return->type = 'error';
			$return->uid  = 0;
			$return->error_message = 'Connection to LDAP server failed';
			return $return;
		}
		$success = $ldap->bind($credentials['username'], $credentials['password']);
	
		/*
			// just a test, please leave
			$search_filters = array( '(objectclass=*)' );
			$attributes = $ldap->search( $search_filters );
			print_r($attributes);
		*/
		
		$ldap->close();

		$userId = 0;
		if ($success) {
			$query = 	"SELECT `id`".
						"\nFROM `#__users`".
						"\nWHERE username=".$db->Quote($credentials['username']).
						$conditions;

			$db->setQuery($query);
			$userId = $db->loadResult();
				
		} else {
			$return->type = 'failure';
			$return->uid  = 0;
			$return->error_message = 'Bind to LDAP server failed';
			return $return;
		}

		if ($userId) {
			$return->type = 'success';
		} else {
			$return->type = 'failure';
			$return->error_message = 'Database returned no result.';
		}
		$return->uid = $userId;

		return $return;
	}
}
?>