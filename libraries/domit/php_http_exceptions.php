<?php
/**
* PHP HTTP Tools is a library for working with the http protocol
* HTTPExceptions is an HTTP Exceptions class
* @package php-http-tools
* @copyright (C) 2004 John Heinstein. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author John Heinstein <johnkarl@nbnet.nb.ca>
* @link http://www.engageinteractive.com/php_http_tools/ PHP HTTP Tools Home Page
* PHP HTTP Tools are Free Software
**/

/** socket connection error */
define('HTTP_SOCKET_CONNECTION_ERR', 1);
/** http transport error */
define('HTTP_TRANSPORT_ERR', 2);

//HTTPExceptions Error Modes
/** continue on error  */
define('HTTP_ONERROR_CONTINUE', 1);
/** die on error  */
define('HTTP_ONERROR_DIE', 2);

/**
* @global object Reference to custom error handler for HTTP Exception class
*/
$GLOBALS['HTTP_Exception_errorHandler'] = null;
/**
* @global int Error mode; specifies whether to die on error or simply return
*/
//$GLOBALS['HTTP_Exception_mode'] = HTTP_ONERROR_RETURN;
// fixes bug identified here: sarahk.pcpropertymanager.com/blog/using-domit-rss/225/
$GLOBALS['HTTP_Exception_mode'] = 1;
/**
* @global string Log file for errors
*/
$GLOBALS['HTTP_Exception_log'] = null;

/**
* An HTTP Exceptions class (not yet implemented)
*
* @package php-http-tools
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class HTTPExceptions {
	function raiseException($errorNum, $errorString) {
		//die('HTTP Exception: ' . $errorNum  .  "\n " . $errorString);

		if ($GLOBALS['HTTP_Exception_errorHandler'] != null) {
			call_user_func($GLOBALS['HTTP_Exception_errorHandler'], $errorNum, $errorString);
		}
		else {
			$errorMessageText = $errorNum  . ' ' . $errorString;
			$errorMessage = 'Error: ' . $errorMessageText;

			if ((!isset($GLOBALS['HTTP_ERROR_FORMATTING_HTML'])) ||
				($GLOBALS['HTTP_ERROR_FORMATTING_HTML'] == true)) {
					$errorMessage = "<p><pre>" . $errorMessage . "</pre></p>";
			}

			//log error to file
			if ((isset($GLOBALS['HTTP_Exception_log'])) &&
				($GLOBALS['HTTP_Exception_log'] != null)) {
					require_once(PHP_HTTP_TOOLS_INCLUDE_PATH . 'php_file_utilities.php');
					$logItem = "\n" . date('Y-m-d H:i:s') . ' HTTP Error ' . $errorMessageText;
					php_file_utilities::putDataToFile($GLOBALS['HTTP_Exception_log'],
										$logItem, 'a');
			}

			switch ($GLOBALS['HTTP_Exception_mode']) {
				case HTTP_ONERROR_CONTINUE:
					return;
					break;

				case HTTP_ONERROR_DIE:
					die($errorMessage);
					break;
			}
		}
	} //raiseException

	/**
	* custom handler for HTTP errors
	* @param object A reference to the custom error handler
	*/
	function setErrorHandler($method) {
		$GLOBALS['HTTP_Exception_errorHandler'] =& $method;
	} //setErrorHandler

	/**
	* Set error mode
	* @param int The HTTP error mode
	*/
	function setErrorMode($mode) {
		$GLOBALS['HTTP_Exception_mode'] = $mode;
	} //setErrorMode

	/**
	* Set error mode
	* @param boolean True if errors should be logged
	* @param string Absolute or relative path to log file
	*/
	function setErrorLog($doLogErrors, $logfile) {
		if ($doLogErrors) {
			$GLOBALS['HTTP_Exception_log'] = $logfile;
		}
		else {
			$GLOBALS['HTTP_Exception_log'] = null;
		}
	} //setErrorLog
} //HTTPExceptions

?>