<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('pattemplate.patErrorManager');

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
class JError extends patErrorManager {

	/**
	* method for checking whether the return value of a pat application method is a pat
	* error object.
	*
	* @static
	* @access	public
	* @param	mixed	&$object
	* @return	boolean $result	True if argument is a JError-object, false otherwise.
	*/
	function isError( &$object ) {
		return patErrorManager::isError($object);
	}
	
	/**
	* method for for retrieving the errors that are stored
	*
	* @static
	* @access	public
	* @return	array 	$result	Chronological array of errors that have been stored during script execution
	*/
    function getErrors( ) {
		return $GLOBALS['_JError_errorStore'];
    }
	
	/**
	* method to check if any errors have been stored
	*
	* @static
	* @access	public
	* @return	array 	$result	True if any error are stored
	*/
    function hasErrors( ) {
		return count($GLOBALS['_JError_errorStore']);
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
	function &raiseError( $code, $msg, $info = null ) {
		$reference = & JError::raise( E_ERROR, $code, $msg, $info );
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
	function &raiseWarning( $code, $msg, $info = null ) {
		$reference = & JError::raise( E_WARNING, $code, $msg, $info );
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
	function &raiseNotice( $code, $msg, $info = null ) {
		$reference = & JError::raise( E_NOTICE, $code, $msg, $info );
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
    function &raise( $level, $code, $msg, $info = null )
    {
		// ignore this error?
		if( in_array( $code, $GLOBALS['_pat_errorIgnores'] ) )
		{
			return false;
		}

		// this error was expected
		if( !empty( $GLOBALS['_pat_errorExpects'] ) )
		{
			$expected =	array_pop( $GLOBALS['_pat_errorExpects'] );
			if( in_array( $code, $expected ) )
			{
				return false;
			}
		}

		// need patError
		$class	=	$GLOBALS['_pat_errorClass'];
		if( !class_exists( $class ) )
		{
			jimport('pattemplate.patError');
		}

		// build error object
		$error			=&	new	$class( $level, $code, $msg, $info );

		// see what to do with this kind of error
		$handling	=	patErrorManager::getErrorHandling( $level );

		$function	=	'handleError' . ucfirst( $handling['mode'] );
		return JError::$function( $error, $handling );
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
    function &handleErrorEcho( &$error, $options )
    {
		$level_human	=	patErrorManager::translateErrorLevel( $error->getLevel() );

		if( isset( $_SERVER['HTTP_HOST'] ) )
		{
			// output as html
			echo "<br /><b>jos-$level_human</b>: " . $error->getMessage() . "<br />\n";
		}
		else
		{
			// output as simple text
			if( defined( 'STDERR' ) )
			{
				fwrite( STDERR, "jos-$level_human: " . $error->getMessage() . "\n" );
			}
			else
			{
				echo "jos-$level_human: " . $error->getMessage() . "\n";
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
    function &handleErrorVerbose( &$error, $options )
    {
		$level_human	=	patErrorManager::translateErrorLevel( $error->getLevel() );
		$info			=	$error->getInfo();

		if( isset( $_SERVER['HTTP_HOST'] ) )
		{
			// output as html
			echo "<br /><b>J$level_human</b>: " . $error->getMessage() . "<br />\n";
			if( $info != null )
			{
				echo "&nbsp;&nbsp;&nbsp;" . $error->getInfo() . "<br />\n";
			}
			echo $error->getBacktrace( true );
		}
		else
		{
			// output as simple text
			echo "J$level_human: " . $error->getMessage() . "\n";
			if( $info != null )
			{
				echo "    " . $error->getInfo() . "\n";
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
    function &handleErrorDie( &$error, $options )
    {
		$level_human	=	patErrorManager::translateErrorLevel( $error->getLevel() );

		if( isset( $_SERVER['HTTP_HOST'] ) )
		{
			// output as html
			die( "<br /><b>J$level_human</b> " . $error->getMessage() . "<br />\n" );
		}
		else
		{
			// output as simple text
			if( defined( 'STDERR' ) )
			{
				fwrite( STDERR, "J$level_human " . $error->getMessage() . "\n" );
			}
			else
			{
				die( "J$level_human " . $error->getMessage() . "\n" );
			}
		}
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
    function setErrorHandling( $level, $mode, $options = null )
    {
		$levels	=	$GLOBALS['_pat_errorLevels'];

		$function	=	'handleError' . ucfirst( $mode );
		if( !is_callable( array( 'JError', $function ) ) )
		{
			return JError::raiseError( E_ERROR,
												'JError:' . PATERRORMANAGER_ERROR_ILLEGAL_MODE,
												'Error Handling mode is not knwon',
												'Mode: ' .  $mode . ' is not implemented.'
												);
		}

		foreach( $levels as $eLevel => $eTitle )
		{
			if( ( $level & $eLevel ) != $eLevel )
			{
				continue;
			}

			// set callback options
			if( $mode == 'callback' )
			{
				if( !is_array( $options ) )
				{
					return JError::raiseError( E_ERROR,
														'JError:' . PATERRORMANAGER_ERROR_ILLEGAL_OPTIONS,
														'Options for callback not valid'
														);
				}

				if( !is_callable( $options ) )
				{
					$tmp	=	array( 'GLOBAL' );
					if( is_array( $options ) )
					{
						$tmp[0]	=	$options[0];
						$tmp[1]	=	$options[1];
					}
					else
					{
						$tmp[1]	=	$options;
					}

					return JError::raiseError(	E_ERROR,
														'JError:' . PATERRORMANAGER_ERROR_CALLBACK_NOT_CALLABLE,
														'Function is not callable',
														'Function:' . $tmp[1]  . ' scope ' . $tmp[0] . '.'
														);
				}
			}


			// save settings
			$GLOBALS['_pat_errorHandling'][$eLevel]	=	array( 'mode' => $mode );
			if( $options	!= null )
			{
				$GLOBALS['_pat_errorHandling'][$eLevel]['options']	=	$options;
			}
		}

        return  true;
    }
}

/**
 * custom JError handler for the callback error handling mode
 *
 * @package 	Joomla.Framework
 * @subpackage	Utilities
 * @since		1.5
 */
class JErrorHandler
{
	
   /**
	* Method to handle an error
	*
	* @access		public
	* @param		object	Error object
	* @return	mixed	Error object or void
	* @since		1.5
	*/
	function &handleError( &$error )
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$document =& JApplication::getDocument();

		/*
		 * Send the error header for the appropriate error code 
		 */
		$this->_sendErrorHeader( $error );

		/*
		 * Get the current template from the application and the appropriate
		 * error file to parse and display
		 */
		$template = $mainframe->getTemplate();
		$file	  = $this->getErrorDocument( $error );

		/*
		 * Need to clear the renderers array so we don't have a bad case of
		 * infinite recursion on a database error among other things.
		 */
		$document->_renderers = array ();

		$document->parse($template, $file);

		$document->setTitle( 'Joomla! - Error: '.$error->code );

		$html = $this->getErrorMessage($error);
		$document->addGlobalVar('error_msg', $html);

		$document->display($file);

		
		/*
		 * If error level is less than E_ERROR, return the object and
		 * continue... otherwise exit
		 */
		$level =	$error->getLevel();
		if( $level != E_ERROR ) {
			return	$error;
		}
		exit();
	}
	
   /**
	* Method to get error template for display
	*
	* @access		public
	* @param		object	Error object
	* @return	string	Error code template
	* @since		1.5
	*/
	function getErrorDocument( &$error )
	{
		switch ($error->code)
		{
			case '403':
				$file	= '403.html';
				break;

			case '404':
				$file	= '404.html';
				break;

			case '500':
			default:
				$file	= '500.html';
				break;
		}
		return $file;
	}
	
   /**
	* Method to get error message for display
	*
	* @access		public
	* @param		object	Error object
	* @return	string	Error message
	* @since		1.5
	*/
	function getErrorMessage( &$error )
	{
		switch ($error->code)
		{
			case '403':
				$msg = $error->message;
				break;

			case '404':
				$msg = $error->message;
				break;

			case '500':
			default:
				$msg = $this->_fetchDebug($error);
				break;
		}
		return $msg;
	}
	
   /**
	* Send appropriate HTTP header for error code
	*
	* @access		public
	* @param		object	Error object
	* @return	void
	* @since		1.5
	*/
	function _sendErrorHeader( &$error )
	{
		switch ($error->code)
		{
			case '403':
				header('HTTP/1.5 403 Forbidden');
				break;

			case '404':
				header('HTTP/1.5 404 Not Found');
				break;

			case '500':
			default:
				header('HTTP/1.5 500 Internal Server Error');
				break;
		}
		return;
	}
	
   /**
	* Method to output pretty debugging HTML
	*
	* Displays:
	* - Error level
	* - Error Message
	* - Error info
	* - Error file
	* - Error line
	* - plus the call stack that lead to the error
	*
	* @static
	* @access		public
	* @param		object	Error object
	* @return	string	HTML Debugging string
	* @since		1.5
	*/
	function _fetchDebug(&$error)
	{
		ob_start();
		
		echo  	'<div class="Frame">';
		printf(
				'<div style="margin-bottom:8px;"><span class="Type">%s:</span> %s in %s on line %s</div>Details: %s',
				patErrorManager::translateErrorLevel( $error->getLevel() ),
				$error->getMessage(),
				$error->getFile(),
				$error->getLine(),
				$error->getInfo()
			);

		$backtrace	=	$error->getBacktrace();
		if( is_array( $backtrace ) )
		{
			$j	=	1;
			echo  	'<table border="0" cellpadding="0" cellspacing="0" class="Table">';
			echo  	'	<tr>';
			echo  	'		<td colspan="3" align="left" class="TD"><strong>Call stack</strong></td>';
			echo  	'	</tr>';
			echo  	'	<tr>';
			echo  	'		<td class="TD"><strong>#</strong></td>';
			echo  	'		<td class="TD"><strong>Function</strong></td>';
			echo  	'		<td class="TD"><strong>Location</strong></td>';
			echo  	'	</tr>';
			for( $i = count( $backtrace )-1; $i >= 0 ; $i-- )
			{
				echo  	'	<tr>';
				echo  	'		<td class="TD">'.$j.'</td>';
				if( isset( $backtrace[$i]['class'] ) )
				{
					echo  	'	<td class="TD">'.$backtrace[$i]['class'].$backtrace[$i]['type'].$backtrace[$i]['function'].'()</td>';
				}
				else
				{
					echo  	'	<td class="TD">'.$backtrace[$i]['function'].'()</td>';
				}
				if( isset( $backtrace[$i]['file'] ) )
				{
					echo  	'		<td class="TD">'.$backtrace[$i]['file'].':'.$backtrace[$i]['line'].'</td>';
				}
				else
				{
					echo  	'		<td class="TD">&nbsp;</td>';
				}
				echo  	'	</tr>';
				$j++;
			}
			echo  	'</table>';
		}
		echo  	'</div>';
		
		$contents = ob_get_contents();
		ob_end_clean();

        return $contents;
	}
}
	
// setup handler for each error-level
JError::setErrorHandling( E_ERROR  , 'callback', array( new JErrorHandler, 'handleError' ) );
JError::setErrorHandling( E_WARNING, 'verbose' );
JError::setErrorHandling( E_NOTICE , 'verbose' );

?>