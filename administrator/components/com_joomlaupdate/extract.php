<?php

/**
 * @package         Joomla.Administrator
 * @subpackage      com_joomlaupdate
 *
 * @copyright       (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

/**
 * Should you want to debug this file, please add a new line ABOVE this comment with the following
 * contents (excluding the space star space at the start of this line):
 *
 * define('_JOOMLA_UPDATE_DEBUG', 1);
 *
 * This will do two things:
 * - it will create the joomla_update.txt file in your site's temporary directory (default: tmp).
 *   This file contains a debug log, detailing everything extract.php is doing during the extraction
 *   of the Joomla update ZIP file.
 * - It will prevent extract.php from being overwritten during the update with a new version. This
 *   is useful if you are testing any changes in extract.php you do not want accidentally
 *   overwritten, or if you are given a modified extract.php by a Joomla core contributor with
 *   changes which might fix your update problem.
 */
\define('_JOOMLA_UPDATE', 1);

/**
 * ZIP archive extraction class
 *
 * This is a derivative work of Akeeba Restore which is Copyright (c)2008-2021 Nicholas K.
 * Dionysopoulos and Akeeba Ltd, distributed under the terms of GNU General Public License version 3
 * or later.
 *
 * The author of the original work has decided to relicense the derivative work under the terms of
 * the GNU General Public License version 2 or later and share the copyright of the derivative work
 * with Open Source Matters, Inc (OSM), granting OSM non-exclusive rights to this work per the terms
 * of the Joomla Contributor Agreement (JCA) the author signed back in 2011 and which is still in
 * effect. This is affirmed by the cryptographically signed commits in the Git repository containing
 * this file, the copyright messages and this notice here.
 *
 * @since  4.0.4
 */
class ZIPExtraction
{
    /**
     * How much data to read at once when processing files
     *
     * @var   int
     * @since 4.0.4
     */
    private const CHUNK_SIZE = 524288;

    /**
     * Maximum execution time (seconds).
     *
     * Each page load will take at most this much time. Please note that if the ZIP archive contains fairly large,
     * compressed files we may overshoot this time since we can't interrupt the decompression. This should not be an
     * issue in the context of updating Joomla as the ZIP archive contains fairly small files.
     *
     * If this is too low it will cause too many requests to hit the server, potentially triggering a DoS protection and
     * causing the extraction to fail. If this is too big the extraction will not be as verbose and the user might think
     * something is broken. A value between 3 and 7 seconds is, therefore, recommended.
     *
     * @var   int
     * @since 4.0.4
     */
    private const MAX_EXEC_TIME = 4;

    /**
     * Run-time execution bias (percentage points).
     *
     * We evaluate the time remaining on the timer before processing each file on the ZIP archive. If we have already
     * consumed at least this much percentage of the MAX_EXEC_TIME we will stop processing the archive in this page
     * load, return the result to the client and wait for it to call us again so we can resume the extraction.
     *
     * This becomes important when the MAX_EXEC_TIME is close to the PHP, PHP-FPM or Apache timeout on the server
     * (whichever is lowest) and there are fairly large files in the backup archive. If we start extracting a large,
     * compressed file close to a hard server timeout it's possible that we will overshoot that hard timeout and see the
     * extraction failing.
     *
     * Since Joomla Update is used to extract a ZIP archive with many small files we can keep at a fairly high 90%
     * without much fear that something will break.
     *
     * Example: if MAX_EXEC_TIME is 10 seconds and RUNTIME_BIAS is 80 each page load will take between 80% and 100% of
     * the MAX_EXEC_TIME, i.e. anywhere between 8 and 10 seconds.
     *
     * Lower values make it less likely to overshoot MAX_EXEC_TIME when extracting large files.
     *
     * @var   int
     * @since 4.0.4
     */
    private const RUNTIME_BIAS = 90;

    /**
     * Minimum execution time (seconds).
     *
     * A request cannot take less than this many seconds. If it does, we add “dead time” (sleep) where the script does
     * nothing except wait. This is essentially a rate limiting feature to avoid hitting a server-side DoS protection
     * which could be triggered if we ended up sending too many requests in a limited amount of time.
     *
     * This should normally be less than MAX_EXEC * (RUNTIME_BIAS / 100). Values between that and MAX_EXEC_TIME have the
     * effect of almost always adding dead time in each request, unless a really large file is being extracted from the
     * ZIP archive. Values larger than MAX_EXEC will always add dead time to the request. This can be useful to
     * artificially reduce the CPU usage limit. Some servers might kill the request if they see a sustained CPU usage
     * spike over a short period of time.
     *
     * The chosen value of 3 seconds belongs to the first category, essentially making sure that we have a decent rate
     * limiting without annoying the user too much but also catering for the most badly configured of shared
     * hosting. It's a happy medium which works for the majority (~90%) of commercial servers out there.
     *
     * @var   int
     * @since 4.0.4
     */
    private const MIN_EXEC_TIME = 3;

    /**
     * Internal state when extracting files: we need to be initialised
     *
     * @var   int
     * @since 4.0.4
     */
    private const AK_STATE_INITIALIZE = -1;

    /**
     * Internal state when extracting files: no file currently being extracted
     *
     * @var   int
     * @since 4.0.4
     */
    private const AK_STATE_NOFILE = 0;

    /**
     * Internal state when extracting files: reading the file header
     *
     * @var   int
     * @since 4.0.4
     */
    private const AK_STATE_HEADER = 1;

    /**
     * Internal state when extracting files: reading file data
     *
     * @var   int
     * @since 4.0.4
     */
    private const AK_STATE_DATA = 2;

    /**
     * Internal state when extracting files: file data has been read thoroughly
     *
     * @var   int
     * @since 4.0.4
     */
    private const AK_STATE_DATAREAD = 3;

    /**
     * Internal state when extracting files: post-processing the file
     *
     * @var   int
     * @since 4.0.4
     */
    private const AK_STATE_POSTPROC = 4;

    /**
     * Internal state when extracting files: done with this file
     *
     * @var   int
     * @since 4.0.4
     */
    private const AK_STATE_DONE = 5;

    /**
     * Internal state when extracting files: finished extracting the ZIP file
     *
     * @var   int
     * @since 4.0.4
     */
    private const AK_STATE_FINISHED = 999;

    /**
     * Internal logging level: debug
     *
     * @var   int
     * @since 4.0.4
     */
    private const LOG_DEBUG = 1;

    /**
     * Internal logging level: information
     *
     * @var   int
     * @since 4.0.4
     */
    private const LOG_INFO = 10;

    /**
     * Internal logging level: warning
     *
     * @var   int
     * @since 4.0.4
     */
    private const LOG_WARNING = 50;

    /**
     * Internal logging level: error
     *
     * @var   int
     * @since 4.0.4
     */
    private const LOG_ERROR = 90;

    /**
     * Singleton instance
     *
     * @var   null|self
     * @since 4.0.4
     */
    private static $instance = null;

    /**
     * Debug log file pointer resource
     *
     * @var   null|resource|boolean
     * @since 4.0.4
     */
    private static $logFP = null;

    /**
     * Debug log filename
     *
     * @var   null|string
     * @since 4.0.4
     */
    private static $logFilePath = null;

    /**
     * The total size of the ZIP archive
     *
     * @var   integer
     * @since 4.0.4
     */
    public $totalSize = 0;

    /**
     * Which files to skip
     *
     * @var   array
     * @since 4.0.4
     */
    public $skipFiles = [];

    /**
     * Current tally of compressed size read
     *
     * @var   integer
     * @since 4.0.4
     */
    public $compressedTotal = 0;

    /**
     * Current tally of bytes written to disk
     *
     * @var   integer
     * @since 4.0.4
     */
    public $uncompressedTotal = 0;

    /**
     * Current tally of files extracted
     *
     * @var   integer
     * @since 4.0.4
     */
    public $filesProcessed = 0;

    /**
     * Maximum execution time allowance per step
     *
     * @var   integer
     * @since 4.0.4
     */
    private $maxExecTime = null;

    /**
     * Timestamp of execution start
     *
     * @var   integer
     * @since 4.0.4
     */
    private $startTime;

    /**
     * The last error message
     *
     * @var   string|null
     * @since 4.0.4
     */
    private $lastErrorMessage = null;

    /**
     * Archive filename
     *
     * @var   string
     * @since 4.0.4
     */
    private $filename = null;

    /**
     * Current archive part number
     *
     * @var   boolean
     * @since 4.0.4
     */
    private $archiveFileIsBeingRead = false;

    /**
     * The offset inside the current part
     *
     * @var   integer
     * @since 4.0.4
     */
    private $currentOffset = 0;

    /**
     * Absolute path to prepend to extracted files
     *
     * @var   string
     * @since 4.0.4
     */
    private $addPath = '';

    /**
     * File pointer to the current archive part file
     *
     * @var   resource|null
     * @since 4.0.4
     */
    private $fp = null;

    /**
     * Run state when processing the current archive file
     *
     * @var   integer
     * @since 4.0.4
     */
    private $runState = self::AK_STATE_INITIALIZE;

    /**
     * File header data, as read by the readFileHeader() method
     *
     * @var   stdClass
     * @since 4.0.4
     */
    private $fileHeader = null;

    /**
     * How much of the uncompressed data we've read so far
     *
     * @var   integer
     * @since 4.0.4
     */
    private $dataReadLength = 0;

    /**
     * Unwritable files in these directories are always ignored and do not cause errors when not
     * extracted.
     *
     * @var   array
     * @since 4.0.4
     */
    private $ignoreDirectories = [];

    /**
     * Internal flag, set when the ZIP file has a data descriptor (which we will be ignoring)
     *
     * @var   boolean
     * @since 4.0.4
     */
    private $expectDataDescriptor = false;

    /**
     * The UNIX last modification timestamp of the file last extracted
     *
     * @var   integer
     * @since 4.0.4
     */
    private $lastExtractedFileTimestamp = 0;

    /**
     * The file path of the file last extracted
     *
     * @var   string
     * @since 4.0.4
     */
    private $lastExtractedFilename = null;

    /**
     * Public constructor.
     *
     * Sets up the internal timer.
     *
     * @since   4.0.4
     */
    public function __construct()
    {
        $this->setupMaxExecTime();

        // Initialize start time
        $this->startTime = microtime(true);
    }

    /**
     * Singleton implementation.
     *
     * @return  static
     * @since   4.0.4
     */
    public static function getInstance(): self
    {
        if (\is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Returns a serialised copy of the object.
     *
     * This is different to calling serialise() directly. This operates on a copy of the object which undergoes a
     * call to shutdown() first so any open files are closed first.
     *
     * @return  string  The serialised data, potentially base64 encoded.
     * @since   4.0.4
     */
    public static function getSerialised(): string
    {
        $clone = clone self::getInstance();
        $clone->shutdown();
        $serialized = serialize($clone);

        return (\function_exists('base64_encode') && \function_exists('base64_decode')) ? base64_encode($serialized) : $serialized;
    }

    /**
     * Restores a serialised instance into the singleton implementation and returns it.
     *
     * If the serialised data is corrupt it will return null.
     *
     * @param   string  $serialised  The serialised data, potentially base64 encoded, to deserialize.
     *
     * @return  static|null  The instance of the object, NULL if it cannot be deserialised.
     * @since   4.0.4
     */
    public static function unserialiseInstance(string $serialised): ?self
    {
        if (\function_exists('base64_encode') && \function_exists('base64_decode')) {
            $serialised = base64_decode($serialised);
        }

        $instance = @unserialize($serialised, [
                'allowed_classes' => [
                    self::class,
                    stdClass::class,
                ],
            ]);

        if (($instance === false) || !\is_object($instance) || !($instance instanceof self)) {
            return null;
        }

        self::$instance = $instance;

        return self::$instance;
    }

    /**
     * Wakeup function, called whenever the class is deserialized.
     *
     * This method does the following:
     * - Restart the timer.
     * - Reopen the archive file, if one is defined.
     * - Seek to the correct offset of the file.
     *
     * @return  void
     * @since   4.0.4
     * @internal
     */
    public function __wakeup(): void
    {
        // Reset the timer when deserializing the object.
        $this->startTime = microtime(true);

        if (!$this->archiveFileIsBeingRead) {
            return;
        }

        $this->fp = @fopen($this->filename, 'rb');

        if ((\is_resource($this->fp)) && ($this->currentOffset > 0)) {
            @fseek($this->fp, $this->currentOffset);
        }
    }

    /**
     * Enforce the minimum execution time.
     *
     * @return  void
     * @since   4.0.4
     */
    public function enforceMinimumExecutionTime()
    {
        $elapsed     = $this->getRunningTime() * 1000;
        $minExecTime = 1000.0 * min(1, (min(self::MIN_EXEC_TIME, $this->getPhpMaxExecTime()) - 1));

        // Only run a sleep delay if we haven't reached the minimum execution time
        if (($minExecTime <= $elapsed) || ($elapsed <= 0)) {
            return;
        }

        $sleepMillisec = \intval($minExecTime - $elapsed);

        /**
         * If we need to sleep for more than 1 second we should be using sleep() or time_sleep_until() to prevent high
         * CPU usage, also because some OS might not support sleeping for over 1 second using these functions. In all
         * other cases we will try to use usleep or time_nanosleep instead.
         */
        $longSleep          = $sleepMillisec > 1000;
        $miniSleepSupported = \function_exists('usleep') || \function_exists('time_nanosleep');

        if (!$longSleep && $miniSleepSupported) {
            if (\function_exists('usleep') && ($sleepMillisec < 1000)) {
                usleep(1000 * $sleepMillisec);

                return;
            }

            if (\function_exists('time_nanosleep') && ($sleepMillisec < 1000)) {
                time_nanosleep(0, 1000000 * $sleepMillisec);

                return;
            }
        }

        if (\function_exists('sleep')) {
            sleep(ceil($sleepMillisec / 1000));

            return;
        }

        if (\function_exists('time_sleep_until')) {
            time_sleep_until(time() + ceil($sleepMillisec / 1000));
        }
    }

    /**
     * Set the filepath to the ZIP archive which will be extracted.
     *
     * @param   string  $value  The filepath to the archive. Only LOCAL files are allowed!
     *
     * @return  void
     * @since   4.0.4
     */
    public function setFilename(string $value)
    {
        // Security check: disallow remote filenames
        if (!empty($value) && strpos($value, '://') !== false) {
            $this->setError('Invalid archive location');

            return;
        }

        $this->filename = $value;
        $this->initializeLog(\dirname($this->filename));
    }

    /**
     * Sets the path to prefix all extracted files with. Essentially, where the archive will be extracted to.
     *
     * @param   string  $addPath  The path where the archive will be extracted.
     *
     * @return  void
     * @since   4.0.4
     */
    public function setAddPath(string $addPath): void
    {
        $this->addPath = $addPath;
        $this->addPath = str_replace('\\', '/', $this->addPath);
        $this->addPath = rtrim($this->addPath, '/');

        if (!empty($this->addPath)) {
            $this->addPath .= '/';
        }
    }

    /**
     * Set the list of files to skip when extracting the ZIP file.
     *
     * @param   array  $skipFiles  A list of files to skip when extracting the ZIP archive
     *
     * @return  void
     * @since   4.0.4
     */
    public function setSkipFiles(array $skipFiles): void
    {
        $this->skipFiles = array_values($skipFiles);
    }

    /**
     * Set the directories to skip over when extracting the ZIP archive
     *
     * @param   array  $ignoreDirectories  The list of directories to ignore.
     *
     * @return  void
     * @since   4.0.4
     */
    public function setIgnoreDirectories(array $ignoreDirectories): void
    {
        $this->ignoreDirectories = array_values($ignoreDirectories);
    }

    /**
     * Prepares for the archive extraction
     *
     * @return  void
     * @since   4.0.4
     */
    public function initialize(): void
    {
        $this->debugMsg(\sprintf('Initializing extraction. Filepath: %s', $this->filename));
        $this->totalSize              = @filesize($this->filename) ?: 0;
        $this->archiveFileIsBeingRead = false;
        $this->currentOffset          = 0;
        $this->runState               = self::AK_STATE_NOFILE;

        $this->readArchiveHeader();

        if (!empty($this->getError())) {
            $this->debugMsg(\sprintf('Error: %s', $this->getError()), self::LOG_ERROR);

            return;
        }

        $this->archiveFileIsBeingRead = true;
        $this->runState               = self::AK_STATE_NOFILE;

        $this->debugMsg('Setting state to NOFILE', self::LOG_DEBUG);
    }

    /**
     * Executes a step of the archive extraction
     *
     * @return  boolean  True if we are done extracting or an error occurred
     * @since   4.0.4
     */
    public function step(): bool
    {
        $status = true;

        $this->debugMsg('Starting a new step', self::LOG_INFO);

        while ($status && ($this->getTimeLeft() > 0)) {
            switch ($this->runState) {
                case self::AK_STATE_INITIALIZE:
                    $this->debugMsg('Current run state: INITIALIZE', self::LOG_DEBUG);
                    $this->initialize();
                    break;

                case self::AK_STATE_NOFILE:
                    $this->debugMsg('Current run state: NOFILE', self::LOG_DEBUG);
                    $status = $this->readFileHeader();

                    if ($status) {
                        $this->debugMsg('Found file header; updating number of files processed and bytes in/out', self::LOG_DEBUG);

                        // Update running tallies when we start extracting a file
                        $this->filesProcessed++;
                        $this->compressedTotal += \array_key_exists('compressed', get_object_vars($this->fileHeader))
                            ? $this->fileHeader->compressed : 0;
                        $this->uncompressedTotal += $this->fileHeader->uncompressed;
                    }

                    break;

                case self::AK_STATE_HEADER:
                case self::AK_STATE_DATA:
                    $runStateHuman = $this->runState === self::AK_STATE_HEADER ? 'HEADER' : 'DATA';
                    $this->debugMsg(\sprintf('Current run state: %s', $runStateHuman), self::LOG_DEBUG);

                    $status = $this->processFileData();
                    break;

                case self::AK_STATE_DATAREAD:
                case self::AK_STATE_POSTPROC:
                    $runStateHuman = $this->runState === self::AK_STATE_DATAREAD ? 'DATAREAD' : 'POSTPROC';
                    $this->debugMsg(\sprintf('Current run state: %s', $runStateHuman), self::LOG_DEBUG);

                    $this->setLastExtractedFileTimestamp($this->fileHeader->timestamp);
                    $this->processLastExtractedFile();

                    $status         = true;
                    $this->runState = self::AK_STATE_DONE;
                    break;

                case self::AK_STATE_DONE:
                default:
                    $this->debugMsg('Current run state: DONE', self::LOG_DEBUG);
                    $this->runState = self::AK_STATE_NOFILE;

                    break;

                case self::AK_STATE_FINISHED:
                    $this->debugMsg('Current run state: FINISHED', self::LOG_DEBUG);
                    $status = false;
                    break;
            }

            if ($this->getTimeLeft() <= 0) {
                $this->debugMsg('Ran out of time; the step will break.');
            } elseif (!$status) {
                $this->debugMsg('Last step status is false; the step will break.');
            }
        }

        $error = $this->getError();

        if (!empty($error)) {
            $this->debugMsg(\sprintf('Step failed with error: %s', $error), self::LOG_ERROR);
        }

        // Did we just finish or run into an error?
        if (!empty($error) || $this->runState === self::AK_STATE_FINISHED) {
            $this->debugMsg('Returning true (must stop running) from step()', self::LOG_DEBUG);

            // Reset internal state, prevents __wakeup from trying to open a non-existent file
            $this->archiveFileIsBeingRead = false;

            return true;
        }

        $this->debugMsg('Returning false (must continue running) from step()', self::LOG_DEBUG);

        return false;
    }

    /**
     * Get the most recent error message
     *
     * @return  string|null  The message string, null if there's no error
     * @since   4.0.4
     */
    public function getError(): ?string
    {
        return $this->lastErrorMessage;
    }

    /**
     * Gets the number of seconds left, before we hit the "must break" threshold
     *
     * @return  float
     * @since   4.0.4
     */
    private function getTimeLeft(): float
    {
        return $this->maxExecTime - $this->getRunningTime();
    }

    /**
     * Gets the time elapsed since object creation/unserialization, effectively how
     * long Akeeba Engine has been processing data
     *
     * @return  float
     * @since   4.0.4
     */
    private function getRunningTime(): float
    {
        return microtime(true) - $this->startTime;
    }

    /**
     * Process the last extracted file or directory
     *
     * This invalidates OPcache for .php files. Also applies the correct permissions and timestamp.
     *
     * @return  void
     * @since   4.0.4
     */
    private function processLastExtractedFile(): void
    {
        $this->debugMsg(\sprintf('Processing last extracted entity: %s', $this->lastExtractedFilename), self::LOG_DEBUG);

        if (@is_file($this->lastExtractedFilename)) {
            @chmod($this->lastExtractedFilename, 0644);

            clearFileInOPCache($this->lastExtractedFilename);
        } else {
            @chmod($this->lastExtractedFilename, 0755);
        }

        if ($this->lastExtractedFileTimestamp > 0) {
            @touch($this->lastExtractedFilename, $this->lastExtractedFileTimestamp);
        }
    }

    /**
     * Set the last extracted filename
     *
     * @param   string|null  $lastExtractedFilename  The last extracted filename
     *
     * @return  void
     * @since   4.0.4
     */
    private function setLastExtractedFilename(?string $lastExtractedFilename): void
    {
        $this->lastExtractedFilename = $lastExtractedFilename;
    }

    /**
     * Set the last modification UNIX timestamp for the last extracted file
     *
     * @param   int  $lastExtractedFileTimestamp  The timestamp
     *
     * @return  void
     * @since   4.0.4
     */
    private function setLastExtractedFileTimestamp(int $lastExtractedFileTimestamp): void
    {
        $this->lastExtractedFileTimestamp = $lastExtractedFileTimestamp;
    }

    /**
     * Sleep function, called whenever the class is serialized
     *
     * @return  void
     * @since   4.0.4
     * @internal
     */
    private function shutdown(): void
    {
        if (\is_resource(self::$logFP)) {
            @fclose(self::$logFP);
        }

        if (!\is_resource($this->fp)) {
            return;
        }

        $this->currentOffset = @ftell($this->fp);

        @fclose($this->fp);
    }

    /**
     * Unicode-safe binary data length
     *
     * @param   string|null  $string  The binary data to get the length for
     *
     * @return  integer
     * @since   4.0.4
     */
    private function binStringLength(?string $string): int
    {
        if (\is_null($string)) {
            return 0;
        }

        if (\function_exists('mb_strlen')) {
            return mb_strlen($string, '8bit') ?: 0;
        }

        return \strlen($string) ?: 0;
    }

    /**
     * Add an error message
     *
     * @param   string  $error  Error message
     *
     * @return  void
     * @since   4.0.4
     */
    private function setError(string $error): void
    {
        $this->lastErrorMessage = $error;
    }

    /**
     * Reads data from the archive.
     *
     * @param   resource  $fp      The file pointer to read data from
     * @param   int|null  $length  The volume of data to read, in bytes
     *
     * @return  string  The data read from the file
     * @since   4.0.4
     */
    private function fread($fp, ?int $length = null): string
    {
        $readLength = (is_numeric($length) && ($length > 0)) ? $length : PHP_INT_MAX;
        $data       = fread($fp, $readLength);

        if ($data === false) {
            $this->debugMsg('No more data could be read from the file', self::LOG_WARNING);

            $data = '';
        }

        return $data;
    }

    /**
     * Read the header of the archive, making sure it's a valid ZIP file.
     *
     * @return  void
     * @since   4.0.4
     */
    private function readArchiveHeader(): void
    {
        $this->debugMsg('Reading the archive header.', self::LOG_DEBUG);

        // Open the first part
        $this->openArchiveFile();

        // Fail for unreadable files
        if ($this->fp === false) {
            return;
        }

        // Read the header data.
        $sigBinary  = fread($this->fp, 4);
        $headerData = unpack('Vsig', $sigBinary);

        // We only support single part ZIP files
        if ($headerData['sig'] != 0x04034b50) {
            $this->setError('The archive file is corrupt: bad header');

            return;
        }

        // Roll back the file pointer
        fseek($this->fp, -4, SEEK_CUR);

        $this->currentOffset  = @ftell($this->fp);
        $this->dataReadLength = 0;
    }

    /**
     * Concrete classes must use this method to read the file header
     *
     * @return boolean True if reading the file was successful, false if an error occurred or we
     *                 reached end of archive.
     * @since   4.0.4
     */
    private function readFileHeader(): bool
    {
        $this->debugMsg('Reading the file entry header.', self::LOG_DEBUG);

        if (!\is_resource($this->fp)) {
            $this->setError('The archive is not open for reading.');

            return false;
        }

        // Unexpected end of file
        if ($this->isEOF()) {
            $this->debugMsg('EOF when reading file header data', self::LOG_WARNING);
            $this->setError('The archive is corrupt or truncated');

            return false;
        }

        $this->currentOffset = ftell($this->fp);

        if ($this->expectDataDescriptor) {
            $this->debugMsg('Expecting data descriptor (bit 3 of general purpose flag was set).', self::LOG_DEBUG);

            /**
             * The last file had bit 3 of the general purpose bit flag set. This means that we have a 12 byte data
             * descriptor we need to skip. To make things worse, there might also be a 4 byte optional data descriptor
             * header (0x08074b50).
             */
            $junk       = @fread($this->fp, 4);
            $junk       = unpack('Vsig', $junk);
            $readLength = ($junk['sig'] == 0x08074b50) ? 12 : 8;
            $junk       = @fread($this->fp, $readLength);

            // And check for EOF, too
            if ($this->isEOF()) {
                $this->debugMsg('EOF when reading data descriptor', self::LOG_WARNING);
                $this->setError('The archive is corrupt or truncated');

                return false;
            }
        }

        // Get and decode Local File Header
        $headerBinary = fread($this->fp, 30);
        $format       = 'Vsig/C2ver/vbitflag/vcompmethod/vlastmodtime/vlastmoddate/Vcrc/Vcompsize/'
            . 'Vuncomp/vfnamelen/veflen';
        $headerData   = unpack($format, $headerBinary);

        // Check signature
        if (!($headerData['sig'] == 0x04034b50)) {
            // The signature is not the one used for files. Is this a central directory record (i.e. we're done)?
            if ($headerData['sig'] == 0x02014b50) {
                $this->debugMsg('Found Central Directory header; the extraction is complete', self::LOG_DEBUG);

                // End of ZIP file detected. We'll just skip to the end of file...
                @fseek($this->fp, 0, SEEK_END);
                $this->runState = self::AK_STATE_FINISHED;

                return false;
            }

            $this->setError('The archive file is corrupt or truncated');

            return false;
        }

        // If bit 3 of the bitflag is set, expectDataDescriptor is true
        $this->expectDataDescriptor  = ($headerData['bitflag'] & 4) == 4;
        $this->fileHeader            = new stdClass();
        $this->fileHeader->timestamp = 0;

        // Read the last modified date and time
        $lastmodtime = $headerData['lastmodtime'];
        $lastmoddate = $headerData['lastmoddate'];

        if ($lastmoddate && $lastmodtime) {
            $vHour    = ($lastmodtime & 0xF800) >> 11;
            $vMInute  = ($lastmodtime & 0x07E0) >> 5;
            $vSeconds = ($lastmodtime & 0x001F) * 2;
            $vYear    = (($lastmoddate & 0xFE00) >> 9) + 1980;
            $vMonth   = ($lastmoddate & 0x01E0) >> 5;
            $vDay     = $lastmoddate & 0x001F;

            $this->fileHeader->timestamp = @mktime($vHour, $vMInute, $vSeconds, $vMonth, $vDay, $vYear);
        }

        $isBannedFile = false;

        $this->fileHeader->compressed   = $headerData['compsize'];
        $this->fileHeader->uncompressed = $headerData['uncomp'];
        $nameFieldLength                = $headerData['fnamelen'];
        $extraFieldLength               = $headerData['eflen'];

        // Read filename field
        $this->fileHeader->file = fread($this->fp, $nameFieldLength);

        // Read extra field if present
        if ($extraFieldLength > 0) {
            $extrafield = fread($this->fp, $extraFieldLength);
        }

        // Decide filetype -- Check for directories
        $this->fileHeader->type = 'file';

        if (strrpos($this->fileHeader->file, '/') == \strlen($this->fileHeader->file) - 1) {
            $this->fileHeader->type = 'dir';
        }

        // Decide filetype -- Check for symbolic links
        if (($headerData['ver1'] == 10) && ($headerData['ver2'] == 3)) {
            $this->fileHeader->type = 'link';
        }

        switch ($headerData['compmethod']) {
            case 0:
                $this->fileHeader->compression = 'none';
                break;
            case 8:
                $this->fileHeader->compression = 'gzip';
                break;
            default:
                $messageTemplate = 'This script cannot handle ZIP compression method %d. '
                    . 'Only 0 (no compression) and 8 (DEFLATE, gzip) can be handled.';
                $actualMessage   = \sprintf($messageTemplate, $headerData['compmethod']);
                $this->setError($actualMessage);

                return false;
        }

        // Find hard-coded banned files
        if ((basename($this->fileHeader->file) == ".") || (basename($this->fileHeader->file) == "..")) {
            $isBannedFile = true;
        }

        // Also try to find banned files passed in class configuration
        if ((\count($this->skipFiles) > 0) && \in_array($this->fileHeader->file, $this->skipFiles)) {
            $isBannedFile = true;
        }

        // If we have a banned file, let's skip it
        if ($isBannedFile) {
            $debugMessage = \sprintf('Current entity (%s) is banned from extraction and will be skipped over.', $this->fileHeader->file);
            $this->debugMsg($debugMessage, self::LOG_DEBUG);

            // Advance the file pointer, skipping exactly the size of the compressed data
            $seekleft = $this->fileHeader->compressed;

            while ($seekleft > 0) {
                // Ensure that we can seek past archive part boundaries
                $curSize = @filesize($this->filename);
                $curPos  = @ftell($this->fp);
                $canSeek = $curSize - $curPos;
                $canSeek = ($canSeek > $seekleft) ? $seekleft : $canSeek;
                @fseek($this->fp, $canSeek, SEEK_CUR);
                $seekleft -= $canSeek;

                if ($seekleft) {
                    $this->setError('The archive is corrupt or truncated');

                    return false;
                }
            }

            $this->currentOffset = @ftell($this->fp);
            $this->runState      = self::AK_STATE_DONE;

            return true;
        }

        // Last chance to prepend a path to the filename
        if (!empty($this->addPath)) {
            $this->fileHeader->file = $this->addPath . $this->fileHeader->file;
        }

        // Get the translated path name
        if ($this->fileHeader->type == 'file') {
            $this->fileHeader->realFile = $this->fileHeader->file;
            $this->setLastExtractedFilename($this->fileHeader->file);
        } elseif ($this->fileHeader->type == 'dir') {
            $this->fileHeader->timestamp = 0;

            $dir = $this->fileHeader->file;

            if (!@is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $this->setLastExtractedFilename(null);
        } else {
            // Symlink; do not post-process
            $this->fileHeader->timestamp = 0;
            $this->setLastExtractedFilename(null);
        }

        $this->createDirectory();

        // Header is read
        $this->runState = self::AK_STATE_HEADER;

        return true;
    }

    /**
     * Creates the directory this file points to
     *
     * @return  void
     * @since   4.0.4
     */
    private function createDirectory(): void
    {
        // Do we need to create a directory?
        if (empty($this->fileHeader->realFile)) {
            $this->fileHeader->realFile = $this->fileHeader->file;
        }

        $lastSlash = strrpos($this->fileHeader->realFile, '/');
        $dirName   = substr($this->fileHeader->realFile, 0, $lastSlash);
        $perms     = 0755;
        $ignore    = $this->isIgnoredDirectory($dirName);

        if (@is_dir($dirName)) {
            return;
        }

        if ((@mkdir($dirName, $perms, true) === false) && (!$ignore)) {
            $this->setError(\sprintf('Could not create %s folder', $dirName));
        }
    }

    /**
     * Concrete classes must use this method to process file data. It must set $runState to self::AK_STATE_DATAREAD when
     * it's finished processing the file data.
     *
     * @return   boolean  True if processing the file data was successful, false if an error occurred
     * @since   4.0.4
     */
    private function processFileData(): bool
    {
        switch ($this->fileHeader->type) {
            case 'dir':
                $this->debugMsg('Extracting entity of type Directory', self::LOG_DEBUG);

                return $this->processTypeDir();

            case 'link':
                $this->debugMsg('Extracting entity of type Symbolic Link', self::LOG_DEBUG);

                return $this->processTypeLink();

            case 'file':
                switch ($this->fileHeader->compression) {
                    case 'none':
                        $this->debugMsg('Extracting entity of type File (Stored)', self::LOG_DEBUG);

                        return $this->processTypeFileUncompressed();

                    case 'gzip':
                    case 'bzip2':
                        $this->debugMsg('Extracting entity of type File (Compressed)', self::LOG_DEBUG);

                        return $this->processTypeFileCompressed();

                    case 'default':
                        $this->setError(\sprintf('Unknown compression type %s.', $this->fileHeader->compression));

                        return false;
                }
                break;
        }

        $this->setError(\sprintf('Unknown entry type %s.', $this->fileHeader->type));

        return false;
    }

    /**
     * Opens the next part file for reading
     *
     * @return  void
     * @since   4.0.4
     */
    private function openArchiveFile(): void
    {
        $this->debugMsg('Opening archive file for reading', self::LOG_DEBUG);

        if ($this->archiveFileIsBeingRead) {
            return;
        }

        if (\is_resource($this->fp)) {
            @fclose($this->fp);
        }

        $this->fp = @fopen($this->filename, 'rb');

        if ($this->fp === false) {
            $message = 'Could not open archive for reading. Check that the file exists, is '
                . 'readable by the web server and is not in a directory made out of reach by chroot, '
                . 'open_basedir restrictions or any other restriction put in place by your host.';
            $this->setError($message);

            return;
        }

        fseek($this->fp, 0);
        $this->currentOffset = 0;
    }

    /**
     * Returns true if we have reached the end of file
     *
     * @return   boolean  True if we have reached End Of File
     * @since   4.0.4
     */
    private function isEOF(): bool
    {
        /**
         * feof() will return false if the file pointer is exactly at the last byte of the file. However, this is a
         * condition we want to treat as a proper EOF for the purpose of extracting a ZIP file. Hence the second part
         * after the logical OR.
         */
        return @feof($this->fp) || (@ftell($this->fp) > @filesize($this->filename));
    }

    /**
     * Handles the permissions of the parent directory to a file and the file itself to make it writeable.
     *
     * @param   string  $path  A path to a file
     *
     * @return  void
     * @since   4.0.4
     */
    private function setCorrectPermissions(string $path): void
    {
        static $rootDir = null;

        if (\is_null($rootDir)) {
            $rootDir = rtrim($this->addPath, '/\\');
        }

        $directory = rtrim(\dirname($path), '/\\');

        // Is this an unwritable directory?
        if (($directory != $rootDir) && !is_writable($directory)) {
            @chmod($directory, 0755);
        }

        @chmod($path, 0644);
    }

    /**
     * Is this file or directory contained in a directory we've decided to ignore
     * write errors for? This is useful to let the extraction work despite write
     * errors in the log, logs and tmp directories which MIGHT be used by the system
     * on some low quality hosts and Plesk-powered hosts.
     *
     * @param   string  $shortFilename  The relative path of the file/directory in the package
     *
     * @return  boolean  True if it belongs in an ignored directory
     * @since   4.0.4
     */
    private function isIgnoredDirectory(string $shortFilename): bool
    {
        $check = substr($shortFilename, -1) == '/' ? rtrim($shortFilename, '/') : \dirname($shortFilename);

        return \in_array($check, $this->ignoreDirectories);
    }

    /**
     * Process the file data of a directory entry
     *
     * @return  boolean
     * @since   4.0.4
     */
    private function processTypeDir(): bool
    {
        // Directory entries do not have file data, therefore we're done processing the entry.
        $this->runState = self::AK_STATE_DATAREAD;

        return true;
    }

    /**
     * Process the file data of a link entry
     *
     * @return  boolean
     * @since   4.0.4
     */
    private function processTypeLink(): bool
    {
        $toReadBytes = 0;
        $leftBytes   = $this->fileHeader->compressed;
        $data        = '';

        while ($leftBytes > 0) {
            $toReadBytes     = min($leftBytes, self::CHUNK_SIZE);
            $mydata          = $this->fread($this->fp, $toReadBytes);
            $reallyReadBytes = $this->binStringLength($mydata);
            $data .= $mydata;
            $leftBytes -= $reallyReadBytes;

            if ($reallyReadBytes < $toReadBytes) {
                // We read less than requested!
                if ($this->isEOF()) {
                    $this->debugMsg('EOF when reading symlink data', self::LOG_WARNING);
                    $this->setError('The archive file is corrupt or truncated');

                    return false;
                }
            }
        }

        $filename = $this->fileHeader->realFile ?? $this->fileHeader->file;

        // Try to remove an existing file or directory by the same name
        if (file_exists($filename)) {
            clearFileInOPCache($filename);
            @unlink($filename);
            @rmdir($filename);
        }

        // Remove any trailing slash
        if (substr($filename, -1) == '/') {
            $filename = substr($filename, 0, -1);
        }

        // Create the symlink
        @symlink($data, $filename);

        $this->runState = self::AK_STATE_DATAREAD;

        // No matter if the link was created!
        return true;
    }

    /**
     * Processes an uncompressed (stored) file
     *
     * @return  boolean
     * @since   4.0.4
     */
    private function processTypeFileUncompressed(): bool
    {
        // Uncompressed files are being processed in small chunks, to avoid timeouts
        if ($this->dataReadLength == 0) {
            // Before processing file data, ensure permissions are adequate
            $this->setCorrectPermissions($this->fileHeader->file);

            // This file is changed during the script's operation so we clear the status cache.
            clearstatcache($this->fileHeader->file);
        }

        // Open the output file
        $ignore = $this->isIgnoredDirectory($this->fileHeader->file);

        $writeMode = ($this->dataReadLength == 0) ? 'wb' : 'ab';
        $outfp     = @fopen($this->fileHeader->realFile, $writeMode);

        // Can we write to the file?
        if (($outfp === false) && (!$ignore)) {
            // An error occurred
            $this->setError(\sprintf('Could not open %s for writing.', $this->fileHeader->realFile));

            return false;
        }

        // Does the file have any data, at all?
        if ($this->fileHeader->compressed == 0) {
            // No file data!
            if (\is_resource($outfp)) {
                @fclose($outfp);
            }

            $this->debugMsg('Zero byte Stored file; no data will be read', self::LOG_DEBUG);

            $this->runState = self::AK_STATE_DATAREAD;

            return true;
        }

        $leftBytes = $this->fileHeader->compressed - $this->dataReadLength;

        // Loop while there's data to read and enough time to do it
        while (($leftBytes > 0) && ($this->getTimeLeft() > 0)) {
            $toReadBytes          = min($leftBytes, self::CHUNK_SIZE);
            $data                 = $this->fread($this->fp, $toReadBytes);
            $reallyReadBytes      = $this->binStringLength($data);
            $leftBytes -= $reallyReadBytes;
            $this->dataReadLength += $reallyReadBytes;

            if ($reallyReadBytes < $toReadBytes) {
                // We read less than requested! Why? Did we hit local EOF?
                if ($this->isEOF()) {
                    // Nope. The archive is corrupt
                    $this->debugMsg('EOF when reading stored file data', self::LOG_WARNING);
                    $this->setError('The archive file is corrupt or truncated');

                    return false;
                }
            }

            if (\is_resource($outfp)) {
                @fwrite($outfp, $data);
            }

            if ($this->getTimeLeft()) {
                $this->debugMsg('Out of time; will resume extraction in the next step', self::LOG_DEBUG);
            }
        }

        // Close the file pointer
        if (\is_resource($outfp)) {
            @fclose($outfp);
        }

        // Was this a pre-timeout bail out?
        if ($leftBytes > 0) {
            $this->debugMsg(\sprintf('We have %d bytes left to extract in the next step', $leftBytes), self::LOG_DEBUG);
            $this->runState = self::AK_STATE_DATA;

            return true;
        }

        // Oh! We just finished!
        $this->runState       = self::AK_STATE_DATAREAD;
        $this->dataReadLength = 0;

        return true;
    }

    /**
     * Processes a compressed file
     *
     * @return  boolean
     * @since   4.0.4
     */
    private function processTypeFileCompressed(): bool
    {
        // Before processing file data, ensure permissions are adequate
        $this->setCorrectPermissions($this->fileHeader->file);

        // This file is changed during the script's operation so we clear the status cache.
        clearstatcache($this->fileHeader->file);

        // Open the output file
        $outfp = @fopen($this->fileHeader->realFile, 'wb');

        // Can we write to the file?
        $ignore = $this->isIgnoredDirectory($this->fileHeader->file);

        if (($outfp === false) && (!$ignore)) {
            // An error occurred
            $this->setError(\sprintf('Could not open %s for writing.', $this->fileHeader->realFile));

            return false;
        }

        // Does the file have any data, at all?
        if ($this->fileHeader->compressed == 0) {
            $this->debugMsg('Zero byte Compressed file; no data will be read', self::LOG_DEBUG);

            // No file data!
            if (\is_resource($outfp)) {
                @fclose($outfp);
            }

            $this->runState = self::AK_STATE_DATAREAD;

            return true;
        }

        // Simple compressed files are processed as a whole; we can't do chunk processing
        $zipData = $this->fread($this->fp, $this->fileHeader->compressed);

        if ($this->binStringLength($zipData) < $this->fileHeader->compressed) {
            // End of local file before reading all data?
            if ($this->isEOF()) {
                $this->debugMsg('EOF reading compressed data', self::LOG_WARNING);
            }

            $this->setError('The archive file is corrupt or truncated');

            return false;
        }

        switch ($this->fileHeader->compression) {
            case 'gzip':
                /** @noinspection PhpComposerExtensionStubsInspection */
                $unzipData = gzinflate($zipData);
                break;

            case 'bzip2':
                /** @noinspection PhpComposerExtensionStubsInspection */
                $unzipData = bzdecompress($zipData);
                break;

            default:
                $this->setError(\sprintf('Unknown compression method %s', $this->fileHeader->compression));

                return false;
        }

        if (!\is_string($unzipData)) {
            $this->setError('Error while decompressing data! The archive file is corrupted');

            return false;
        }

        unset($zipData);

        // Write to the file.
        if (\is_resource($outfp)) {
            $result = @fwrite($outfp, $unzipData, $this->fileHeader->uncompressed);
            @fclose($outfp);

            if ($result === false) {
                $this->setError('Could not write ' . $this->fileHeader->realFile . '!');

                return false;
            }
        }

        unset($unzipData);

        $this->runState = self::AK_STATE_DATAREAD;

        return true;
    }

    /**
     * Set up the maximum execution time
     *
     * @return  void
     * @since   4.0.4
     */
    private function setupMaxExecTime(): void
    {
        $configMaxTime     = self::MAX_EXEC_TIME;
        $bias              = self::RUNTIME_BIAS / 100;
        $this->maxExecTime = min($this->getPhpMaxExecTime(), $configMaxTime) * $bias;
    }

    /**
     * Get the PHP maximum execution time.
     *
     * If it's not defined or it's zero (infinite) we use a fake value of 10 seconds.
     *
     * @return  integer
     * @since   4.0.4
     */
    private function getPhpMaxExecTime(): int
    {
        if (!@\function_exists('ini_get')) {
            return 10;
        }

        $phpMaxTime = @\ini_get("maximum_execution_time");
        $phpMaxTime = (!is_numeric($phpMaxTime) ? 10 : @\intval($phpMaxTime)) ?: 10;

        return max(1, $phpMaxTime);
    }

    /**
     * Write a message to the debug error log
     *
     * @param   string  $message   The message to log
     * @param   int     $priority  The message's log priority
     *
     * @return  void
     * @since   4.0.4
     */
    private function debugMsg(string $message, int $priority = self::LOG_INFO): void
    {
        if (!\defined('_JOOMLA_UPDATE_DEBUG')) {
            return;
        }

        if (!\is_resource(self::$logFP) && !\is_bool(self::$logFP)) {
            self::$logFP = @fopen(self::$logFilePath, 'at');
        }

        if (!\is_resource(self::$logFP)) {
            return;
        }

        switch ($priority) {
            case self::LOG_DEBUG:
                $priorityString = 'DEBUG';
                break;

            case self::LOG_INFO:
                $priorityString = 'INFO';
                break;

            case self::LOG_WARNING:
                $priorityString = 'WARNING';
                break;

            case self::LOG_ERROR:
                $priorityString = 'ERROR';
                break;
        }

        fwrite(self::$logFP, \sprintf('%s | %7s | %s' . "\r\n", gmdate('Y-m-d H:i:s'), $priorityString, $message));
    }

    /**
     * Initialise the debug log file
     *
     * @param   string  $logPath  The path where the log file will be written to
     *
     * @return  void
     * @since   4.0.4
     */
    private function initializeLog(string $logPath): void
    {
        if (!\defined('_JOOMLA_UPDATE_DEBUG')) {
            return;
        }

        $logPath = $logPath ?: \dirname($this->filename);
        $logFile = rtrim($logPath, '/' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'joomla_update.txt';

        self::$logFilePath = $logFile;
    }
}

// Skip over the mini-controller for testing purposes
if (\defined('_JOOMLA_UPDATE_TESTING')) {
    return;
}

/**
 * Invalidate a file in OPcache.
 *
 * Only applies if the file has a .php extension.
 *
 * @param   string  $file  The filepath to clear from OPcache
 *
 * @return  boolean
 * @since   4.0.4
 */
function clearFileInOPCache(string $file): bool
{
    static $hasOpCache = null;

    if (\is_null($hasOpCache)) {
        $hasOpCache = \ini_get('opcache.enable')
            && \function_exists('opcache_invalidate')
            && (!\ini_get('opcache.restrict_api') || stripos(realpath($_SERVER['SCRIPT_FILENAME']), \ini_get('opcache.restrict_api')) === 0);
    }

    if ($hasOpCache && (strtolower(substr($file, -4)) === '.php')) {
        return opcache_invalidate($file, true);
    }

    return false;
}

/**
 * A timing safe equals comparison.
 *
 * Uses the built-in hash_equals() method if it exists. It SHOULD exist, as it's available since PHP 5.6 whereas even
 * Joomla 4.0 requires PHP 7.2 or later. If for any reason the built-in function is not available (for example, a host
 * has disabled it because they do not understand the first thing about security) we will fall back to a safe, userland
 * implementation.
 *
 * @param   string  $known  The known value to check against
 * @param   string  $user   The user submitted value to check
 *
 * @return  boolean  True if the two strings are identical.
 * @since   4.0.4
 *
 * @link    http://blog.ircmaxell.com/2014/11/its-all-about-time.html
 */
function timingSafeEquals($known, $user)
{
    if (\function_exists('hash_equals')) {
        return hash_equals($known, $user);
    }

    $safeLen = \strlen($known);
    $userLen = \strlen($user);

    if ($userLen != $safeLen) {
        return false;
    }

    $result = 0;

    for ($i = 0; $i < $userLen; $i++) {
        $result |= (\ord($known[$i]) ^ \ord($user[$i]));
    }

    // They are only identical strings if $result is exactly 0...
    return $result === 0;
}

/**
 * Gets the configuration parameters from the update.php file and validates the password sent with
 * the request.
 *
 * @return  array|null  The configuration parameters to use. NULL if this is an invalid request.
 * @since   4.0.4
 */
function getConfiguration(): ?array
{
    // Make sure the locale is correct for basename() to work
    if (\function_exists('setlocale')) {
        @setlocale(LC_ALL, 'en_US.UTF8');
    }

    // Require update.php or fail
    $setupFile = __DIR__ . '/update.php';

    if (!file_exists($setupFile)) {
        return null;
    }

    /**
     * If the setup file was created more than 1.5 hours ago we can assume that it's stale and someone forgot to
     * remove it from the server.
     *
     * This prevents brute force attacks against the randomly generated password. Even a simple 8 character simple
     * alphanum (a-z, 0-9) password yields over 2.8e12 permutation. Assuming a very fast server which can
     * serve 100 requests to extract.php per second and an easy to attack password requiring going over just 1% of
     * the search space it'd still take over 282 million seconds to brute force it. Our limit is more than 4 orders
     * of magnitude lower than this best practical case scenario, giving us adequate protection against all but the
     * luckiest attacker (spoiler alert: the mathematics of probabilities say you're not gonna get too lucky).
     *
     * It is still advisable to remove the update.php file once you are done with the extraction. This check
     * here is only meant as a failsafe in case of a server error during the extraction and subsequent lack of user
     * action to remove the update.php file from their server.
     */
    clearstatcache(true);
    $setupFileCreationTime = filectime($setupFile);

    if (abs(time() - $setupFileCreationTime) > 5400) {
        return null;
    }

    // Load update.php. It pulls a variable named $restoration_setup into the local scope.
    clearFileInOPCache($setupFile);

    require_once $setupFile;

    /** @var array $extractionSetup */

    // The file exists but no configuration is present?
    if (empty($extractionSetup ?? null) || !\is_array($extractionSetup)) {
        return null;
    }

    /**
     * Immediately reject any attempt to run extract.php without a password.
     *
     * Doing that is a GRAVE SECURITY RISK. It makes it trivial to hack a site. Therefore we are preventing this script
     * to run without a password.
     */
    $password     = $extractionSetup['security.password'] ?? null;
    $userPassword = $_REQUEST['password'] ?? '';
    $userPassword = !\is_string($userPassword) ? '' : trim($userPassword);

    if (empty($password) || !\is_string($password) || (trim($password) == '') || (\strlen(trim($password)) < 32)) {
        return null;
    }

    // Timing-safe password comparison. See http://blog.ircmaxell.com/2014/11/its-all-about-time.html
    if (!timingSafeEquals($password, $userPassword)) {
        return null;
    }

    // An "instance" variable will resume the engine from the serialised instance
    $serialized = $_REQUEST['instance'] ?? null;

    if (!\is_null($serialized) && empty(ZIPExtraction::unserialiseInstance($serialized))) {
        // The serialised instance is corrupt or someone tries to trick us. YOU SHALL NOT PASS!
        return null;
    }

    return $extractionSetup;
}

// Import configuration
$retArray = [
    'status'  => true,
    'message' => null,
];

$configuration = getConfiguration();
$enabled       = !empty($configuration);

/**
 * Sets the PHP timeout to 3600 seconds
 *
 * @return  void
 * @since   4.2.0
 */
function setLongTimeout()
{
    if (!\function_exists('ini_set')) {
        return;
    }

    ini_set('max_execution_time', 3600);
}

/**
 * Sets the memory limit to 1GiB
 *
 * @return  void
 * @since   4.2.0
 */
function setHugeMemoryLimit()
{
    if (!\function_exists('ini_set')) {
        return;
    }

    ini_set('memory_limit', 1073741824);
}

if ($enabled) {
    // Try to set a very large memory and timeout limit
    setLongTimeout();
    setHugeMemoryLimit();

    $sourcePath = $configuration['setup.sourcepath'] ?? '';
    $sourceFile = $configuration['setup.sourcefile'] ?? '';
    $destDir    = ($configuration['setup.destdir'] ?? null) ?: __DIR__;
    $basePath   = rtrim(str_replace('\\', '/', __DIR__), '/');
    $basePath   = empty($basePath) ? $basePath : ($basePath . '/');
    $sourceFile = (empty($sourcePath) ? '' : (rtrim($sourcePath, '/\\') . '/')) . $sourceFile;
    $engine     = ZIPExtraction::getInstance();

    $engine->setFilename($sourceFile);
    $engine->setAddPath($destDir);
    $skipFiles = [
        'administrator/components/com_joomlaupdate/update.php',
    ];

    if (\defined('_JOOMLA_UPDATE_DEBUG')) {
        $skipFiles[] = 'administrator/components/com_joomlaupdate/extract.php';
    }

    $engine->setSkipFiles($skipFiles);
    $engine->setIgnoreDirectories([
            'tmp', 'administrator/logs',
        ]);

    $task = $_REQUEST['task'] ?? null;

    switch ($task) {
        case 'startExtract':
        case 'stepExtract':
            $done  = $engine->step();
            $error = $engine->getError();

            if ($error != '') {
                $retArray['status']  = false;
                $retArray['done']    = true;
                $retArray['message'] = $error;
            } elseif ($done) {
                $retArray['files']    = $engine->filesProcessed;
                $retArray['bytesIn']  = $engine->compressedTotal;
                $retArray['bytesOut'] = $engine->uncompressedTotal;
                $retArray['percent']  = 80;
                $retArray['status']   = true;
                $retArray['done']     = true;

                $retArray['percent'] = min($retArray['percent'], 100);
            } else {
                $retArray['files']    = $engine->filesProcessed;
                $retArray['bytesIn']  = $engine->compressedTotal;
                $retArray['bytesOut'] = $engine->uncompressedTotal;
                $retArray['percent']  = ($engine->totalSize > 0) ? (80 * $engine->compressedTotal / $engine->totalSize) : 0;
                $retArray['status']   = true;
                $retArray['done']     = false;
                $retArray['instance'] = ZIPExtraction::getSerialised();
            }

            $engine->enforceMinimumExecutionTime();

            break;

        case 'finalizeUpdate':
            $root = $configuration['setup.destdir'] ?? '';

            // Remove the administrator/cache/autoload_psr4.php file
            $filename = $root . (empty($root) ? '' : '/') . 'administrator/cache/autoload_psr4.php';

            if (file_exists($filename)) {
                clearFileInOPCache($filename);
                clearstatcache(true, $filename);

                @unlink($filename);
            }

            // Remove update.php
            clearFileInOPCache($basePath . 'update.php');
            @unlink($basePath . 'update.php');

            // Import a custom finalisation file
            $filename = \dirname(__FILE__) . '/finalisation.php';

            if (file_exists($filename)) {
                clearFileInOPCache($filename);

                include_once $filename;
            }

            // Run a custom finalisation script
            if (\function_exists('finalizeUpdate')) {
                finalizeUpdate($root, $basePath);
            }

            $engine->enforceMinimumExecutionTime();

            break;

        default:
            // Invalid task!
            $enabled = false;
            break;
    }
}

// This could happen even if $enabled was true, e.g. if we were asked for an invalid task.
if (!$enabled) {
    // Maybe we weren't authorized or the task was invalid?
    $retArray['status']  = false;
    $retArray['message'] = 'Invalid login';
}

// JSON encode the message
echo json_encode($retArray);
