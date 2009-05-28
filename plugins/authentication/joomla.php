<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Joomla Authentication plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 1.5
 */
class plgAuthenticationJoomla extends JPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param   array 	$credentials Array holding the user credentials
	 * @param 	array   $options     Array of extra options
	 * @param	object	$response	 Authentication response object
	 * @return	boolean
	 * @since 1.5
	 */
	function onAuthenticate($credentials, $options, &$response)
	{
		jimport('joomla.user.helper');

		// Joomla does not like blank passwords
		if (empty($credentials['password']))
		{
			$response->status = JAUTHENTICATE_STATUS_FAILURE;
			$response->error_message = 'Empty password not allowed';
			return false;
		}

		// Initialize variables
		$conditions = '';

		// Get a database object
		$db = &JFactory::getDbo();

		$query = 'SELECT `id`, `password`'
			. ' FROM `#__users`'
			. ' WHERE username=' . $db->Quote($credentials['username'])
			;
		$db->setQuery($query);
		$result = $db->loadObject();


		if ($result)
		{
			$parts	= explode(':', $result->password);
			$crypt	= $parts[0];
			$salt	= @$parts[1];
			$testcrypt = JUserHelper::getCryptedPassword($credentials['password'], $salt);

			if ($crypt == $testcrypt) {
				$user = JUser::getInstance($result->id); // Bring this in line with the rest of the system
				$response->email = $user->email;
				$response->fullname = $user->name;
				$response->status = JAUTHENTICATE_STATUS_SUCCESS;
				$response->error_message = '';
			} else {
				$response->status = JAUTHENTICATE_STATUS_FAILURE;
				$response->error_message = 'Invalid password';
			}
		}
		else
		{
			$response->status = JAUTHENTICATE_STATUS_FAILURE;
			$response->error_message = 'User does not exist';
		}
	}
}
