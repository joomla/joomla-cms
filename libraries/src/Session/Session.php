<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Session;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Input\Input;
use Joomla\CMS\User\UserHelper;

/**
 * Class for managing HTTP sessions
 *
 * Provides access to session-state values as well as session-level
 * settings and lifetime management methods.
 * Based on the standard PHP session handling mechanism it provides
 * more advanced features such as expire timeouts.
 *
 * @since  1.7.0
 */
class Session implements \IteratorAggregate
{
	/**
	 * Internal state.
	 * One of 'inactive'|'active'|'expired'|'destroyed'|'error'
	 *
	 * @var    string
	 * @see    Session::getState()
	 * @since  1.7.0
	 */
	protected $_state = 'inactive';

	/**
	 * Maximum age of unused session in seconds
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $_expire = 900;

	/**
	 * The session store object.
	 *
	 * @var    \JSessionStorage
	 * @since  1.7.0
	 */
	protected $_store = null;

	/**
	 * Security policy.
	 * List of checks that will be done.
	 *
	 * Default values:
	 * - fix_browser
	 * - fix_adress
	 *
	 * @var    array
	 * @since  1.7.0
	 */
	protected $_security = array('fix_browser');

	/**
	 * Session instances container.
	 *
	 * @var    Session
	 * @since  1.7.3
	 */
	protected static $instance;

	/**
	 * The type of storage for the session.
	 *
	 * @var    string
	 * @since  3.0.1
	 */
	protected $storeName;

	/**
	 * Holds the \JInput object
	 *
	 * @var    \JInput
	 * @since  3.0.1
	 */
	private $_input = null;

	/**
	 * Holds the event dispatcher object
	 *
	 * @var    \JEventDispatcher
	 * @since  3.0.1
	 */
	private $_dispatcher = null;

	/**
	 * Holds the event dispatcher object
	 *
	 * @var    \JSessionHandlerInterface
	 * @since  3.5
	 */
	protected $_handler = null;

	/**
	 * Internal data store for the session data
	 *
	 * @var  \Joomla\Registry\Registry
	 */
	protected $data;

	/**
	 * Constructor
	 *
	 * @param   string                     $store             The type of storage for the session.
	 * @param   array                      $options           Optional parameters
	 * @param   \JSessionHandlerInterface  $handlerInterface  The session handler
	 *
	 * @since   1.7.0
	 */
	public function __construct($store = 'none', array $options = array(), \JSessionHandlerInterface $handlerInterface = null)
	{
		// Set the session handler
		$this->_handler = $handlerInterface instanceof \JSessionHandlerInterface ? $handlerInterface : new \JSessionHandlerJoomla($options);

		// Initialize the data variable, let's avoid fatal error if the session is not corretly started (ie in CLI).
		$this->data = new \Joomla\Registry\Registry;

		// Clear any existing sessions
		if ($this->_handler->getId())
		{
			$this->_handler->clear();
		}

		// Create handler
		$this->_store = \JSessionStorage::getInstance($store, $options);

		$this->storeName = $store;

		$this->_setOptions($options);

		$this->_state = 'inactive';
	}

	/**
	 * Magic method to get read-only access to properties.
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  mixed   The value of the property
	 *
	 * @since   3.0.1
	 */
	public function __get($name)
	{
		if ($name === 'storeName')
		{
			return $this->$name;
		}

		if ($name === 'state' || $name === 'expire')
		{
			$property = '_' . $name;

			return $this->$property;
		}
	}

	/**
	 * Returns the global Session object, only creating it if it doesn't already exist.
	 *
	 * @param   string                     $store             The type of storage for the session.
	 * @param   array                      $options           An array of configuration options.
	 * @param   \JSessionHandlerInterface  $handlerInterface  The session handler
	 *
	 * @return  Session  The Session object.
	 *
	 * @since   1.7.0
	 */
	public static function getInstance($store, $options, \JSessionHandlerInterface $handlerInterface = null)
	{
		if (!is_object(self::$instance))
		{
			self::$instance = new Session($store, $options, $handlerInterface);
		}

		return self::$instance;
	}

	/**
	 * Get current state of session
	 *
	 * @return  string  The session state
	 *
	 * @since   1.7.0
	 */
	public function getState()
	{
		return $this->_state;
	}

	/**
	 * Get expiration time in seconds
	 *
	 * @return  integer  The session expiration time in seconds
	 *
	 * @since   1.7.0
	 */
	public function getExpire()
	{
		return $this->_expire;
	}

	/**
	 * Get a session token, if a token isn't set yet one will be generated.
	 *
	 * Tokens are used to secure forms from spamming attacks. Once a token
	 * has been generated the system will check the post request to see if
	 * it is present, if not it will invalidate the session.
	 *
	 * @param   boolean  $forceNew  If true, force a new token to be created
	 *
	 * @return  string  The session token
	 *
	 * @since   1.7.0
	 */
	public function getToken($forceNew = false)
	{
		$token = $this->get('session.token');

		// Create a token
		if ($token === null || $forceNew)
		{
			$token = $this->_createToken();
			$this->set('session.token', $token);
		}

		return $token;
	}

	/**
	 * Method to determine if a token exists in the session. If not the
	 * session will be set to expired
	 *
	 * @param   string   $tCheck       Hashed token to be verified
	 * @param   boolean  $forceExpire  If true, expires the session
	 *
	 * @return  boolean
	 *
	 * @since   1.7.0
	 */
	public function hasToken($tCheck, $forceExpire = true)
	{
		// Check if a token exists in the session
		$tStored = $this->get('session.token');

		// Check token
		if (($tStored !== $tCheck))
		{
			if ($forceExpire)
			{
				$this->_state = 'expired';
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
	 * @since   1.7.0
	 */
	public static function getFormToken($forceNew = false)
	{
		$user    = \JFactory::getUser();
		$session = \JFactory::getSession();

		return ApplicationHelper::getHash($user->get('id', 0) . $session->getToken($forceNew));
	}

	/**
	 * Retrieve an external iterator.
	 *
	 * @return  \ArrayIterator
	 *
	 * @since   3.0.1
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->getData());
	}

	/**
	 * Checks for a form token in the request.
	 *
	 * Use in conjunction with \JHtml::_('form.token') or Session::getFormToken.
	 *
	 * @param   string  $method  The request method in which to look for the token key.
	 *
	 * @return  boolean  True if found and valid, false otherwise.
	 *
	 * @since   3.0.0
	 */
	public static function checkToken($method = 'post')
	{
		$token = self::getFormToken();
		$app = \JFactory::getApplication();

		// Check from header first
		if ($token === $app->input->server->get('HTTP_X_CSRF_TOKEN', '', 'alnum'))
		{
			return true;
		}

		// Then fallback to HTTP query
		if (!$app->input->$method->get($token, '', 'alnum'))
		{
			if (\JFactory::getSession()->isNew())
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
	 * Get session name
	 *
	 * @return  string  The session name
	 *
	 * @since   1.7.0
	 */
	public function getName()
	{
		if ($this->getState() === 'destroyed')
		{
			// @TODO : raise error
			return;
		}

		return $this->_handler->getName();
	}

	/**
	 * Get session id
	 *
	 * @return  string  The session id
	 *
	 * @since   1.7.0
	 */
	public function getId()
	{
		if ($this->getState() === 'destroyed')
		{
			// @TODO : raise error
			return;
		}

		return $this->_handler->getId();
	}

	/**
	 * Returns a clone of the internal data pointer
	 *
	 * @return  \Joomla\Registry\Registry
	 */
	public function getData()
	{
		return clone $this->data;
	}

	/**
	 * Get the session handlers
	 *
	 * @return  array  An array of available session handlers
	 *
	 * @since   1.7.0
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
	 *
	 * @return  boolean
	 *
	 * @since   3.0.1
	 */
	public function isActive()
	{
		return (bool) ($this->getState() == 'active');
	}

	/**
	 * Check whether this session is currently created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 */
	public function isNew()
	{
		return (bool) ($this->get('session.counter') === 1);
	}

	/**
	 * Check whether this session is currently created
	 *
	 * @param   Input              $input       Input object for the session to use.
	 * @param   \JEventDispatcher  $dispatcher  Dispatcher object for the session to use.
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 */
	public function initialise(Input $input, \JEventDispatcher $dispatcher = null)
	{
		// With the introduction of the handler class this variable is no longer required
		// however we keep setting it for b/c
		$this->_input      = $input;

		// Nasty workaround to deal in a b/c way with JInput being required in the 3.4+ Handler class.
		if ($this->_handler instanceof \JSessionHandlerJoomla)
		{
			$this->_handler->input = $input;
		}

		$this->_dispatcher = $dispatcher;
	}

	/**
	 * Get data from the session store
	 *
	 * @param   string  $name       Name of a variable
	 * @param   mixed   $default    Default value of a variable if not set
	 * @param   string  $namespace  Namespace to use, default to 'default'
	 *
	 * @return  mixed  Value of a variable
	 *
	 * @since   1.7.0
	 */
	public function get($name, $default = null, $namespace = 'default')
	{
		if (!$this->isActive())
		{
			$this->start();
		}

		// Add prefix to namespace to avoid collisions
		$namespace = '__' . $namespace;

		if ($this->getState() === 'destroyed')
		{
			// @TODO :: generated error here
			$error = null;

			return $error;
		}

		return $this->data->get($namespace . '.' . $name, $default);
	}

	/**
	 * Set data into the session store.
	 *
	 * @param   string  $name       Name of a variable.
	 * @param   mixed   $value      Value of a variable.
	 * @param   string  $namespace  Namespace to use, default to 'default'.
	 *
	 * @return  mixed  Old value of a variable.
	 *
	 * @since   1.7.0
	 */
	public function set($name, $value = null, $namespace = 'default')
	{
		if (!$this->isActive())
		{
			$this->start();
		}

		// Add prefix to namespace to avoid collisions
		$namespace = '__' . $namespace;

		if ($this->getState() !== 'active')
		{
			// @TODO :: generated error here
			return;
		}

		$prev = $this->data->get($namespace . '.' . $name, null);
		$this->data->set($namespace . '.' . $name, $value);

		return $prev;
	}

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string  $name       Name of variable
	 * @param   string  $namespace  Namespace to use, default to 'default'
	 *
	 * @return  boolean  True if the variable exists
	 *
	 * @since   1.7.0
	 */
	public function has($name, $namespace = 'default')
	{
		if (!$this->isActive())
		{
			$this->start();
		}

		// Add prefix to namespace to avoid collisions.
		$namespace = '__' . $namespace;

		if ($this->getState() !== 'active')
		{
			// @TODO :: generated error here
			return;
		}

		return !is_null($this->data->get($namespace . '.' . $name, null));
	}

	/**
	 * Unset data from the session store
	 *
	 * @param   string  $name       Name of variable
	 * @param   string  $namespace  Namespace to use, default to 'default'
	 *
	 * @return  mixed   The value from session or NULL if not set
	 *
	 * @since   1.7.0
	 */
	public function clear($name, $namespace = 'default')
	{
		if (!$this->isActive())
		{
			$this->start();
		}

		// Add prefix to namespace to avoid collisions
		$namespace = '__' . $namespace;

		if ($this->getState() !== 'active')
		{
			// @TODO :: generated error here
			return;
		}

		return $this->data->set($namespace . '.' . $name, null);
	}

	/**
	 * Start a session.
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 */
	public function start()
	{
		if ($this->getState() === 'active')
		{
			return;
		}

		$this->_start();

		$this->_state = 'active';

		// Initialise the session
		$this->_setCounter();
		$this->_setTimers();

		// Perform security checks
		if (!$this->_validate())
		{
			// If the session isn't valid because it expired try to restart it
			// else destroy it.
			if ($this->_state === 'expired')
			{
				$this->restart();
			}
			else
			{
				$this->destroy();
			}
		}

		if ($this->_dispatcher instanceof \JEventDispatcher)
		{
			$this->_dispatcher->trigger('onAfterSessionStart');
		}
	}

	/**
	 * Start a session.
	 *
	 * Creates a session (or resumes the current one based on the state of the session)
	 *
	 * @return  boolean  true on success
	 *
	 * @since   1.7.0
	 */
	protected function _start()
	{
		$this->_handler->start();

		// Ok let's unserialize the whole thing
		// Try loading data from the session
		if (isset($_SESSION['joomla']) && !empty($_SESSION['joomla']))
		{
			$data = $_SESSION['joomla'];

			$data = base64_decode($data);

			$this->data = unserialize($data);
		}

		// Temporary, PARTIAL, data migration of existing session data to avoid logout on update from J < 3.4.7
		if (isset($_SESSION['__default']) && !empty($_SESSION['__default']))
		{
			$migratableKeys = array(
				'user',
				'session.token',
				'session.counter',
				'session.timer.start',
				'session.timer.last',
				'session.timer.now'
			);

			foreach ($migratableKeys as $migratableKey)
			{
				if (!empty($_SESSION['__default'][$migratableKey]))
				{
					// Don't overwrite existing session data
					if (!is_null($this->data->get('__default.' . $migratableKey, null)))
					{
						continue;
					}

					$this->data->set('__default.' . $migratableKey, $_SESSION['__default'][$migratableKey]);
					unset($_SESSION['__default'][$migratableKey]);
				}
			}

			/**
			 * Finally, empty the __default key since we no longer need it. Don't unset it completely, we need this
			 * for the administrator/components/com_admin/script.php to detect upgraded sessions and perform a full
			 * session cleanup.
			 */
			$_SESSION['__default'] = array();
		}

		return true;
	}

	/**
	 * Frees all session variables and destroys all data registered to a session
	 *
	 * This method resets the data pointer and destroys all of the data associated
	 * with the current session in its storage. It forces a new session to be
	 * started after this method is called. It does not unset the session cookie.
	 *
	 * @return  boolean  True on success
	 *
	 * @see     session_destroy()
	 * @see     session_unset()
	 * @since   1.7.0
	 */
	public function destroy()
	{
		// Session was already destroyed
		if ($this->getState() === 'destroyed')
		{
			return true;
		}

		// Kill session
		$this->_handler->clear();

		// Create new data storage
		$this->data = new \Joomla\Registry\Registry;

		$this->_state = 'destroyed';

		return true;
	}

	/**
	 * Restart an expired or locked session.
	 *
	 * @return  boolean  True on success
	 *
	 * @see     Session::destroy()
	 * @since   1.7.0
	 */
	public function restart()
	{
		$this->destroy();

		if ($this->getState() !== 'destroyed')
		{
			// @TODO :: generated error here
			return false;
		}

		// Re-register the session handler after a session has been destroyed, to avoid PHP bug
		$this->_store->register();

		$this->_state = 'restart';

		// Regenerate session id
		$this->_start();
		$this->_handler->regenerate(true, null);
		$this->_state = 'active';

		if (!$this->_validate())
		{
			/**
			 * Destroy the session if it's not valid - we can't restart the session here unlike in the start method
			 * else we risk recursion.
			 */
			$this->destroy();
		}

		$this->_setCounter();

		return true;
	}

	/**
	 * Create a new session and copy variables from the old one
	 *
	 * @return  boolean $result true on success
	 *
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
	 */
	protected function _setOptions(array $options)
	{
		// Set name
		if (isset($options['name']))
		{
			$this->_handler->setName(md5($options['name']));
		}

		// Set id
		if (isset($options['id']))
		{
			$this->_handler->setId($options['id']);
		}

		// Set expire time
		if (isset($options['expire']))
		{
			$this->_expire = $options['expire'];
		}

		// Get security options
		if (isset($options['security']))
		{
			$this->_security = explode(',', $options['security']);
		}

		// Sync the session maxlifetime
		if (!headers_sent())
		{
			ini_set('session.gc_maxlifetime', $this->_expire);
		}

		return true;
	}

	/**
	 * Do some checks for security reason
	 *
	 * - timeout check (expire)
	 * - ip-fixiation
	 * - browser-fixiation
	 *
	 * If one check failed, session data has to be cleaned.
	 *
	 * @param   boolean  $restart  Reactivate session
	 *
	 * @return  boolean  True on success
	 *
	 * @link    http://shiflett.org/articles/the-truth-about-sessions
	 * @since   1.7.0
	 */
	protected function _validate($restart = false)
	{
		// Allow to restart a session
		if ($restart)
		{
			$this->_state = 'active';

			$this->set('session.client.address', null);
			$this->set('session.client.forwarded', null);
			$this->set('session.client.browser', null);
			$this->set('session.token', null);
		}

		// Check if session has expired
		if ($this->getExpire())
		{
			$curTime = $this->get('session.timer.now', 0);
			$maxTime = $this->get('session.timer.last', 0) + $this->getExpire();

			// Empty session variables
			if ($maxTime < $curTime)
			{
				$this->_state = 'expired';

				return false;
			}
		}

		// Check for client address
		if (in_array('fix_adress', $this->_security) && isset($_SERVER['REMOTE_ADDR'])
			&& filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) !== false)
		{
			$ip = $this->get('session.client.address');

			if ($ip === null)
			{
				$this->set('session.client.address', $_SERVER['REMOTE_ADDR']);
			}
			elseif ($_SERVER['REMOTE_ADDR'] !== $ip)
			{
				$this->_state = 'error';

				return false;
			}
		}

		// Record proxy forwarded for in the session in case we need it later
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP) !== false)
		{
			$this->set('session.client.forwarded', $_SERVER['HTTP_X_FORWARDED_FOR']);
		}

		return true;
	}
}
