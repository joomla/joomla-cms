<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Log;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Log Entry class
 *
 * This class is designed to hold log entries for either writing to an engine, or for
 * supported engines, retrieving lists and building in memory (PHP based) search operations.
 *
 * @since  1.7.0
 */
#[\AllowDynamicProperties]
class LogEntry
{
    /**
     * Application responsible for log entry.
     *
     * @var    string
     * @since  1.7.0
     */
    public $category;

    /**
     * The message context.
     *
     * @var    array
     * @since  3.8.0
     */
    public $context;

    /**
     * The date the message was logged.
     *
     * @var    Date
     * @since  1.7.0
     */
    public $date;

    /**
     * Message to be logged.
     *
     * @var    string
     * @since  1.7.0
     */
    public $message;

    /**
     * The priority of the message to be logged.
     *
     * @var    string
     * @since  1.7.0
     * @see    LogEntry::$priorities
     */
    public $priority = Log::INFO;

    /**
     * List of available log priority levels [Based on the Syslog default levels].
     *
     * @var    array
     * @since  1.7.0
     */
    protected $priorities = [
        Log::EMERGENCY,
        Log::ALERT,
        Log::CRITICAL,
        Log::ERROR,
        Log::WARNING,
        Log::NOTICE,
        Log::INFO,
        Log::DEBUG,
    ];

    /**
     * Call stack and back trace of the logged call.
     * @var    array
     * @since  3.1.4
     */
    public $callStack = [];

    /**
     * Constructor
     *
     * @param   string  $message   The message to log.
     * @param   int     $priority  Message priority based on {$this->priorities}.
     * @param   string  $category  Type of entry
     * @param   string  $date      Date of entry (defaults to now if not specified or blank)
     * @param   array   $context   An optional array with additional message context.
     *
     * @since   1.7.0
     * @change  3.10.7  If the message contains a full path, the root path (JPATH_ROOT) is removed from it
     *          to avoid any full path disclosure. Before 3.10.7, the path was propagated as provided.
     */
    public function __construct($message, $priority = Log::INFO, $category = '', $date = null, array $context = [])
    {
        $this->message = Path::removeRoot((string) $message);

        // Sanitize the priority.
        if (!\in_array($priority, $this->priorities, true)) {
            $priority = Log::INFO;
        }

        $this->priority = $priority;
        $this->context  = $context;

        // Sanitize category if it exists.
        if (!empty($category)) {
            $this->category = (string) strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $category));
        }

        // Get the current call stack and back trace (without args to save memory).
        $this->callStack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        // Get the date as a Date object.
        $this->date = new Date($date ?: 'now');
    }
}
