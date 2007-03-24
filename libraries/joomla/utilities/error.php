<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Utilities
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

// Import library dependencies
jimport('joomla.i18n.language');

define('JERR_PHP5', version_compare(phpversion(), '5') >= 0);
// Error Definition: Illegal Options
define( 'JERR_ILLEGAL_OPTIONS', 1 );
// Error Definition: Callback does not exist
define( 'JERR_CALLBACK_NOT_CALLABLE', 2 );
// Error Definition: Illegal Handler
define( 'JERR_ILLEGAL_MODE', 3 );

/*
 * JError exception stack
 */
$GLOBALS['_JError_Stack'] = array();

/*
 * Default available error levels
 */
$GLOBALS['_JError_Levels'] = array( E_NOTICE => 'Notice', E_WARNING => 'Warning', E_ERROR => 'Error' );

/*
 * Default error handlers
 */
$GLOBALS['_JError_Handlers'] = array( E_NOTICE => array( 'mode' => 'message' ), E_WARNING => array( 'mode' => 'message' ), E_ERROR => array( 'mode' => 'callback', 'options' => array('JError','customErrorPage') ) );

/**
 * Error Handling Class
 *
 * This class is inspired in design and concept by patErrorManager <http://www.php-tools.net>
 *
 * patErrorManager contributors include:
 * 	- gERD Schaufelberger	<gerd@php-tools.net>
 * 	- Sebastian Mordziol	<argh@php-tools.net>
 * 	- Stephan Schmidt		<scst@php-tools.net>
 *
 * @static
 * @author		Louis Landry <louis.landry@joomla.org>
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	Utilities
 * @since		1.5
 */
class JError
{
	/**
	 * Method to determine if a value is an exception object.  This check supports both JException and PHP5 Exception objects
	 *
	 * @static
	 * @access	public
	 * @param	mixed	&$object	Object to check
	 * @return	boolean	True if argument is an exception, false otherwise.
	 * @since	1.5
	 */
	function isError(& $object)
	{
		if (!is_object($object)) {
			return false;
		}
		// supports PHP 5 exception handling
		return is_a($object, 'JException') | is_a($object, 'Exception');
	}

	/**
	 * Method for retrieving the last exception object in the error stack
	 *
	 * @static
	 * @access	public
	 * @return	mixed	Last exception object in the error stack or boolean false if none exist
	 * @since	1.5
	 */
	function & getError($unset = false)
	{
		if (!isset($GLOBALS['_JError_Stack'][0])) {
			$false = false;
			return $false;
		}
		if ($unset) {
			$error = array_shift($GLOBALS['_JError_Stack']);
		} else {
			$error = &$GLOBALS['_JError_Stack'][0];
		}
		return $error;
	}

	/**
	 * Method for retrieving the exception stack
	 *
	 * @static
	 * @access	public
	 * @return	array 	Chronological array of errors that have been stored during script execution
	 * @since	1.5
	 */
	function & getErrors()
	{
		return $GLOBALS['_JError_Stack'];
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
	function & raise($level, $code, $msg, $info = null, $backtrace = false)
	{
		// build error object
		$error = new JException($level, $code, $msg, $info, $backtrace);

		// see what to do with this kind of error
		$handler = JError::getErrorHandling($level);

		$function = 'handle'.ucfirst($handler['mode']);
		if (is_callable(array('JError', $function))) {
			$reference =& JError::$function ($error, (isset($handler['options'])) ? $handler['options'] : array());
		} else {
			// This is required to prevent a very unhelpful white-screen-of-death
			die(
				'JError::raise -> Static method JError::' . $function . ' does not exist.' .
				' Contact a developer to debug' .
				'<br/><strong>Error was</strong> ' .
				'<br/>' . $error->get('message')
			);
		}

		//store and return the error
		$GLOBALS['_JError_Stack'][] =& $reference;
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
	function & raiseError($code, $msg, $info = null)
	{
		$reference = & JError::raise(E_ERROR, $code, $msg, $info, true);
		return $reference;
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
	function & raiseWarning($code, $msg, $info = null)
	{
		$reference = & JError::raise(E_WARNING, $code, $msg, $info);
		return $reference;
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
	function & raiseNotice($code, $msg, $info = null)
	{
		$reference = & JError::raise(E_NOTICE, $code, $msg, $info);
		return $reference;
	}

   /**
	* Method to get the current error handler settings for a specified error level.
	*
	* @static
	* @param	int		$level	The error level to retrieve. This can be any of PHP's own error levels, e.g. E_ALL, E_NOTICE...
	* @return	array	All error handling details
	* @since	1.5
	*/
    function getErrorHandling( $level )
    {
		return $GLOBALS['_JError_Handlers'][$level];
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
	 * - trigger
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
	function setErrorHandling($level, $mode, $options = null)
	{
		$levels = $GLOBALS['_JError_Levels'];

		$function = 'handle'.ucfirst($mode);
		if (!is_callable(array ('JError',$function))) {
			return JError::raiseError(E_ERROR, 'JError:'.JERR_ILLEGAL_MODE, 'Error Handling mode is not knwon', 'Mode: '.$mode.' is not implemented.');
		}

		foreach ($levels as $eLevel => $eTitle) {
			if (($level & $eLevel) != $eLevel) {
				continue;
			}

			// set callback options
			if ($mode == 'callback') {
				if (!is_array($options)) {
					return JError::raiseError(E_ERROR, 'JError:'.JERR_ILLEGAL_OPTIONS, 'Options for callback not valid');
				}

				if (!is_callable($options)) {
					$tmp = array ('GLOBAL');
					if (is_array($options)) {
						$tmp[0] = $options[0];
						$tmp[1] = $options[1];
					} else {
						$tmp[1] = $options;
					}

					return JError::raiseError(E_ERROR, 'JError:'.JERR_CALLBACK_NOT_CALLABLE, 'Function is not callable', 'Function:'.$tmp[1].' scope '.$tmp[0].'.');
				}
			}

			// save settings
			$GLOBALS['_JError_Handlers'][$eLevel] = array ('mode' => $mode);
			if ($options != null) {
				$GLOBALS['_JError_Handlers'][$eLevel]['options'] = $options;
			}
		}

		return true;
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
	function registerErrorLevel( $level, $name, $handler = 'ignore' )
	{
		if( isset($GLOBALS['_JError_Levels'][$level]) ) {
			return false;
		}
		$GLOBALS['_JError_Levels'][$level] = $name;
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
	function translateErrorLevel( $level )
	{
		if( isset($GLOBALS['_JError_Levels'][$level]) ) {
			return $GLOBALS['_JError_Levels'][$level];
		}
		return false;
	}

	/**
	 * Ignore error handler
	 * 	- Ignores the error
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	function & handleIgnore(&$error, $options)
	{
		return $error;
	}

	/**
	 * Echo error handler
	 * 	- Echos the error message to output
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	function & handleEcho(&$error, $options)
	{
		$level_human = JError::translateErrorLevel($error->get('level'));

		if (isset ($_SERVER['HTTP_HOST'])) {
			// output as html
			echo "<br /><b>jos-$level_human</b>: ".$error->get('message')."<br />\n";
		} else {
			// output as simple text
			if (defined('STDERR')) {
				fwrite(STDERR, "jos-$level_human: ".$error->get('message')."\n");
			} else {
				echo "jos-$level_human: ".$error->get('message')."\n";
			}
		}
		return $error;
	}

	/**
	 * Verbose error handler
	 * 	- Echos the error message to output as well as related info
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	function & handleVerbose(& $error, $options)
	{
		$level_human = JError::translateErrorLevel($error->get('level'));
		$info = $error->get('info');

		if (isset ($_SERVER['HTTP_HOST'])) {
			// output as html
			echo "<br /><b>J$level_human</b>: ".$error->get('message')."<br />\n";
			if ($info != null) {
				echo "&nbsp;&nbsp;&nbsp;".$info."<br />\n";
			}
			echo $error->getBacktrace(true);
		} else {
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
	 * 	- Echos the error message to output and then dies
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	function & handleDie(& $error, $options)
	{
		$level_human = JError::translateErrorLevel($error->get('level'));

		if (isset ($_SERVER['HTTP_HOST'])) {
			// output as html
			die("<br /><b>J$level_human</b> ".$error->get('message')."<br />\n");
		} else {
			// output as simple text
			if (defined('STDERR')) {
				fwrite(STDERR, "J$level_human ".$error->get('message')."\n");
			} else {
				die("J$level_human ".$error->get('message')."\n");
			}
		}
		return $error;
	}

	/**
	 * Message error handler
	 * 	- Enqueues the error message into the system queue
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	function & handleMessage(& $error, $options)
	{
		global $mainframe;
		$type = ($error->get('level') == E_NOTICE) ? 'notice' : 'error';
		$mainframe->enqueueMessage($error->get('message'), $type);
		return $error;
	}

	/**
	 * Log error handler
	 * 	- Logs the error message to a system log file
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	function & handleLog(& $error, $options)
	{
		static $log;

		if ($log == null) {
			jimport('joomla.utilities.log');
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
	 * Trigger error handler
	 * 	- Triggers a PHP native error with the error message
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
    function &handleTrigger( &$error, $options )
    {
		switch( $error->get('level') )
		{
			case	E_NOTICE:
				$level	=	E_USER_NOTICE;
				break;
			case	E_WARNING:
				$level	=	E_USER_WARNING;
				break;
			case	E_NOTICE:
				$level =	E_NOTICE;
				break;
			default:
				$level	=	E_USER_ERROR;
				break;
		}

		trigger_error( $error->get('message'), $level );
		return $error;
    }

 	/**
	 * Callback error handler
	 * 	- Send the error object to a callback method for error handling
	 *
	 * @static
	 * @param	object	$error		Exception object to handle
	 * @param	array	$options	Handler options
	 * @return	object	The exception object
	 * @since	1.5
	 *
	 * @see	raise()
	 */
	function &handleCallback( &$error, $options )
	{
		$result = call_user_func( $options, $error );
		return $result;
	}

	/**
	 * Display a custom error page and exit gracefully
	 *
	 * @static
	 * @param	object	$error Exception object
	 * @return	void
	 * @since	1.5
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
		$data = $document->render(false, array (
			'template' => $template,
			'directory' => JPATH_BASE.DS.'templates',
			'debug' => $config->getValue('config.debug')
		));

		JResponse::setBody($data);
		echo JResponse::toString();
		$mainframe->close(0);
	}
}

/**
 * Joomla! Exception object.
 *
 * This class is inspired in design and concept by patError <http://www.php-tools.net>
 *
 * patError contributors include:
 * 	- gERD Schaufelberger	<gerd@php-tools.net>
 * 	- Sebastian Mordziol	<argh@php-tools.net>
 * 	- Stephan Schmidt		<scst@php-tools.net>
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	Utilities
 * @since		1.5
 */
class JException extends JObject
{
   /**
	* Error level
	* @var string
	*/
	var	$level		= null;

   /**
	* Error code
	* @var string
	*/
	var	$code		= null;

   /**
	* Error message
	* @var string
	*/
	var	$message	= null;

   /**
	* Additional info about the error relevant to the developer
	*  - e.g. if a database connect fails, the dsn used
	* @var string
	*/
	var	$info		= '';

   /**
	* Name of the file the error occurred in [Available if backtrace is enabled]
	* @var string
	*/
	var	$file		= null;

   /**
	* Line number the error occurred in [Available if backtrace is enabled]
	* @var int
	*/
	var	$line		= 0;

   /**
	* Name of the method the error occurred in [Available if backtrace is enabled]
	* @var string
	*/
	var	$function	= null;

   /**
	* Name of the class the error occurred in [Available if backtrace is enabled]
	* @var string
	*/
	var	$class		= null;

   /**
    * Error type
	* @var string
	*/
	var	$type		= null;

   /**
	* Arguments recieved by the method the error occurred in [Available if backtrace is enabled]
	* @var array
	*/
	var	$args		= array();

   /**
	* Backtrace information
	* @var mixed
	*/
	var	$backtrace	= false;

   /**
	* Constructor
	* 	- used to set up the error with all needed error details.
	*
	* @access	protected
	* @param	int		$level	The error level (use the PHP constants E_ALL, E_NOTICE etc.).
	* @param	string	$code	The error code from the application
	* @param	string	$msg	The error message
	* @param	string	$info	Optional: The additional error information.
	*/
    function __construct( $level, $code, $msg, $info = null, $backtrace = false )
    {
		$this->level	=	$level;
		$this->code		=	$code;
		$this->message	=	$msg;

		if( $info != null ) {
			$this->info = $info;
		}

		if( $backtrace && function_exists( 'debug_backtrace' ) ) {
			$this->backtrace = debug_backtrace();

			for( $i = count( $this->backtrace ) - 1; $i >= 0; --$i )
			{
				++$i;
				if( isset( $this->backtrace[$i]['file'] ) )
					$this->file		= $this->backtrace[$i]['file'];
				if( isset( $this->backtrace[$i]['line'] ) )
					$this->line		= $this->backtrace[$i]['line'];
				if( isset( $this->backtrace[$i]['class'] ) )
					$this->class	= $this->backtrace[$i]['class'];
				if( isset( $this->backtrace[$i]['function'] ) )
					$this->function	= $this->backtrace[$i]['function'];
				if( isset( $this->backtrace[$i]['type'] ) )
					$this->type		= $this->backtrace[$i]['type'];

				$this->args		= false;
				if( isset( $this->backtrace[$i]['args'] ) ) {
					$this->args		= $this->backtrace[$i]['args'];
				}
				break;
			}
		}
    }

   /**
	* Method to get the backtrace information for an exception object
	*
	* @access	public
	* @return	array backtrace
	* @since	1.5
	*/
	function getBacktrace( $formatted=false )
	{
		if ($formatted && is_array( $this->backtrace )) {
			$result = '';
			foreach( $this->backtrace as $back) {
				if (isset($back['file']) && strpos($back['file'], 'error.php') === false) {
					$result .= '<br />'.$back['file'].':'.$back['line'];
				}
			}
			return $result;
		}
		return $this->backtrace;
	}
}
?>
