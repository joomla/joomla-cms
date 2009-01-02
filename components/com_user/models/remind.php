<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	User
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * User Component Remind Model
 *
 * @package		Joomla
 * @subpackage	User
 * @since		1.5
 */
class UserModelRemind extends JModel
{
	/**
	 * Registry namespace prefix
	 *
	 * @var	string
	 */
	var $_namespace	= 'com_user.remind.';

	/**
	 * Takes a user supplied e-mail address, looks
	 * it up in the database to find the username
	 * and then e-mails the username to the e-mail
	 * address given.
	 *
	 * @since	1.5
	 * @param	string	E-mail address
	 * @return	bool	True on success/false on failure
	 */
	function remindUsername($email)
	{
		jimport('joomla.mail.helper');

		global $mainframe;

		// Validate the e-mail address
		if (!JMailHelper::isEmailAddress($email))
		{
			$this->setError(JText::_('INVALID_EMAIL_ADDRESS'));
			return false;
		}

		$db = &JFactory::getDBO();
		$db->setQuery('SELECT username FROM #__users WHERE email = '.$db->Quote($email), 0, 1);

		// Get the username
		if (!($username = $db->loadResult()))
		{
			$this->setError(JText::_('COULD_NOT_FIND_EMAIL'));
			return false;
		}

		// Push the email address into the session
		$mainframe->setUserState($this->_namespace.'email', $email);

		// Send the reminder email
		if (!$this->_sendReminderMail($email, $username))
		{
			return false;
		}

		return true;
	}

	/**
	 * Sends a username reminder to the e-mail address
	 * specified containing the specified username.
	 *
	 * @since	1.5
	 * @param	string	A user's e-mail address
	 * @param	string	A user's username
	 * @return	bool	True on success/false on failure
	 */
	function _sendReminderMail($email, $username)
	{
		$config		= &JFactory::getConfig();
		$uri		= &JFactory::getURI();
		$url		= $uri->toString( array('scheme', 'host', 'port')).JRoute::_('index.php?option=com_user&view=login', false);

		$from		= $config->getValue('mailfrom');
		$fromname	= $config->getValue('fromname');
		$subject	= JText::sprintf('USERNAME_REMINDER_EMAIL_TITLE', $config->getValue('sitename'));
		$body		= JText::sprintf('USERNAME_REMINDER_EMAIL_TEXT', $config->getValue('sitename'), $username, $url);

		if (!JUtility::sendMail($from, $fromname, $email, $subject, $body))
		{
			$this->setError('ERROR_SENDING_REMINDER_EMAIL');
			return false;
		}

		return true;
	}
}