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

		// Why store in App? Better move this params to the cookie auth plugin anyway
		$this->app->rememberCookieSecure   = $this->app->isSSLConnection();
		$this->app->rememberCookieLifetime = $this->params->get('cookie_lifetime', '60') * 24 * 60 * 60;
		$this->app->rememberCookieLength   = $this->params->get('key_length', '16');

		// Check for a cookie if user is not logged in
		if (JFactory::getUser()->get('guest'))
		{
			// Check for a cookie
			if (JUserHelper::getRememberCookieData())
			{
				$options				= array();
				$options['silent']		= true;
				$options['secure']		= $this->app->isSSLConnection();
				$options['lifetime']	= $this->params->get('cookie_lifetime', '60') * 24 * 60 * 60;
				$options['length']		= $this->params->get('key_length', '16');
				return $this->app->login(array('username' => ''), $options);
			}
		}

		return false;
	}
}
