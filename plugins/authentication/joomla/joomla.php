<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.joomla
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla Authentication plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Authentication.joomla
 * @since       1.5
 */
class PlgAuthenticationJoomla extends JPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array   $credentials  Array holding the user credentials
	 * @param   array   $options      Array of extra options
	 * @param   object  &$response    Authentication response object
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		$response->type = 'Joomla';

		// Joomla does not like blank passwords
		if (empty($credentials['password']))
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');

			return false;
		}

		// Get a database object
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true)
			->select('id, password')
			->from('#__users')
			->where('username=' . $db->quote($credentials['username']));

		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result)
		{
			// Check the password
			$parts	= explode(':', $result->password);
			$crypt	= $parts[0];
			$salt	= @$parts[1];
			$testcrypt = JUserHelper::getCryptedPassword($credentials['password'], $salt);

			if ($crypt == $testcrypt)
			{
				// Bring this in line with the rest of the system
				$user = JUser::getInstance($result->id);
				$response->email = $user->email;
				$response->fullname = $user->name;

				if (JFactory::getApplication()->isAdmin())
				{
					$response->language = $user->getParam('admin_language');
				}
				else
				{
					$response->language = $user->getParam('language');
				}

				$response->status = JAuthentication::STATUS_SUCCESS;
				$response->error_message = '';
			}
			else
			{
				// Invalid password
				$response->status = JAuthentication::STATUS_FAILURE;
				$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_PASS');
			}
		}
		else
		{
			// Invalid user
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
		}

		// Check the two factor authentication
		if ($response->status == JAuthentication::STATUS_SUCCESS)
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

			$methods = UsersHelper::getTwoFactorMethods();

			if (count($methods) <= 1)
			{
				// No two factor authentication method is enabled
				return;
			}

            require_once JPATH_ADMINISTRATOR . '/components/com_users/models/user.php';
            $model = new UsersModelUser;

			$check = $model->isValidSecretKey($result->id, $credentials['secretkey']);

			if (!$check)
			{
				$response->status = JAuthentication::STATUS_FAILURE;
				$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_SECRETKEY');
			}
		}
	}
}
