<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.ldap
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * LDAP Authentication Plugin
 *
 * @since  1.5
 */
class PlgAuthenticationLdap extends JPlugin
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
		$userdetails = null;
		$success = 0;
		$userdetails = array();

		// For JLog
		$response->type = 'LDAP';

		// Strip null bytes from the password
		$credentials['password'] = str_replace(chr(0), '', $credentials['password']);

		// LDAP does not like Blank passwords (tries to Anon Bind which is bad)
		if (empty($credentials['password']))
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_PASS_BLANK');

			return false;
		}

		// Load plugin params info
		$ldap_email		= $this->params->get('ldap_email');
		$ldap_fullname	= $this->params->get('ldap_fullname');
		$ldap_uid		= $this->params->get('ldap_uid');
		$auth_method	= $this->params->get('auth_method');

		$ldap = new JClientLdap($this->params);

		if (!$ldap->connect())
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_CONNECT');

			return;
		}

		switch ($auth_method)
		{
			case 'search':
			{
				// Bind using Connect Username/password
				// Force anon bind to mitigate misconfiguration like [#7119]
				if (strlen($this->params->get('username')))
				{
					$bindtest = $ldap->bind();
				}
				else
				{
					$bindtest = $ldap->anonymous_bind();
				}

				if ($bindtest)
				{
					// Search for users DN
					$binddata = $ldap->simple_search(str_replace("[search]", $credentials['username'], $this->params->get('search_string')));

					if (isset($binddata[0]) && isset($binddata[0]['dn']))
					{
						// Verify Users Credentials
						$success = $ldap->bind($binddata[0]['dn'], $credentials['password'], 1);

						// Get users details
						$userdetails = $binddata;
					}
					else
					{
						$response->status = JAuthentication::STATUS_FAILURE;
						$response->error_message = JText::_('JGLOBAL_AUTH_USER_NOT_FOUND');
					}
				}
				else
				{
					$response->status = JAuthentication::STATUS_FAILURE;
					$response->error_message = JText::_('JGLOBAL_AUTH_NO_BIND');
				}
			}	break;

			case 'bind':
			{
				// We just accept the result here
				$success = $ldap->bind($credentials['username'], $credentials['password']);

				if ($success)
				{
					$userdetails = $ldap->simple_search(str_replace("[search]", $credentials['username'], $this->params->get('search_string')));
				}
				else
				{
					$response->status = JAuthentication::STATUS_FAILURE;
					$response->error_message = JText::_('JGLOBAL_AUTH_BIND_FAILED');
				}
			}	break;
		}

		if (!$success)
		{
			$response->status = JAuthentication::STATUS_FAILURE;

			if (!strlen($response->error_message))
			{
				$response->error_message = JText::_('JGLOBAL_AUTH_INCORRECT');
			}
		}
		else
		{
			// Grab some details from LDAP and return them
			if (isset($userdetails[0][$ldap_uid][0]))
			{
				$response->username = $userdetails[0][$ldap_uid][0];
			}

			if (isset($userdetails[0][$ldap_email][0]))
			{
				$response->email = $userdetails[0][$ldap_email][0];
			}

			if (isset($userdetails[0][$ldap_fullname][0]))
			{
				$response->fullname = $userdetails[0][$ldap_fullname][0];
			}
			else
			{
				$response->fullname = $credentials['username'];
			}

			// Were good - So say so.
			$response->status		= JAuthentication::STATUS_SUCCESS;
			$response->error_message = '';
		}

		$ldap->close();

		// Check the two factor authentication
		if ($response->status == JAuthentication::STATUS_SUCCESS)
		{
			// Get a database object
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('id, password')
				->from('#__users')
				->where('username=' . $db->quote($credentials['username']));

			$db->setQuery($query);
			$result = $db->loadObject();

			require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

			$methods = UsersHelper::getTwoFactorMethods();

			if (count($methods) <= 1)
			{
				// No two factor authentication method is enabled
				return;
			}

			require_once JPATH_ADMINISTRATOR . '/components/com_users/models/user.php';

			$model = new UsersModelUser;

			// Load the user's OTP (one time password, a.k.a. two factor auth) configuration
			if (!array_key_exists('otp_config', $options))
			{
				$otpConfig             = $model->getOtpConfig($result->id);
				$options['otp_config'] = $otpConfig;
			}
			else
			{
				$otpConfig = $options['otp_config'];
			}

			// Check if the user has enabled two factor authentication
			if (empty($otpConfig->method) || ($otpConfig->method == 'none'))
			{
				// Warn the user if he's using a secret code but he has not
				// enabed two factor auth in his account.
				if (!empty($credentials['secretkey']))
				{
					try
					{
						$app = JFactory::getApplication();

						$this->loadLanguage();

						$app->enqueueMessage(JText::_('PLG_AUTH_JOOMLA_ERR_SECRET_CODE_WITHOUT_TFA'), 'warning');
					}
					catch (Exception $exc)
					{
						// This happens when we are in CLI mode. In this case
						// no warning is issued
						return;
					}
				}

				return;
			}

			// Load the Joomla! RAD layer
			if (!defined('FOF_INCLUDED'))
			{
				include_once JPATH_LIBRARIES . '/fof/include.php';
			}

			// Try to validate the OTP
			FOFPlatform::getInstance()->importPlugin('twofactorauth');

			$otpAuthReplies = FOFPlatform::getInstance()->runPlugins('onUserTwofactorAuthenticate', array($credentials, $options));

			$check = false;

			/*
			 * This looks like noob code but DO NOT TOUCH IT and do not convert
			 * to in_array(). During testing in_array() inexplicably returned
			 * null when the OTEP begins with a zero! o_O
			 */
			if (!empty($otpAuthReplies))
			{
				foreach ($otpAuthReplies as $authReply)
				{
					$check = $check || $authReply;
				}
			}

			// Fall back to one time emergency passwords
			if (!$check)
			{
				// Did the user use an OTEP instead?
				if (empty($otpConfig->otep))
				{
					if (empty($otpConfig->method) || ($otpConfig->method == 'none'))
					{
						// Two factor authentication is not enabled on this account.
						// Any string is assumed to be a valid OTEP.

						return true;
					}
					else
					{
						/*
						 * Two factor authentication enabled and no OTEPs defined. The
						 * user has used them all up. Therefore anything he enters is
						 * an invalid OTEP.
						 */
						return false;
					}
				}

				// Clean up the OTEP (remove dashes, spaces and other funny stuff
				// our beloved users may have unwittingly stuffed in it)
				$otep  = $credentials['secretkey'];
				$otep  = filter_var($otep, FILTER_SANITIZE_NUMBER_INT);
				$otep  = str_replace('-', '', $otep);
				$check = false;

				// Did we find a valid OTEP?
				if (in_array($otep, $otpConfig->otep))
				{
					// Remove the OTEP from the array
					$otpConfig->otep = array_diff($otpConfig->otep, array($otep));

					$model->setOtpConfig($result->id, $otpConfig);

					// Return true; the OTEP was a valid one
					$check = true;
				}
			}

			if (!$check)
			{
				$response->status        = JAuthentication::STATUS_FAILURE;
				$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_SECRETKEY');
			}
		}
	}
}
