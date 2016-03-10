<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session;

use Joomla\Event\DispatcherInterface;
use Joomla\Input\Input;

/**
 * Class for managing HTTP sessions
 *
 * Provides access to session-state values as well as session-level
 * settings and lifetime management methods.
 * Based on the standard PHP session handling mechanism it provides
 * more advanced features such as expire timeouts.
 *
 * @since  1.0
 */
class Session implements \IteratorAggregate
{
	/**
	 * Internal state.
	 * One of 'inactive'|'active'|'expired'|'destroyed'|'error'
	 *
	 * @var    string
	 * @see    getState()
	 * @since  1.0
	 */
	protected $state = 'inactive';

	/**
	 * Maximum age of unused session in minutes
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $expire = 15;

	/**
	 * The session store object.
	 *
	 * @var    Storage
	 * @since  1.0
	 */
	protected $store = null;

	/**
	 * Security policy.
	 * List of checks that will be done.
	 *
	 * Default values:
	 * - fix_browser
	 * - fix_adress
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $security = array('fix_browser');

	/**
	 * Force cookies to be SSL only
	 * Default  false
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $force_ssl = false;

	/**
	 * The domain to use when setting cookies.
	 *
	 * @var    mixed
	 * @since  1.0
	 * @deprecated  2.0
	 */
	protected $cookie_domain;

	/**
	 * The path to use when setting cookies.
	 *
	 * @var    mixed
	 * @since  1.0
	 * @deprecated  2.0
	 */
	protected $cookie_path;

	/**
	 * Session instances container.
	 *
	 * @var    Session
	 * @since  1.0
	 * @deprecated  2.0
	 */
	protected static $instance;

	/**
	 * The type of storage for the session.
	 *
	 * @var    string
	 * @since  1.0
	 * @deprecated  2.0
	 */
	protected $storeName;

	/**
	 * Holds the Input object
	 *
	 * @var    Input
	 * @since  1.0
	 */
	private $input = null;

	/**
	 * Holds the Dispatcher object
	 *
	 * @var    DispatcherInterface
	 * @since  1.0
	 */
	private $dispatcher = null;

	/**
	 * Constructor
	 *
	 * @param   string  $store    The type of storage for the session.
	 * @param   array   $options  Optional parameters
	 *
	 * @since   1.0
	 */
	public function __construct($store = 'none', array $options = array())
	{
		// Need to destroy any existing sessions started with session.auto_start
		if (session_id())
		{
			session_unset();
			session_destroy();
		}

		// Disable transparent sid support
		ini_set('session.use_trans_sid', '0');

		// Only allow the session ID to come from cookies and nothing else.
		ini_set('session.use_only_cookies', '1');

		// Create handler
		$this->store = Storage::getInstance($store, $options);

		$this->storeName = $store;

		// Set options
		$this->_setOptions($options);

		$this->_setCookieParams();

		$this->setState('inactive');
	}

	/**
	 * Magic method to get read-only access to properties.
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  mixed   The value of the property
	 *
	 * @since   1.0
	 * @deprecated  2.0  Use get methods for non-deprecated properties
	 */
	public function __get($name)
	{
		if ($name === 'storeName' || $name === 'state' || $name === 'expire')
		{
			return $this->$name;
		}
	}

	/**
	 * Returns the global Session object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $handler  The type of session handler.
	 * @param   array   $options  An array of configuration options (for new sessions only).
	 *
	 * @return  Session  The Session object.
	 *
	 * @since   1.0
	 * @deprecated  2.0  A singleton object store will no longer be supported
	 */
	public static function getInstance($handler, array $options = array ())
	{
		if (!is_object(self::$instance))
		{
			self::$instance = new self($handler, $options);
		}

		return self::$instance;
	}

	/**
	 * Get current state of session
	 *
	 * @return  string  The session state
	 *
	 * @since   1.0
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Get expiration time in minutes
	 *
	 * @return  integer  The session expiration time in minutes
	 *
	 * @since   1.0
	 */
	public function getExpire()
	{
		return $this->expire;
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
	 * @since   1.0
	 */
	public function getToken($forceNew = false)
	{
		$token = $this->get('session.token');

		// Create a token
		if ($token === null || $forceNew)
		{
			$token = $this->_createToken(12);
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
	 * @since   1.0
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
				$this->setState('expired');
			}

			return false;
		}

		return true;
	}

	/**
	 * Retrieve an external iterator.
	 *
	 * @return  \ArrayIterator  Return an ArrayIterator of $_SESSION.
	 *
	 * @since   1.0
	 */
	public function getIterator()
	{
		return new \ArrayIterator($_SESSION);
	}

	/**
	 * Get session name
	 *
	 * @return  string  The session name
	 *
	 * @since   1.0
	 */
	public function getName()
	{
		if ($this->getState() === 'destroyed')
		{
			// @TODO : raise error
			return null;
		}

		return session_name();
	}

	/**
	 * Get session id
	 *
	 * @return  string  The session name
	 *
	 * @since   1.0
	 */
	public function getId()
	{
		if ($this->getState() === 'destroyed')
		{
			return null;
		}

		return session_id();
	}

	/**
	 * Get the session handlers
	 *
	 * @return  array  An array of available session handlers
	 *
	 * @since   1.0
	 * @deprecated  2.0  The Storage class chain will be removed
	 */
	public static function getStores()
	{
		$connectors = array();

		// Get an iterator and loop trough the driver classes.
		$iterator = new \DirectoryIterator(__DIR__ . '/Storage');

		foreach ($iterator as $file)
		{
			$fileName = $file->getFilename();

			// Only load for php files.
			if (!$file->isFile() || $file->getExtension() != 'php')
			{
				continue;
			}

			// Derive the class name from the type.
			$class = str_ireplace('.php', '', '\\Joomla\\Session\\Storage\\' . ucfirst(trim($fileName)));

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
	 * @since   1.0
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
	 * @since   1.0
	 */
	public function isNew()
	{
		$counter = $this->get('session.counter');

		return (bool) ($counter === 1);
	}

	/**
	 * Check whether this session is currently created
	 *
	 * @param   Input                $input       Input object for the session to use.
	 * @param   DispatcherInterface  $dispatcher  Dispatcher object for the session to use.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @deprecated  2.0  In 2.0 the DispatcherInterface should be injected via the object constructor
	 */
	public function initialise(Input $input, DispatcherInterface $dispatcher = null)
	{
		$this->input      = $input;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Get data from the session store
	 *
	 * @param   string  $name       Name of a variable
	 * @param   mixed   $default    Default value of a variable if not set
	 * @param   string  $namespace  Namespace to use, default to 'default' {@deprecated 2.0 Namespace support will be removed.}
	 *
	 * @return  mixed  Value of a variable
	 *
	 * @since   1.0
	 */
	public function get($name, $default = null, $namespace = 'default')
	{
		// Add prefix to namespace to avoid collisions
		$namespace = '__' . $namespace;

		if ($this->getState() !== 'active' && $this->getState() !== 'expired')
		{
			// @TODO :: generated error here
			$error = null;

			return $error;
		}

		if (isset($_SESSION[$namespace][$name]))
		{
			return $_SESSION[$namespace][$name];
		}

		return $default;
	}

	/**
	 * Set data into the session store.
	 *
	 * @param   string  $name       Name of a variable.
	 * @param   mixed   $value      Value of a variable.
	 * @param   string  $namespace  Namespace to use, default to 'default' {@deprecated 2.0 Namespace support will be removed.}
	 *
	 * @return  mixed  Old value of a variable.
	 *
	 * @since   1.0
	 */
	public function set($name, $value = null, $namespace = 'default')
	{
		// Add prefix to namespace to avoid collisions
		$namespace = '__' . $namespace;

		if ($this->getState() !== 'active')
		{
			// @TODO :: generated error here
			return null;
		}

		$old = isset($_SESSION[$namespace][$name]) ? $_SESSION[$namespace][$name] : null;

		if (null === $value)
		{
			unset($_SESSION[$namespace][$name]);
		}
		else
		{
			$_SESSION[$namespace][$name] = $value;
		}

		return $old;
	}

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string  $name       Name of variable
	 * @param   string  $namespace  Namespace to use, default to 'default' {@deprecated 2.0 Namespace support will be removed.}
	 *
	 * @return  boolean  True if the variable exists
	 *
	 * @since   1.0
	 */
	public function has($name, $namespace = 'default')
	{
		// Add prefix to namespace to avoid collisions.
		$namespace = '__' . $namespace;

		if ($this->getState() !== 'active')
		{
			// @TODO :: generated error here
			return null;
		}

		return isset($_SESSION[$namespace][$name]);
	}

	/**
	 * Unset data from the session store
	 *
	 * @param   string  $name       Name of variable
	 * @param   string  $namespace  Namespace to use, default to 'default' {@deprecated 2.0 Namespace support will be removed.}
	 *
	 * @return  mixed   The value from session or NULL if not set
	 *
	 * @since   1.0
	 */
	public function clear($name, $namespace = 'default')
	{
		// Add prefix to namespace to avoid collisions
		$namespace = '__' . $namespace;

		if ($this->getState() !== 'active')
		{
			// @TODO :: generated error here
			return null;
		}

		$value = null;

		if (isset($_SESSION[$namespace][$name]))
		{
			$value = $_SESSION[$namespace][$name];
			unset($_SESSION[$namespace][$name]);
		}

		return $value;
	}

	/**
	 * Start a session.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function start()
	{
		if ($this->getState() === 'active')
		{
			return;
		}

		$this->_start();

		$this->setState('active');

		// Initialise the session
		$this->_setCounter();
		$this->_setTimers();

		// Perform security checks
		$this->_validate();

		if ($this->dispatcher instanceof DispatcherInterface)
		{
			$this->dispatcher->triggerEvent('onAfterSessionStart');
		}
	}

	/**
	 * Start a session.
	 *
	 * Creates a session (or resumes the current one based on the state of the session)
	 *
	 * @return  boolean  true on success
	 *
	 * @since   1.0
	 * @deprecated  2.0
	 */
	protected function _start()
	{
		// Start session if not started
		if ($this->getState() === 'restart')
		{
			session_regenerate_id(true);
		}
		else
		{
			$session_name = session_name();

			// Get the Joomla\Input\Cookie object
			$cookie = $this->input->cookie;

			if (is_null($cookie->get($session_name)))
			{
				$session_clean = $this->input->get($session_name, false, 'string');

				if ($session_clean)
				{
					session_id($session_clean);
					$cookie->set($session_name, '', time() - 3600);
				}
			}
		}

		/**
		 * Write and Close handlers are called after destructing objects since PHP 5.0.5.
		 * Thus destructors can use sessions but session handler can't use objects.
		 * So we are moving session closure before destructing objects.
		 *
		 * Replace with session_register_shutdown() when dropping compatibility with PHP 5.3
		 */
		register_shutdown_function('session_write_close');

		session_cache_limiter('none');
		session_start();

		return true;
	}

	/**
	 * Frees all session variables and destroys all data registered to a session
	 *
	 * This method resets the $_SESSION variable and destroys all of the data associated
	 * with the current session in its storage (file or DB). It forces new session to be
	 * started after this method is called. It does not unset the session cookie.
	 *
	 * @return  boolean  True on success
	 *
	 * @see     session_destroy()
	 * @see     session_unset()
	 * @since   1.0
	 */
	public function destroy()
	{
		// Session was already destroyed
		if ($this->getState() === 'destroyed')
		{
			return true;
		}

		/*
		 * In order to kill the session altogether, such as to log the user out, the session id
		 * must also be unset. If a cookie is used to propagate the session id (default behavior),
		 * then the session cookie must be deleted.
		 */
		if (isset($_COOKIE[session_name()]))
		{
			setcookie(session_name(), '', time() - 42000, $this->cookie_path, $this->cookie_domain);
		}

		session_unset();
		session_destroy();

		$this->setState('destroyed');

		return true;
	}

	/**
	 * Restart an expired or locked session.
	 *
	 * @return  boolean  True on success
	 *
	 * @see     destroy
	 * @since   1.0
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
		$this->store->register();

		$this->setState('restart');

		// Regenerate session id
		session_regenerate_id(true);
		$this->_start();
		$this->setState('active');

		$this->_validate();
		$this->_setCounter();

		return true;
	}

	/**
	 * Create a new session and copy variables from the old one
	 *
	 * @return  boolean $result true on success
	 *
	 * @since   1.0
	 */
	public function fork()
	{
		if ($this->getState() !== 'active')
		{
			// @TODO :: generated error here
			return false;
		}

		// Keep session config
		$cookie = session_get_cookie_params();

		// Kill session
		session_destroy();

		// Re-register the session store after a session has been destroyed, to avoid PHP bug
		$this->store->register();

		// Restore config
		session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], true);

		// Restart session with new id
		session_regenerate_id(true);
		session_start();

		return true;
	}

	/**
	 * Writes session data and ends session
	 *
	 * Session data is usually stored after your script terminated without the need
	 * to call JSession::close(), but as session data is locked to prevent concurrent
	 * writes only one script may operate on a session at any time. When using
	 * framesets together with sessions you will experience the frames loading one
	 * by one due to this locking. You can reduce the time needed to load all the
	 * frames by ending the session as soon as all changes to session variables are
	 * done.
	 *
	 * @return  void
	 *
	 * @see     session_write_close()
	 * @since   1.0
	 */
	public function close()
	{
		session_write_close();
	}

	/**
	 * Set the session expiration
	 *
	 * @param   integer  $expire  Maximum age of unused session in minutes
	 *
	 * @return  $this
	 *
	 * @since   1.3.0
	 */
	protected function setExpire($expire)
	{
		$this->expire = $expire;

		return $this;
	}

	/**
	 * Set the session state
	 *
	 * @param   string  $state  Internal state
	 *
	 * @return  $this
	 *
	 * @since   1.3.0
	 */
	protected function setState($state)
	{
		$this->state = $state;

		return $this;
	}

	/**
	 * Set session cookie parameters
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @deprecated  2.0
	 */
	protected function _setCookieParams()
	{
		$cookie = session_get_cookie_params();

		if ($this->force_ssl)
		{
			$cookie['secure'] = true;
		}

		if ($this->cookie_domain)
		{
			$cookie['domain'] = $this->cookie_domain;
		}

		if ($this->cookie_path)
		{
			$cookie['path'] = $this->cookie_path;
		}

		session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], true);
	}

	/**
	 * Create a token-string
	 *
	 * @param   integer  $length  Length of string
	 *
	 * @return  string  Generated token
	 *
	 * @since   1.0
	 * @deprecated  2.0  Use createToken instead
	 */
	protected function _createToken($length = 32)
	{
		return $this->createToken($length);
	}

	/**
	 * Create a token-string
	 *
	 * @param   integer  $length  Length of string
	 *
	 * @return  string  Generated token
	 *
	 * @since   1.3.1
	 */
	protected function createToken($length = 32)
	{
		static $chars = '0123456789abcdef';
		$max = strlen($chars) - 1;
		$token = '';
		$name = session_name();

		for ($i = 0; $i < $length; ++$i)
		{
			$token .= $chars[(rand(0, $max))];
		}

		return md5($token . $name);
	}

	/**
	 * Set counter of session usage
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 * @deprecated  2.0  Use setCounter instead
	 */
	protected function _setCounter()
	{
		return $this->setCounter();
	}

	/**
	 * Set counter of session usage
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.3.0
	 */
	protected function setCounter()
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
	 * @since   1.0
	 * @deprecated  2.0  Use setTimers instead
	 */
	protected function _setTimers()
	{
		return $this->setTimers();
	}

	/**
	 * Set the session timers
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.3.0
	 */
	protected function setTimers()
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
	 * @since   1.0
	 * @deprecated  2.0  Use setOptions instead
	 */
	protected function _setOptions(array $options)
	{
		return $this->setOptions($options);
	}

	/**
	 * Set additional session options
	 *
	 * @param   array  $options  List of parameter
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.3.0
	 */
	protected function setOptions(array $options)
	{
		// Set name
		if (isset($options['name']))
		{
			session_name(md5($options['name']));
		}

		// Set id
		if (isset($options['id']))
		{
			session_id($options['id']);
		}

		// Set expire time
		if (isset($options['expire']))
		{
			$this->setExpire($options['expire']);
		}

		// Get security options
		if (isset($options['security']))
		{
			$this->security = explode(',', $options['security']);
		}

		if (isset($options['force_ssl']))
		{
			$this->force_ssl = (bool) $options['force_ssl'];
		}

		if (isset($options['cookie_domain']))
		{
			$this->cookie_domain = $options['cookie_domain'];
		}

		if (isset($options['cookie_path']))
		{
			$this->cookie_path = $options['cookie_path'];
		}

		// Sync the session maxlifetime
		ini_set('session.gc_maxlifetime', $this->getExpire());

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
	 * @see     http://shiflett.org/articles/the-truth-about-sessions
	 * @since   1.0
	 * @deprecated  2.0  Use validate instead
	 */
	protected function _validate($restart = false)
	{
		return $this->validate($restart);
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
	 * @see     http://shiflett.org/articles/the-truth-about-sessions
	 * @since   1.3.0
	 */
	protected function validate($restart = false)
	{
		// Allow to restart a session
		if ($restart)
		{
			$this->setState('active');

			$this->set('session.client.address', null);
			$this->set('session.client.forwarded', null);
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
				$this->setState('expired');

				return false;
			}
		}

		$remoteAddr = $this->input->server->getString('REMOTE_ADDR', '');

		// Check for client address
		if (in_array('fix_adress', $this->security) && !empty($remoteAddr) && filter_var($remoteAddr, FILTER_VALIDATE_IP) !== false)
		{
			$ip = $this->get('session.client.address');

			if ($ip === null)
			{
				$this->set('session.client.address', $remoteAddr);
			}
			elseif ($remoteAddr !== $ip)
			{
				$this->setState('error');

				return false;
			}
		}

		$xForwardedFor = $this->input->server->getString('HTTP_X_FORWARDED_FOR', '');

		// Record proxy forwarded for in the session in case we need it later
		if (!empty($xForwardedFor) && filter_var($xForwardedFor, FILTER_VALIDATE_IP) !== false)
		{
			$this->set('session.client.forwarded', $xForwardedFor);
		}

		return true;
	}
}
