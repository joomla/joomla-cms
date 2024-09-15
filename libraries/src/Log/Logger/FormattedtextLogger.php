<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Log\Logger;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Log\Logger;
use Joomla\CMS\Version;
use Joomla\Filesystem\Exception\FilesystemException;
use Joomla\Filesystem\File;
use Joomla\Utilities\IpHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Formatted Text File Log class
 *
 * This class is designed to use as a base for building formatted text files for output. By
 * default it emulates the Syslog style format output. This is a disk based output format.
 *
 * @since  1.7.0
 */
class FormattedtextLogger extends Logger
{
    /**
     * The format which each entry follows in the log file.
     *
     * All fields must be named in all caps and be within curly brackets eg. {FOOBAR}.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $format = '{DATETIME}	{PRIORITY} {CLIENTIP}	{CATEGORY}	{MESSAGE}';

    /**
     * The parsed fields from the format string.
     *
     * @var    array
     * @since  1.7.0
     */
    protected $fields = [];

    /**
     * The full filesystem path for the log file.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $path;

    /**
     * If true, all writes will be deferred as long as possible.
     * NOTE: Deferred logs may never be written if the application encounters a fatal error.
     *
     * @var    boolean
     * @since  3.9.0
     */
    protected $defer = false;

    /**
     * If deferring, entries will be stored here prior to writing.
     *
     * @var    array
     * @since  3.9.0
     */
    protected $deferredEntries = [];

    /**
     * Constructor.
     *
     * @param   array  &$options  Log object options.
     *
     * @since   1.7.0
     */
    public function __construct(array &$options)
    {
        // Call the parent constructor.
        parent::__construct($options);

        // The name of the text file defaults to 'error.php' if not explicitly given.
        if (empty($this->options['text_file'])) {
            $this->options['text_file'] = 'error.php';
        }

        // The name of the text file path defaults to that which is set in configuration if not explicitly given.
        if (empty($this->options['text_file_path'])) {
            $this->options['text_file_path'] = Factory::getApplication()->get('log_path', JPATH_ADMINISTRATOR . '/logs');
        }

        // False to treat the log file as a php file.
        if (empty($this->options['text_file_no_php'])) {
            $this->options['text_file_no_php'] = false;
        }

        // Build the full path to the log file.
        $this->path = $this->options['text_file_path'] . '/' . $this->options['text_file'];

        // Use the default entry format unless explicitly set otherwise.
        if (!empty($this->options['text_entry_format'])) {
            $this->format = (string) $this->options['text_entry_format'];
        }

        // Wait as long as possible before writing logs
        if (!empty($this->options['defer'])) {
            $this->defer = (bool) $this->options['defer'];
        }

        // Build the fields array based on the format string.
        $this->parseFields();
    }

    /**
     * If deferred, write all pending logs.
     *
     * @since  3.9.0
     */
    public function __destruct()
    {
        // Nothing to do
        if (!$this->defer || empty($this->deferredEntries)) {
            return;
        }

        // Initialise the file if not already done.
        $this->initFile();

        // Format all lines and write to file.
        $lines = array_map([$this, 'formatLine'], $this->deferredEntries);

        try {
            File::write($this->path, implode("\n", $lines) . "\n", false, true);
        } catch (FilesystemException $exception) {
            throw new \RuntimeException('Cannot write to log file.', 500, $exception);
        }
    }

    /**
     * Method to add an entry to the log.
     *
     * @param   LogEntry  $entry  The log entry object to add to the log.
     *
     * @return  void
     *
     * @since   1.7.0
     * @throws  \RuntimeException
     */
    public function addEntry(LogEntry $entry)
    {
        // Store the entry to be written later.
        if ($this->defer) {
            $this->deferredEntries[] = $entry;
        } else {
            // Write it immediately.
            // Initialise the file if not already done.
            $this->initFile();

            // Write the new entry to the file.
            $line = $this->formatLine($entry);
            $line .= "\n";

            try {
                File::write($this->path, $line, false, true);
            } catch (FilesystemException $exception) {
                throw new \RuntimeException('Cannot write to log file.', 500, $exception);
            }
        }
    }

    /**
     * Format a line for the log file.
     *
     * @param   LogEntry  $entry  The log entry to format as a string.
     *
     * @return  String
     *
     * @since  3.9.0
     */
    protected function formatLine(LogEntry $entry)
    {
        // Set some default field values if not already set.
        if (!isset($entry->clientIP)) {
            $ip = IpHelper::getIp();

            if ($ip !== '') {
                $entry->clientIP = $ip;
            }
        }

        // If the time field is missing or the date field isn't only the date we need to rework it.
        if ((\strlen($entry->date) != 10) || !isset($entry->time)) {
            // Get the date and time strings in GMT.
            $entry->datetime = $entry->date->toISO8601();
            $entry->time     = $entry->date->format('H:i:s', false);
            $entry->date     = $entry->date->format('Y-m-d', false);
        }

        // Get a list of all the entry keys and make sure they are upper case.
        $tmp = array_change_key_case(get_object_vars($entry), CASE_UPPER);

        // Decode the entry priority into an English string.
        $tmp['PRIORITY'] = $this->priorities[$entry->priority];

        // Fill in field data for the line.
        $line = $this->format;

        foreach ($this->fields as $field) {
            $line = str_replace('{' . $field . '}', $tmp[$field] ?? '-', $line);
        }

        return $line;
    }

    /**
     * Method to generate the log file header.
     *
     * @return  string  The log file header
     *
     * @since   1.7.0
     */
    protected function generateFileHeader()
    {
        $head = [];

        // Build the log file header.

        // If the no php flag is not set add the php die statement.
        if (empty($this->options['text_file_no_php'])) {
            // Blank line to prevent information disclose: https://bugs.php.net/bug.php?id=60677
            $head[] = '#';
            $head[] = '#<?php die(\'Forbidden.\'); ?>';
        }

        $head[] = '#Date: ' . gmdate('Y-m-d H:i:s') . ' UTC';
        $head[] = '#Software: ' . (new Version())->getLongVersion();
        $head[] = '';

        // Prepare the fields string
        $head[] = '#Fields: ' . strtolower(str_replace('}', '', str_replace('{', '', $this->format)));
        $head[] = '';

        return implode("\n", $head);
    }

    /**
     * Method to initialise the log file.  This will create the folder path to the file if it doesn't already
     * exist and also get a new file header if the file doesn't already exist.  If the file already exists it
     * will simply open it for writing.
     *
     * @return  void
     *
     * @since   1.7.0
     * @throws  \RuntimeException
     */
    protected function initFile()
    {
        // We only need to make sure the file exists
        if (is_file($this->path)) {
            return;
        }

        // Make sure the folder exists in which to create the log file.
        Folder::create(\dirname($this->path));

        // Build the log file header.
        $head = $this->generateFileHeader();

        try {
            File::write($this->path, $head);
        } catch (FilesystemException $exception) {
            throw new \RuntimeException('Cannot write to log file.', 500, $exception);
        }
    }

    /**
     * Method to parse the format string into an array of fields.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    protected function parseFields()
    {
        $this->fields = [];
        $matches      = [];

        // Get all of the available fields in the format string.
        preg_match_all('/{(.*?)}/i', $this->format, $matches);

        // Build the parsed fields list based on the found fields.
        foreach ($matches[1] as $match) {
            $this->fields[] = strtoupper($match);
        }
    }
}
