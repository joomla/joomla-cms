<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.logout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Plugin class for logout redirect handling.
 *
 * @since  1.6
 */
class PlgSystemLogout extends JPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.4
	 */
	protected $app;
	
	/**
	 * Register the custom error handler if the cookie is set
	 * The cookie is set on logout of the user
	 * 
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onAfterInitialise()
	{
		$hash  = JApplicationHelper::getHash('PlgSystemLogout');

		if ($this->app->isSite() && $this->app->input->cookie->getString($hash))
		{
			// Destroy the cookie.
			$cookie_domain 	= $this->app->get('cookie_domain');
			$cookie_path 	= $this->app->get('cookie_path', $uri->base(true));
			$this->app->input->cookie->set($hash, false, time() - 86400, $cookie_path, $cookie_domain);

			// Set the error handler for E_ALL to be the class handleError method.
			JError::setErrorHandling(E_ALL, 'callback', array('PlgSystemLogout', 'handleError'));
		}
	}

	/**
	 * Method to handle any logout logic and report back to the subject.
	 *
	 * @param   array  $user     Holds the user data.
	 * @param   array  $options  Array holding options (client, ...).
	 *
	 * @return  boolean  Always returns true.
	 *
	 * @since   1.6
	 */
	public function onUserLogout($user, $options = array())
	{
		if ($this->app->isSite())
		{
			// Create the cookie.
			$hash 			= JApplicationHelper::getHash('PlgSystemLogout');
			$cookie_domain 	= $this->app->get('cookie_domain');
			$cookie_path 	= $this->app->get('cookie_path', $uri->base(true));
			$this->app->input->cookie->set($hash, true, time() + 86400, $cookie_path, $cookie_domain);
		}

		return true;
	}

	/**
	 * Method to handle an error condition.
	 *
	 * @param   Exception  &$error  The Exception object to be handled.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function handleError(&$error)
	{
		// Make sure the error is a 403 and we are in the frontend.
		if ($error->getCode() == 403 and $this->app->isSite())
		{
			// Redirect to the home page.
			$this->loadLanguage();
			$this->app->enqueueMessage(JText::_('PLG_SYSTEM_LOGOUT_REDIRECT'));
			$this->app->redirect('index.php', true);
		}
		else
		{
			// Render the custom error page.
			JError::customErrorPage($error);
		}
	}
}
