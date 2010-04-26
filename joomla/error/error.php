<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Error
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

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
 *	- gERD Schaufelberger	<gerd@php-tools.net>
 *	- Sebastian Mordziol	<argh@php-tools.net>
 *	- Stephan Schmidt		<scst@php-tools.net>
 *
 * @static
 * @package	Joomla.Framework
 * @subpackage	Error
 * @since		1.5
 */
abstract class JError
{
	protected static $levels = array(
		E_NOTICE => 'Notice',
		E_WARNING => 'Warning',
		E_ERROR => 'Error'
	);
	protected static $handlers = array(
		E_NOTICE	=> array('mode' => 'message'),
		E_WARNING	=> array('mode' => 'message'),
		E_ERROR	=> array('mode' => 'callback', 'options' => array('JError','customErrorPage'))
	);
	protected static $stack = array();

	/**
	 * Method to determine if a value is an exception object.  This check supports both JException and PHP5 Exception objects
	 *
	 * @static
	 * @access	public
	 * @param	mixed	&$object	Object to check
	 * @return	boolean	True if argument is an exception, false otherwise.
	 * @since	1.5
w
	 */
	public static function isError(& $object)
	{
		// supports PHP 5 exception handling
		return $object INSTANCEOF Exception;
	}

	/**
	 * Method for retrieving the last exception object in the error stack
	 *
	 * @static
	 * @access	public
	 * @return	mixed	Last exception object in the error stack or boolean false if none exist
	 * @since	1.5
	 */
	public static function getError($unset = false)
	{
		if (!isset(JError::$stack[0])) {
			return false;
		}
		if ($unset) {
			$error = array_shift(JError::$stack);
		}
		else {
			$error = &JError::$stack[0];
		}
		return $error;
	}

	/**
	 * Method for retrieving the exception stack
	 *
	 * @static
	 * @access	public
	 * @return	array	Chronological array of errors that have been stored during script execution
	 * @since	1.5
	 */
	public static function getErrors()
	{
		return JError::$stack;
	}

	/**
	 * Method to add non-JError thrown JExceptions to the JError stack for debugging purposes
	 *
	 * @access	public
	 * @param	object JException
	 * @return	void
	 * @since	1.6
	 */
	public static function addToStack(JException &$e) {
		JError::$stack[] = &$e;
	}

	/**
	 * Create a new JException object given the passed arguments
	 *
	 * @static
	 * @param	int		$level	The error level - use any of PHP's own error levels for this: E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE.
	 * @param	string	$code	The application-internal error code for this error
	 * @param	string	$msg	The error message, which may also be shown the user if need be.
	 * @param	mixed	$info	Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	 * @return	mixed	The JException object
	 * @since	1.5
	 *
	 * @see		JException
	 */
	public static function raise($level, $code, $msg, $info = null, $backtrace = false)
	{
		jimport('joomla.error.exception');

		// build error object
		$exception = new JException($msg, $code, $level, $info, $backtrace);
		return JError::throwError($exception);
	}

	public static function throwError(&$exception)
	{
		static $thrown = false;

		// If thrown is hit again, we've come back to JError in the middle of throwing another JError, so die!
		if ($thrown)
		{
			//echo debug_print_backtrace();
			jexit(JText::_('JLIB_ERROR_INFINITE_LOOP'));
		}

		$thrown = true;
		$level = $exception->get('level');

		// see what to do with this kind of error
		$handler = JError::getErrorHandling($level);

		$function = 'handle'.ucfirst($handler['mode']);
		if (is_callable(array('JError', $function))) {
			$reference = &call_user_func_array(array('JError',$function), array(&$exception, (isset($handler['options'])) ? $handler['options'] : array()));
		}
		else {
			// This is required to prevent a very unhelpful white-screen-of-death
			jexit(
				'JError::raise -> Static method JError::' . $function . ' does not exist.' .
				' Contact a developer to debug' .
				'<br /><strong>Error was</strong> ' .
				'<br />' . $exception->getMessage()
			);
		}
		//we don't need to store the error, since JException already does that for us!
		//remove loop check
		$thrown = false;
		return $reference;
	}

	/**
	 * Wrapper method for the {@link raise()} method with predefined error level of E_ERROR and backtrace set to true.
	 *
	 * @static
	 * @param	string	$code	The application-internal error code for this error
	 * @param	string	$msg	The error message, which may also be shown the user if need be.
	 * @param	mixed	$info	Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	 * @return	object	$error	The configured JError object
	 * @since	1.5
	 */
	public static function raiseError($code, $msg, $info = null)
	{
		return JError::raise(E_ERROR, $code, $msg, $info, true);
	}

	/**
	 * Wrapper method for the {@link raise()} method with predefined error level of E_WARNING and backtrace set to false.
	 *
	 * @static
	 * @param	string	$code	The application-internal error code for this error
	 * @param	string	$msg	The error message, which may also be shown the user if need be.
	 * @param	mixed	$info	Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	 * @return	object	$error	The configured JError object
	 * @since	1.5
	 */
	public static function raiseWarning($code, $msg, $info = null)
	{
		return JError::raise(E_WARNING, $code, $msg, $info);
	}

	/**
	 * Wrapper method for the {@link raise()} method with predefined error level of E_NOTICE and backtrace set to false.
	 *
	 * @static
	 * @param	string	$code	The application-internal error code for this error
	 * @param	string	$msg	The error message, which may also be shown the user if need be.
	 * @param	mixed	$info	Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	 * @return	object	$error	The configured JError object
	 * @since	1.5
	 */
	public static function raiseNotice($code, $msg, $info = null)
	{
		return JError::raise(E_NOTICE, $code, $msg, $info);
	}

	/**
	* Method to get the current error handler settings for a specified error level.
	*
	* @static
	* @param	int		$level	The error level to retrieve. This can be any of PHP's own error levels, e.g. E_ALL, E_NOTICE...
	* @return	array	All error handling details
	* @since	1.5
	*/
	public static function getErrorHandling($level)
	{
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
	 * @static
	 * @param	int		$level		The error level for which to set the error handling
	 * @param	string	$mode		The mode to use for the error handling.
	 * @param	mixed	$options	Optional: Any options needed for the given mode.
	 * @return	mixed	True on success, or a JException object if failed.
	 * @since	1.5
	 */
	public static function setErrorHandling($level, $mode, $options = null)
	{
		$levels = JError::$levels;

		$function = 'handle'.ucfirst($mode);
		if (!is_callable(array ('JError',$function))) {
			return JError::raiseError(E_ERROR, 'JError:'.JERROR_ILLEGAL_MODE, 'Error Handling mode is not known', 'Mode: '.$mode.' is not implemented.');
		}

		foreach ($levels as $eLevel => $eTitle)
		{
			if (($level & $eLevel) != $eLevel) {
				continue;
			}

			// set callback options
			if ($mode == 'callback')
			{
				if (!is_array($options)) {
					return JError::raiseError(E_ERROR, 'JError:'.JERROR_ILLEGAL_OPTIONS, 'Options for callback not valid');
				}

				if (!is_callable($options))
				{
					$tmp = array ('GLOBAL');
					if (is_array($options)) {
						$tmp[0] = $options[0];
						$tmp[1] = $options[1];
					}
					else {
						$tmp[1] = $options;
					}

					return JError::raiseError(E_ERROR, 'JError:'.JERROR_CALLBACK_NOT_CALLABLE, 'Function is not callable', 'Function:'.$tmp[1].' scope '.$tmp[0].'.');
				}
			}

			// save settings
			JError::$handlers[$eLevel] = array ('mode' => $mode);
			if ($options != null) {
				JError::$handlers[$eLevel]['options'] = $options;
			}
		}

		return true;
	}

	/**
	 * Method that attaches the error handler to JError
	 *
	 * @access public
	 * @see set_error_handler
	 */
	public static function attachHandler()
	{
		set_error_handler(array('JError', 'customErrorHandler'));
	}

	/**
	 * Method that dettaches the error handler from JError
	 *
	 * @access public
	 * @see restore_error_handler
	 */
	public static function detachHandler()
	{
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
	* @static
	* @param	int		$level		Error level to register
	* @param	string	$name		Human readable name for the error level
	* @param	string	$handler	Error handler to set for the new error level [optional]
	* @return	boolean	True on success; false if the level already has been registered
	* @since	1.5
	*/
	public static function registerErrorLevel($level, $name, $handler = 'ignore')
	{
		if (isset(JError::$levels[$level])) {
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
	* @static
	* @param	int		$level	Error level to translate
	* @return	mixed	Human readable error level name or boolean false if it doesn't exist
	* @since	1.5
	*/
	public static function translateErrorLevel($level)
	{
		if (isset(JError::$levels[$level])) {
			return JError::$levels[$level];
		}
		return false;
	}

	/**
	 * Ignore error handler
	 *	- Ignores the error
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	public static function handleIgnore(&$error, $options)
	{
		return $error;
	}

	/**
	 * Echo error handler
	 *	- Echos the error message to output
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	public static function handleEcho(&$error, $options)
	{
		$level_human = JError::translateErrorLevel($error->get('level'));

		if (isset ($_SERVER['HTTP_HOST']))
		{
			// output as html
			echo "<br /><b>jos-$level_human</b>: ".$error->get('message')."<br />\n";
		}
		else
		{
			// output as simple text
			if (defined('STDERR')) {
				fwrite(STDERR, "J$level_human: ".$error->get('message')."\n");
			}
			else {
				echo "J$level_human: ".$error->get('message')."\n";
			}
		}
		return $error;
	}

	/**
	 * Verbose error handler
	 *	- Echos the error message to output as well as related info
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	public static function handleVerbose(& $error, $options)
	{
		$level_human = JError::translateErrorLevel($error->get('level'));
		$info = $error->get('info');

		if (isset ($_SERVER['HTTP_HOST']))
		{
			// output as html
			echo "<br /><b>J$level_human</b>: ".$error->get('message')."<br />\n";
			if ($info != null) {
				echo "&nbsp;&nbsp;&nbsp;".$info."<br />\n";
			}
			echo $error->getBacktrace(true);
		}
		else
		{
			// output as simple text
			echo "J$level_human: ".$error->get('message')."\n";
			if ($info != null) {
				echo "\t".$info."\n";
			}

		}
		return $error;
	}

	/**
	 * Die error handler
	 *	- Echos the error message to output and then dies
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	public static function handleDie(& $error, $options)
	{
		$level_human = JError::translateErrorLevel($error->get('level'));

		if (isset ($_SERVER['HTTP_HOST']))
		{
			// output as html
			jexit("<br /><b>J$level_human</b> ".$error->get('message')."<br />\n");
		}
		else
		{
			// output as simple text
			if (defined('STDERR')) {
				fwrite(STDERR, "J$level_human ".$error->get('message')."\n");
			}
			else {
				jexit("J$level_human ".$error->get('message')."\n");
			}
		}
		return $error;
	}

	/**
	 * Message error handler
	 *	- Enqueues the error message into the system queue
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	public static function handleMessage(& $error, $options)
	{
		$appl = JFactory::getApplication();
		$type = ($error->get('level') == E_NOTICE) ? 'notice' : 'error';
		$appl->enqueueMessage($error->get('message'), $type);
		return $error;
	}

	/**
	 * Log error handler
	 *	- Logs the error message to a system log file
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	public static function handleLog(& $error, $options)
	{
		static $log;

		if ($log == null)
		{
			jimport('joomla.error.log');
			$fileName = date('Y-m-d').'.error.log';
			$options['format'] = "{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}";
			$log = & JLog::getInstance($fileName, $options);
		}

		$entry['level'] = $error->get('level');
		$entry['code'] = $error->get('code');
		$entry['message'] = str_replace(array ("\r","\n"), array ('','\\n'), $error->get('message'));
		$log->addEntry($entry);

		return $error;
	}

	/**
	 * Callback error handler
	 *	- Send the error object to a callback method for error handling
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	public static function handleCallback(&$error, $options)
	{
		return call_user_func($options, $error);
	}

	/**
	 * Display a custom error page and exit gracefully
	 *
	 * @static
	 * @param	object	$error Exception object
	 * @return	void
	 * @since	1.5
	 */
	public static function customErrorPage(& $error)
	{
		// Initialise variables.
		jimport('joomla.document.document');
		$app		= & JFactory::getApplication();
		$document	= & JDocument::getInstance('error');
		$config		= & JFactory::getConfig();

		// Get the current template from the application
		$template = $app->getTemplate();

		// Push the error object into the document
		$document->setError($error);

		@ob_end_clean();
		$document->setTitle(JText::_('Error').': '.$error->get('code'));
		$data = $document->render(false, array (
			'template' => $template,
			'directory' => JPATH_THEMES,
			'debug' => $config->get('debug')
		));

		JResponse::setBody($data);
		echo JResponse::toString();
		$app->close(0);
	}

	public static function customErrorHandler($level, $msg)
	{
		JError::raise($level, '', $msg);
	}

	public static function renderBacktrace($error)
	{
		$contents	= null;
		$backtrace	= $error->getTrace();
		if (is_array($backtrace))
		{
			ob_start();
			$j = 1;
			echo '<table border="0" cellpadding="0" cellspacing="0" class="Table">';
			echo '		<tr>';
			echo '				<td colspan="3" align="left" class="TD"><strong>Call stack</strong></td>';
			echo '		</tr>';
			echo '		<tr>';
			echo '				<td class="TD"><strong>#</strong></td>';
			echo '				<td class="TD"><strong>Function</strong></td>';
			echo '				<td class="TD"><strong>Location</strong></td>';
			echo '		</tr>';
			for ($i = count($backtrace)-1; $i >= 0 ; $i--)
			{
				echo	'		<tr>';
				echo	'				<td class="TD">'.$j.'</td>';
				if (isset($backtrace[$i]['class'])) {
						echo	'		<td class="TD">'.$backtrace[$i]['class'].$backtrace[$i]['type'].$backtrace[$i]['function'].'()</td>';
				} else {
						echo	'		<td class="TD">'.$backtrace[$i]['function'].'()</td>';
				}
				if (isset($backtrace[$i]['file'])) {
						echo	'				<td class="TD">'.$backtrace[$i]['file'].':'.$backtrace[$i]['line'].'</td>';
				} else {
						echo	'				<td class="TD">&nbsp;</td>';
				}
				echo	'		</tr>';
				$j++;
			}
			echo '</table>';
			$contents = ob_get_contents();
			ob_end_clean();
		}
		return $contents;
	}
}
