<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

/**
 * Delegating logger which delegates log messages received from the PSR-3 interface to the Joomla! Log object.
 *
 * @since  __DEPLOY_VERSION__
 */
class DelegatingPsrLogger extends AbstractLogger
{
	/**
	 * The Log instance to delegate messages to.
	 *
	 * @var    Log
	 * @since  __DEPLOY_VERSION__
	 */
	protected $logger;

	/**
	 * Mapping array to map a PSR-3 level to a Joomla priority.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $priorityMap = array(
		LogLevel::EMERGENCY => Log::EMERGENCY,
		LogLevel::ALERT     => Log::ALERT,
		LogLevel::CRITICAL  => Log::CRITICAL,
		LogLevel::ERROR     => Log::ERROR,
		LogLevel::WARNING   => Log::WARNING,
		LogLevel::NOTICE    => Log::NOTICE,
		LogLevel::INFO      => Log::INFO,
		LogLevel::DEBUG     => Log::DEBUG
	);

	/**
	 * Constructor.
	 *
	 * @param   Log  $logger  The Log instance to delegate messages to.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(Log $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param   mixed   $level    The log level.
	 * @param   string  $message  The log message.
	 * @param   array   $context  Additional message context.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  InvalidArgumentException
	 */
	public function log($level, $message, array $context = array())
	{
		// Make sure the log level is valid
		if (!array_key_exists($level, $this->priorityMap))
		{
			throw new \InvalidArgumentException('An invalid log level has been given.');
		}

		// Map the level to Joomla's priority
		$priority = $this->priorityMap[$level];

		$category = null;
		$date     = null;

		// If a message category is given, map it
		if (!empty($context['category']))
		{
			$category = $context['category'];
		}

		// If a message timestamp is given, map it
		if (!empty($context['date']))
		{
			$date = $context['date'];
		}

		// Joomla's logging API will only process a string or a LogEntry object, if $message is an object without __toString() we can't use it
		if (!is_string($message) && !($message instanceof LogEntry))
		{
			if (!is_object($message) || !method_exists($message, '__toString'))
			{
				throw new \InvalidArgumentException(
					'The message must be a string, a LogEntry object, or an object implementing the __toString() method.'
				);
			}

			$message = (string) $message;
		}

		$this->logger->add($message, $priority, $category, $date, $context);
	}
}
