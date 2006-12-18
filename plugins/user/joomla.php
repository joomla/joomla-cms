<?php
/**
* @version $Id: example.php 5979 2006-12-11 22:13:41Z hackwar $
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
 * Joomla User plugin
 *
 * @author		Johan Janssens  <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	JFramework
 * @since 		1.5
 */
class JUserJoomla extends JPlugin
{
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
	function JUserJoomla(& $subject) {
		parent::__construct($subject);
	}

	/**
	 * Remove all sessions for the user name
	 *
	 * Method is called before user data is deleted from the database
	 *
	 * @param 	array	  	holds the user data
	 */
	function onBeforeDeleteUser($user)
	{
		global $mainframe;

		$db = JFactory::getDBO();
		if($user = JUser::getInstance( $user['id'] )) {
			$username = $user->get('username');
		} else {
			// This should never happen?!?
			return false;
		}
		$db->setQuery('DELETE FROM #__session WHERE username = "'.$username.'"');
		$db->Query();


		//Make sure
		mysql_select_db($mainframe->getCfg('db'));

	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @access	public
	 * @param 	array	holds the user data
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function onLoginUser($user, $remember)
	{
		// load plugin parameters
	 	$plugin =& JPluginHelper::getPlugin('authentication', 'joomla');
	 	$params = new JParameter( $plugin->params );
		
		// We need to create the user if they don't exist
		if(!$id = intval(JUserHelper::getUserId($user['username']))) 
		{
			$my = new JUser();
			$my->set( 'id'			, 0 );
			$my->set( 'name'		, $user['fullname'] );
			$my->set( 'username'	, $user['username'] );
			$my->set( 'email'		, $user['email'] );	// Result should contain an email (check)
			$my->set( 'gid'			, 18 );			  	//Make configurable
			$my->set( 'usertype'	, 'Registered' ); 	//Make configurable
					
			if(!$my->save()) {
				return false;
			}
			
			//get the id of the new user
			$id = intval($my->get('id'));
		}
					
		// Get the JUser object for the user to login
		$my =& JUser::getInstance( $id );

		// If the user is blocked, redirect with an error
		if ($my->get('block') == 1) {
			return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_NOLOGIN_BLOCKED'));
		}

		// Fudge the ACL stuff for now...
		// TODO: Implement ACL :)
		jimport('joomla.factory');
		$acl = &JFactory::getACL();
		$grp = $acl->getAroGroup($my->get('id'));
		$my->set('gid', 1);

		// ToDO: Add simple mapping based on the group table to allow positive references between content and user groups
		if ($acl->is_group_child_of($grp->name, 'Registered', 'ARO') || $acl->is_group_child_of($grp->name, 'Public Backend', 'ARO')) {
				// fudge Authors, Editors, Publishers and Super Administrators into the Special Group
				$my->set('gid', 2);
		}
		$my->set('usertype', $grp->name);

		// Register the needed session variables
		$session =& JFactory::getSession();
		$session->set('session.user.id', $my->get('id'));

		// Get the session object
		$table = & JTable::getInstance('session');
		$table->load( $session->getId() );

		$table->guest 		= 0;
		$table->username 	= $my->get('username');
		$table->userid 		= intval($my->get('id'));
		$table->usertype 	= $my->get('usertype');
		$table->gid 		= intval($my->get('gid'));

		$table->update();

		// Hit the user last visit field
		$my->setLastVisit();

		// Set remember me option
		if ($remember == 'yes')
		{
			$lifetime = time() + 365*24*60*60;
			setcookie( 'usercookie[username]', $my->get('username'), $lifetime, '/' );
			setcookie( 'usercookie[password]', $my->get('password'), $lifetime, '/' );
		}

		return true;
	}

	/**
	 * This method should handle any logout logic and report back to the subject
	 *
	 * @access public
	* @param   array	  holds the user data
	 * @return boolean True on success
	 * @since 1.5
	 */
	function onLogoutUser($user)
	{
		$session =& JFactory::getSession();

		// Remove the session from the session table
		$table = & JTable::getInstance('session');
		$table->load( $session->getId());
		$table->destroy();

		// Destroy the php session for this user
		$session->destroy();
	}
}

/**
 * Attach the plugin to the event dispatcher
 */
$dispatcher =& JEventDispatcher::getInstance();
$dispatcher->attach(new JUserJoomla($dispatcher));
?>