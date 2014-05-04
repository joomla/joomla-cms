<?php
/**
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Joomla User plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	User.joomla
 * @since		1.5
 */
class plgUserJoomla extends JPlugin
{
	/**
	 * Remove all sessions for the user name
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user	Holds the user data
	 * @param	boolean		$succes	True if user was succesfully stored in the database
	 * @param	string		$msg	Message
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	public function onUserAfterDelete($user, $succes, $msg)
	{
		if (!$succes) {
			return false;
		}

		$db = JFactory::getDbo();
		$db->setQuery(
			'DELETE FROM '.$db->quoteName('#__session') .
			' WHERE '.$db->quoteName('userid').' = '.(int) $user['id']
		);
		$db->Query();

		return true;
	}

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method sends a registration email to new users created in the backend.
	 *
	 * @param	array		$user		Holds the new user data.
	 * @param	boolean		$isnew		True if a new user is stored.
	 * @param	boolean		$success	True if user was succesfully stored in the database.
	 * @param	string		$msg		Message.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		$config	= JFactory::getConfig();
		$mail_to_user = $this->params->get('mail_to_user', 1);

		if ($isnew) {
			// TODO: Suck in the frontend registration emails here as well. Job for a rainy day.

			if ($app->isAdmin()) {
				if ($mail_to_user)
				{
					$lang = JFactory::getLanguage();
					$defaultLocale = $lang->getTag();

					/**
					 * Look for user language. Priority:
					 * 	1. User frontend language
					 * 	2. User backend language
					 */
					$userParams = new JRegistry($user['params']);
					$userLocale = $userParams->get('language', $userParams->get('admin_language', $defaultLocale));

					if ($userLocale != $defaultLocale)
					{
						$lang->setLanguage($userLocale);
					}

					$lang->load('plg_user_joomla', JPATH_ADMINISTRATOR);

					// Compute the mail subject.
					$emailSubject = JText::sprintf(
						'PLG_USER_JOOMLA_NEW_USER_EMAIL_SUBJECT',
						$user['name'],
						$config->get('sitename')
					);

					// Compute the mail body.
					$emailBody = JText::sprintf(
						'PLG_USER_JOOMLA_NEW_USER_EMAIL_BODY',
						$user['name'],
						$config->get('sitename'),
						JUri::root(),
						$user['username'],
						$user['password_clear']
					);

					// Assemble the email data...the sexy way!
					$mail = JFactory::getMailer()
						->setSender(
							array(
								$config->get('mailfrom'),
								$config->get('fromname')
							)
						)
						->addRecipient($user['email'])
						->setSubject($emailSubject)
						->setBody($emailBody);

					// Set application language back to default if we changed it
					if ($userLocale != $defaultLocale)
					{
						$lang->setLanguage($defaultLocale);
					}

					if (!$mail->Send()) {
						// TODO: Probably should raise a plugin error but this event is not error checked.
						JError::raiseWarning(500, JText::_('ERROR_SENDING_EMAIL'));
					}
				}
			}
		}
		else {
			// Existing user - nothing to do...yet.
		}
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @param	array	$user		Holds the user data
	 * @param	array	$options	Array holding options (remember, autoregister, group)
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function onUserLogin($user, $options = array())
	{
		$instance = $this->_getUser($user, $options);

		// If _getUser returned an error, then pass it back.
		if ($instance instanceof Exception) {
			return false;
		}

		// If the user is blocked, redirect with an error
		if ($instance->get('block') == 1) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('JERROR_NOLOGIN_BLOCKED'));
			return false;
		}

		// Authorise the user based on the group information
		if (!isset($options['group'])) {
			$options['group'] = 'USERS';
		}

		// Chek the user can login.
		$result	= $instance->authorise($options['action']);
		if (!$result) {

			JError::raiseWarning(401, JText::_('JERROR_LOGIN_DENIED'));
			return false;
		}

		// Mark the user as logged in
		$instance->set('guest', 0);

		// Register the needed session variables
		$session = JFactory::getSession();
		$session->set('user', $instance);

		$db = JFactory::getDBO();

		// Check to see the the session already exists.
		$app = JFactory::getApplication();
		$app->checkSession();

		// Update the user related fields for the Joomla sessions table.
		$db->setQuery(
			'UPDATE '.$db->quoteName('#__session') .
			' SET '.$db->quoteName('guest').' = '.$db->quote($instance->get('guest')).',' .
			'	'.$db->quoteName('username').' = '.$db->quote($instance->get('username')).',' .
			'	'.$db->quoteName('userid').' = '.(int) $instance->get('id') .
			' WHERE '.$db->quoteName('session_id').' = '.$db->quote($session->getId())
		);
		$db->query();

		// Hit the user last visit field
		$instance->setLastVisit();

		return true;
	}

	/**
	 * This method should handle any logout logic and report back to the subject
	 *
	 * @param	array	$user		Holds the user data.
	 * @param	array	$options	Array holding options (client, ...).
	 *
	 * @return	object	True on success
	 * @since	1.5
	 */
	public function onUserLogout($user, $options = array())
	{
		$my 		= JFactory::getUser();
		$session 	= JFactory::getSession();
		$app 		= JFactory::getApplication();

		// Make sure we're a valid user first
		if ($user['id'] == 0 && !$my->get('tmp_user')) {
			return true;
		}

		// Check to see if we're deleting the current session
		if ($my->get('id') == $user['id'] && $options['clientid'] == $app->getClientId()) {
			// Hit the user last visit field
			$my->setLastVisit();

			// Destroy the php session for this user
			$session->destroy();
		}

		// Force logout all users with that userid
		$db = JFactory::getDBO();
		$db->setQuery(
			'DELETE FROM '.$db->quoteName('#__session') .
			' WHERE '.$db->quoteName('userid').' = '.(int) $user['id'] .
			' AND '.$db->quoteName('client_id').' = '.(int) $options['clientid']
		);
		$db->query();

		return true;
	}

	/**
	 * This method will return a user object
	 *
	 * If options['autoregister'] is true, if the user doesn't exist yet he will be created
	 *
	 * @param	array	$user		Holds the user data.
	 * @param	array	$options	Array holding options (remember, autoregister, group).
	 *
	 * @return	object	A JUser object
	 * @since	1.5
	 */
	protected function _getUser($user, $options = array())
	{
		$instance = JUser::getInstance();
		if ($id = intval(JUserHelper::getUserId($user['username'])))  {
			$instance->load($id);
			return $instance;
		}

		//TODO : move this out of the plugin
		jimport('joomla.application.component.helper');
		$config	= JComponentHelper::getParams('com_users');
		// Default to Registered.
		$defaultUserGroup = $config->get('new_usertype', 2);

		$acl = JFactory::getACL();

		$instance->set('id'			, 0);
		$instance->set('name'			, $user['fullname']);
		$instance->set('username'		, $user['username']);
		$instance->set('password_clear'	, $user['password_clear']);
		$instance->set('email'			, $user['email']);	// Result should contain an email (check)
		$instance->set('usertype'		, 'deprecated');
		$instance->set('groups'		, array($defaultUserGroup));

		//If autoregister is set let's register the user
		$autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $this->params->get('autoregister', 1);

		if ($autoregister) {
			if (!$instance->save()) {
				return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
			}
		}
		else {
			// No existing user and autoregister off, this is a temporary user.
			$instance->set('tmp_user', true);
		}

		return $instance;
	}
}
