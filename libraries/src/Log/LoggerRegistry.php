<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Log;

use Joomla\CMS\Log\Logger\CallbackLogger;
use Joomla\CMS\Log\Logger\DatabaseLogger;
use Joomla\CMS\Log\Logger\EchoLogger;
use Joomla\CMS\Log\Logger\FormattedtextLogger;
use Joomla\CMS\Log\Logger\MessagequeueLogger;
use Joomla\CMS\Log\Logger\SyslogLogger;
use Joomla\CMS\Log\Logger\W3cLogger;
use Joomla\CMS\Log\Logger\InMemoryLogger;
/**
 * Service registry for loggers
 *
 * @since  4.0.0
 */
final class LoggerRegistry
{
    /**
     * Array holding the registered services
     *
     * @var    string[]
     * @since  4.0.0
     */
    private array $loggerMap = [
        'callback'      => CallbackLogger::class,
        'database'      => DatabaseLogger::class,
        'echo'          => EchoLogger::class,
        'formattedtext' => FormattedtextLogger::class,
        'messagequeue'  => MessagequeueLogger::class,
        'syslog'        => SyslogLogger::class,
        'w3c'           => W3cLogger::class,
        'inmemory'      => InMemoryLogger::class,
    ];

    /**
     * Get the logger class for a given key
     *
     * @param   string  $key  The key to look up
     *
     *
     * @since   4.0.0
     * @throws  \InvalidArgumentException
     */
    public function getLoggerClass(string $key): string
    {
        if (!$this->hasLogger($key)) {
            throw new \InvalidArgumentException("The '$key' key is not registered.");
        }

        return $this->loggerMap[$key];
    }

    /**
     * Check if the registry has a logger for the given key
     *
     * @param   string  $key  The key to look up
     *
     *
     * @since   4.0.0
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
     * @since   4.0.0
     */
    public function register(string $key, string $class, bool $replace = false)
    {
        // If the key exists already and we aren't instructed to replace existing services, bail early
        if (isset($this->loggerMap[$key]) && !$replace) {
            throw new \RuntimeException("The '$key' key is already registered.");
        }

        // The class must exist
        if (!class_exists($class)) {
            throw new \RuntimeException("The '$class' class for key '$key' does not exist.");
        }

        $this->loggerMap[$key] = $class;
    }
}
