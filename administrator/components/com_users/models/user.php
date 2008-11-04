<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package		Users
 * @subpackage	com_users
 */
class UserModelUser extends JModel
{
	/**
	 * Proxy for getTable
	 */
	function &getTable()
	{
		return parent::getTable('User', 'JTable');
	}

	/**
	 * @return	JUser
	 */
	function &getItem()
	{
		$session	= &JFactory::getSession();
		$id			= (int) $session->get('users.'.$this->getName().'.id', $this->getState('id'));
		$user		= &JUser::getInstance($id);
		return $user;
	}

	/**
	 * Perform batch operations
	 *
	 * @param	array	An array of variable for the batch operation
	 * @param	array	An array of IDs on which to operate
	 */
	function batch($vars, $ids)
	{
		$db		= $this->getDBO();
		$result	= true;

		JArrayHelper::toInteger($ids);

		// Do stuff

		return $result;
	}

	/**
	 * Saves the record
	 */
	function save($input)
	{
		// Initialize some variables
		$app		= &JFactory::getApplication();
		$db			= &JFactory::getDBO();
		$me			= &JFactory::getUser();
		$acl		= &JFactory::getACL();
		$MailFrom	= $app->getCfg('mailfrom');
		$FromName	= $app->getCfg('fromname');
		$SiteName	= $app->getCfg('sitename');
		$userId		= JArrayHelper::getValue($input, 'id', 0, 'int');

 		// Create a new JUser object
		$user		= JUser::getInstance($userId);
		// @todo How does this work with multi-mapping groups
		$oldGroupId	= $user->get('gid');

		if (!$user->bind($input)) {
			$this->setError($user->getError());
			return false;
		}

		$objectID 	= $acl->get_object_id('users', $user->get('id'), 'ARO');
		$groups 	= $acl->get_object_groups($objectID, 'ARO');
		$this_group = strtolower($acl->get_group_name($groups[0], 'ARO'));

		if ($user->get('id') == $me->get('id') && $user->get('block') == 1) {
			$this->setError(JText::_('You cannot block Yourself!'));
			return false;
		}

		if (($this_group == 'super administrator') && $user->get('block') == 1) {
			$this->setError(JText::_('You cannot block a Super Administrator'));
			return false;
		}

		if (($this_group == 'administrator') && ($me->get('gid') == 24) && $user->get('block') == 1) {
			$this->setError(JText::_('WARNBLOCK'));
			return false;
		}

		if (($this_group == 'super administrator') && ($me->get('gid') != 25))
		{
			$this->setError(JText::_('You cannot edit a super administrator account'));
			return false;
		}
		// Are we dealing with a new user which we need to create?
		$isNew 	= ($user->get('id') < 1);
		if (!$isNew)
		{
			// if group has been changed and where original group was a Super Admin
			if ($user->get('gid') != $oldGroupId && $oldGroupId == 25)
			{
				// count number of active super admins
				$db->setQuery(
					'SELECT COUNT(id)'
					. ' FROM #__users'
					. ' WHERE gid = 25'
					. ' AND block = 0'
				);
				$count = $db->loadResult();

				if ($count <= 1) {
					// disallow change if only one Super Admin exists
					$this->setError(JText::_('WARN_ONLY_SUPER'));
					return false;
				}
			}
		}

		// Lets save the JUser object
		if (!$user->save())
		{
			$this->setError($user->getError());
			return false;
		}

		/*
	 	 * Time for the email magic so get ready to sprinkle the magic dust...
	 	 */
		if ($isNew)
		{
			$adminEmail = $me->get('email');
			$adminName	= $me->get('name');

			$subject = JText::_('NEW_USER_MESSAGE_SUBJECT');
			$message = sprintf (JText::_('NEW_USER_MESSAGE'), $user->get('name'), $SiteName, JUri::root(), $user->get('username'), $user->password_clear);

			if ($MailFrom != '' && $FromName != '')
			{
				$adminName 	= $FromName;
				$adminEmail = $MailFrom;
			}
			JUtility::sendMail($adminEmail, $adminName, $user->get('email'), $subject, $message);
		}

		// If updating self, load the new user object into the session
		if ($user->get('id') == $me->get('id'))
		{
			// Get an ACL object
			$acl = &JFactory::getACL();

			// Get the user group from the ACL
			$grp = $acl->getAroGroup($user->get('id'));

			// Mark the user as logged in
			$user->set('guest', 0);
			$user->set('aid', 1);

			// Fudge Authors, Editors, Publishers and Super Administrators into the special access group
			if ($acl->is_group_child_of($grp->name, 'Registered') ||
				$acl->is_group_child_of($grp->name, 'Public Backend')) {
				$user->set('aid', 2);
			}

			// Set the usertype based on the ACL group name
			$user->set('usertype', $grp->name);

			$session = &JFactory::getSession();
			$session->set('user', $user);
		}

		return true;
	}

	/**
	 * Removes the record(s) from the database
	 *
	 * @param	array	An array of User IDs
	 * @return	boolean
	 */
	function delete($ids)
	{
		JArrayHelper::toInteger($ids);

		if (count($ids) < 1) {
			$this->setError(JText::_('Select a User to delete'));
			return false;
		}

		foreach ($ids as $id)
		{
			// check for a super admin ... can't delete them
			$objectID 	= $acl->get_object_id('users', $id, 'ARO');
			$groups 	= $acl->get_object_groups($objectID, 'ARO');
			$thisGroup = strtolower($acl->get_group_name($groups[0], 'ARO'));

			$success = false;
			// @todo The group checking need to be done via the API
			if ($thisGroup == 'super administrator') {
				$this->setError(JText::_('You cannot delete a Super Administrator'));
			}
			else if ($id == $currentUser->get('id')) {
				$this->setError(JText::_('You cannot delete Yourself!'));
			}
			// @todo The group checking need to be done via the API
			else if (($thisGroup == 'administrator') && ($currentUser->get('gid') == 24)) {
				$this->setError(JText::_('WARNDELETE'));
			}
			else {
				$user	=& JUser::getInstance((int)$id);
				$count	= 2;

				if ($user->get('gid') == 25) {
					// count number of active super admins
					$db->setQuery(
						'SELECT COUNT(id)'
						. ' FROM #__users'
						. ' WHERE gid = 25'
						. ' AND block = 0'
					);
					$count = $db->loadResult();
				}

				if ($count <= 1 && $user->get('gid') == 25) {
					// cannot delete Super Admin where it is the only one that exists
					$this->setError(JText::_('You cannot delete this Super Administrator as it is the only active Super Administrator for your site'));
				}
				else {
					// @todo Log the user out/delete user acounts active sessions
					$user->delete();
					// @todo Error check delete
					return true;
				}
			}
		}
		return false;
	}

}