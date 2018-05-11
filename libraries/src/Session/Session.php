<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Session;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\Session\Session as BaseSession;

/**
 * Class for managing HTTP sessions
 *
 * @since  1.5
 */
class Session extends BaseSession
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
		$app   = \JFactory::getApplication();
		$token = static::getFormToken();

		if (!$app->input->$method->get($token, '', 'alnum'))
		{
			if ($app->getSession()->isNew())
			{
				// Redirect to login screen.
				$app->enqueueMessage(\JText::_('JLIB_ENVIRONMENT_SESSION_EXPIRED'), 'warning');
				$app->redirect(\JRoute::_('index.php'));

				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * Method to determine a hash for anti-spoofing variable names
	 *
<<<<<<< HEAD
	 * @param   boolean  $forceNew  If true, force a new token to be created
=======
	 * @since   11.1
	 */
	public static function getStores()
	{
		$connectors = array();

		// Get an iterator and loop trough the driver classes.
		$iterator = new \DirectoryIterator(JPATH_LIBRARIES . '/joomla/session/storage');

		/** @type  $file  \DirectoryIterator */
		foreach ($iterator as $file)
		{
			$fileName = $file->getFilename();

			// Only load for php files.
			if (!$file->isFile() || $file->getExtension() != 'php')
			{
				continue;
			}

			// Derive the class name from the type.
			$class = str_ireplace('.php', '', 'JSessionStorage' . ucfirst(trim($fileName)));

			// If the class doesn't exist we have nothing left to do but look at the next type. We did our best.
			if (!class_exists($class))
			{
				continue;
			}

			// Sweet!  Our class exists, so now we just need to know if it passes its test method.
			if ($class::isSupported())
			{
				// Connector names should not have file extensions.
				$connectors[] = str_ireplace('.php', '', $fileName);
			}
		}

		return $connectors;
	}

	/**
	 * Shorthand to check if the session is active
>>>>>>> staging
	 *
	 * @return  string  Hashed var name
	 *
	 * @since   1.6
	 */
	public static function getFormToken($forceNew = false)
	{
		$user = \JFactory::getUser();

		return ApplicationHelper::getHash($user->get('id', 0) . \JFactory::getApplication()->getSession()->getToken($forceNew));
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
		\JLog::add(
			__METHOD__ . '() is deprecated. Load the session from the dependency injection container or via JFactory::getApplication()->getSession().',
			\JLog::WARNING,
			'deprecated'
		);

		return \JFactory::getApplication()->getSession();
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
				\JLog::add(
					'Passing a namespace as a parameter to ' . __METHOD__ . '() is deprecated. The namespace should be prepended to the name instead.',
					\JLog::WARNING,
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
				\JLog::add(
					'Passing a namespace as a parameter to ' . __METHOD__ . '() is deprecated. The namespace should be prepended to the name instead.',
					\JLog::WARNING,
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
				\JLog::add(
					'Passing a namespace as a parameter to ' . __METHOD__ . '() is deprecated. The namespace should be prepended to the name instead.',
					\JLog::WARNING,
					'deprecated'
				);

				$name = $args[1] . '.' . $name;
			}
		}

		return parent::has($name);
	}

	/**
<<<<<<< HEAD
	 * Clears all variables from the session store
	 *
	 * @return  void
	 *
	 * @since   1.5
=======
	 * Create a new session and copy variables from the old one
	 *
	 * @return  boolean $result true on success
	 *
	 * @since   11.1
	 */
	public function fork()
	{
		if ($this->getState() !== 'active')
		{
			// @TODO :: generated error here
			return false;
		}

		// Restart session with new id
		$this->_handler->regenerate(true, null);

		return true;
	}

	/**
	 * Writes session data and ends session
	 *
	 * Session data is usually stored after your script terminated without the need
	 * to call Session::close(), but as session data is locked to prevent concurrent
	 * writes only one script may operate on a session at any time. When using
	 * framesets together with sessions you will experience the frames loading one
	 * by one due to this locking. You can reduce the time needed to load all the
	 * frames by ending the session as soon as all changes to session variables are
	 * done.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function close()
	{
		$this->_handler->save();
		$this->_state = 'inactive';
	}

	/**
	 * Delete expired session data
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   3.8.6
	 */
	public function gc()
	{
		return $this->_store->gc($this->getExpire());
	}

	/**
	 * Set the session handler
	 *
	 * @param   \JSessionHandlerInterface  $handler  The session handler
	 *
	 * @return  void
	 */
	public function setHandler(\JSessionHandlerInterface $handler)
	{
		$this->_handler = $handler;
	}

	/**
	 * Create a token-string
	 *
	 * @param   integer  $length  Length of string
	 *
	 * @return  string  Generated token
	 *
	 * @since   11.1
	 */
	protected function _createToken($length = 32)
	{
		return UserHelper::genRandomPassword($length);
	}

	/**
	 * Set counter of session usage
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	protected function _setCounter()
	{
		$counter = $this->get('session.counter', 0);
		++$counter;

		$this->set('session.counter', $counter);

		return true;
	}

	/**
	 * Set the session timers
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	protected function _setTimers()
	{
		if (!$this->has('session.timer.start'))
		{
			$start = time();

			$this->set('session.timer.start', $start);
			$this->set('session.timer.last', $start);
			$this->set('session.timer.now', $start);
		}

		$this->set('session.timer.last', $this->get('session.timer.now'));
		$this->set('session.timer.now', time());

		return true;
	}

	/**
	 * Set additional session options
	 *
	 * @param   array  $options  List of parameter
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
>>>>>>> staging
	 */
	public function clear()
	{
		// Handle B/C by checking if parameters were passed to this method; if so proxy to the new remove() method, will be removed at 5.0
		if (func_num_args() >= 1)
		{
			$args = func_get_args();

			if (!empty($args[0]))
			{
				\JLog::add(
					'Using ' . __METHOD__ . '() to remove a single element from the session is deprecated.  Use ' . __CLASS__ . '::remove() instead.',
					\JLog::WARNING,
					'deprecated'
				);

				$name = $args[0];

				// Also check for a namespace
				if (func_num_args() > 1 && !empty($args[1]))
				{
					\JLog::add(
						'Passing a namespace as a parameter to ' . __METHOD__ . '() is deprecated. The namespace should be prepended to the name instead.',
						\JLog::WARNING,
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
