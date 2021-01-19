<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Akeeba\Engine\Util\Log\LogInterface;
use Akeeba\Engine\Util\Log\WarningsLoggerAware;
use Akeeba\Engine\Util\Log\WarningsLoggerInterface;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Writes messages to the backup log file
 */
class Logger implements LoggerInterface, LogInterface, WarningsLoggerInterface
{
	use WarningsLoggerAware;

	/** @var  string  Full path to log file */
	protected $logName = null;

	/** @var  string  The current log tag */
	protected $currentTag = null;

	/** @var  resource  The file pointer to the current log file */
	protected $fp = null;

	/** @var  bool  Is the logging currently paused? */
	protected $paused = false;

	/** @var  int  The minimum log level */
	protected $configuredLoglevel;

	/** @var  string  The untranslated path to the site's root */
	protected $site_root_untranslated;

	/** @var  string  The translated path to the site's root */
	protected $site_root;

	/**
	 * Public constructor. Initialises the properties with the parameters from the backup profile and platform.
	 */
	public function __construct()
	{
		$this->initialiseWithProfileParameters();
	}

	/**
	 * When shutting down this class always close any open log files.
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Clears the logfile
	 *
	 * @param   string  $tag  Backup origin
	 */
	public function reset($tag = null)
	{
		// Pause logging
		$this->pause();

		// Get the file names for the default log and the tagged log
		$currentLogName = $this->logName;
		$this->logName  = $this->getLogFilename($tag);

		// Close the file if it's open
		if ($currentLogName == $this->logName)
		{
			$this->close();
		}

		// Remove the log file if it exists
		@unlink($this->logName);

		// Reset the log file
		$fp = @fopen($this->logName, 'w');
		$hasWritten = false;

		if ($fp !== false)
		{
			$hasWritten = fwrite($fp, '<' . '?' . 'php die(); ' . '?' . '>' . "\n") !== false;
			@fclose($fp);
		}

		// If I could not write to a .log.php file try using a .log file instead.
		if (!$hasWritten)
		{
			$this->logName  = $this->getLogFilename($tag, '');
			$fp = @fopen($this->logName, 'w');
			$hasWritten = false;

			if ($fp !== false)
			{
				$hasWritten = fwrite($fp, "\n") !== false;
				@fclose($fp);
			}
		}

		// Delete the default log file(s) if they exists
		$defaultLog     = $this->getLogFilename(null);

		if (!empty($tag) && @file_exists($defaultLog))
		{
			@unlink($defaultLog);
		}

		$defaultLog     = $this->getLogFilename(null, '');

		if (!empty($tag) && @file_exists($defaultLog))
		{
			@unlink($defaultLog);
		}

		// Set the current log tag
		$this->currentTag = $tag;

		// Unpause logging
		$this->unpause();
	}

	/**
	 * Writes a line to the log, if the log level is high enough
	 *
	 * @param   string  $level    The log level
	 * @param   string  $message  The message to write to the log
	 * @param   array   $context  The logging context. For PSR-3 compatibility but not used in text file logs.
	 *
	 * @return  void
	 */
	public function log($level, $message = '', array $context = [])
	{
		// Warnings are enqueued no matter what is the minimum log level to report in the log file
		if (in_array($level, [LogLevel::WARNING, LogLevel::NOTICE]))
		{
			$this->enqueueWarning($message);
		}

		// If we are told to not log anything we can't continue
		if ($this->configuredLoglevel == 0)
		{
			return;
		}

		// Open the log if it's closed
		if (is_null($this->fp))
		{
			$this->open($this->currentTag);
		}

		// If the log could not be opened we can't continue
		if (is_null($this->fp))
		{
			return;
		}

		// If the logging is paused we can't continue
		if ($this->paused)
		{
			return;
		}

		// Get the log level as an integer (compatibility with our minimum log level configuration parameter)
		switch ($level)
		{
			case LogLevel::EMERGENCY:
			case LogLevel::ALERT:
			case LogLevel::CRITICAL:
			case LogLevel::ERROR:
				$intLevel = 1;
				break;

			case LogLevel::WARNING:
			case LogLevel::NOTICE:
				$intLevel = 2;
				break;

			case LogLevel::INFO:
				$intLevel = 3;
				break;

			case LogLevel::DEBUG:
				$intLevel = 4;
				break;

			default:
				throw new InvalidArgumentException("Unknown log level $level", 500);
				break;
		}

		// If the minimum log level is lower than what we're trying to log we cannot continue
		if ($this->configuredLoglevel < $intLevel)
		{
			return;
		}

		$translateRoot = true;

		if (array_key_exists('root_translate', $context))
		{
			$translateRoot = ($context['root_translate'] === 1) || ($context['root_translate'] === '1') || ($context['root_translate'] === true);
		}

		// Replace the site's root with <root> in the log file
		if ($translateRoot && !defined('AKEEBADEBUG'))
		{
			$message = str_replace($this->site_root_untranslated, "<root>", $message);
			$message = str_replace($this->site_root, "<root>", $message);
		}

		// Replace new lines
		$message = str_replace("\r\n", "\n", $message);
		$message = str_replace("\r", "\n", $message);
		$message = str_replace("\n", ' \n ', $message);

		switch ($level)
		{
			case LogLevel::EMERGENCY:
			case LogLevel::ALERT:
			case LogLevel::CRITICAL:
			case LogLevel::ERROR:
				$string = "ERROR   |";
				break;

			case LogLevel::WARNING:
			case LogLevel::NOTICE:
				$string = "WARNING |";
				break;

			case LogLevel::INFO:
				$string = "INFO    |";
				break;

			default:
				$string = "DEBUG   |";
				break;
		}

		$string .= @strftime("%y%m%d %H:%M:%S") . "|$message\r\n";

		@fwrite($this->fp, $string);
	}

	/**
	 * Calculates the absolute path to the log file
	 *
	 * @param   string  $tag  The backup run's tag
	 *
	 * @return    string    The absolute path to the log file
	 */
	public function getLogFilename($tag = null, $extension = '.php')
	{
		if (empty($tag))
		{
			$fileName = 'akeeba.log' . $extension;
		}
		else
		{
			$fileName = "akeeba.$tag.log" . $extension;
		}

		// Get output directory
		$registry        = Factory::getConfiguration();
		$outputDirectory = $registry->get('akeeba.basic.output_directory');

		// Get the log file name
		$absoluteLogFilename = Factory::getFilesystemTools()->TranslateWinPath($outputDirectory . DIRECTORY_SEPARATOR . $fileName);

		return $absoluteLogFilename;
	}

	/**
	 * Close the currently active log and set the current tag to null.
	 *
	 * @return  void
	 */
	public function close()
	{
		// The log file changed. Close the old log.
		if (is_resource($this->fp))
		{
			@fclose($this->fp);
		}

		$this->fp         = null;
		$this->currentTag = null;
	}

	/**
	 * Open a new log instance with the specified tag. If another log is already open it is closed before switching to
	 * the new log tag. If the tag is null use the default log defined in the logging system.
	 *
	 * @param   string|null  $tag  The log to open
	 *
	 * @return void
	 */
	public function open($tag = null, $extension = '.php')
	{
		// If the log is already open do nothing
		if (is_resource($this->fp) && ($tag == $this->currentTag))
		{
			return;
		}

		// If another log is open, close it
		if (is_resource($this->fp))
		{
			$this->close();
		}

		// Re-initialise site root and minimum log level since the active profile might have changed in the meantime
		$this->initialiseWithProfileParameters();

		// Set the current tag
		$this->currentTag = $tag;

		// Get the log filename
		$this->logName = $this->getLogFilename($tag, $extension);

		// Touch the file
		@touch($this->logName);

		// Open the log file. DO NOT USE APPEND ('ab') MODE. I NEED TO SEEK INTO THE FILE. SEE FURTHER BELOW!
		$this->fp = @fopen($this->logName, 'cb');

		// If we couldn't open the file set the file pointer to null
		if ($this->fp === false)
		{
			$this->fp = null;

			return;
		}

		// Go to the end of the file, emulating append mode. DO NOT REPLACE THE fopen() FILE MODE!
		if (@fseek($this->fp, 0, SEEK_END) === -1)
		{
			@fclose($this->fp);
			@unlink($this->logName);

			$this->fp = null;

			return;
		}

		/**
		 * The following sounds pretty stupid but there is a reason for that convoluted code.
		 *
		 * Some hosts, like WP Engine, will now allow you to write to a log file with a .php extension. The code below
		 * tries to anticipate that when the log extension is .php. It will try to write to the *.log.php file and the
		 * text is actually resembling PHP code. Hosts like WP Engine will fail the fwrite() which will cause this
		 * method to terminate early and return a null pointer. Our code will catch this case and try to use a .log
		 * extension as a safe fallback.
		 */
		if ($extension !== '.php')
		{
			return;
		}

		// Try to write something into the file
		$written = @fwrite($this->fp, '<?php die("test"); ?>' . "\n");

		if ($written === false)
		{
			@fclose($this->fp);
			@unlink($this->logName);

			$this->fp = null;

			$this->open($tag, '');

			return;
		}

		if (ftruncate($this->fp, ftell($this->fp) - $written) === false)
		{
			@fclose($this->fp);
			@unlink($this->logName);

			$this->fp = null;

			$this->open($tag, '');

			return;
		}
	}

	/**
	 * Temporarily pause log output. The log() method MUST respect this.
	 *
	 * @return  void
	 */
	public function pause()
	{
		$this->paused = true;
	}

	/**
	 * Resume the previously paused log output. The log() method MUST respect this.
	 *
	 * @return  void
	 */
	public function unpause()
	{
		$this->paused = false;
	}

	/**
	 * Returns the timestamp (in UNIX time long integer format) of the last log message written to the log with the
	 * specific tag. The timestamp MUST be read from the log itself, not from the logger object. It is used by the
	 * engine to find out the age of stalled backups which may have crashed.
	 *
	 * @param   string|null  $tag  The log tag for which the last timestamp is returned
	 *
	 * @return  int|null  The timestamp of the last log message, in UNIX time. NULL if we can't get the timestamp.
	 */
	public function getLastTimestamp($tag = null)
	{
		$fileName = $this->getLogFilename($tag);

		/**
		 * The log file akeeba.tag.log.php may not exist but the akeeba.tag.log does. This would be the case in some bad
		 * hosts, like WPEngine, which do not allow us to create .php files EVEN THOUGH that's the only way to ensure
		 * the privileged information in the log file is not readable over the web. You can't fix bad hosts, you can
		 * only work around them.
		 */
		if (!@file_exists($fileName) && @file_exists(substr($fileName, 0, -4)))
		{
			$fileName = substr($fileName, 0, -4);
		}

		$timestamp = @filemtime($fileName);

		if ($timestamp === false)
		{
			return null;
		}

		return $timestamp;
	}

	/**
	 * System is unusable.
	 *
	 * @param   string  $message
	 * @param   array   $context
	 *
	 * @return void
	 */
	public function emergency($message, array $context = [])
	{
		$this->log(LogLevel::EMERGENCY, $message, $context);
	}

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param   string  $message
	 * @param   array   $context
	 *
	 * @return void
	 */
	public function alert($message, array $context = [])
	{
		$this->log(LogLevel::ALERT, $message, $context);
	}

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param   string  $message
	 * @param   array   $context
	 *
	 * @return void
	 */
	public function critical($message, array $context = [])
	{
		$this->log(LogLevel::CRITICAL, $message, $context);
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param   string  $message
	 * @param   array   $context
	 *
	 * @return void
	 */
	public function error($message, array $context = [])
	{
		$this->log(LogLevel::ERROR, $message, $context);
	}

	/**
	 * \Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param   string  $message
	 * @param   array   $context
	 *
	 * @return void
	 */
	public function warning($message, array $context = [])
	{
		$this->log(LogLevel::WARNING, $message, $context);
	}

	/**
	 * Normal but significant events.
	 *
	 * @param   string  $message
	 * @param   array   $context
	 *
	 * @return void
	 */
	public function notice($message, array $context = [])
	{
		$this->log(LogLevel::NOTICE, $message, $context);
	}

	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param   string  $message
	 * @param   array   $context
	 *
	 * @return void
	 */
	public function info($message, array $context = [])
	{
		$this->log(LogLevel::INFO, $message, $context);
	}

	/**
	 * Detailed debug information.
	 *
	 * @param   string  $message
	 * @param   array   $context
	 *
	 * @return void
	 */
	public function debug($message, array $context = [])
	{
		$this->log(LogLevel::DEBUG, $message, $context);
	}

	/**
	 * Initialise the logger properties with parameters from the backup profile and the platform
	 *
	 * @return  void
	 */
	protected function initialiseWithProfileParameters()
	{
		// Get the site's translated and untranslated root
		$this->site_root_untranslated = Platform::getInstance()->get_site_root();
		$this->site_root              = Factory::getFilesystemTools()->TranslateWinPath($this->site_root_untranslated);

		// Load the registry and fetch log level
		$registry                 = Factory::getConfiguration();
		$this->configuredLoglevel = $registry->get('akeeba.basic.log_level');
		$this->configuredLoglevel = $this->configuredLoglevel * 1;
	}
}
