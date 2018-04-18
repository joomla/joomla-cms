<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.trust
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla Authentication plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgTwofactorauthTrust extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Set the trust this device cookie only after login is successfully finished.
	 *
	 * @param   array  $options  Array holding options
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserAfterLogin($options)
	{
		if (!empty($options['trust']) || $this->app->input->getBool('trust', false))
		{
			// Trust checkbox is set
			$cookieName = 'joomla_trust_this_device_' . JUserHelper::getShortHashedUserAgent();

			// Trust checkbox is set
			$old_series = null;

			// Get series from cookie
			$cookieValue = $this->app->input->cookie->get($cookieName);

			if ($cookieValue)
			{
				$cookieArray = explode('.', $cookieValue);

				// Check for valid cookie value
				if (count($cookieArray) !== 2)
				{
					JLog::add('Invalid cookie detected.', JLog::WARNING, 'error');
				}
				else
				{
					// Filter series since we're going to use it in the query
					$filter = new JFilterInput;
					$old_series = $filter->clean($cookieArray[1], 'ALNUM');
				}
				// Destroy the cookie in the browser anyway
				$this->app->input->cookie->set($cookieName, '', 1, $this->app->get('cookie_path', '/'), $this->app->get('cookie_domain', ''));
			}

			// Create a unique series which will be used over the lifespan of the cookie
			$unique     = false;
			$errorCount = 0;

			do
			{
				$series = JUserHelper::genRandomPassword(20);
				$query  = $this->db->getQuery(true)
					->select($this->db->quoteName('series'))
					->from($this->db->quoteName('#__user_keys'))
					->where($this->db->quoteName('series') . ' = ' . $this->db->quote($series));

				try
				{
					$results = $this->db->setQuery($query)->loadResult();

					if ($results === null)
					{
						$unique = true;
					}
				}
				catch (RuntimeException $e)
				{
					$errorCount++;

					// We'll let this query fail up to 5 times before giving up, there's probably a bigger issue at this point
					if ($errorCount === 5)
					{
						return false;
					}
				}
			}

			while ($unique === false);

			// Get the parameter values
			$lifetime = $this->params->get('cookie_lifetime', '30') * 24 * 60 * 60;
			$length   = $this->params->get('key_length', '16');

			// Generate new cookie
			$token       = JUserHelper::genRandomPassword($length);
			$cookieValue = $token . '.' . $series;

			// Overwrite existing cookie with new value
			$this->app->input->cookie->set(
				$cookieName,
				$cookieValue,
				time() + $lifetime,
				$this->app->get('cookie_path', '/'),
				$this->app->get('cookie_domain', ''),
				$this->app->isHttpsForced(),
				true
				);

			$hashed_token = JUserHelper::hashPassword($token);

			try
			{
				$query = $this->db->getQuery(true)
					->set($this->db->quoteName('series') . ' = ' . $this->db->quote($series))
					->set($this->db->quoteName('time') . ' = ' . (time() + $lifetime))
					->set($this->db->quoteName('token') . ' = ' . $this->db->quote($hashed_token));

				if ($old_series)
				{
					$query = $query
						->update($this->db->quoteName('#__user_keys'))
						->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($options['user']->username))
						->where($this->db->quoteName('uastring') . ' = ' . $this->db->quote($cookieName))
						->where($this->db->quoteName('series') . ' = ' . $this->db->quote($old_series));
				}
				else 
				{
					$query = $query
						->insert($this->db->quoteName('#__user_keys'))
						->set($this->db->quoteName('user_id') . ' = ' . $this->db->quote($options['user']->username))
						->set($this->db->quoteName('uastring') . ' = ' . $this->db->quote($cookieName));
				}

				$this->db->setQuery($query)->execute();
			}
			catch (RuntimeException $e)
			{
			}
		}
	}

	/**
	 * This method should handle any two factor authentication and report back
	 * to the subject.
	 *
	 * @param   array  $credentials  Array holding the user credentials
	 * @param   array  $options      Array of extra options
	 *
	 * @return  boolean  True if the user is authorised with this two-factor authentication method
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserTwofactorAuthenticate($credentials, $options)
	{
		// Remove expired tokens
		$query = $this->db->getQuery(true)
			->delete('#__user_keys')
			->where($this->db->quoteName('time') . ' < ' . $this->db->quote(time()));

		try
		{
			$this->db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			// We aren't concerned with errors from this query, carry on
		}

		// Get cookie
		$cookieName  = 'joomla_trust_this_device_' . JUserHelper::getShortHashedUserAgent();
		$cookieValue = $this->app->input->cookie->get($cookieName);

		if (!$cookieValue)
		{
			return false;
		}

		$cookieArray = explode('.', $cookieValue);

		// Check for valid cookie value
		if (count($cookieArray) !== 2)
		{
			// Destroy the cookie in the browser.
			$this->app->input->cookie->set($cookieName, '', 1, $this->app->get('cookie_path', '/'), $this->app->get('cookie_domain', ''));
			JLog::add('Invalid cookie detected.', JLog::WARNING, 'error');

			return false;
		}

		// Filter series since we're going to use it in the query
		$filter = new JFilterInput;
		$series = $filter->clean($cookieArray[1], 'ALNUM');

		// Find the matching record if it exists.
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('user_id', 'token', 'series', 'time')))
			->from($this->db->quoteName('#__user_keys'))
			->where($this->db->quoteName('series') . ' = ' . $this->db->quote($series))
			->where($this->db->quoteName('uastring') . ' = ' . $this->db->quote($cookieName))
			->order($this->db->quoteName('time') . ' DESC');

		try
		{
			$results = $this->db->setQuery($query)->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		if (count($results) !== 1)
		{
			// Destroy the cookie in the browser.
			$this->app->input->cookie->set($cookieName, '', 1, $this->app->get('cookie_path', '/'), $this->app->get('cookie_domain', ''));
			return false;
		}

		return true;
	}
}
