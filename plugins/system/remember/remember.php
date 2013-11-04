<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! System Remember Me Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	System.remember
 */
class plgSystemRemember extends JPlugin
{
	function onAfterInitialise()
	{
		$app = JFactory::getApplication();

		// No remember me for admin
		if ($app->isAdmin()) {
			return;
		}

		$user = JFactory::getUser();
		if ($user->get('guest'))
		{
			$hash = JApplication::getHash('JLOGIN_REMEMBER');

			if ($str = JRequest::getString($hash, '', 'cookie', JREQUEST_ALLOWRAW | JREQUEST_NOTRIM))
			{
				jimport('joomla.utilities.simplecrypt');
				$credentials = array();
				$goodCookie = true;
				$filter = JFilterInput::getInstance();

				// Create the encryption key, apply extra hardening using the user agent string.
                // Since we're decoding, no UA validity check is required.
				$privateKey = JApplication::getHash(@$_SERVER['HTTP_USER_AGENT']);

				$key = new JCryptKey('simple', $privateKey, $privateKey);
				$crypt = new JCrypt(new JCryptCipherSimple, $key);
			
				try
				{
					$str = $crypt->decrypt($str);
					if (!is_string($str))
					{
						throw new Exception('Decoded cookie is not a string.');
					}

					$cookieData = json_decode($str);
					if (null === $cookieData)
					{
						throw new Exception('JSON could not be docoded.');
					}
					if (!is_object($cookieData))
					{
						throw new Exception('Decoded JSON is not an object.');
					}

					// json_decoded cookie could be any object structure, so make sure the
					// credentials are well structured and only have user and password.
					if (isset($cookieData->username) && is_string($cookieData->username))
					{
						$credentials['username'] = $filter->clean($cookieData->username, 'username');
					}
					else
					{
						throw new Exception('Malformed username.');
					}
					if (isset($cookieData->password) && is_string($cookieData->password))
					{
						$credentials['password'] = $filter->clean($cookieData->password, 'string');
					}
					else
					{
						throw new Exception('Malformed password.');
					}

					$return = $app->login($credentials, array('silent' => true));
					if (!$return)
					{
						throw new Exception('Log-in failed.');
					}

				}
				catch (Exception $e)
				{
					$config = JFactory::getConfig();
					$cookie_domain = $config->get('cookie_domain', '');
					$cookie_path = $config->get('cookie_path', '/');
					// Clear the remember me cookie
					setcookie(
						JApplication::getHash('JLOGIN_REMEMBER'), false, time() - 86400,
						$cookie_path, $cookie_domain
					);
					JLog::add('A remember me cookie was unset for the following reason: ' . $e->getMessage(), JLog::WARNING, 'security');
				}
			}
		}
	}
}
