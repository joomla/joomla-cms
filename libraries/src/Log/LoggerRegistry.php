<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Log;

defined('JPATH_PLATFORM') or die;

/**
 * Service registry for loggers
 *
 * @since  __DEPLOY_VERSION__
 */
final class LoggerRegistry
{
	/**
	 * Array holding the registered services
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $loggerMap = [
		'callback'      => Logger\CallbackLogger::class,
		'database'      => Logger\DatabaseLogger::class,
		'echo'          => Logger\EchoLogger::class,
		'formattedtext' => Logger\FormattedtextLogger::class,
		'messagequeue'  => Logger\MessagequeueLogger::class,
		'syslog'        => Logger\SyslogLogger::class,
		'w3c'           => Logger\W3cLogger::class,
	];

	/**
	 * Get the logger class for a given key
	 *
	 * @param   string  $key  The key to look up
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException
	 */
	public function getLoggerClass(string $key): string
	{
		if (!$this->hasLogger($key))
		{
			throw new \InvalidArgumentException("The '$key' key is not registered.");
		}

		return $this->loggerMap[$key];
	}

	/**
	 * Check if the registry has a logger for the given key
	 *
	 * @param   string  $key  The key to look up
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasLogger(string $key): bool
	{
		return isset($this->loggerMap[$key]);
	}

	/**
	 * Register a logger
	 *
	 * @param   string   $key      The service key to be registered
	 * @param   string   $class    The class name of the logger
	 * @param   boolean  $replace  Flag indicating the service key may replace an existing definition
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function register(string $key, string $class, bool $replace = false)
	{
		// If the key exists already and we aren't instructed to replace existing services, bail early
		if (isset($this->loggerMap[$key]) && !$replace)
		{
			throw new \RuntimeException("The '$key' key is already registered.");
		}

		// The class must exist
		if (!class_exists($class))
		{
			throw new \RuntimeException("The '$class' class for key '$key' does not exist.");
		}

		$this->loggerMap[$key] = $class;
	}
}
