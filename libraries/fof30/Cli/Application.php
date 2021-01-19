<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

// Do not put the JEXEC or die check on this file

/**
 * FOF-powered Joomla! CLI application implementation.
 *
 * Get all the power of Joomla in CLI without all the awkward decisions which make CLI scripts fail on many common,
 * commercial hosting environments. We've been doing that in our software before Joomla got CLI support. We know of all
 * the pitfalls and this little gem here will work around most of them (or at least fail gracefully).
 *
 * Your CLI script must begin with the following boilerplate code:
 *
 * // Boilerplate -- START
 * define('_JEXEC', 1);
 *
 * foreach ([__DIR__, getcwd()] as $curdir)
 * {
 *   if (file_exists($curdir . '/defines.php'))
 *     {
 *        define('JPATH_BASE', realpath($curdir . '/..'));
 *        require_once $curdir . '/defines.php';
 *
 *      break;
 *     }
 *
 *   if (file_exists($curdir . '/../includes/defines.php'))
 *     {
 *       define('JPATH_BASE', realpath($curdir . '/..'));
 *       require_once $curdir . '/../includes/defines.php';
 *
 *     break;
 *     }
 * }
 *
 * defined('JPATH_LIBRARIES') || die ('This script must be placed in or run from the cli folder of your site.');
 *
 * require_once JPATH_LIBRARIES . '/fof30/Cli/Application.php';
 * // Boilerplate -- END
 *
 * Create a class which extends FOFCliApplication and implements doExecute, e.g.
 *
 * // Class definition -- START
 * class YourClassName extends FOFCliApplication
 * {
 *   protected function doExecute()
 *   {
 *     // Do something useful
 *   }
 * }
 * // Class definition -- END
 *
 * Finally, execute your script with:
 *
 * // Execute script -- START
 * FOFCliApplication::getInstance('YourClassName')->execute();
 * // Execute script -- END
 *
 * You can optionally define $minphp before the boilerplate code to enforce a different minimum PHP version.
 */

// Abort immediately when this file is executed from a web SAPI
if (array_key_exists('REQUEST_METHOD', $_SERVER))
{
	die('This is a command line script. You are not allowed to access it over the web.');
}

// Work around some misconfigured servers which print out notices
if (function_exists('error_reporting'))
{
	$oldLevel = error_reporting(E_ERROR | E_NOTICE | E_DEPRECATED);
}

// Minimum PHP version check
if (!isset($minphp))
{
	$minphp = '5.6.0';
}

if (version_compare(PHP_VERSION, $minphp, 'lt'))
{
	require_once __DIR__ . '/wrong_php.php';

	die;
}

// Required by scripts written for old Joomla! versions.
defined('DS') || define('DS', DIRECTORY_SEPARATOR);

/**
 * Timezone fix
 *
 * This piece of code was originally put here because some PHP 5.3 servers forgot to declare a default timezone.
 * Unfortunately it's still required because some hosts STILL forget to provide a timezone in their php.ini files or,
 * worse, use invalid timezone names.
 */
if (function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set'))
{
	$serverTimezone = @date_default_timezone_get();

	// Do I have no timezone set?
	if (empty($serverTimezone) || !is_string($serverTimezone))
	{
		$serverTimezone = 'UTC';
	}

	// Do I have an invalid timezone?
	try
	{
		$testTimeZone = new DateTimeZone($serverTimezone);
	}
	catch (\Exception $e)
	{
		$serverTimezone = 'UTC';
	}

	// Set the default timezone to a correct thing
	@date_default_timezone_set($serverTimezone);
}

// This is not necessary if you have used the boilerplate code.
if (!isset($curdir) && !defined('JPATH_ROOT'))
{
	foreach ([__DIR__ . '/../../../cli', getcwd()] as $curdir)
	{
		if (file_exists($curdir . '/defines.php'))
		{
			define('JPATH_BASE', realpath($curdir . '/..'));
			require_once $curdir . '/defines.php';

			break;
		}

		if (file_exists($curdir . '/../includes/defines.php'))
		{
			define('JPATH_BASE', realpath($curdir . '/..'));
			require_once $curdir . '/../includes/defines.php';

			break;
		}
	}

	defined('JPATH_LIBRARIES') || die ('This script must be placed in or run from the cli folder of your site.');
}

// Restore the error reporting before importing Joomla core code
if (function_exists('error_reporting'))
{
	error_reporting($oldLevel);
}

// Awkward Joomla version detection before we can actually load Joomla! itself
$joomlaMajorVersion = 3;
$joomlaMinorVersion = 0;
$jVersionFile       = JPATH_LIBRARIES . '/src/Version.php';

if ($versionFileContents = @file_get_contents($jVersionFile))
{
	preg_match("/MAJOR_VERSION\s*=\s*(\d*)\s*;/", $versionFileContents, $versionMatches);
	$joomlaMajorVersion = (int) $versionMatches[1];
	preg_match("/MINOR_VERSION\s*=\s*(\d*)\s*;/", $versionFileContents, $versionMatches);
	$joomlaMinorVersion = (int) $versionMatches[1];
}

// Load the Trait files
include_once __DIR__ . '/Traits/CGIModeAware.php';
include_once __DIR__ . '/Traits/CustomOptionsAware.php';
include_once __DIR__ . '/Traits/JoomlaConfigAware.php';
include_once __DIR__ . '/Traits/MemStatsAware.php';
include_once __DIR__ . '/Traits/MessageAware.php';
include_once __DIR__ . '/Traits/TimeAgoAware.php';

// The actual implementation of the CliApplication depends on the Joomla version we're running under
switch ($joomlaMajorVersion)
{
	case 3:
	default:
		require_once __DIR__ . '/Joomla3.php';

		abstract class FOFApplicationCLI extends FOFCliApplicationJoomla3
		{
		}

		;

		break;

	case 4:
		require_once __DIR__ . '/Joomla4.php';

		abstract class FOFApplicationCLI extends FOFCliApplicationJoomla4
		{
		}

		;

		break;
}

/**
 * A default exception handler. Catches all unhandled exceptions, displays debug information about them and sets the
 * error level to 254.
 *
 * @param   Throwable  $ex  The Exception / Error being handled
 */
function FOFCliExceptionHandler($ex)
{
	echo "\n\n";
	echo "********** ERROR! **********\n\n";
	echo $ex->getMessage();
	echo "\n\nTechnical information:\n\n";
	echo "Code: " . $ex->getCode() . "\n";
	echo "File: " . $ex->getFile() . "\n";
	echo "Line: " . $ex->getLine() . "\n";
	echo "\nStack Trace:\n\n" . $ex->getTraceAsString();
	echo "\n\n";
	exit(254);
}

/**
 * Timeout handler
 *
 * This function is registered as a shutdown script. If a catchable timeout occurs it will detect it and print a helpful
 * error message instead of just dying cold. The error level is set to 253 in this case.
 *
 * @return  void
 */
function FOFCliTimeoutHandler()
{
	$connection_status = connection_status();

	if ($connection_status == 0)
	{
		// Normal script termination, do not report an error.
		return;
	}

	echo "\n\n";
	echo "********** ERROR! **********\n\n";

	if ($connection_status == 1)
	{
		echo <<< END
The process was aborted on user's request.

This usually means that you pressed CTRL-C to terminate the script (if you're
running it from a terminal / SSH session), or that your host's CRON daemon
aborted the execution of this script.

If you are running this script through a CRON job and saw this message, please
contact your host and request an increase in the timeout limit for CRON jobs.
Moreover you need to ask them to increase the max_execution_time in the
php.ini file or, even better, set it to 0.
END;
	}
	else
	{
		echo <<< END
This script has timed out. As a result, the process has FAILED to complete.

Your host applies a maximum execution time for CRON jobs which is too low for
this script to work properly. Please contact your host and request an increase
in the timeout limit for CRON jobs. Moreover you need to ask them to increase
the max_execution_time in the php.ini file or, even better, set it to 0.
END;


		if (!function_exists('php_ini_loaded_file'))
		{
			echo "\n\n";

			return;
		}

		$ini_location = php_ini_loaded_file();

		echo <<<END
The php.ini file your host will need to modify is located at:
$ini_location
Info for the host: the location above is reported by PHP's php_ini_loaded_file() method.

END;

		echo "\n\n";
		exit(253);
	}
}

/**
 * Error handler. It tries to catch fatal errors and report them in a meaningful way. Obviously it only works for
 * catchable fatal errors. It sets the error level to 252.
 *
 * IMPORTANT! Under PHP 7 the default exception handler will be called instead, including when there is a non-catchable
 *            fatal error.
 *
 * @param   int     $errno    Error number
 * @param   string  $errstr   Error string, tells us what went wrong
 * @param   string  $errfile  Full path to file where the error occurred
 * @param   int     $errline  Line number where the error occurred
 *
 * @return  void
 */
function FOFCliErrorHandler($errno, $errstr, $errfile, $errline)
{
	switch ($errno)
	{
		case E_ERROR:
		case E_USER_ERROR:
			echo "\n\n";
			echo "********** ERROR! **********\n\n";
			echo "PHP Fatal Error: $errstr";
			echo "\n\nTechnical information:\n\n";
			echo "File: " . $errfile . "\n";
			echo "Line: " . $errline . "\n";
			echo "\nStack Trace:\n\n" . debug_backtrace();
			echo "\n\n";

			exit(252);
			break;

		default:
			break;
	}
}

/**
 * Custom default handlers for otherwise unhandled exceptions and PHP catchable errors.
 *
 * Moreover, we register a shutdown function to catch timeouts and SIGTERM signals, because some hosts *are* monsters.
 */
set_exception_handler('FOFCliExceptionHandler');
set_error_handler('FOFCliErrorHandler', E_ERROR | E_USER_ERROR);
register_shutdown_function('FOFCliTimeoutHandler');