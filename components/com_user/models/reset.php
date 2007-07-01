<?php
/**
 * @version		$Id: reset.php 7399 2007-05-14 04:10:09Z eddieajau $
 * @package		Joomla
 * @subpackage	User
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * User Component Reset Model
 *
 * @author		Rob Schley <rob.schley@joomla.org>
 * @package		Joomla
 * @subpackage	User
 * @since		1.5
 */
class UserModelReset extends JModel
{
	/**
	 * Registry namespace prefix
	 *
	 * @var	string
	 */
	var $_namespace	= 'com_user.reset.';

	/**
	 * Verifies the validity of a username/e-mail address
	 * combination and creates a token to verify the request
	 * was initiated by the account owner.  The token is
	 * sent to the account owner by e-mail
	 *
	 * @since	1.5
	 * @param	string	Username string
	 * @param	string	E-mail address
	 * @return	bool	True on success/false on failure
	 */
	function requestReset($username, $email)
	{
		jimport('joomla.utilities.mail');
		jimport('joomla.user.helper');

		$db = &JFactory::getDBO();

		// Make sure the e-mail address is valid
		if (!JMailHelper::isEmailAddress($email))
		{
			$this->setError(JText::_('INVALID_EMAIL_ADDRESS'));
			return false;
		}

		// Build a query to find the user
		$query	= 'SELECT id FROM #__users'
				. ' WHERE username = '.$db->Quote($username)
				. ' AND email = '.$db->Quote($email)
				. ' AND block = 0';

		$db->setQuery($query);

		// Check the results
		if (!($id = $db->loadResult()))
		{
			$this->setError(JText::_('COULD_NOT_FIND_USER'));
			return false;
		}

		// Generate a new token
		$token = md5(JUserHelper::genRandomPassword());

		$query	= 'UPDATE #__users'
				. ' SET activation = '.$db->Quote($token)
				. ' WHERE id = '.$db->Quote($id);

		$db->setQuery($query);

		// Save the token
		if (!$db->query())
		{
			$this->setError(JText::_('DATABASE_ERROR'));
			return false;
		}

		// Send the token to the user via e-mail
		if (!$this->_sendConfirmationMail($email, $token))
		{
			return false;
		}

		return true;
	}

	/**
	 * Checks a user supplied token for validity
	 * If the token is valid, it pushes the token
	 * and user id into the session for security checks.
	 *
	 * @since	1.5
	 * @param	token	An md5 hashed randomly generated string
	 * @return	bool	True on success/false on failure
	 */
	function confirmReset($token)
	{
		global $mainframe;

		$db	= &JFactory::getDBO();
		$db->setQuery('SELECT id FROM #__users WHERE activation = '.$db->Quote($token));

		// Verify the token
		if (!($id = $db->loadResult()))
		{
			$this->setError(JText::_('INVALID_TOKEN'));
			return false;
		}

		// Push the token and user id into the session
		$mainframe->setUserState($this->_namespace.'token',	$token);
		$mainframe->setUserState($this->_namespace.'id',	$id);

		return true;
	}

	/**
	 * Takes the new password and saves it to the database.
	 * It will only save the password if the user has the
	 * correct user id and token stored in her session.
	 *
	 * @since	1.5
	 * @param	string	New Password
	 * @param	string	New Password
	 * @return	bool	True on success/false on failure
	 */
	function completeReset($password1, $password2)
	{
		jimport('joomla.user.helper');

		global $mainframe;

		// Verify that the passwords match
		if ($password1 != $password2)
		{
			$this->setError(JText::_('PASSWORDS_DO_NOT_MATCH'));
			return false;
		}

		// Get the necessary variables
		$db			= &JFactory::getDBO();
		$id			= $mainframe->getUserState($this->_namespace.'id');
		$token		= $mainframe->getUserState($this->_namespace.'token');
		$salt		= JUserHelper::genRandomPassword(32);
		$crypt		= JUserHelper::getCryptedPassword($password1, $salt);
		$password	= $crypt.':'.$salt;

		// Build the query
		$query 	= 'UPDATE #__users'
				. ' SET password = '.$db->Quote($password)
				. ' WHERE id = '.(int) $id
				. ' AND activation = '.$db->Quote($token);

		$db->setQuery($query);

		// Save the password
		if (!$db->query())
		{
			$this->setError(JText::_('DATABASE_ERROR'));
			return false;
		}

		// Flush the variables from the session
		$mainframe->setUserState($this->_namespace.'id',	null);
		$mainframe->setUserState($this->_namespace.'token',	null);

		return true;
	}

	/**
	 * Sends a password reset request confirmation to the
	 * specified e-mail address with the specified token.
	 *
	 * @since	1.5
	 * @param	string	An e-mail address
	 * @param	string	An md5 hashed randomly generated string
	 * @return	bool	True on success/false on failure
	 */
	function _sendConfirmationMail($email, $token)
	{
		$config		= &JFactory::getConfig();
		$uri		= &JFactory::getURI();
		$url		= 'index.php?option=com_user&view=reset&layout=confirm';
		$sitename	= $config->getValue('sitename');

		// Set the e-mail parameters
		$from		= $config->getValue('mailfrom');
		$fromname	= $config->getValue('fromname');
		$subject	= JText::sprintf('PASSWORD_RESET_CONFIRMATION_EMAIL_TITLE', $sitename);
		$body		= JText::sprintf('PASSWORD_RESET_CONFIRMATION_EMAIL_TEXT', $sitename, $token, $url);

		// Send the e-mail
		if (!JUtility::sendMail($from, $fromname, $email, $subject, $body))
		{
			$this->setError('ERROR_SENDING_CONFIRMATION_EMAIL');
			return false;
		}

		return true;
	}
}