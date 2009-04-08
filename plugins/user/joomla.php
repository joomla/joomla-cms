<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	JFramework
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

/**
 * Joomla User plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 		1.5
 */
class plgUserJoomla extends JPlugin
{
	/**
	 * Remove all sessions for the user name
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param 	array		holds the user data
	 * @param	boolean		true if user was succesfully stored in the database
	 * @param	string		message
	 */
	public function onAfterDeleteUser($user, $succes, $msg)
	{
		if (!$succes) {
			return false;
		}

		try
		{
			$db =& JFactory::getDBO();
			$db->setQuery('DELETE FROM #__session WHERE userid = '.(int) $user['id']);
			$db->query();

			// Clean up private messaging data

			$db->setQuery('DELETE FROM #__messages_cfg WHERE user_id = '.(int) $user['id']);
			$db->query();

			$db->setQuery('DELETE FROM #__messages WHERE user_id_to = '.(int) $user['id']);
			$db->query();
		}
		catch (JException $e) {
			// @todo Should we do a setError on the dispatcher?
			JError::raiseWarning(500, $e->getMessage());
			return false;
		}

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

		// if _getUser returned an error, then pass it back.
		if (JError::isError( $instance )) {
			return $instance;
		}

		// If the user is blocked, redirect with an error
		if ($instance->get('block') == 1) {
			return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_NOLOGIN_BLOCKED'));
		}

		// Get an ACL object
		$acl =& JFactory::getACL();
// @todo Fix these groups
/**		// Get the user group from the ACL

if ($instance->get('tmp_user') == 1) {
			$grp = new JObject;
			// This should be configurable at some point
			$grp->set('name', 'Registered');
		} else {
			//$grp = $acl->getAroGroup($instance->get('id'));
		}
*/
		//Authorise the user based on the group information
		if (!isset($options['group'])) {
			$options['group'] = 'USERS';
		}

		//if (!$acl->is_group_child_of( $grp->name, $options['group'])) {
		//	return JError::raiseWarning('SOME_ERROR_CODE', JText::_('E_NOLOGIN_ACCESS'));
		//}

		// THE NEW WAY
		jimport('joomla.access.access');
		$userId	= $instance->id;
		// Always let the Root User in
		if ($userId != JFactory::getApplication()->getCfg('root_user'))
		{
			$acs	= new JAccess;
			$result	= $acs->check($instance->id, $options['action']);
			if (!$result['allow']) {
				return JError::raiseWarning(401, JText::_('JError_Login_denied'));
			}
		}

		//Mark the user as logged in
		$instance->set( 'guest', 0);
		$instance->set('aid', 1);

		// Register the needed session variables
		$session =& JFactory::getSession();
		$session->set('user', $instance);

		// Get the session object
		$table = & JTable::getInstance('session');
		$table->load( $session->getId() );

		$table->guest 		= $instance->get('guest');
		$table->username 	= $instance->get('username');
		$table->userid 		= intval($instance->get('id'));
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
		//Make sure we're a valid user first
		if ($user['id'] == 0) return true;

		$my =& JFactory::getUser();
		//Check to see if we're deleting the current session
		if ($my->get('id') == $user['id'])
		{
			// Hit the user last visit field
			$my->setLastVisit();

			// Destroy the php session for this user
			$session =& JFactory::getSession();
			$session->destroy();
		} else {
			// Force logout all users with that userid
			$table = & JTable::getInstance('session');
			$table->destroy($user['id'], $options['clientid']);
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
		$instance = JUser::getInstance();
		if ($id = intval(JUserHelper::getUserId($user['username'])))  {
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

		if ($autoregister)
		{
			if (!$instance->save()) {
				return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
			}
		} else {
			// No existing user and autoregister off, this is a temporary user.
			$instance->set( 'tmp_user', true );
		}

		return $instance;
	}
}
