<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Profiler;

/**
 * Utility class to assist in the process of benchmarking the execution
 * of sections of code to understand where time is being spent.
 *
 * @since  1.7.0
 */
class Profiler
{
    /**
     * @var    integer  The start time.
     * @since  3.0.0
     */
    protected $start = 0;

    /**
     * @var    string  The prefix to use in the output
     * @since  3.0.0
     */
    protected $prefix = '';

    /**
     * @var    array  The buffer of profiling messages.
     * @since  3.0.0
     */
    protected $buffer = null;

    /**
     * @var    array  The profiling messages.
     * @since  3.0.0
     */
    protected $marks = null;

    /**
     * @var    float  The previous time marker
     * @since  3.0.0
     */
    protected $previousTime = 0.0;

    /**
     * @var    float  The previous memory marker
     * @since  3.0.0
     */
    protected $previousMem = 0.0;

    /**
     * @var    array  JProfiler instances container.
     * @since  1.7.3
     */
    protected static $instances = array();

    /**
     * Constructor
     *
     * @param   string  $prefix  Prefix for mark messages
     *
     * @since   1.7.0
     */
    public function __construct($prefix = '')
    {
        $this->start = microtime(1);
        $this->prefix = $prefix;
        $this->marks = array();
        $this->buffer = array();
    }

    /**
     * Returns the global Profiler object, only creating it
     * if it doesn't already exist.
     *
     * @param   string  $prefix  Prefix used to distinguish profiler objects.
     *
     * @return  Profiler  The Profiler object.
     *
     * @since   1.7.0
     */
    public static function getInstance($prefix = '')
    {
        if (empty(self::$instances[$prefix])) {
            self::$instances[$prefix] = new static($prefix);
        }

        return self::$instances[$prefix];
    }

    /**
     * Output a time mark
     *
     * @param   string  $label  A label for the time mark
     *
     * @return  string
     *
     * @since   1.7.0
     */
    public function mark($label)
    {
        $current = microtime(1) - $this->start;
        $currentMem = memory_get_usage() / 1048576;

        $m = (object) array(
            'prefix' => $this->prefix,
            'time' => ($current - $this->previousTime) * 1000,
            'totalTime' => ($current * 1000),
            'memory' => $currentMem - $this->previousMem,
            'totalMemory' => $currentMem,
            'label' => $label,
        );
        $this->marks[] = $m;

        $mark = sprintf(
            '%s %.3f seconds (%.3f); %0.2f MB (%0.3f) - %s',
            $m->prefix,
            $m->totalTime / 1000,
            $m->time / 1000,
            $m->totalMemory,
            $m->memory,
            $m->label
        );
        $this->buffer[] = $mark;

        $this->previousTime = $current;
        $this->previousMem = $currentMem;

        return $mark;
    }

    /**
     * Get all profiler marks.
     *
     * Returns an array of all marks created since the Profiler object
     * was instantiated.  Marks are objects as per {@link JProfiler::mark()}.
     *
     * @return  array  Array of profiler marks
     *
     * @since   1.7.0
     */
    public function getMarks()
    {
        return $this->marks;
    }

    /**
     * Get all profiler mark buffers.
     *
     * Returns an array of all mark buffers created since the Profiler object
     * was instantiated.  Marks are strings as per {@link Profiler::mark()}.
     *
     * @return  array  Array of profiler marks
     *
     * @since   1.7.0
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * Sets the start time.
     *
     * @param   double  $startTime  Unix timestamp in microseconds for setting the Profiler start time.
     * @param   int     $startMem   Memory amount in bytes for setting the Profiler start memory.
     *
     * @return  $this   For chaining
     *
     * @since   3.0.0
     */
    public function setStart($startTime = 0.0, $startMem = 0)
    {
        $this->start       = (double) $startTime;
        $this->previousMem = (int) $startMem / 1048576;

        return $this;
    }
}
