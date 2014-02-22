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

		// Check for a cookie if user is not logged in
		if (JFactory::getUser()->get('guest'))
		{
			$cookieName = JUserHelper::getShortHashedUserAgent();

			// Check for the cookie
			if ($this->app->input->cookie->get($cookieName))
			{
				return $this->app->login(array('username' => ''), array('silent' => true));
			}
		}

		return false;
	}

	public function onUserLogout($options)
	{
		// No remember me for admin
		if ($this->app->isAdmin())
		{
			return false;
		}

		$cookieName = JUserHelper::getShortHashedUserAgent();

		// Check for the cookie
		if ($this->app->input->cookie->get($cookieName))
		{
			// Make sure authentication group is loaded to process onUserAfterLogout event
			JPluginHelper::importPlugin('authentication');
		}
	}
}
