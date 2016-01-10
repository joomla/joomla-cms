<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.remember
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! System Remember Me Plugin
 *
 * @since  1.5
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
	 * Remember me method to run onAfterInitialise
	 * Only purpose is to initialise the login authentication process if a cookie is present
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @throws  InvalidArgumentException
	 */
	public function onAfterInitialise()
	{
		// Get the application if not done by JPlugin. This may happen during upgrades from Joomla 2.5.
		if (!$this->app)
		{
			$this->app = JFactory::getApplication();
		}

		// No remember me for admin.
		if ($this->app->isAdmin())
		{
			return;
		}

		// Check for a cookie if user is not logged in
		if (JFactory::getUser()->get('guest'))
		{
			$cookieName = JUserHelper::getShortHashedUserAgent();

			// Check for the cookie
			if ($this->app->input->cookie->get($cookieName))
			{
				$this->app->login(array('username' => ''), array('silent' => true));
			}
		}
	}

	/**
	 * Imports the authentication plugin on user logout to make sure that the cookie is destroyed.
	 *
	 * @param   array  $user     Holds the user data.
	 * @param   array  $options  Array holding options (remember, autoregister, group).
	 *
	 * @return  boolean
	 */
	public function onUserLogout($user, $options)
	{
		// No remember me for admin
		if ($this->app->isAdmin())
		{
			return true;
		}

		$cookieName = JUserHelper::getShortHashedUserAgent();

		// Check for the cookie
		if ($this->app->input->cookie->get($cookieName))
		{
			// Make sure authentication group is loaded to process onUserAfterLogout event
			JPluginHelper::importPlugin('authentication');
		}

		return true;
	}
}
