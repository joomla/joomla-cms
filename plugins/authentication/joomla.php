<?php
/**
* @version $Id: joomla.php 2034 2006-01-28 20:45:57Z webImagery $
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


/**
 * Joomla Core JAuthenticate plugin
 *
 * @author Louis Landry <louis.landry@joomla.org>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.5
 */
class JAuthenticateJoomla extends JPlugin {

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @since 1.5
	 */
	function JAuthenticateJoomla(& $subject) {
		parent::__construct($subject);
	}

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param	string	$username	Username for authentication
	 * @param	string	$password	Password for authentication
	 * @return	object	JAuthenticateResponse
	 * @since 1.5
	 */
	function onAuthenticate( $username, $password )
	{
		global $mainframe;

		// Initialize variables
		$conditions = '';

		// If we are in the admin panel, make sure we have access to it
		if($mainframe->isAdmin()) {
			$conditions = " AND gid > 22";
		}

		// Get a database object
		$db = $mainframe->getDBO();

		$query = "SELECT `id`"
			. "\nFROM `#__users`"
			. "\nWHERE username=" . $db->Quote( $username )
			. "\n AND password=" . $db->Quote( JAuthenticateHelper::getCryptedPassword( $password ) )
			. $conditions;

		$db->setQuery( $query );
		$result = $db->loadResult();

		return $result ? true : false;
	}
}
?>