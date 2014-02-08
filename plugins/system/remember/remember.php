<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.remember
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! System Remember Me Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  System.remember
 * @since       1.5
 * @note        Code improvements inspired by http://jaspan.com/improved_persistent_login_cookie_best_practice
 *              and http://fishbowl.pastiche.org/2004/01/19/persistent_login_cookie_best_practice/
 */
class PlgSystemRemember extends JPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  3.2
	 */
	protected $db;

	/**
	 * Domain for the cookie.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $cookie_domain;

	/**
	 * Path for the cookie.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $cookie_path;

	/**
	 * Whether to set as secure or not.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $secure = false;

	/**
	 * Cookie lifetime in days.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $lifetime;

	/**
	 * Length of random string.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $length;

	/**
	 * Constructor.
	 * 
	 * Used to set the application and database properties.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   3.2
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		// Use domain and path set in config for cookie if it exists.
		$this->cookie_domain = $this->app->get('cookie_domain', '');
		$this->cookie_path = $this->app->get('cookie_path', '/');
		$this->lifetime = time() + ($this->params->get('cookie_lifetime', '60') * 24 * 60 * 60);
		$this->secure = $this->app->isSSLConnection();
		$this->length = $this->params->get('key_length', '16');
	}

	/**
	 * Remember me method to run onAfterInitialise.
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 * @throws  InvalidArgumentException
	 */
	public function onAfterInitialise()
	{
		// No remember me for admin.
		if ($this->app->isAdmin())
		{
			return false;
		}

		$user = JFactory::getUser();

		$this->app->rememberCookieLifetime = $this->lifetime;
		$this->app->rememberCookieSecure   = $this->secure;
		$this->app->rememberCookieLength   = $this->length;

		// Check for a cookie.
		if ($user->get('guest') == 1)
		{
			// Create the cookie name and data.
			$rememberArray = JUserHelper::getRememberCookieData();

			if ($rememberArray !== false)
			{
				if (count($rememberArray) != 3)
				{
					// Destroy the cookie in the browser.
					$this->app->input->cookie->set(end($rememberArray), false, time() - 42000, $this->app->get('cookie_path'), $this->app->get('cookie_domain'));
					JLog::add('Invalid cookie detected.', JLog::WARNING, 'error');

					return false;
				}

				list($privateKey, $series, $uastring) = $rememberArray;

				if (!JUserHelper::clearExpiredTokens($this))
				{
					JLog::add('Error in deleting expired cookie tokens.', JLog::WARNING, 'error');
				}

				// Find the matching record if it exists.
				$query = $this->db->getQuery(true)
					->select($this->db->quoteName(array('user_id', 'token', 'series', 'time', 'invalid')))
					->from($this->db->quoteName('#__user_keys'))
					->where($this->db->quoteName('series') . ' = ' . $this->db->quote(base64_encode($series)))
					->where($this->db->quoteName('uastring') . ' = ' . $this->db->quote($uastring))
					->order($this->db->quoteName('time') . ' DESC');

				$results = $this->db->setQuery($query)->loadObjectList();

				$countResults = count($results);

				// We have a user but a cookie that is not in the database, or it is invalid. This is a possible attack, so invalidate everything.
				if (($countResults === 0 || $results[0]->invalid != 0) && !empty($results[0]->user_id))
				{
					JUserHelper::invalidateCookie($results[0]->user_id, $uastring);
					JLog::add(JText::sprintf('PLG_SYSTEM_REMEMBER_ERROR_LOG_INVALIDATED_COOKIES', $user->username), JLog::WARNING, 'security');

					// Possibly e-mail user and admin here.
					return false;
				}

				// We have a user with one cookie with a valid series and a corresponding record in the database.
				if ($countResults === 1)
				{
					if (!JCrypt::timingSafeCompare($results[0]->token, $privateKey))
					{
						JUserHelper::invalidateCookie($results[0]->user_id, $uastring);
						JLog::add(JText::sprintf('PLG_SYSTEM_REMEMBER_ERROR_LOG_LOGIN_FAILED', $user->username), JLog::WARNING, 'security');

						return false;
					}

					// Set up the credentials array to pass to onUserAuthenticate.
					$credentials = array(
						'username' => $results[0]->user_id,
					);

					return $this->app->login($credentials, array('silent' => true, 'lifetime' => $this->lifetime, 'secure' => $this->secure, 'length' => $this->length));
				}
			}
		}

		return false;
	}
}
