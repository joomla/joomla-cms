<?php
/**
 * patError error object used by the patFormsError manager as error messages
 * container for precise error management.
 *
 *	$Id$
 *
 * @access		public
 * @package		patError
 */

/**
 * patError error object used by the patFormsError manager as error messages
 * container for precise error management.
 *
 * $Id$
 *
 * @access		public
 * @package		patError
 * @version		0.3
 * @author		gERD Schaufelberger <gerd@php-tools.net>
 * @author		Sebastian Mordziol <argh@php-tools.net>
 * @author		Stephan Schmidt <schst@php-tools.net>
 * @license		LGPL
 * @link		http://www.php-tools.net
 */
class patError
{
   /**
	* stores the error level for this error
	*
	* @access	private
	* @var		string
	*/
	var	$level  =   null;

   /**
	* stores the code of the error
	*
	* @access	private
	* @var		string
	*/
	var	$code  =   null;

   /**
	* stores the error message - this is the message that can also be shown the
	* user if need be.
	*
	* @access	private
	* @var		string
	*/
	var	$message  =   null;

   /**
	* additional info that is relevant for the developer of the script (e.g. if
	* a database connect fails, the dsn used) and that the end-user should not
	* see.
	*
	* @access	private
	* @var		string
	*/
	var	$info  =   '';

   /**
	* stores the filename of the file the error occurred in.
	*
	* @access	private
	* @var		string
	*/
	var	$file  =   '';

   /**
	* stores the line number the error occurred in.
	*
	* @access	private
	* @var		integer
	*/
	var	$line  =   0;

   /**
	* stores the name of the method the error occurred in
	*
	* @access	private
	* @var		string
	*/
	var	$function  =   '';

   /**
	* stores the name of the class (if any) the error occurred in.
	*
	* @access	private
	* @var		string
	*/
	var	$class  =   '';

   /**
	* stores the type of error, as it is listed in the error backtrace
	*
	* @access	private
	* @var		string
	*/
	var	$type  =   '';

   /**
	* stores the arguments the method that the error occurred in had received.
	*
	* @access	private
	* @var		array
	*/
	var	$args  =   array();

   /**
	* stores the complete debug backtrace (if your PHP version has the
	* debug_backtrace function)
	*
	* @access	private
	* @var		mixed
	*/
	var	$backtrace  =   false;

   /**
	* constructor, wrapper for the upcoming PHP5 constructors for upward
	* compatibility.
	*
	* @access	public
	* @param	int		$level	The error level (use the PHP constants E_ALL, E_NOTICE etc.).
	* @param	string	$code	The error code from the application
	* @param	string	$msg	The error message
	* @param	string	$info	Optional: The additional error information.
	* @see		__construct()
	*/
    function patError( $level, $code, $msg, $info = null )
    {
		$this->__construct( $level, $code, $msg, $info );
    }

   /**
	* constructor - used to set up the error with all needed error details.
	*
	* @access	public
	* @param	int		$level	The error level (use the PHP constants E_ALL, E_NOTICE etc.).
	* @param	string	$code	The error code from the application
	* @param	string	$msg	The error message
	* @param	string	$info	Optional: The additional error information.
	* @todo		all calls to patErrorManager::raise* should not be included in backtrace
	*/
    function __construct( $level, $code, $msg, $info = null )
    {
		static $raise		=	array(	'raise',
										'raiseerror',
										'raisewarning',
										'raisenotice'
									);

		$this->level	=	$level;
		$this->code		=	$code;
		$this->message	=	$msg;

		if( $info != null )
		{
			$this->info = $info;
		}

		if( function_exists( 'debug_backtrace' ) )
		{
			$this->backtrace	=	debug_backtrace();

			for( $i = count( $this->backtrace ) - 1; $i >= 0; --$i )
			{
				if( in_array( $this->backtrace[$i]['function'], $raise ) )
				{
					++$i;
					if( isset( $this->backtrace[$i]['file'] ) )
						$this->file		=	$this->backtrace[$i]['file'];
					if( isset( $this->backtrace[$i]['line'] ) )
						$this->line		=	$this->backtrace[$i]['line'];
					if( isset( $this->backtrace[$i]['class'] ) )
						$this->class	=	$this->backtrace[$i]['class'];
					if( isset( $this->backtrace[$i]['function'] ) )
						$this->function	=	$this->backtrace[$i]['function'];
					if( isset( $this->backtrace[$i]['type'] ) )
						$this->type		=	$this->backtrace[$i]['type'];

					$this->args		=	false;
					if( isset( $this->backtrace[$i]['args'] ) )
					{
						$this->args		=	$this->backtrace[$i]['args'];
					}
					break;
				}
			}
		}
    }

   /**
	* returns the error level of the error - corresponds to the PHP
	* error levels (E_ALL, E_NOTICE...)
	*
	* @access	public
	* @return	int $level	The error level
	* @see		$level
	*/
    function getLevel()
    {
        return  $this->level;
    }


   /**
	* retrieves the error message
	*
	* @access	public
	* @return	string 	$msg	The stored error message
	* @see		$message
	*/
    function getMessage()
    {
		return $this->message;
    }

   /**
	* retrieves the additional error information (information usually
	* only relevant for developers)
	*
	* @access	public
	* @return	mixed $info	The additional information
	* @see		$info
	*/
    function getInfo()
    {
		return $this->info;
    }

   /**
	* recieve error code
	*
	* @access	public
	* @return	string|integer		error code (may be a string or an integer)
	* @see		$code
	*/
    function getCode()
    {
		return $this->code;
    }

   /**
	* get the backtrace
	*
	* This is only possible, if debug_backtrace() is available.
	*
	* @access	public
	* @return	array backtrace
	* @see		$backtrace
	*/
    function getBacktrace( $formatted=false )
    {
    	if ($formatted && is_array( $this->backtrace )) {
    		$result = '';
    		foreach( debug_backtrace() as $back) {
			    if (!eregi( 'patErrorManager.php', $back['file'])) {
				    $result .= '<br />'.$back['file'].':'.$back['line'];
				}
			}
			return $result;
		}
		return $this->backtrace;
    }

   /**
	* get the filename in which the error occured
	*
	* This is only possible, if debug_backtrace() is available.
	*
	* @access	public
	* @return	string filename
	* @see		$file
	*/
    function getFile()
    {
		return $this->file;
    }

   /**
	* get the line number in which the error occured
	*
	* This is only possible, if debug_backtrace() is available.
	*
	* @access	public
	* @return	integer line number
	* @see		$line
	*/
    function getLine()
    {
		return $this->line;
    }
}
?>