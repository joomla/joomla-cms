<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Session\Session;

/**
 * Class for managing HTTP sessions
 *
 * @since  1.5
 */
class JSession extends Session
{
	/**
	 * Checks for a form token in the request.
	 *
	 * Use in conjunction with JHtml::_('form.token') or JSession::getFormToken.
	 *
	 * @param   string  $method  The request method in which to look for the token key.
	 *
	 * @return  boolean  True if found and valid, false otherwise.
	 *
	 * @since   2.5.4
	 */
	public static function checkToken($method = 'post')
	{
		$app   = JFactory::getApplication();
		$token = static::getFormToken();

		if (!$app->input->$method->get($token, '', 'alnum'))
		{
			if ($app->getSession()->isNew())
			{
				// Redirect to login screen.
				$app->enqueueMessage(JText::_('JLIB_ENVIRONMENT_SESSION_EXPIRED'), 'warning');
				$app->redirect(JRoute::_('index.php'));

				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * Method to determine a hash for anti-spoofing variable names
	 *
	 * @param   boolean  $forceNew  If true, force a new token to be created
	 *
	 * @return  string  Hashed var name
	 *
	 * @since   1.6
	 */
	public static function getFormToken($forceNew = false)
	{
		$user = JFactory::getUser();

		return JApplicationHelper::getHash($user->get('id', 0) . JFactory::getApplication()->getSession()->getToken($forceNew));
	}

	/**
	 * Returns the global session object.
	 *
	 * @return  static  The Session object.
	 *
	 * @since   1.5
	 * @deprecated  5.0  Load the session service from the dependency injection container or via $app->getSession()
	 */
	public static function getInstance()
	{
		JLog::add(
			__METHOD__ . '() is deprecated. Load the session from the dependency injection container or via JFactory::getApplication()->getSession().',
			JLog::WARNING,
			'deprecated'
		);

		return JFactory::getApplication()->getSession();
	}

	/**
	 * Get data from the session store
	 *
	 * @param   string  $name     Name of a variable
	 * @param   mixed   $default  Default value of a variable if not set
	 *
	 * @return  mixed  Value of a variable
	 *
	 * @since   1.5
	 */
	public function get($name, $default = null)
	{
		// Handle B/C by checking if a namespace was passed to the method, will be removed at 5.0
		if (func_num_args() > 2)
		{
			$args = func_get_args();

			if (!empty($args[2]))
			{
				JLog::add(
					'Passing a namespace as a parameter to ' . __METHOD__ . '() is deprecated. The namespace should be prepended to the name instead.',
					JLog::WARNING,
					'deprecated'
				);

				$name = $args[2] . '.' . $name;
			}
		}

		return parent::get($name, $default);
	}

	/**
	 * Set data into the session store.
	 *
	 * @param   string  $name   Name of a variable.
	 * @param   mixed   $value  Value of a variable.
	 *
	 * @return  mixed  Old value of a variable.
	 *
	 * @since   1.5
	 */
	public function set($name, $value = null)
	{
		// Handle B/C by checking if a namespace was passed to the method, will be removed at 5.0
		if (func_num_args() > 2)
		{
			$args = func_get_args();

			if (!empty($args[2]))
			{
				JLog::add(
					'Passing a namespace as a parameter to ' . __METHOD__ . '() is deprecated. The namespace should be prepended to the name instead.',
					JLog::WARNING,
					'deprecated'
				);

				$name = $args[2] . '.' . $name;
			}
		}

		return parent::set($name, $value);
	}

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  boolean  True if the variable exists
	 *
	 * @since   1.5
	 */
	public function has($name)
	{
		// Handle B/C by checking if a namespace was passed to the method, will be removed at 5.0
		if (func_num_args() > 1)
		{
			$args = func_get_args();

			if (!empty($args[1]))
			{
				JLog::add(
					'Passing a namespace as a parameter to ' . __METHOD__ . '() is deprecated. The namespace should be prepended to the name instead.',
					JLog::WARNING,
					'deprecated'
				);

				$name = $args[1] . '.' . $name;
			}
		}

		return parent::has($name);
	}

	/**
	 * Clears all variables from the session store
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function clear()
	{
		// Handle B/C by checking if parameters were passed to this method; if so proxy to the new remove() method, will be removed at 5.0
		if (func_num_args() >= 1)
		{
			$args = func_get_args();

			if (!empty($args[0]))
			{
				JLog::add(
					'Using ' . __METHOD__ . '() to remove a single element from the session is deprecated.  Use ' . __CLASS__ . '::remove() instead.',
					JLog::WARNING,
					'deprecated'
				);

				$name = $args[0];

				// Also check for a namespace
				if (func_num_args() > 1 && !empty($args[1]))
				{
					JLog::add(
						'Passing a namespace as a parameter to ' . __METHOD__ . '() is deprecated. The namespace should be prepended to the name instead.',
						JLog::WARNING,
						'deprecated'
					);

					$name = $args[1] . '.' . $name;
				}

				return $this->remove($name);
			}
		}

		return parent::clear();
	}
}
