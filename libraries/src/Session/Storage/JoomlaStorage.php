<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Session\Storage;

defined('JPATH_PLATFORM') or die;

use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Session\Storage\NativeStorage;

/**
 * Service provider for the application's session dependency
 *
 * @since  4.0
 */
class JoomlaStorage extends NativeStorage
{
	/**
	 * Internal data store for the session data
	 *
	 * @var    Registry
	 * @since  4.0
	 */
	private $data;

	/**
	 * Force cookies to be SSL only
	 *
	 * @var    boolean
	 * @since  4.0
	 */
	private $forceSSL = false;

	/**
	 * Input object
	 *
	 * @var    Input
	 * @since  4.0
	 */
	private $input;

	/**
	 * Constructor
	 *
	 * @param   Input                     $input    Input object
	 * @param   \SessionHandlerInterface  $handler  Session save handler
	 * @param   array                     $options  Session options
	 *
	 * @since   4.0
	 */
	public function __construct(Input $input, \SessionHandlerInterface $handler = null, array $options = [])
	{
		// Disable transparent sid support
		ini_set('session.use_trans_sid', '0');

		ini_set('session.use_cookies', 1);
		session_cache_limiter('none');

		$this->setOptions($options);
		$this->setHandler($handler);
		$this->setCookieParams();

		$this->data = new Registry;
		$this->input = $input;

		// Register our function as shutdown method, so we can manipulate it
		register_shutdown_function([$this, 'close']);
	}

	/**
	 * Retrieves all variables from the session store
	 *
	 * @return  array
	 *
	 * @since   4.0
	 */
	public function all(): array
	{
		return $this->data->toArray();
	}

	/**
	 * Clears all variables from the session store
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function clear()
	{
		$session_name = $this->getName();

		/*
		 * In order to kill the session altogether, such as to log the user out, the session id
		 * must also be unset. If a cookie is used to propagate the session id (default behavior),
		 * then the session cookie must be deleted.
		 */
		if (isset($_COOKIE[$session_name]))
		{
			$config        = \JFactory::getConfig();
			$cookie_domain = $config->get('cookie_domain', '');
			$cookie_path   = $config->get('cookie_path', '/');
			setcookie($session_name, '', time() - 42000, $cookie_path, $cookie_domain);
		}

		$this->data = new Registry;
	}

	/**
	 * Writes session data and ends session
	 *
	 * @return  void
	 *
	 * @see     session_write_close()
	 * @since   4.0
	 */
	public function close()
	{
		// Before storing data to the session, we serialize and encode the Registry
		$_SESSION['joomla'] = base64_encode(serialize(clone $this->data));

		parent::close();
	}

	/**
	 * Get data from the session store
	 *
	 * @param   string  $name     Name of a variable
	 * @param   mixed   $default  Default value of a variable if not set
	 *
	 * @return  mixed  Value of a variable
	 *
	 * @since   4.0
	 */
	public function get(string $name, $default)
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		return $this->data->get($name, $default);
	}

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  boolean  True if the variable exists
	 *
	 * @since   4.0
	 */
	public function has(string $name): bool
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		return $this->data->exists($name);
	}

	/**
	 * Unset a variable from the session store
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  mixed  The value from session or NULL if not set
	 *
	 * @since   4.0
	 */
	public function remove(string $name)
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		$old = $this->data->get($name);

		unset($this->data[$name]);

		return $old;
	}

	/**
	 * Set data into the session store
	 *
	 * @param   string  $name   Name of a variable.
	 * @param   mixed   $value  Value of a variable.
	 *
	 * @return  mixed  Old value of a variable.
	 *
	 * @since   4.0
	 */
	public function set(string $name, $value = null)
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		$old = $this->data->get($name);

		$this->data->set($name, $value);

		return $old;
	}

	/**
	 * Set session cookie parameters
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	protected function setCookieParams()
	{
		$cookie = session_get_cookie_params();

		if ($this->forceSSL)
		{
			$cookie['secure'] = true;
		}

		$config = \JFactory::getConfig();

		if ($config->get('cookie_domain', '') != '')
		{
			$cookie['domain'] = $config->get('cookie_domain');
		}

		if ($config->get('cookie_path', '') != '')
		{
			$cookie['path'] = $config->get('cookie_path');
		}

		session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], true);
	}

	/**
	 * Sets session options
	 *
	 * @param   array  $options  Session ini directives array(key => value).
	 *
	 * @return  $this
	 *
	 * @see     http://php.net/session.configuration
	 * @since   4.0
	 */
	public function setOptions(array $options)
	{
		if (isset($options['force_ssl']))
		{
			$this->forceSSL = (bool) $options['force_ssl'];
		}

		return parent::setOptions($options);
	}

	/**
	 * Start a session
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function start()
	{
		$session_name = $this->getName();

		// Get the cookie object
		$cookie = $this->input->cookie;

		if (is_null($cookie->get($session_name)))
		{
			$session_clean = $this->input->getString($session_name);

			if ($session_clean)
			{
				$this->setId($session_clean);
				$cookie->set($session_name, '', time() - 3600);
			}
		}

		parent::start();

		// Try loading data from the session
		if (isset($_SESSION['joomla']) && !empty($_SESSION['joomla']))
		{
			$this->data = unserialize(base64_decode($_SESSION['joomla']));
		}
	}
}
