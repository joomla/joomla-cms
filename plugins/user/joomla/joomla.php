<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.joomla
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Joomla User plugin
 *
 * @since  1.5
 */
class PlgUserJoomla extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.2
	 */
	protected $db;

	/**
	 * Remove all sessions for the user name
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was successfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->delete($this->db->quoteName('#__session'))
			->where($this->db->quoteName('userid') . ' = ' . (int) $user['id']);

		try
		{
			$this->db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method sends a registration email to new users created in the backend.
	 *
	 * @param   array    $user     Holds the new user data.
	 * @param   boolean  $isnew    True if a new user is stored.
	 * @param   boolean  $success  True if user was successfully stored in the database.
	 * @param   string   $msg      Message.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		$mail_to_user = $this->params->get('mail_to_user', 1);

		if ($isnew)
		{
			// TODO: Suck in the frontend registration emails here as well. Job for a rainy day.
			if ($this->app->isAdmin())
			{
				if ($mail_to_user)
				{
					$lang = JFactory::getLanguage();
					$defaultLocale = $lang->getTag();

					/**
					 * Look for user language. Priority:
					 * 	1. User frontend language
					 * 	2. User backend language
					 */
					$userParams = new Registry($user['params']);
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
						$config = $this->app->get('sitename')
					);

					// Compute the mail body.
					$emailBody = JText::sprintf(
						'PLG_USER_JOOMLA_NEW_USER_EMAIL_BODY',
						$user['name'],
						$this->app->get('sitename'),
						JUri::root(),
						$user['username'],
						$user['password_clear']
					);

					// Assemble the email data...the sexy way!
					$mail = JFactory::getMailer()
						->setSender(
							array(
								$this->app->get('mailfrom'),
								$this->app->get('fromname')
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

					if (!$mail->Send())
					{
						$this->app->enqueueMessage(JText::_('JERROR_SENDING_EMAIL'), 'warning');
					}
				}
			}
		}
		else
		{
			// Existing user - nothing to do...yet.
		}
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @param   array  $user     Holds the user data
	 * @param   array  $options  Array holding options (remember, autoregister, group)
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.5
	 */
	public function onUserLogin($user, $options = array())
	{
		$instance = $this->_getUser($user, $options);

		// If _getUser returned an error, then pass it back.
		if ($instance instanceof Exception)
		{
			return false;
		}

		// If the user is blocked, redirect with an error
		if ($instance->get('block') == 1)
		{
			$this->app->enqueueMessage(JText::_('JERROR_NOLOGIN_BLOCKED'), 'warning');

			return false;
		}

		// Authorise the user based on the group information
		if (!isset($options['group']))
		{
			$options['group'] = 'USERS';
		}

		// Check the user can login.
		$result = $instance->authorise($options['action']);

		if (!$result)
		{
			$this->app->enqueueMessage(JText::_('JERROR_LOGIN_DENIED'), 'warning');

			return false;
		}

		// Mark the user as logged in
		$instance->set('guest', 0);

		// Register the needed session variables
		$session = JFactory::getSession();
		$session->set('user', $instance);

		// Check to see the the session already exists.
		$this->app->checkSession();

		// Update the user related fields for the Joomla sessions table.
		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__session'))
			->set($this->db->quoteName('guest') . ' = ' . $this->db->quote($instance->guest))
			->set($this->db->quoteName('username') . ' = ' . $this->db->quote($instance->username))
			->set($this->db->quoteName('userid') . ' = ' . (int) $instance->id)
			->where($this->db->quoteName('session_id') . ' = ' . $this->db->quote($session->getId()));

		try
		{
			$this->db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		// Hit the user last visit field
		$instance->setLastVisit();

		// Add "user state" cookie used for reverse caching proxies like Varnish, Nginx etc.
		$conf          = JFactory::getConfig();
		$cookie_domain = $conf->get('cookie_domain', '');
		$cookie_path   = $conf->get('cookie_path', '/');

		if ($this->app->isSite())
		{
			$this->app->input->cookie->set("joomla_user_state", "logged_in", 0, $cookie_path, $cookie_domain, 0);
		}

		return true;
	}

	/**
	 * This method should handle any logout logic and report back to the subject
	 *
	 * @param   array  $user     Holds the user data.
	 * @param   array  $options  Array holding options (client, ...).
	 *
	 * @return  object  True on success
	 *
	 * @since   1.5
	 */
	public function onUserLogout($user, $options = array())
	{
		$my      = JFactory::getUser();
		$session = JFactory::getSession();

		// Make sure we're a valid user first
		if ($user['id'] == 0 && !$my->get('tmp_user'))
		{
			return true;
		}

		// Check to see if we're deleting the current session
		if ($my->get('id') == $user['id'] && $options['clientid'] == $this->app->getClientId())
		{
			// Hit the user last visit field
			$my->setLastVisit();

			// Destroy the php session for this user
			$session->destroy();
		}

		// Enable / Disable Forcing logout all users with same userid
		$forceLogout = $this->params->get('forceLogout', 1);

		if ($forceLogout)
		{
			$query = $this->db->getQuery(true)
				->delete($this->db->quoteName('#__session'))
				->where($this->db->quoteName('userid') . ' = ' . (int) $user['id'])
				->where($this->db->quoteName('client_id') . ' = ' . (int) $options['clientid']);

			try
			{
				$this->db->setQuery($query)->execute();
			}
			catch (RuntimeException $e)
			{
				return false;
			}
		}

		// Delete "user state" cookie used for reverse caching proxies like Varnish, Nginx etc.
		$conf          = JFactory::getConfig();
		$cookie_domain = $conf->get('cookie_domain', '');
		$cookie_path   = $conf->get('cookie_path', '/');

		if ($this->app->isSite())
		{
			$this->app->input->cookie->set("joomla_user_state", "", time() - 86400, $cookie_path, $cookie_domain, 0);
		}

		return true;
	}

	/**
	 * This method will return a user object
	 *
	 * If options['autoregister'] is true, if the user doesn't exist yet he will be created
	 *
	 * @param   array  $user     Holds the user data.
	 * @param   array  $options  Array holding options (remember, autoregister, group).
	 *
	 * @return  object  A JUser object
	 *
	 * @since   1.5
	 */
	protected function _getUser($user, $options = array())
	{
		$instance = JUser::getInstance();
		$id = (int) JUserHelper::getUserId($user['username']);

		if ($id)
		{
			$instance->load($id);

			return $instance;
		}

		// TODO : move this out of the plugin
		$config = JComponentHelper::getParams('com_users');

		// Hard coded default to match the default value from com_users.
		$defaultUserGroup = $config->get('new_usertype', 2);

		$instance->set('id', 0);
		$instance->set('name', $user['fullname']);
		$instance->set('username', $user['username']);
		$instance->set('password_clear', $user['password_clear']);

		// Result should contain an email (check).
		$instance->set('email', $user['email']);
		$instance->set('groups', array($defaultUserGroup));

		// If autoregister is set let's register the user
		$autoregister = isset($options['autoregister']) ? $options['autoregister'] : $this->params->get('autoregister', 1);

		if ($autoregister)
		{
			if (!$instance->save())
			{
				JLog::add('Error in autoregistration for user ' . $user['username'] . '.', JLog::WARNING, 'error');
			}
		}
		else
		{
			// No existing user and autoregister off, this is a temporary user.
			$instance->set('tmp_user', true);
		}

		return $instance;
	}
}
