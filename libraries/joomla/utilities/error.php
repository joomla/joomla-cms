<?php
/**
* @version $Id$
* @package Joomla.Framework
* @subpackage Utilities
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Import library dependencies
jimport('pattemplate.patErrorManager');
jimport('joomla.i18n.language');

define('JERR_PHP5', version_compare(phpversion(), '5') >= 0);

/**
 * global definition needed to store the raised errors
 */
$GLOBALS['_JError_errorStore'] = array();

/**
 * Error Handling Class
 *
 * This class is an proxy of the patError class
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	Utilities
 * @since		1.5
 */
class JError extends patErrorManager
{
	/**
	 * method for checking whether the return value of a pat application method is a pat
	 * error object.
	 *
	 * @static
	 * @access	public
	 * @param	mixed	&$object
	 * @return	boolean $result	True if argument is a JError-object, false otherwise.
	 */
	function isError(& $object)
	{
		if (JERR_PHP5) {
			// supports PHP 5 exception handling
			return patErrorManager::isError($object) | is_a($object, 'Exception');
		} else {
			return patErrorManager::isError($object);
		}
	}

	/**
	 * method for retrieving the last error stored
	 *
	 * @static
	 * @access	public
	 * @return	array 	$result	Chronological array of errors that have been stored during script execution
	 */
	function & getError()
	{
		return $GLOBALS['_JError_errorStore'][0];
	}

	/**
	 * method for for retrieving the errors that are stored
	 *
	 * @static
	 * @access	public
	 * @return	array 	$result	Chronological array of errors that have been stored during script execution
	 */
	function & getErrors()
	{
		return $GLOBALS['_JError_errorStore'];
	}

	/**
	 * wrapper for the {@link raise()} method where you do not have to specify the
	 * error level - a {@link patError} object with error level E_ERROR will be returned.
	 *
	 * @static
	 * @access	public
	 * @param	string	$code	The application-internal error code for this error
	 * @param	string	$msg	The error message, which may also be shown the user if need be.
	 * @param	mixed	$info	Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	 * @return	object	$error	The configured JError object
	 * @see		patErrorManager
	 */
	function & raiseError($code, $msg, $info = null)
	{
		$reference = & JError::raise(E_ERROR, $code, $msg, $info);
		return $reference;
	}

	/**
	 * wrapper for the {@link raise()} method where you do not have to specify the
	 * error level - a {@link patError} object with error level E_WARNING will be returned.
	 *
	 * @static
	 * @access	public
	 * @param	string	$code	The application-internal error code for this error
	 * @param	string	$msg	The error message, which may also be shown the user if need be.
	 * @param	mixed	$info	Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	 * @return	object	$error	The configured JError object
	 * @see		patErrorManager
	 */
	function & raiseWarning($code, $msg, $info = null)
	{
		$reference = & JError::raise(E_WARNING, $code, $msg, $info);
		return $reference;
	}

	/**
	 * wrapper for the {@link raise()} method where you do not have to specify the
	 * error level - a {@link patError} object with error level E_NOTICE will be returned.
	 *
	 * @static
	 * @access	public
	 * @param	string	$code	The application-internal error code for this error
	 * @param	string	$msg	The error message, which may also be shown the user if need be.
	 * @param	mixed	$info	Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	 * @return	object	$error	The configured JError object
	 * @see		patErrorManager
	 */
	function & raiseNotice($code, $msg, $info = null)
	{
		$reference = & JError::raise(E_NOTICE, $code, $msg, $info);
		return $reference;
	}

	/**
	 * creates a new patError object given the specified information.
	 *
	 * @access	public
	 * @param	int		$level	The error level - use any of PHP's own error levels for this: E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE.
	 * @param	string	$code	The application-internal error code for this error
	 * @param	string	$msg	The error message, which may also be shown the user if need be.
	 * @param	mixed	$info	Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	 * @return	mixed	$error	The configured patError object or false if this error should be ignored
	 * @see		patError
	 */
	function & raise($level, $code, $msg, $info = null)
	{
		// ignore this error?
		if (in_array($code, $GLOBALS['_pat_errorIgnores'])) {
			return false;
		}

		// this error was expected
		if (!empty ($GLOBALS['_pat_errorExpects'])) {
			$expected = array_pop($GLOBALS['_pat_errorExpects']);
			if (in_array($code, $expected)) {
				return false;
			}
		}

		// need patError
		$class = $GLOBALS['_pat_errorClass'];
		if (!class_exists($class)) {
			jimport('pattemplate.patError');
		}

		// build error object
		$error = new $class($level, $code, $msg, $info);

		// see what to do with this kind of error
		$handling = patErrorManager::getErrorHandling($level);

		//store the error
		$GLOBALS['_JError_errorStore'][] = & $error;

		$function = 'handleError'.ucfirst($handling['mode']);
		if (is_callable(array('JError', $function)))
		{
			return JError::$function ($error, $handling);
		} else {
			// This is required to prevent a very unhelpful white-screen-of-death
			die(
				'JError::raise -> Static method JError::' . $function . ' does not exist.' .
				' Contact a developer to debug' .
				'<br/><strong>Error was</strong> ' .
				'<br/>' . $error->getMessage()
			);
		}
	}

	/**
	 * handleError: Echo
	 * display error message
	 *
	 * @access private
	 * @param object $error patError-Object
	 * @param array $options options for handler
	 * @return object $error error-object
	 * @see raise()
	 */
	function & handleErrorEcho(& $error, $options)
	{
		$level_human = patErrorManager::translateErrorLevel($error->getLevel());

		if (isset ($_SERVER['HTTP_HOST'])) {
			// output as html
			echo "<br /><b>jos-$level_human</b>: ".$error->getMessage()."<br />\n";
		} else {
			// output as simple text
			if (defined('STDERR')) {
				fwrite(STDERR, "jos-$level_human: ".$error->getMessage()."\n");
			} else {
				echo "jos-$level_human: ".$error->getMessage()."\n";
			}
		}
		return $error;
	}

	/**
	 * handleError: Verbose
	 * display verbose output for developing purpose
	 *
	 * @access private
	 * @param object $error patError-Object
	 * @param array $options options for handler
	 * @return object $error error-object
	 * @see raise()
	 */
	function & handleErrorVerbose(& $error, $options)
	{
		$level_human = patErrorManager::translateErrorLevel($error->getLevel());
		$info = $error->getInfo();

		if (isset ($_SERVER['HTTP_HOST'])) {
			// output as html
			echo "<br /><b>J$level_human</b>: ".$error->getMessage()."<br />\n";
			if ($info != null) {
				echo "&nbsp;&nbsp;&nbsp;".$error->getInfo()."<br />\n";
			}
			echo $error->getBacktrace(true);
		} else {
			// output as simple text
			echo "J$level_human: ".$error->getMessage()."\n";
			if ($info != null) {
				echo "    ".$error->getInfo()."\n";
			}

		}
		return $error;
	}

	/**
	 * handleError: die
	 * display error-message and die
	 *
	 * @access private
	 * @param object $error patError-Object
	 * @param array $options options for handler
	 * @return object $error error-object
	 * @see raise()
	 */
	function & handleErrorDie(& $error, $options)
	{
		$level_human = patErrorManager::translateErrorLevel($error->getLevel());

		if (isset ($_SERVER['HTTP_HOST'])) {
			// output as html
			die("<br /><b>J$level_human</b> ".$error->getMessage()."<br />\n");
		} else {
			// output as simple text
			if (defined('STDERR')) {
				fwrite(STDERR, "J$level_human ".$error->getMessage()."\n");
			} else {
				die("J$level_human ".$error->getMessage()."\n");
			}
		}
		return $error;
	}

	/**
	 * handleError: Message
	 * enqueue error message in system queue
	 *
	 * @access private
	 * @param object $error patError-Object
	 * @param array $options options for handler
	 * @return object $error error-object
	 * @see raise()
	 */
	function & handleErrorMessage(& $error, $options)
	{
		global $mainframe;
		$mainframe->enqueueMessage(JText::_($error->getMessage()), 'error');
		return $error;
	}

	/**
	 * handleError: Log
	 * log error message
	 *
	 * @access private
	 * @param object $error patError-Object
	 * @param array $options options for handler
	 * @return object $error error-object
	 * @see raise()
	 */
	function & handleErrorLog(& $error, $options)
	{
		static $log;

		if ($log == null) {
			jimport('joomla.utilities.log');
			$fileName = date('Y-m-d').'.error.log';
			$options['format'] = "{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}";
			$log = & JLog::getInstance($fileName, $options);
		}

		$entry['level'] = $error->getLevel();
		$entry['code'] = $error->getCode();
		$entry['message'] = str_replace(array ("\r","\n"), array ('','\\n'), $error->getMessage());
		$log->addEntry($entry);

		return $error;
	}

	/**
	 * sets the way the patErrorManager will handle teh different error levels. Use this
	 * if you want to override the default settings.
	 *
	 * Error handling modes:
	 * - ignore
	 * - trigger
	 * - verbose
	 * - echo
	 * - callback
	 * - die
	 * - store
	 *
	 * You may also set the error handling for several modes at once using PHP's bit operations.
	 * Examples:
	 * - E_ALL = Set the handling for all levels
	 * - E_ERROR | E_WARNING = Set the handling for errors and warnings
	 * - E_ALL ^ E_ERROR = Set the handling for all levels except errors
	 *
	 * @static
	 * @access	public
	 * @param	int		$level		The error level for which to set the error handling
	 * @param	string	$mode		The mode to use for the error handling.
	 * @param	mixed	$options	Optional: Any options needed for the given mode.
	 * @return	mixed	$result		True on success, or a patError object if failed.
	 * @see		getErrorHandling()
	 */
	function setErrorHandling($level, $mode, $options = null)
	{
		$levels = $GLOBALS['_pat_errorLevels'];

		$function = 'handleError'.ucfirst($mode);
		if (!is_callable(array ('JError',$function))) {
			return JError::raiseError(E_ERROR, 'JError:'.PATERRORMANAGER_ERROR_ILLEGAL_MODE, 'Error Handling mode is not knwon', 'Mode: '.$mode.' is not implemented.');
		}

		foreach ($levels as $eLevel => $eTitle) {
			if (($level & $eLevel) != $eLevel) {
				continue;
			}

			// set callback options
			if ($mode == 'callback') {
				if (!is_array($options)) {
					return JError::raiseError(E_ERROR, 'JError:'.PATERRORMANAGER_ERROR_ILLEGAL_OPTIONS, 'Options for callback not valid');
				}

				if (!is_callable($options)) {
					$tmp = array ('GLOBAL');
					if (is_array($options)) {
						$tmp[0] = $options[0];
						$tmp[1] = $options[1];
					} else {
						$tmp[1] = $options;
					}

					return JError::raiseError(E_ERROR, 'JError:'.PATERRORMANAGER_ERROR_CALLBACK_NOT_CALLABLE, 'Function is not callable', 'Function:'.$tmp[1].' scope '.$tmp[0].'.');
				}
			}

			// save settings
			$GLOBALS['_pat_errorHandling'][$eLevel] = array ('mode' => $mode);
			if ($options != null) {
				$GLOBALS['_pat_errorHandling'][$eLevel]['options'] = $options;
			}
		}

		return true;
	}

	/**
	 * Display a custom error page and exit gracefully
	 *
	 * @access public
	 * @param object $error patError-Object
	 * @return void
	 */
	function customErrorPage(& $error)
	{
		global $mainframe;

		// Initialize variables
		jimport('joomla.document.document');
		$document	= & JDocument::getInstance('error');
		$config		= & JFactory::getConfig();

		// Get the current template from the application
		$template = $mainframe->getTemplate();

		// Push the error object into the document
		$document->setError($error);

		@ob_end_clean();
		jimport('joomla.i18n.language');
		$document->setTitle(JText::_('Error').': '.$error->code);
		$document->display(false, false, array (
			'template' => $template,
			'directory' => JPATH_BASE.DS.'templates',
			'debug' => $config->getValue('config.debug'
		)));
		exit(0);
	}
}

JError::setErrorHandling(E_ERROR, 'callback', array('JError','customErrorPage'));
JError::setErrorHandling(E_WARNING, 'message');
JError::setErrorHandling(E_NOTICE, 'message');
?>
