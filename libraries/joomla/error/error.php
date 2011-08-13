<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

// Error Definition: Illegal Options
define('JERROR_ILLEGAL_OPTIONS', 1);
// Error Definition: Callback does not exist
define('JERROR_CALLBACK_NOT_CALLABLE', 2);
// Error Definition: Illegal Handler
define('JERROR_ILLEGAL_MODE', 3);

/**
 * Error Handling Class
 *
 * This class is inspired in design and concept by patErrorManager <http://www.php-tools.net>
 *
 * patErrorManager contributors include:
 * - gERD Schaufelberger	<gerd@php-tools.net>
 * - Sebastian Mordziol	<argh@php-tools.net>
 * - Stephan Schmidt		<scst@php-tools.net>
 *
 * @package     Joomla.Platform
 * @subpackage  Error
 * @since       11.1
 * @deprecated  12.1   Use PHP Exception
 */
abstract class JError
{
	/**
	 * @var    boolean  True to enable legacy error handling using JError, false to use exception handling.  This flag
	 * is present to allow an easy transition into exception handling for code written against the
	 * existing JError API in Joomla.
	 *
	 * @since  11.1
	 */
	public static $legacy = false;

	/**
	 * Array of message levels
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $levels = array(E_NOTICE => 'Notice', E_WARNING => 'Warning', E_ERROR => 'Error');

	protected static $handlers = array(
		E_NOTICE => array('mode' => 'message'),
		E_WARNING => array('mode' => 'message'),
		E_ERROR => array('mode' => 'callback', 'options' => array('JError', 'customErrorPage')));

	protected static $stack = array();

	/**
	 * Method to determine if a value is an exception object.  This check supports
	 * both JException and PHP5 Exception objects
	 *
	 * @param   mixed  &$object  Object to check
	 *
	 * @return  boolean  True if argument is an exception, false otherwise.
	 *
	 * @since   11.1
	 *
	 * @deprecated  12.1
	 */
	public static function isError(& $object)
	{
		// Deprecation warning.
		JLog::add('JError::isError() is deprecated.', JLog::WARNING, 'deprecated');

		// Supports PHP 5 exception handling
		return $object instanceof Exception;
	}

	/**
	 * Method for retrieving the last exception object in the error stack
	 *
	 * @param   boolean  $unset  True to remove the error from the stack.
	 *
	 * @return  mixed  Last exception object in the error stack or boolean false if none exist
	 *
	 * @since   11.1
	 *
	 * @deprecated  12.1
	 */
	public static function getError($unset = false)
	{
		// Deprecation warning.
		JLog::add('JError::getError() is deprecated.', JLog::WARNING, 'deprecated');

		if (!isset(JError::$stack[0]))
		{
			return false;
		}

		if ($unset)
		{
			$error = array_shift(JError::$stack);
		}
		else
		{
			$error = &JError::$stack[0];
		}
		return $error;
	}

	/**
	 * Method for retrieving the exception stack
	 *
	 * @return  array  Chronological array of errors that have been stored during script execution
	 *
	 * @since   11.1
	 *
	 * @deprecated  12.1
	 */
	public static function getErrors()
	{
		// Deprecation warning.
		JLog::add('JError::getErrors() is deprecated.', JLog::WARNING, 'deprecated');

		return JError::$stack;
	}

	/**
	 * Method to add non-JError thrown JExceptions to the JError stack for debugging purposes
	 *
	 * @param   JException  &$e  Add an exception to the stack.
	 *
	 * @return  void
	 *
	 * @since       11.1
	 * @deprecated  12.1
	 */
	public static function addToStack(JException &$e)
	{
		// Deprecation warning.
		JLog::add('JError::addToStack() is deprecated.', JLog::WARNING, 'deprecated');

		JError::$stack[] = &$e;
	}

	/**
	 * Create a new JException object given the passed arguments
	 *
	 * @param   integer  $level      The error level - use any of PHP's own error levels for this: E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE.
	 * @param   string   $code       The application-internal error code for this error
	 * @param   string   $msg        The error message, which may also be shown the user if need be.
	 * @param   mixed    $info       Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	 * @param   boolean  $backtrace  Add a stack backtrace to the exception.
	 *
	 * @return  mixed    The JException object
	 *
	 * @since       11.1
	 * @deprecated  12.1  Use PHP Exception
	 * @see         JException
	 */
	public static function raise($level, $code, $msg, $info = null, $backtrace = false)
	{
		// Deprecation warning.
		JLog::add('JError::raise() is deprecated.', JLog::WARNING, 'deprecated');

		jimport('joomla.error.exception');

		// Build error object
		$exception = new JException($msg, $code, $level, $info, $backtrace);
		return JError::throwError($exception);
	}

	/**
	 * Throw an error
	 *
	 * @param   object  &$exception  An exception to throw.
	 *
	 * @return  reference
	 *
	 * @deprecated  12.1  Use PHP Exception
	 * @see     JException
	 * @since   11.1
	 */
	public static function throwError(&$exception)
	{
		// Deprecation warning.
		JLog::add('JError::throwError() is deprecated.', JLog::WARNING, 'deprecated');

		static $thrown = false;

		// If thrown is hit again, we've come back to JError in the middle of throwing another JError, so die!
		if ($thrown)
		{
			// echo debug_print_backtrace();
			jexit(JText::_('JLIB_ERROR_INFINITE_LOOP'));
		}

		$thrown = true;
		$level = $exception->get('level');

		// See what to do with this kind of error
		$handler = JError::getErrorHandling($level);

		$function = 'handle' . ucfirst($handler['mode']);
		if (is_callable(array('JError', $function)))
		{
			$reference = call_user_func_array(array('JError', $function), array(&$exception, (isset($handler['options'])) ? $handler['options'] : array()));
		}
		else
		{
			// This is required to prevent a very unhelpful white-screen-of-death
			jexit('JError::raise -> Static method JError::' . $function . ' does not exist.' . ' Contact a developer to debug' . '<br /><strong>Error was</strong> ' . '<br />' . $exception->getMessage());
		}
		// We don't need to store the error, since JException already does that for us!
		// Remove loop check
		$thrown = false;

		return $reference;
	}

	/**
	 * Wrapper method for the raise() method with predefined error level of E_ERROR and backtrace set to true.
	 *
	 * @param   string   $code  The application-internal error code for this error
	 * @param   string   $msg   The error message, which may also be shown the user if need be.
	 * @param   mixed    $info  Optional: Additional error information (usually only developer-relevant information that
	 *                          the user should never see, like a database DSN).
	 *
	 * @return  object  $error  The configured JError object
	 *
	 * @deprecated   12.1     Use PHP Exception
	 * @see        raise()
	 * @since   11.1
	 */
	public static function raiseError($code, $msg, $info = null)
	{
		// Deprecation warning.
		JLog::add('JError::raiseError() is deprecated.', JLog::WARNING, 'deprecated');

		return JError::raise(E_ERROR, $code, $msg, $info, true);
	}

	/**
	 * Wrapper method for the {@link raise()} method with predefined error level of E_WARNING and backtrace set to false.
	 *
	 * @param   string  $code  The application-internal error code for this error
	 * @param   string  $msg   The error message, which may also be shown the user if need be.
	 * @param   mixed   $info  Optional: Additional error information (usually only developer-relevant information that
	 *                         the user should never see, like a database DSN).
	 *
	 * @return  object  The configured JError object
	 *
	 * @deprecated  12.1  Use PHP Exception
	 * @see        JError
	 * @see        raise()
	 * @since      11.1
	 */
	public static function raiseWarning($code, $msg, $info = null)
	{
		// Deprecation warning.
		JLog::add('JError::raiseWarning() is deprecated.', JLog::WARNING, 'deprecated');

		return JError::raise(E_WARNING, $code, $msg, $info);
	}

	/**
	 * Wrapper method for the {@link raise()} method with predefined error level of E_NOTICE and backtrace set to false.
	 *
	 * @param   string  $code  The application-internal error code for this error
	 * @param   string  $msg   The error message, which may also be shown the user if need be.
	 * @param   mixed   $info  Optional: Additional error information (usually only developer-relevant information
	 *                         that the user should never see, like a database DSN).
	 *
	 * @return  object   The configured JError object
	 *
	 * @deprecated       12.1   Use PHP Exception
	 * @see     raise()
	 * @since   11.1
	 */
	public static function raiseNotice($code, $msg, $info = null)
	{
		// Deprecation warning.
		JLog::add('JError::raiseNotice() is deprecated.', JLog::WARNING, 'deprecated');

		return JError::raise(E_NOTICE, $code, $msg, $info);
	}

	/**
	 * Method to get the current error handler settings for a specified error level.
	 *
	 * @param   integer  $level  The error level to retrieve. This can be any of PHP's own error levels, e.g. E_ALL, E_NOTICE...
	 *
	 * @return  array  All error handling details
	 *
	 * @deprecated   12.1  Use PHP Exception
	 * @since   11.1
	 */
	public static function getErrorHandling($level)
	{
		// Deprecation warning.
		JLog::add('JError::getErrorHandling() is deprecated.', JLog::WARNING, 'deprecated');

		return JError::$handlers[$level];
	}

	/**
	 * Method to set the way the JError will handle different error levels. Use this if you want to override the default settings.
	 *
	 * Error handling modes:
	 * - ignore
	 * - echo
	 * - verbose
	 * - die
	 * - message
	 * - log
	 * - callback
	 *
	 * You may also set the error handling for several modes at once using PHP's bit operations.
	 * Examples:
	 * - E_ALL = Set the handling for all levels
	 * - E_ERROR | E_WARNING = Set the handling for errors and warnings
	 * - E_ALL ^ E_ERROR = Set the handling for all levels except errors
	 *
	 * @param   integer  $level    The error level for which to set the error handling
	 * @param   string   $mode     The mode to use for the error handling.
	 * @param   mixed    $options  Optional: Any options needed for the given mode.
	 *
	 * @return  mixed  True on success or a JException object if failed.
	 *
	 * @deprecated  12.1  Use PHP Exception
	 * @since   11.1
	 */
	public static function setErrorHandling($level, $mode, $options = null)
	{
		// Deprecation warning.
		JLog::add('JError::setErrorHandling() is deprecated.', JLog::WARNING, 'deprecated');

		$levels = JError::$levels;

		$function = 'handle' . ucfirst($mode);

		if (!is_callable(array('JError', $function)))
		{
			return JError::raiseError(E_ERROR, 'JError:' . JERROR_ILLEGAL_MODE, 'Error Handling mode is not known', 'Mode: ' . $mode . ' is not implemented.');
		}

		foreach ($levels as $eLevel => $eTitle)
		{
			if (($level & $eLevel) != $eLevel)
			{
				continue;
			}

			// Set callback options
			if ($mode == 'callback')
			{
				if (!is_array($options))
				{
					return JError::raiseError(E_ERROR, 'JError:' . JERROR_ILLEGAL_OPTIONS, 'Options for callback not valid');
				}

				if (!is_callable($options))
				{
					$tmp = array('GLOBAL');
					if (is_array($options))
					{
						$tmp[0] = $options[0];
						$tmp[1] = $options[1];
					}
					else
					{
						$tmp[1] = $options;
					}

					return JError::raiseError(E_ERROR, 'JError:' . JERROR_CALLBACK_NOT_CALLABLE, 'Function is not callable', 'Function:' . $tmp[1] . ' scope ' . $tmp[0] . '.');
				}
			}

			// Save settings
			JError::$handlers[$eLevel] = array('mode' => $mode);
			if ($options != null)
			{
				JError::$handlers[$eLevel]['options'] = $options;
			}
		}

		return true;
	}

	/**
	 * Method that attaches the error handler to JError
	 *
	 * @return  void
	 *
	 * @deprecated  12.1
	 * @see     set_error_handler
	 * @since   11.1
	 */
	public static function attachHandler()
	{
		// Deprecation warning.
		JLog::add('JError::getErrorHandling() is deprecated.', JLog::WARNING, 'deprecated');

		set_error_handler(array('JError', 'customErrorHandler'));
	}

	/**
	 * Method that detaches the error handler from JError
	 *
	 * @return  void
	 *
	 * @deprecated  12.1
	 * @see restore_error_handler
	 * @since   11.1
	 */
	public static function detachHandler()
	{
		// Deprecation warning.
		JLog::add('JError::detachHandler() is deprecated.', JLog::WARNING, 'deprecated');

		restore_error_handler();
	}

	/**
	 * Method to register a new error level for handling errors
	 *
	 * This allows you to add custom error levels to the built-in
	 * - E_NOTICE
	 * - E_WARNING
	 * - E_NOTICE
	 *
	 * @param   integer  $level    Error level to register
	 * @param   string   $name     Human readable name for the error level
	 * @param   string   $handler  Error handler to set for the new error level [optional]
	 *
	 * @return  boolean  True on success; false if the level already has been registered
	 *
	 * @deprecated  12.1
	 * @since   11.1
	 */
	public static function registerErrorLevel($level, $name, $handler = 'ignore')
	{
		// Deprecation warning.
		JLog::add('JError::registerErrorLevel() is deprecated.', JLog::WARNING, 'deprecated');

		if (isset(JError::$levels[$level]))
		{
			return false;
		}

		JError::$levels[$level] = $name;
		JError::setErrorHandling($level, $handler);

		return true;
	}

	/**
	 * Translate an error level integer to a human readable string
	 * e.g. E_ERROR will be translated to 'Error'
	 *
	 * @param   integer  $level  Error level to translate
	 *
	 * @return  mixed  Human readable error level name or boolean false if it doesn't exist
	 *
	 * @deprecated  12.1
	 * @since   11.1
	 */

	public static function translateErrorLevel($level)
	{
		// Deprecation warning.
		JLog::add('JError::translateErrorLevel() is deprecated.', JLog::WARNING, 'deprecated');

		if (isset(JError::$levels[$level]))
		{
			return JError::$levels[$level];
		}

		return false;
	}

	/**
	 * Ignore error handler
	 * - Ignores the error
	 *
	 * @param   object  &$error   Exception object to handle
	 * @param   array   $options  Handler options
	 *
	 * @return  object   The exception object
	 *
	 * @deprecated  12.1
	 * @see     raise()
	 * @since   11.1
	 */
	public static function handleIgnore(&$error, $options)
	{
		// Deprecation warning.
		JLog::add('JError::handleIgnore() is deprecated.', JLog::WARNING, 'deprecated');

		return $error;
	}

	/**
	 * Echo error handler
	 * - Echos the error message to output
	 *
	 * @param   object  &$error   Exception object to handle
	 * @param   array   $options  Handler options
	 *
	 * @return  object  The exception object
	 *
	 * @deprecated  12.1
	 * @see         raise()
	 * @since       11.1
	 */
	public static function handleEcho(&$error, $options)
	{
		// Deprecation warning.
		JLog::add('JError::handleEcho() is deprecated.', JLog::WARNING, 'deprecated');

		$level_human = JError::translateErrorLevel($error->get('level'));

		if (isset($_SERVER['HTTP_HOST']))
		{
			// output as html
			echo "<br /><b>jos-$level_human</b>: " . $error->get('message') . "<br />\n";
		}
		else
		{
			// Output as simple text
			if (defined('STDERR'))
			{
				fwrite(STDERR, "J$level_human: " . $error->get('message') . "\n");
			}
			else
			{
				echo "J$level_human: " . $error->get('message') . "\n";
			}
		}

		return $error;
	}

	/**
	 * Verbose error handler
	 * - Echos the error message to output as well as related info
	 *
	 * @param   object  &$error   Exception object to handle
	 * @param   array   $options  Handler options
	 *
	 * @return  object  The exception object
	 *
	 * @deprecated  12.1
	 * @see         raise()
	 * @since       11.1
	 */
	public static function handleVerbose(&$error, $options)
	{
		// Deprecation warning.
		JLog::add('JError::handleVerbose() is deprecated.', JLog::WARNING, 'deprecated');

		$level_human = JError::translateErrorLevel($error->get('level'));
		$info = $error->get('info');

		if (isset($_SERVER['HTTP_HOST']))
		{
			// Output as html
			echo "<br /><b>J$level_human</b>: " . $error->get('message') . "<br />\n";

			if ($info != null)
			{
				echo "&#160;&#160;&#160;" . $info . "<br />\n";
			}

			echo $error->getBacktrace(true);
		}
		else
		{
			// Output as simple text
			echo "J$level_human: " . $error->get('message') . "\n";
			if ($info != null)
			{
				echo "\t" . $info . "\n";
			}

		}

		return $error;
	}

	/**
	 * Die error handler
	 * - Echos the error message to output and then dies
	 *
	 * @param   object  &$error   Exception object to handle
	 * @param   array   $options  Handler options
	 *
	 * @return  object  The exception object
	 *
	 * @deprecated  12.1
	 * @see         raise()
	 * @since       11.1
	 */
	public static function handleDie(&$error, $options)
	{
		// Deprecation warning.
		JLog::add('JError::handleDie() is deprecated.', JLog::WARNING, 'deprecated');

		$level_human = JError::translateErrorLevel($error->get('level'));

		if (isset($_SERVER['HTTP_HOST']))
		{
			// Output as html
			jexit("<br /><b>J$level_human</b>: " . $error->get('message') . "<br />\n");
		}
		else
		{
			// Output as simple text
			if (defined('STDERR'))
			{
				fwrite(STDERR, "J$level_human: " . $error->get('message') . "\n");
				jexit();
			}
			else
			{
				jexit("J$level_human: " . $error->get('message') . "\n");
			}
		}

		return $error;
	}

	/**
	 * Message error handler
	 * Enqueues the error message into the system queue
	 *
	 * @param   object  &$error   Exception object to handle
	 * @param   array   $options  Handler options
	 *
	 * @return  object  The exception object
	 *
	 * @deprecated  12.1
	 * @see         raise()
	 * @since       11.1
	 */
	public static function handleMessage(&$error, $options)
	{
		// Deprecation warning.
		JLog::add('JError::hanleMessage() is deprecated.', JLog::WARNING, 'deprecated');

		$appl = JFactory::getApplication();
		$type = ($error->get('level') == E_NOTICE) ? 'notice' : 'error';
		$appl->enqueueMessage($error->get('message'), $type);

		return $error;
	}

	/**
	 * Log error handler
	 * Logs the error message to a system log file
	 *
	 * @param   object  &$error   Exception object to handle
	 * @param   array   $options  Handler options
	 *
	 * @return  object  The exception object
	 *
	 * @deprecated  12.1
	 * @see         raise()
	 * @since       11.1
	 */
	public static function handleLog(&$error, $options)
	{
		// Deprecation warning.
		JLog::add('JError::handleLog() is deprecated.', JLog::WARNING, 'deprecated');

		static $log;

		if ($log == null)
		{
			jimport('joomla.error.log');
			$fileName = date('Y-m-d') . '.error.log';
			$options['format'] = "{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}";
			$log = JLog::getInstance($fileName, $options);
		}

		$entry['level'] = $error->get('level');
		$entry['code'] = $error->get('code');
		$entry['message'] = str_replace(array("\r", "\n"), array('', '\\n'), $error->get('message'));
		$log->addEntry($entry);

		return $error;
	}

	/**
	 * Callback error handler
	 * - Send the error object to a callback method for error handling
	 *
	 * @param   object  &$error   Exception object to handle
	 * @param   array   $options  Handler options
	 *
	 * @return  object  The exception object
	 *
	 * @deprecated  12.1
	 * @see         raise()
	 * @since       11.1
	 */
	public static function handleCallback(&$error, $options)
	{
		// Deprecation warning.
		JLog::add('JError::handleCallback() is deprecated.', JLog::WARNING, 'deprecated');

		return call_user_func($options, $error);
	}

	/**
	 * Display a custom error page and exit gracefully
	 *
	 * @param   object  &$error  Exception object
	 *
	 * @return  void
	 *
	 * @deprecated  12.1
	 * @since   11.1
	 */
	public static function customErrorPage(&$error)
	{
		// Deprecation warning.
		JLog::add('JError::customErrorPage() is deprecated.', JLog::WARNING, 'deprecated');

		// Initialise variables.
		jimport('joomla.document.document');
		$app = JFactory::getApplication();
		$document = JDocument::getInstance('error');
		$config = JFactory::getConfig();

		// Get the current template from the application
		$template = $app->getTemplate();

		// Push the error object into the document
		$document->setError($error);

		@ob_end_clean();
		$document->setTitle(JText::_('Error') . ': ' . $error->get('code'));
		$data = $document->render(false, array('template' => $template, 'directory' => JPATH_THEMES, 'debug' => $config->get('debug')));

		// Do not allow cache
		JResponse::allowCache(false);

		JResponse::setBody($data);
		echo JResponse::toString();
		$app->close(0);
	}

	/**
	 * Display a message to the user
	 *
	 * @param   integer  $level  The error level - use any of PHP's own error levels for this: E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE.
	 * @param   string   $msg    Error message, shown to user if need be.
	 *
	 * @return  void
	 *
	 * @deprecated  12.1
	 * @since   11.1
	 */
	public static function customErrorHandler($level, $msg)
	{
		// Deprecation warning.
		JLog::add('JError::customErrorHandler() is deprecated.', JLog::WARNING, 'deprecated');

		JError::raise($level, '', $msg);
	}

	/**
	 * Render the backtrace
	 *
	 * @param   integer  $error  The error
	 *
	 * @return  string  Contents of the backtrace
	 *
	 * @deprecated  12.1
	 * @since   11.1
	 */
	public static function renderBacktrace($error)
	{
		// Deprecation warning.
		JLog::add('JError::renderBacktrace() is deprecated.', JLog::WARNING, 'deprecated');

		$contents = null;
		$backtrace = $error->getTrace();

		if (is_array($backtrace))
		{
			ob_start();
			$j = 1;
			echo '<table cellpadding="0" cellspacing="0" class="Table">';
			echo '		<tr>';
			echo '				<td colspan="3" class="TD"><strong>Call stack</strong></td>';
			echo '		</tr>';
			echo '		<tr>';
			echo '				<td class="TD"><strong>#</strong></td>';
			echo '				<td class="TD"><strong>Function</strong></td>';
			echo '				<td class="TD"><strong>Location</strong></td>';
			echo '		</tr>';

			for ($i = count($backtrace) - 1; $i >= 0; $i--)
			{
				echo '		<tr>';
				echo '				<td class="TD">' . $j . '</td>';

				if (isset($backtrace[$i]['class']))
				{
					echo '		<td class="TD">' . $backtrace[$i]['class'] . $backtrace[$i]['type'] . $backtrace[$i]['function'] . '()</td>';
				}
				else
				{
					echo '		<td class="TD">' . $backtrace[$i]['function'] . '()</td>';
				}

				if (isset($backtrace[$i]['file']))
				{
					echo '				<td class="TD">' . $backtrace[$i]['file'] . ':' . $backtrace[$i]['line'] . '</td>';
				}
				else
				{
					echo '				<td class="TD">&#160;</td>';
				}

				echo '		</tr>';
				$j++;
			}

			echo '</table>';
			$contents = ob_get_contents();
			ob_end_clean();
		}

		return $contents;
	}
}
