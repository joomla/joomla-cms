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

jimport('joomla.plugin.plugin');
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
	 * Method is called after user data is deleted from the database
	 *
	 * @param 	array	  	holds the user data
	 * @param	boolean		true if user was succesfully stored in the database
	 * @param	string		message
	 */
	function onAfterDeleteUser($user, $succes, $msg)
	{
		if(!$succes) {
			return false;
		}

		$db =& JFactory::getDBO();
		$db->setQuery('DELETE FROM #__session WHERE userid = '.$db->Quote($user['id']));
		$db->Query();

		return true;
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @access	public
	 * @param   array   holds the user data
	 * @param 	array   array holding options (remember, autoregister, group)
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function onLoginUser($user, $options = array())
	{
		jimport('joomla.user.helper');

		$instance =& $this->_getUser($user, $options);

		// If the user is blocked, redirect with an error
		if ($instance->get('block') == 1) {
			return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_NOLOGIN_BLOCKED'));
		}

		// Get an ACL object
		$acl =& JFactory::getACL();

		// Get the user group from the ACL
		if ($instance->get('tmp_user') == 1) {
			$grp = new JObject;
			// This should be configurable at some point
			$grp->set('name', 'Registered');
		} else {
			$grp = $acl->getAroGroup($instance->get('id'));
		}

		//Authorise the user based on the group information
		if(!isset($options['group'])) {
			$options['group'] = 'USERS';
		}

		if(!$acl->is_group_child_of( $grp->name, $options['group'])) {
			return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_NOLOGIN_ACCESS'));
		}

		//Mark the user as logged in
		$instance->set( 'guest', 0);
		$instance->set('aid', 1);

		// Fudge Authors, Editors, Publishers and Super Administrators into the special access group
		if ($acl->is_group_child_of($grp->name, 'Registered')      ||
		    $acl->is_group_child_of($grp->name, 'Public Backend'))    {
			$instance->set('aid', 2);
		}

		//Set the usertype based on the ACL group name
		$instance->set('usertype', $grp->name);

		// Register the needed session variables
		$session =& JFactory::getSession();
		$session->set('user', $instance);

		// Get the session object
		$table = & JTable::getInstance('session');
		$table->load( $session->getId() );

		$table->guest 		= $instance->get('guest');
		$table->username 	= $instance->get('username');
		$table->userid 		= intval($instance->get('id'));
		$table->usertype 	= $instance->get('usertype');
		$table->gid 		= intval($instance->get('gid'));

		$table->update();

		// Hit the user last visit field
		$instance->setLastVisit();

		return true;
	}

	/**
	 * This method should handle any logout logic and report back to the subject
	 *
	 * @access public
	 * @param  array	holds the user data
	 * @param 	array   array holding options (client, ...)
	 * @return object   True on success
	 * @since 1.5
	 */
	function onLogoutUser($user, $options = array())
	{
		// Remove the session from the session table
		$table = & JTable::getInstance('session');
		$table->destroy($user['id'], $options['clientid']);

		$my =& JFactory::getUser();
		if($my->get('id') == $user['id'])
		{
			// Hit the user last visit field
			$my->setLastVisit();

			// Destroy the php session for this user
			$session =& JFactory::getSession();
			$session->destroy();
		}

		return true;
	}

	/**
	 * This method will return a user object
	 *
	 * If options['autoregister'] is true, if the user doesn't exist yet he will be created
	 *
	 * @access	public
	 * @param   array   holds the user data
	 * @param 	array   array holding options (remember, autoregister, group)
	 * @return	object	A JUser object
	 * @since	1.5
	 */
	function &_getUser($user, $options = array())
	{
		$instance = new JUser();
		if($id = intval(JUserHelper::getUserId($user['username'])))  {
			$instance->load($id);
			return $instance;
		}

		//TODO : move this out of the plugin
		jimport('joomla.application.component.helper');
		$config   = &JComponentHelper::getParams( 'com_users' );
		$usertype = $config->get( 'new_usertype', 'Registered' );

		$acl =& JFactory::getACL();

		$instance->set( 'id'			, 0 );
		$instance->set( 'name'			, $user['fullname'] );
		$instance->set( 'username'		, $user['username'] );
		$instance->set( 'password_clear'	, $user['password_clear'] );
		$instance->set( 'email'			, $user['email'] );	// Result should contain an email (check)
		$instance->set( 'gid'			, $acl->get_group_id( '', $usertype));
		$instance->set( 'usertype'		, $usertype );

		//If autoregister is set let's register the user
		$autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $this->params->get('autoregister', 1);

		if($autoregister)
		{
			if(!$instance->save()) {
				return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
			}
		} else {
			// No existing user and autoregister off, this is a temporary user.
			$instance->set( 'tmp_user', true );
		}

		return $instance;
	}
}
