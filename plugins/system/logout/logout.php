<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.logout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Plugin class for logout redirect handling.
 *
 * @since  1.6
 */
class PlgSystemLogout extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe -- event dispatcher.
	 * @param   object  $config    An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$input = JFactory::getApplication()->input;
		$hash  = JApplicationHelper::getHash('PlgSystemLogout');

		if (JFactory::getApplication()->isSite() && $input->cookie->getString($hash))
		{
			// Destroy the cookie.
			$conf = JFactory::getConfig();
			$cookie_domain = $conf->get('cookie_domain', '');
			$cookie_path   = $conf->get('cookie_path', '/');
			setcookie($hash, false, time() - 86400, $cookie_path, $cookie_domain);

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
		if (JFactory::getApplication()->isSite())
		{
			// Create the cookie.
			$hash = JApplicationHelper::getHash('PlgSystemLogout');
			$conf = JFactory::getConfig();
			$cookie_domain = $conf->get('cookie_domain', '');
			$cookie_path   = $conf->get('cookie_path', '/');
			setcookie($hash, true, time() + 86400, $cookie_path, $cookie_domain);
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
		// Get the application object.
		$app = JFactory::getApplication();

		// Make sure the error is a 403 and we are in the frontend.
		if ($error->getCode() == 403 and $app->isSite())
		{
			// Redirect to the home page.
			$app->enqueueMessage(JText::_('PLG_SYSTEM_LOGOUT_REDIRECT'));
			$app->redirect('index.php');
		}
		else
		{
			// Render the custom error page.
			JError::customErrorPage($error);
		}
	}
}
