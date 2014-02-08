<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.cookie
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla Authentication plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Authentication.cookie
 * @since       3.2
 */
class PlgAuthenticationCookie extends JPlugin
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
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array   $credentials  Array holding the user credentials
	 * @param   array   $options      Array of extra options
	 * @param   object  &$response    Authentication response object
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		// No remember me for admin
		if ($this->app->isAdmin())
		{
			return false;
		}

		JLoader::register('JAuthentication', JPATH_LIBRARIES . '/joomla/user/authentication.php');

		$response->type = 'Cookie';

		// We need to validate the cookie data because there may be no Remember Me plugin to do it.
		// Create the cookie name and data.
		$rememberArray = JUserHelper::getRememberCookieData();

		if ($rememberArray == false)
		{
			return false;
		}

		list($privateKey, $series, $uastring) = $rememberArray;

		// Find the matching record if it exists.
		$query = $this->db->getQuery(true)
		->select($this->db->quoteName(array('user_id', 'token', 'series', 'time', 'invalid')))
		->from($this->db->quoteName('#__user_keys'))
		->where($this->db->quoteName('series') . ' = ' . $this->db->quote(base64_encode($series)))
		->where($this->db->quoteName('uastring') . ' = ' . $this->db->quote($uastring))
		->order($this->db->quoteName('time') . ' DESC');

		$results = $this->db->setQuery($query)->loadObjectList();

		$countResults = count($results);

		if ($countResults !== 1)
		{
			$response->status = JAuthentication::STATUS_FAILURE;

			return;
		}

		// We have a user with one cookie with a valid series and a corresponding record in the database.
		else
		{
			if (substr($results[0]->token, 0, 4) === '$2y$')
			{
				if (JCrypt::hasStrongPasswordSupport())
				{
					$match = password_verify($privateKey, $results[0]->token);
				}
			}
			else
			{
				if (JCrypt::timingSafeCompare($results[0]->token, $privateKey))
				{
					$match = true;
				}
			}

			if (empty($match))
			{
				JUserHelper::invalidateCookie($results[0]->user_id, $uastring);
				JLog::add(JText::sprintf('PLG_SYSTEM_REMEMBER_ERROR_LOG_LOGIN_FAILED', $user->username), JLog::WARNING, 'security');
				$response->status  = JAuthentication::STATUS_FAILURE;

				return false;
			}
		}

		// Set cookie params.
		if (!empty($options['lifetime']) && !empty($options['length']) && !empty($options['secure']))
		{
			$response->lifetime = $options['lifetime'];
			$response->length = $options['length'];
			$response->secure = $options['secure'];
		}

		// Make sure there really is a user with this name and get the data for the session.
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'username', 'password')))
			->from($this->db->quoteName('#__users'))
			->where($this->db->quoteName('username') . ' = ' . $this->db->quote($credentials['username']));

		$result = $this->db->setQuery($query)->loadObject();

		if ($result)
		{
			// Bring this in line with the rest of the system
			$user = JUser::getInstance($result->id);
			$cookieName = JUserHelper::getShortHashedUserAgent();

			// If there is no cookie, bail out
			if (!$this->app->input->cookie->get($cookieName))
			{
				return;
			}

			// Set response data.
			$response->username = $result->username;
			$response->email    = $user->email;
			$response->fullname = $user->name;
			$response->password = $result->password;
			$response->language = $user->getParam('language');

			// Set response status.
			$response->status        = JAuthentication::STATUS_SUCCESS;
			$response->error_message = '';
		}
		else
		{
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
		}
	}
}
