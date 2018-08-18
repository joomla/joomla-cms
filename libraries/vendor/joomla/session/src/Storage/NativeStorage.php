<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Storage;

use Joomla\Session\HandlerInterface;
use Joomla\Session\StorageInterface;

/**
 * Base class providing a session store
 *
 * @since  __DEPLOY_VERSION__
 */
class NativeStorage implements StorageInterface
{
	/**
	 * Flag if the session is active
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $active = false;

	/**
	 * Internal flag identifying whether the session has been closed
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $closed = false;

	/**
	 * Session save handler
	 *
	 * @var    \SessionHandlerInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $handler;

	/**
	 * Internal flag identifying whether the session has been started
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $started = false;

	/**
	 * Constructor
	 *
	 * @param   \SessionHandlerInterface  $handler  Session save handler
	 * @param   array                     $options  Session options
	 *
	 * @since   1.0
	 */
	public function __construct(\SessionHandlerInterface $handler = null, array $options = [])
	{
		// Disable transparent sid support and default use cookies
		$options += [
			'use_cookies'   => 1,
			'use_trans_sid' => 0,
		];

		if (!headers_sent())
		{
			session_cache_limiter('none');
		}

		session_register_shutdown();

		$this->setOptions($options);
		$this->setHandler($handler);
	}

	/**
	 * Retrieves all variables from the session store
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function all(): array
	{
		return $_SESSION;
	}

	/**
	 * Clears all variables from the session store
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clear()
	{
		$_SESSION = [];
	}

	/**
	 * Writes session data and ends session
	 *
	 * @return  void
	 *
	 * @see     session_write_close()
	 * @since   __DEPLOY_VERSION__
	 */
	public function close()
	{
		session_write_close();

		$this->closed  = true;
		$this->started = false;
	}

	/**
	 * Perform session data garbage collection
	 *
	 * @return  integer|boolean  Number of deleted sessions on success or boolean false on failure or if the function is unsupported
	 *
	 * @see     session_gc()
	 * @since   __DEPLOY_VERSION__
	 */
	public function gc()
	{
		if (!function_exists('session_gc'))
		{
			return false;
		}

		if (!$this->isStarted())
		{
			$this->start();
		}

		return session_gc();
	}

	/**
	 * Aborts the current session
	 *
	 * @return  boolean
	 *
	 * @see     session_abort()
	 * @since   __DEPLOY_VERSION__
	 */
	public function abort()
	{
		if (!$this->isStarted())
		{
			return true;
		}

		return session_abort();
	}

	/**
	 * Get data from the session store
	 *
	 * @param   string  $name     Name of a variable
	 * @param   mixed   $default  Default value of a variable if not set
	 *
	 * @return  mixed  Value of a variable
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get(string $name, $default)
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		if (isset($_SESSION[$name]))
		{
			return $_SESSION[$name];
		}

		return $default;
	}

	/**
	 * Gets the save handler instance
	 *
	 * @return  \SessionHandlerInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getHandler(): \SessionHandlerInterface
	{
		return $this->handler;
	}

	/**
	 * Get the session ID
	 *
	 * @return  string  The session ID
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getId(): string
	{
		return session_id();
	}

	/**
	 * Get the session name
	 *
	 * @return  string  The session name
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getName(): string
	{
		return session_name();
	}

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  boolean  True if the variable exists
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function has(string $name): bool
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		return isset($_SESSION[$name]);
	}

	/**
	 * Check if the session is active
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isActive(): bool
	{
		return $this->active = session_status() === \PHP_SESSION_ACTIVE;
	}

	/**
	 * Check if the session is started
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isStarted(): bool
	{
		return $this->started;
	}

	/**
	 * Unset a variable from the session store
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  mixed  The value from session or NULL if not set
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function remove(string $name)
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		$old = $_SESSION[$name] ?? null;

		unset($_SESSION[$name]);

		return $old;
	}

	/**
	 * Regenerates the session ID that represents this storage.
	 *
	 * This method must invoke session_regenerate_id($destroy) unless this interface is used for a storage object designed for unit
	 * or functional testing where a real PHP session would interfere with testing.
	 *
	 * @param   boolean  $destroy  Destroy session when regenerating?
	 *
	 * @return  boolean  True on success
	 *
	 * @see     session_regenerate_id()
	 * @since   __DEPLOY_VERSION__
	 */
	public function regenerate(bool $destroy = false): bool
	{
		if (headers_sent() || !$this->isActive())
		{
			return false;
		}

		return session_regenerate_id($destroy);
	}

	/**
	 * Set data into the session store
	 *
	 * @param   string  $name   Name of a variable.
	 * @param   mixed   $value  Value of a variable.
	 *
	 * @return  mixed  Old value of a variable.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function set(string $name, $value = null)
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		$old = $_SESSION[$name] ?? null;

		$_SESSION[$name] = $value;

		return $old;
	}

	/**
	 * Registers session save handler as a PHP session handler
	 *
	 * @param   \SessionHandlerInterface  $handler  The save handler to use
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function setHandler(\SessionHandlerInterface $handler = null)
	{
		// If the handler is an instance of our HandlerInterface, check whether it is supported
		if ($handler instanceof HandlerInterface)
		{
			if (!$handler::isSupported())
			{
				throw new \RuntimeException(
					sprintf(
						'The "%s" handler is not supported in this environment.',
						get_class($handler)
					)
				);
			}
		}

		$this->handler = $handler;

		if (!headers_sent() && !$this->isActive())
		{
			session_set_save_handler($this->handler, false);
		}

		return $this;
	}

	/**
	 * Set the session ID
	 *
	 * @param   string  $id  The session ID
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \LogicException
	 */
	public function setId(string $id)
	{
		if ($this->isActive())
		{
			throw new \LogicException('Cannot change the ID of an active session');
		}

		session_id($id);

		return $this;
	}

	/**
	 * Set the session name
	 *
	 * @param   string  $name  The session name
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \LogicException
	 */
	public function setName(string $name)
	{
		if ($this->isActive())
		{
			throw new \LogicException('Cannot change the name of an active session');
		}

		session_name($name);

		return $this;
	}

	/**
	 * Sets session.* ini variables.
	 *
	 * For convenience we omit 'session.' from the beginning of the keys.
	 * Explicitly ignores other ini keys.
	 *
	 * @param   array  $options  Session ini directives array(key => value).
	 *
	 * @return  $this
	 *
	 * @note    Based on \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage::setOptions()
	 * @see     http://php.net/session.configuration
	 * @since   __DEPLOY_VERSION__
	 */
	public function setOptions(array $options)
	{
		if (headers_sent() || $this->isActive())
		{
			return $this;
		}

		$validOptions = array_flip(
			[
				'cache_limiter', 'cache_expire', 'cookie_domain', 'cookie_httponly', 'cookie_lifetime', 'cookie_path', 'cookie_secure', 'gc_divisor',
				'gc_maxlifetime', 'gc_probability', 'lazy_write', 'name', 'referer_check', 'serialize_handler', 'use_strict_mode', 'use_cookies',
				'use_only_cookies', 'use_trans_sid', 'upload_progress.enabled', 'upload_progress.cleanup', 'upload_progress.prefix',
				'upload_progress.name', 'upload_progress.freq', 'upload_progress.min-freq', 'url_rewriter.tags', 'sid_length',
				'sid_bits_per_character', 'trans_sid_hosts', 'trans_sid_tags',
			]
		);

		foreach ($options as $key => $value)
		{
			if (isset($validOptions[$key]))
			{
				ini_set('session.' . $key, $value);
			}
		}

		return $this;
	}

	/**
	 * Start a session
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function start()
	{
		if ($this->isStarted())
		{
			return;
		}

		if ($this->isActive())
		{
			throw new \RuntimeException('Failed to start the session: already started by PHP.');
		}

		if (ini_get('session.use_cookies') && headers_sent($file, $line))
		{
			throw new \RuntimeException(
				sprintf('Failed to start the session because headers have already been sent by "%s" at line %d.', $file, $line)
			);
		}

		if (!session_start())
		{
			throw new \RuntimeException('Failed to start the session');
		}

		$this->isActive();
		$this->closed  = false;
		$this->started = true;
	}
}
