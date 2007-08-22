<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	JFramework
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.event.plugin');
/**
 * Joomla User plugin
 *
 * @author		Johan Janssens  <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	JFramework
 * @since 		1.5
 */
class plgUserJoomla extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgUserJoomla(& $subject, $config) {
		parent::__construct($subject, $config);
	}

	/**
	 * Remove all sessions for the user name
	 *
	 * Method is called before user data is deleted from the database
	 *
	 * @param 	array		holds the user data
	 */
	function onBeforeDeleteUser($user)
	{
		$db =& JFactory::getDBO();
		if($user =& JUser::getInstance( $user['id'] )) {
			$username = $user->get('username');
		} else {
			// This should never happen?!?
			return false;
		}
		$db->setQuery('DELETE FROM #__session WHERE username = '.$db->Quote($username));
		$db->Query();
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @access	public
	 * @param   array   holds the user data
	 * @param 	array   array holding options (remember, autoregister, ...)
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function onLoginUser($user, $options = array())
	{
		jimport('joomla.user.helper');

		$my = new JUser();
		if($id = intval(JUserHelper::getUserId($user['username'])))  {
			$my->load($id);
		}
		else
		{
			$usersConfig = &JComponentHelper::getParams( 'com_users' );
			$newUsertype = $usersConfig->get( 'new_usertype', 'Registered' );

			$authorize	=& JFactory::getACL();

			$my->set( 'id'				, 0 );
			$my->set( 'name'			, $user['fullname'] );
			$my->set( 'username'		, $user['username'] );
			$my->set( 'password_clear'	, $user['password_clear'] );
			$my->set( 'email'			, $user['email'] );	// Result should contain an email (check)
			$my->set( 'gid'				, $authorize->get_group_id( '', $newUsertype, 'ARO' ));
			$my->set( 'usertype'		, $newUsertype );

			//If autoregister is set let's register the user
			$autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $this->params->get('autoregister', 1);

			if($autoregister) {
				if(!$my->save()) {
					return JError::raiseWarning('SOME_ERROR_CODE', $my->getError());
				}
			}
		}

		// If the user is blocked, redirect with an error
		if ($my->get('block') == 1) {
			return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_NOLOGIN_BLOCKED'));
		}

		//Mark the user as logged in
		$my->set( 'guest', 0);

		// Discover the access group identifier
		// NOTE : this is a very basic for of permission handling, will be replaced by a full ACL in 1.6
		jimport('joomla.factory');
		$acl = &JFactory::getACL();
		$grp = $acl->getAroGroup($my->get('id'));

		$my->set('aid', 1);
		if ($acl->is_group_child_of($grp->name, 'Registered', 'ARO') || $acl->is_group_child_of($grp->name, 'Public Backend', 'ARO')) {
			// fudge Authors, Editors, Publishers and Super Administrators into the special access group
			$my->set('aid', 2);
		}

		//Set the usertype based on the ACL group name
		$my->set('usertype', $grp->name);

		// Register the needed session variables
		$session =& JFactory::getSession();
		$session->set('user', $my);

		// Get the session object
		$table = & JTable::getInstance('session');
		$table->load( $session->getId() );

		$table->guest 		= $my->get('guest');
		$table->username 	= $my->get('username');
		$table->userid 		= intval($my->get('id'));
		$table->usertype 	= $my->get('usertype');
		$table->gid 		= intval($my->get('gid'));

		$table->update();

		// Hit the user last visit field
		$my->setLastVisit();

		return true;
	}

	/**
	 * This method should handle any logout logic and report back to the subject
	 *
	 * @access public
	 * @param  array	holds the user data
	 * @return boolean True on success
	 * @since 1.5
	 */
	function onLogoutUser($user)
	{
  		$session =& JFactory::getSession();

		// Remove the session from the session table
		$table = & JTable::getInstance('session');
		$table->destroy($session->getId());

		// Destroy the php session for this user
		$session->destroy();
	}
}

?>
