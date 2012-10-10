<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Joomla Authentication plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Authentication.joomla
 * @since 1.5
 */
class plgAuthenticationJoomla extends JPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param	array	Array holding the user credentials
	 * @param	array	Array of extra options
	 * @param	object	Authentication response object
	 * @return	boolean
	 * @since 1.5
	 */
	function onUserAuthenticate($credentials, $options, &$response)
	{
		$response->type = 'Joomla';
		// Joomla does not like blank passwords
		if (empty($credentials['password'])) {
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');
			return false;
		}

		// Initialise variables.
		$conditions = '';

		// Get a database object
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('id, password');
		$query->from('#__users');
		$query->where('username=' . $db->Quote($credentials['username']));

		$db->setQuery($query);
		$result = $db->loadObject();
		
		// Get the hashing algorithm stored in the user settings
		$algo = JComponentHelper::getParams('com_users')->get('hashing_algorithm');
		// If preffered algorithm is bcrypt do a check to see if blowfish is installed
		if ($algo == 'crypt-blowfish' && CRYPT_BLOWFISH != 1) { // if blowfish is not installed or enabled default back to MD5
   			 $algo = 'md5-hex';
   			 JComponentHelper::getParams('com_users')->set('hashing_algorithm', 'md5-hex');
		};
		// Slight bug, after installation $algo doesnt seem to exist untill the user makes a change in the user manager.
		// So we need to force it to be md5 until the user changes it themselves.
		if (!$algo) {
			$algo = 'md5-hex';
   			 JComponentHelper::getParams('com_users')->set('hashing_algorithm', 'md5-hex');
		}
		
		
		if ($result) {
			if (strlen($result->password) == 32) {
				$crypt = $result->password;
				$encryption = 'md5-hex';
				$salt = 'nosalt';
				}
			else 
				{
			    $parts	= explode(':', $result->password);
			    // New passwords will have 3 parts, old passwords will only have 2.
			    if (count($parts) == 2) {
			    	$crypt	= $parts[0];
			    	$salt = $parts[1];
			    	$encryption = 'md5-hex'; // The old scheme had MD5 by default so we know its using this.
			    	
			    }
			    else {
					$encryption	= $parts[0];
					$crypt	= @$parts[1];
					$salt = $parts[2];
					
			    }
			}
				
			$testcrypt = JUserHelper::getCryptedPassword($credentials['password'], $salt, $encryption);

			if ($crypt == $testcrypt) {

				$user = JUser::getInstance($result->id);
				
				$response->email = $user->email;
				$response->fullname = $user->name;
				if (JFactory::getApplication()->isAdmin()) {
					$response->language = $user->getParam('admin_language');
				}
				else {
					$response->language = $user->getParam('language');
				}
				$response->status = JAuthentication::STATUS_SUCCESS;
				$response->error_message = '';
			} else {
				$response->status = JAuthentication::STATUS_FAILURE;
				$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_PASS');
			}
		} else {
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
		}
	}
}
