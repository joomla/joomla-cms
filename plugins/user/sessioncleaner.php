<?php
/**
* @version $Id: example.php 5173 2006-09-25 18:12:39Z Jinx $
* @package Joomla
* @subpackage JFramework
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.application.plugin.helper');

/**
 * Attach the plugin to the event dispatcher
 */
$dispatcher =& JEventDispatcher::getInstance();
$dispatcher->attach(new JSessionCleaner($dispatcher));

/**
 * Session Cleaner Plugin
 * Ensure that the session table is wiped out
 *
 * @author		Sam Moffatt <sam.moffatt@joomla.org>
 * @package		Joomla
 * @subpackage	JFramework
 * @since 		1.5
 */
class JSessionCleaner extends JPlugin {

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
	function JSessionCleaner(& $subject) {
		parent::__construct($subject);
	}

	/**
	 * Remove all sessions for the user name
	 *
	 * Method is called before user data is deleted from the database
	 * @param 	array	  	holds the user data
	 */
	function onBeforeDeleteUser($user)
	{
		global $mainframe;
		$db = JFactory::getDBO();
		$username = JUserHelper::getUserName($user['id']);
		$db->setQuery('DELETE FROM #__session WHERE username = "'.$username.'"');
		$db->Query();


		//Make sure
		mysql_select_db($mainframe->getCfg('db'));
	}

}
?>
