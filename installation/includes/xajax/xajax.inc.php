<?php
///////////////////////////////////////////////////////////////////////////////
// xajax.inc.php::Main xajax class and setup file
//
// xajax version 0.2
// Copyright (C) 2005 - 2006 by Jared White & J. Max Wilson
// http://xajax.sourceforge.net
//
// xajax is an open source PHP class library for easily creating powerful
// PHP-driven, web-based AJAX Applications. Using xajax, you can asynchronously
// call PHP functions and update the content of your your webpage without
// reloading the page.
//
// xajax is released under the terms of the LGPL license
// http://www.gnu.org/copyleft/lesser.html#SEC3
//
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or (at your option) any later version.
//
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public
// License along with this library; if not, write to the Free Software
// Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
///////////////////////////////////////////////////////////////////////////////

// Define XAJAX_DEFAULT_CHAR_ENCODING that is used by both
// the xajax and xajaxResponse classes
if (!defined ('XAJAX_DEFAULT_CHAR_ENCODING')) {
	define ('XAJAX_DEFAULT_CHAR_ENCODING', 'utf-8' );
}

require_once(dirname( __FILE__ ).DS."xajaxResponse.inc.php");

// Communication Method Defines
if (!defined ('XAJAX_GET'))
{
	define ('XAJAX_GET', 0);
}
if (!defined ('XAJAX_POST'))
{
	define ('XAJAX_POST', 1);
}

// the xajax class generates the xajax javascript for your page including the
// javascript wrappers for the PHP functions that you want to call from your page.
// It also handles processing and executing the command messages in the xml responses
// sent back to your page from your PHP functions.
class xajax
{
	var $aFunctions;				// Array of PHP functions that will be callable through javascript wrappers
	var $aObjects;				// Array of object callbacks that will allow Javascript to call PHP methods (key=function name)
	var $aFunctionRequestTypes;	// Array of RequestTypes to be used with each function (key=function name)
	var $aFunctionIncludeFiles;	// Array of Include Files for any external functions (key=function name)
	var $sCatchAllFunction;		// Name of the PHP function to call if no callable function was found
	var $sPreFunction;			// Name of the PHP function to call before any other function
	var $sRequestURI;			// The URI for making requests to the xajax object
	var $bDebug;					// Show debug messages (true/false)
	var $bExitAllowed;			// Allow xajax to exit after processing a request (true/false)
	var $bErrorHandler;			// Use an special xajax error handler so the errors are sent to the browser properly
	var $sLogFile;				// Specify if xajax should log errors (and more information in a future release)
	var $sWrapperPrefix;			// The prefix to prepend to the javascript wraper function name
	var $bStatusMessages;			// Show debug messages (true/false)
	var $bWaitCursor;			// Use wait cursor in browser (true/false)
	var $bCleanBuffer;			// Clean all output buffers before outputting response (true/false)
	var $aObjArray;				// Array for parsing complex objects
	var $iPos;					// Position in $aObjArray
	var $sEncoding;				// The Character Encoding to use

	// Contructor
	// $sRequestURI - defaults to the current page
	// $sWrapperPrefix - defaults to "xajax_";
	// $sEncoding - defaults to XAJAX_DEFAULT_CHAR_ENCODING defined above
	// $bDebug Mode - defaults to false
	// usage: $xajax = new xajax();
	function xajax($sRequestURI="",$sWrapperPrefix="xajax_",$sEncoding=XAJAX_DEFAULT_CHAR_ENCODING,$bDebug=false)
	{
		$this->aFunctions = array();
		$this->aObjects = array();
		$this->aFunctionIncludeFiles = array();
		$this->sRequestURI = $sRequestURI;
		if ($this->sRequestURI == "")
			$this->sRequestURI = $this->_detectURI();
		$this->sWrapperPrefix = $sWrapperPrefix;
		$this->setCharEncoding($sEncoding);
		$this->bDebug = $bDebug;
		$this->bWaitCursor = true;
		$this->bExitAllowed = true;
		$this->bErrorHandler = false;
		$this->sLogFile = "";
		$this->bCleanBuffer = true;
	}

	// setRequestURI() sets the URI to which requests will be made
	// usage: $xajax->setRequestURI("http://xajax.sourceforge.net");
	function setRequestURI($sRequestURI)
	{
		$this->sRequestURI = $sRequestURI;
	}

	// debugOn() enables debug messages for xajax
	function debugOn()
	{
		$this->bDebug = true;
	}

	// debugOff() disables debug messages for xajax (default behavior)
	function debugOff()
	{
		$this->bDebug = false;
	}

	// statusMessagesOn() enables messages in the statusbar for xajax
	function statusMessagesOn()
	{
		$this->bStatusMessages = true;
	}

	// statusMessagesOff() disables messages in the statusbar for xajax (default behavior)
	function statusMessagesOff()
	{
		$this->bStatusMessages = false;
	}

	// waitCursor() enables the wait cursor to be displayed in the browser (default behavior)
	function waitCursorOn()
	{
		$this->bWaitCursor = true;
	}

	// waitCursorOff() disables the wait cursor to be displayed in the browser
	function waitCursorOff()
	{
		$this->bWaitCursor = false;
	}

	// exitAllowedOn() enables xajax to exit immediately after processing a request
	// and sending the response back to the browser (default behavior)
	function exitAllowedOn()
	{
		$this->bExitAllowed = true;
	}

	// exitAllowedOff() disables xajax's default behavior of exiting immediately
	// after processing a request and sending the response back to the browser
	function exitAllowedOff()
	{
		$this->bExitAllowed = false;
	}

	// errorHandlerOn() turns on xajax's error handling system so that PHP errors
	// that occur during a request are trapped and pushed to the browser in the
	// form of a Javascript alert
	function errorHandlerOn()
	{
		$this->bErrorHandler = true;
	}
	// errorHandlerOff() turns off xajax's error handling system (default behavior)
	function errorHandlerOff()
	{
		$this->bErrorHandler = false;
	}

	// setLogFile() specifies a log file that will be written to by xajax during
	// a request (used only by the error handling system at present). If you don't
	// invoke this method, or you pass in "", then no log file will be written to.
	// usage: $xajax->setLogFile("/xajax_logs/errors.log");
	function setLogFile($sFilename)
	{
		$this->sLogFile = $sFilename;
	}

	// cleanBufferOn() causes xajax to clean out all output buffers before outputting
	// a response (default behavior)
	function cleanBufferOn()
	{
		$this->bCleanBuffer = true;
	}
	// cleanBufferOff() turns off xajax's output buffer cleaning
	function cleanBufferOff()
	{
		$this->bCleanBuffer = false;
	}

	// setWrapperPrefix() sets the prefix that will be appended to the Javascript
	// wrapper functions (default is "xajax_").
	function setWrapperPrefix($sPrefix)
	{
		$this->sWrapperPrefix = $sPrefix;
	}

	// setCharEncoding() sets the character encoding to be used by xajax
	// usage: $xajax->setCharEncoding("utf-8");
	// *Note: to change the default character encoding for all xajax responses, set
	// the XAJAX_DEFAULT_CHAR_ENCODING constant near the beginning of the xajax.inc.php file
	function setCharEncoding($sEncoding)
	{
		$this->sEncoding = $sEncoding;
	}

	// registerFunction() registers a PHP function or method to be callable through
	// xajax in your Javascript. If you want to register a function, pass in the name
	// of that function. If you want to register a static class method, pass in an array
	// like so:
	// array("myFunctionName", "myClass", "myMethod")
	// For an object instance method, use an object variable for the second array element
	// (and in PHP 4 make sure you put an & before the variable to pass the object by
	// reference). Note: the function name is what you call via Javascript, so it can be
	// anything as long as it doesn't conflict with any other registered function name.
	//
	// $mFunction is a string containing the function name or an object callback array
	// $sRequestType is the RequestType (XAJAX_GET/XAJAX_POST) that should be used
	//		for this function.  Defaults to XAJAX_POST.
	// usage: $xajax->registerFunction("myFunction");
	//    or: $xajax->registerFunction(array("myFunctionName", &$myObject, "myMethod"));
	function registerFunction($mFunction,$sRequestType=XAJAX_POST)
	{
		if (is_array($mFunction)) {
			$this->aFunctions[$mFunction[0]] = 1;
			$this->aFunctionRequestTypes[$mFunction[0]] = $sRequestType;
			$this->aObjects[$mFunction[0]] = array_slice($mFunction, 1);
		}
		else {
			$this->aFunctions[$mFunction] = 1;
			$this->aFunctionRequestTypes[$mFunction] = $sRequestType;
		}
	}

	// registerExternalFunction() registers a PHP function to be callable through xajax
	// which is located in some other file.  If the function is requested the external
	// file will be included to define the function before the function is called
	// $mFunction is a string containing the function name or an object callback array
	//   see registerFunction() for more info on object callback arrays
	// $sIncludeFile is a string containing the path and filename of the include file
	// $sRequestType is the RequestType (XAJAX_GET/XAJAX_POST) that should be used
	//		for this function.  Defaults to XAJAX_POST.
	// usage: $xajax->registerExternalFunction("myFunction","myFunction.inc.php",XAJAX_POST);
	function registerExternalFunction($mFunction,$sIncludeFile,$sRequestType=XAJAX_POST)
	{
		$this->registerFunction($mFunction, $sRequestType);

		if (is_array($mFunction)) {
			$this->aFunctionIncludeFiles[$mFunction[0]] = $sIncludeFile;
		}
		else {
			$this->aFunctionIncludeFiles[$mFunction] = $sIncludeFile;
		}
	}

	// registerCatchAllFunction() registers a PHP function to be called when xajax cannot
	// find the function being called via Javascript. Because this is technically
	// impossible when using "wrapped" functions, the catch-all feature is only useful
	// when you're directly using the xajax.call() Javascript method. Use the catch-all
	// feature when you want more dynamic ability to intercept unknown calls and handle
	// them in a custom way.
	// $mFunction is a string containing the function name or an object callback array
	//   see registerFunction() for more info on object callback arrays
	// usage: $xajax->registerCatchAllFunction("myCatchAllFunction");
	function registerCatchAllFunction($mFunction)
	{
		if (is_array($mFunction)) {
			$this->sCatchAllFunction = $mFunction[0];
			$this->aObjects[$mFunction[0]] = array_slice($mFunction, 1);
		}
		else {
			$this->sCatchAllFunction = $mFunction;
		}
	}

	// registerPreFunction() registers a PHP function to be called before xajax calls
	// the requested function. xajax will automatically add the request function's response
	// to the pre-function's response to create a single response. Another feature is
	// the ability to return not just a response, but an array with the first element
	// being false (a boolean) and the second being the response. In this case, the
	// pre-function's response will be returned to the browser without xajax calling
	// the requested function.
	// $mFunction is a string containing the function name or an object callback array
	//   see registerFunction() for more info on object callback arrays
	// usage $xajax->registerPreFunction("myPreFunction");
	function registerPreFunction($mFunction)
	{
		if (is_array($mFunction)) {
			$this->sPreFunction = $mFunction[0];
			$this->aObjects[$mFunction[0]] = array_slice($mFunction, 1);
		}
		else {
			$this->sPreFunction = $mFunction;
		}
	}

	// returns true if xajax can process the request, false if otherwise
	// you can use this to determine if xajax needs to process the request or not
	function canProcessRequests()
	{
		if ($this->getRequestMode() != -1) return true;
		return false;
	}

	// returns the current request mode, or -1 if there is none
	function getRequestMode()
	{
		if (!empty($_GET["xajax"]))
			return XAJAX_GET;

		if (!empty($_POST["xajax"]))
			return XAJAX_POST;

		return -1;
	}

	// processRequests() is the main communications engine of xajax
	// The engine handles all incoming xajax requests, calls the apporiate PHP functions
	// and passes the xml responses back to the javascript response handler
	// if your RequestURI is the same as your web page then this function should
	// be called before any headers or html has been sent.
	// usage: $xajax->processRequests()
	function processRequests()
	{

		$requestMode = -1;
		$sFunctionName = "";
		$bFoundFunction = true;
		$bFunctionIsCatchAll = false;
		$sFunctionNameForSpecial = "";
		$aArgs = array();
		$sPreResponse = "";
		$bEndRequest = false;
		$sResponse = "";

		$requestMode = $this->getRequestMode();
		if ($requestMode == -1) return;

		if ($requestMode == XAJAX_POST)
		{
			$sFunctionName = $_POST["xajax"];

			if (!empty($_POST["xajaxargs"]))
				$aArgs = $_POST["xajaxargs"];
		}
		else
		{
			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header ("Cache-Control: no-cache, must-revalidate");
			header ("Pragma: no-cache");
			header("Content-type: text/xml");

			$sFunctionName = $_GET["xajax"];

			if (!empty($_GET["xajaxargs"]))
				$aArgs = $_GET["xajaxargs"];
		}

		// Use xajax error handler if necessary
		if ($this->bErrorHandler) {
			$GLOBALS['xajaxErrorHandlerText'] = "";
			set_error_handler("xajaxErrorHandler");
		}

		if ($this->sPreFunction) {
			if (!$this->_isFunctionCallable($this->sPreFunction)) {
				$bFoundFunction = false;
				$objResponse = new xajaxResponse();
				$objResponse->addAlert("Unknown Pre-Function ". $this->sPreFunction);
				$sResponse = $objResponse->getXML();
			}
		}
		//include any external dependencies associated with this function name
		if (array_key_exists($sFunctionName,$this->aFunctionIncludeFiles))
		{
			ob_start();
			include_once($this->aFunctionIncludeFiles[$sFunctionName]);
			ob_end_clean();
		}

		if ($bFoundFunction) {
			$sFunctionNameForSpecial = $sFunctionName;
			if (!array_key_exists($sFunctionName, $this->aFunctions))
			{
				if ($this->sCatchAllFunction) {
					$sFunctionName = $this->sCatchAllFunction;
					$bFunctionIsCatchAll = true;
				}
				else {
					$bFoundFunction = false;
					$objResponse = new xajaxResponse();
					$objResponse->addAlert("Unknown Function $sFunctionName.");
					$sResponse = $objResponse->getXML();
				}
			}
			else if ($this->aFunctionRequestTypes[$sFunctionName] != $requestMode)
			{
				$bFoundFunction = false;
				$objResponse = new xajaxResponse();
				$objResponse->addAlert("Incorrect Request Type.");
				$sResponse = $objResponse->getXML();
			}
		}

		if ($bFoundFunction)
		{
			for ($i = 0; $i < sizeof($aArgs); $i++)
			{
				// If magic quotes is on, then we need to strip the slashes from the args
				if (get_magic_quotes_gpc() == 1 && is_string($aArgs[$i])) {

					$aArgs[$i] = stripslashes($aArgs[$i]);
				}
				if (stristr($aArgs[$i],"<xjxobj>") != false)
				{
					$aArgs[$i] = $this->_xmlToArray("xjxobj",$aArgs[$i]);
				}
				else if (stristr($aArgs[$i],"<xjxquery>") != false)
				{
					$aArgs[$i] = $this->_xmlToArray("xjxquery",$aArgs[$i]);
				}
			}

			if ($this->sPreFunction) {
				$mPreResponse = $this->_callFunction($this->sPreFunction, array($sFunctionNameForSpecial, $aArgs));
				if (is_array($mPreResponse) && $mPreResponse[0] === false) {
					$bEndRequest = true;
					$sPreResponse = $mPreResponse[1];
				}
				else {
					$sPreResponse = $mPreResponse;
				}
				if (is_a($sPreResponse, "xajaxResponse")) {
					$sPreResponse = $sPreResponse->getXML();
				}
				if ($bEndRequest) $sResponse = $sPreResponse;
			}

			if (!$bEndRequest) {
				if (!$this->_isFunctionCallable($sFunctionName)) {
					$objResponse = new xajaxResponse();
					$objResponse->addAlert("The Registered Function $sFunctionName Could Not Be Found.");
					$sResponse = $objResponse->getXML();
				}
				else {
					if ($bFunctionIsCatchAll) {
						$aArgs = array($sFunctionNameForSpecial, $aArgs);
					}
					$sResponse = $this->_callFunction($sFunctionName, $aArgs);
				}
				if (is_a($sResponse, "xajaxResponse")) {
					$sResponse = $sResponse->getXML();
				}
				if (!is_string($sResponse) || strpos($sResponse, "<xjx>") === FALSE) {
					$objResponse = new xajaxResponse();
					$objResponse->addAlert("No XML Response Was Returned By Function $sFunctionName.");
					$sResponse = $objResponse->getXML();
				}
				else if ($sPreResponse != "") {
					$sNewResponse = new xajaxResponse();
					$sNewResponse->loadXML($sPreResponse);
					$sNewResponse->loadXML($sResponse);
					$sResponse = $sNewResponse->getXML();
				}
			}
		}

		$sContentHeader = "Content-type: text/xml;";
		if ($this->sEncoding && strlen(trim($this->sEncoding)) > 0)
			$sContentHeader .= " charset=".$this->sEncoding;
		header($sContentHeader);
		if ($this->bErrorHandler && !empty( $GLOBALS['xajaxErrorHandlerText'] )) {
			$sErrorResponse = new xajaxResponse();
			$sErrorResponse->addAlert("** PHP Error Messages: **" . $GLOBALS['xajaxErrorHandlerText']);
			if ($this->sLogFile) {
				$fH = @fopen($this->sLogFile, "a");
				if (!$fH) {
					$sErrorResponse->addAlert("** Logging Error **\n\nxajax was unable to write to the error log file:\n" . $this->sLogFile);
				}
				else {
					fwrite($fH, "** xajax Error Log - " . strftime("%b %e %Y %I:%M:%S %p") . " **" . $GLOBALS['xajaxErrorHandlerText'] . "\n\n\n");
					fclose($fH);
				}
			}

			$sErrorResponse->loadXML($sResponse);
			$sResponse = $sErrorResponse->getXML();

		}
		if ($this->bCleanBuffer) while (@ob_end_clean());
		print $sResponse;
		if ($this->bErrorHandler) restore_error_handler();

		if ($this->bExitAllowed)
			exit();
	}

	// printJavascript() prints the xajax javascript code into your page by printing
	// the output of the getJavascript() method. It should only be called between the
	// <head> </head> tags in your HTML page. Remember, if you only want to obtain the
	// result of this function, use getJavascript() instead.
	// $sJsURI is the relative address of the folder where xajax has been installed.
	//   For instance, if your PHP file is "http://www.myserver.com/myfolder/mypage.php"
	//   and xajax was installed in "http://www.myserver.com/anotherfolder", then
	//   $sJsURI should be set to "../anotherfolder". Defaults to assuming xajax is in
	//   the same folder as your PHP file.
	// $sJsFile is the relative folder/file pair of the xajax Javascript engine located
	// within the xajax installation folder. Defaults to xajax_js/xajax.js.
	// usage:
	//	<head>
	//		...
	//		< ?php $xajax->printJavascript(); ? >
	function printJavascript($sJsURI="", $sJsFile=NULL, $sJsFullFilename=NULL)
	{
		print $this->getJavascript($sJsURI, $sJsFile, $sJsFullFilename);
	}

	// getJavascript() returns the xajax javascript code that should be added to
	// your HTML page between the <head> </head> tags. See printJavascript()
	// for information about the function arguments.
	// usage:
	//  < ?php $xajaxJSHead = $xajax->getJavascript(); ? >
	//	<head>
	//		...
	//		< ?php echo $xajaxJSHead; ? >
	function getJavascript($sJsURI="", $sJsFile=NULL, $sJsFullFilename=NULL)
	{
		if ($sJsFile == NULL) $sJsFile = "xajax_js/xajax.js";

		if ($sJsURI != "" && substr($sJsURI, -1) != "/") $sJsURI .= "/";

		$html  = "\t<script type=\"text/javascript\">\n";
		$html .= "var xajaxRequestUri=\"".$this->sRequestURI."\";\n";
		$html .= "var xajaxDebug=".($this->bDebug?"true":"false").";\n";
		$html .= "var xajaxStatusMessages=".($this->bStatusMessages?"true":"false").";\n";
		$html .= "var xajaxWaitCursor=".($this->bWaitCursor?"true":"false").";\n";
		$html .= "var xajaxDefinedGet=".XAJAX_GET.";\n";
		$html .= "var xajaxDefinedPost=".XAJAX_POST.";\n";

		foreach($this->aFunctions as $sFunction => $bExists) {
			$html .= $this->_wrap($sFunction,$this->aFunctionRequestTypes[$sFunction]);
		}

		$html .= "</script>\n";

		// Create a compressed file if necessary
		if ($sJsFullFilename) {
			$realJsFile = $sJsFullFilename;
		}
		else {
			$realPath = realpath(dirname(__FILE__));
			$realJsFile = $realPath . "/". $sJsFile;
		}
		$srcFile = str_replace(".js", "_uncompressed.js", $realJsFile);
		if (!file_exists($srcFile)) {
			trigger_error("The xajax uncompressed Javascript file could not be found in the <b>" . dirname($realJsFile) . "</b> folder. Error ", E_USER_ERROR);
		}

		if ($this->bDebug) {
			if (!@copy($srcFile, $realJsFile)) {
				trigger_error("The xajax uncompressed javascript file could not be copied to the <b>" . dirname($realJsFile) . "</b> folder. Error ", E_USER_ERROR);
			}
		}
		else if (!file_exists($realJsFile)) {
			require(dirname($realJsFile) . "/xajaxCompress.php");
			$javaScript = implode('', file($srcFile));
			$compressedScript = xajaxCompressJavascript($javaScript);
			$fH = @fopen($realJsFile, "w");
			if (!$fH) {
				trigger_error("The xajax compressed javascript file could not be written in the <b>" . dirname($realJsFile) . "</b> folder. Error ", E_USER_ERROR);
			}
			else {
				fwrite($fH, $compressedScript);
				fclose($fH);
			}
		}

		$html .= "\t<script type=\"text/javascript\" src=\"" . $sJsURI . $sJsFile . "\"></script>\n";

		return $html;
	}

	// _detectURL() returns the current URL based upon the SERVER vars
	// used internally
	function _detectURI() {
		$aURL = array();

		// Try to get the request URL
		if (!empty($_SERVER['REQUEST_URI'])) {
			$aURL = parse_url($_SERVER['REQUEST_URI']);
		}

		// Fill in the empty values
		if (empty($aURL['scheme'])) {
			if (!empty($_SERVER['HTTP_SCHEME'])) {
				$aURL['scheme'] = $_SERVER['HTTP_SCHEME'];
			} else {
				$aURL['scheme'] = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ? 'https' : 'http';
			}
		}

		if (empty($aURL['host'])) {
			if (!empty($_SERVER['HTTP_HOST'])) {
				if (strpos($_SERVER['HTTP_HOST'], ':') > 0) {
					list($aURL['host'], $aURL['port']) = explode(':', $_SERVER['HTTP_HOST']);
				} else {
					$aURL['host'] = $_SERVER['HTTP_HOST'];
				}
			} else if (!empty($_SERVER['SERVER_NAME'])) {
				$aURL['host'] = $_SERVER['SERVER_NAME'];
			} else {
				print "xajax Error: xajax failed to automatically identify your Request URI.";
				print "Please set the Request URI explicitly when you instantiate the xajax object.";
				exit();
			}
		}

		if (empty($aURL['port']) && !empty($_SERVER['SERVER_PORT'])) {
			$aURL['port'] = $_SERVER['SERVER_PORT'];
		}

		if (empty($aURL['path'])) {
			if (!empty($_SERVER['PATH_INFO'])) {
				$sPath = parse_url($_SERVER['PATH_INFO']);
			} else {
				$sPath = parse_url($_SERVER['PHP_SELF']);
			}
			$aURL['path'] = $sPath['path'];
			unset($sPath);
		}

		if (!empty($aURL['query'])) {
			$aURL['query'] = '?'.$aURL['query'];
		}

		// Build the URL: Start with scheme, user and pass
		$sURL = $aURL['scheme'].'://';
		if (!empty($aURL['user'])) {
			$sURL.= $aURL['user'];
			if (!empty($aURL['pass'])) {
				$sURL.= ':'.$aURL['pass'];
			}
			$sURL.= '@';
		}

		// Add the host
		$sURL.= $aURL['host'];

		// Add the port if needed
		if (!empty($aURL['port']) && (($aURL['scheme'] == 'http' && $aURL['port'] != 80) || ($aURL['scheme'] == 'https' && $aURL['port'] != 443))) {
			$sURL.= ':'.$aURL['port'];
		}

		// Add the path and the query string
		$sURL.= $aURL['path'].@$aURL['query'];

		// Clean up
		unset($aURL);
		return $sURL;
	}

	// returns true if the function name is associated with an object callback,
	// false if not.
	// user internally
	function _isObjectCallback($sFunction)
	{
		if (array_key_exists($sFunction, $this->aObjects)) return true;
		return false;
	}

	// return true if the function or object callback can be called, false if not
	// user internally
	function _isFunctionCallable($sFunction)
	{
		if ($this->_isObjectCallback($sFunction)) {
			if (is_object($this->aObjects[$sFunction][0])) {
				return method_exists($this->aObjects[$sFunction][0], $this->aObjects[$sFunction][1]);
			}
			else {
				return is_callable($this->aObjects[$sFunction]);
			}
		}
		else {
			return function_exists($sFunction);
		}
	}

	// calls the function, class method, or object method with the supplied arguments
	// user internally
	function _callFunction($sFunction, $aArgs)
	{
		if ($this->_isObjectCallback($sFunction)) {
			$mReturn = call_user_func_array($this->aObjects[$sFunction], $aArgs);
		}
		else {
			$mReturn = call_user_func_array($sFunction, $aArgs);
		}
		return $mReturn;
	}

	// generates the javascript wrapper for the specified PHP function
	// used internally
	function _wrap($sFunction,$sRequestType=XAJAX_POST)
	{
		$js = "function ".$this->sWrapperPrefix."$sFunction(){return xajax.call(\"$sFunction\", arguments, ".$sRequestType.");}\n";
		return $js;
	}

	// _xmlToArray() takes a string containing xajax xjxobj xml or xjxquery xml
	// and builds an array representation of it to pass as an argument to
	// the php function being called. Returns an array.
	// used internally
	function _xmlToArray($rootTag, $sXml)
	{
		$aArray = array();
		$sXml = str_replace("<$rootTag>","<$rootTag>|~|",$sXml);
		$sXml = str_replace("</$rootTag>","</$rootTag>|~|",$sXml);
		$sXml = str_replace("<e>","<e>|~|",$sXml);
		$sXml = str_replace("</e>","</e>|~|",$sXml);
		$sXml = str_replace("<k>","<k>|~|",$sXml);
		$sXml = str_replace("</k>","|~|</k>|~|",$sXml);
		$sXml = str_replace("<v>","<v>|~|",$sXml);
		$sXml = str_replace("</v>","|~|</v>|~|",$sXml);
		$sXml = str_replace("<q>","<q>|~|",$sXml);
		$sXml = str_replace("</q>","|~|</q>|~|",$sXml);

		$this->aObjArray = explode("|~|",$sXml);

		$this->iPos = 0;
		$aArray = $this->_parseObjXml($rootTag);

		return $aArray;
	}

	// _parseObjXml() is a recursive function that generates an array from the
	// contents of $this->aObjArray. Returns an array.
	// used internally
	function _parseObjXml($rootTag)
	{
		$aArray = array();

		if ($rootTag == "xjxobj")
		{
			while(!stristr($this->aObjArray[$this->iPos],"</xjxobj>"))
			{
				$this->iPos++;
				if(stristr($this->aObjArray[$this->iPos],"<e>"))
				{
					$key = "";
					$value = null;

					$this->iPos++;
					while(!stristr($this->aObjArray[$this->iPos],"</e>"))
					{
						if(stristr($this->aObjArray[$this->iPos],"<k>"))
						{
							$this->iPos++;
							while(!stristr($this->aObjArray[$this->iPos],"</k>"))
							{
								$key .= $this->aObjArray[$this->iPos];
								$this->iPos++;
							}
						}
						if(stristr($this->aObjArray[$this->iPos],"<v>"))
						{
							$this->iPos++;
							while(!stristr($this->aObjArray[$this->iPos],"</v>"))
							{
								if(stristr($this->aObjArray[$this->iPos],"<xjxobj>"))
								{
									$value = $this->_parseObjXml("xjxobj");
									$this->iPos++;
								}
								else
								{
									$value .= $this->aObjArray[$this->iPos];
								}
								$this->iPos++;
							}
						}
						$this->iPos++;
					}

					$aArray[$key]=$value;
				}
			}
		}

		if ($rootTag == "xjxquery")
		{
			$sQuery = "";
			$this->iPos++;
			while(!stristr($this->aObjArray[$this->iPos],"</xjxquery>"))
			{
				if (stristr($this->aObjArray[$this->iPos],"<q>") || stristr($this->aObjArray[$this->iPos],"</q>"))
				{
					$this->iPos++;
					continue;
				}
				$sQuery	.= $this->aObjArray[$this->iPos];
				$this->iPos++;
			}

			parse_str($sQuery, $aArray);
			// If magic quotes is on, then we need to strip the slashes from the
			// array values because of the parse_str pass which adds slashes
			if (get_magic_quotes_gpc() == 1) {
				$newArray = array();
				foreach ($aArray as $sKey => $sValue) {
					if (is_string($sValue))
						$newArray[$sKey] = stripslashes($sValue);
					else
						$newArray[$sKey] = $sValue;
				}
				$aArray = $newArray;
			}
		}

		return $aArray;
	}

}// end class xajax

// xajaxErrorHandler() is registered with PHP's set_error_handler() function if
// the xajax error handling system is turned on
// used by the xajax class
function xajaxErrorHandler($errno, $errstr, $errfile, $errline)
{
	$errorReporting = error_reporting();
	if ($errorReporting == 0) return;

	if ($errno == E_NOTICE) {
		$errTypeStr = "NOTICE";
	}
	else if ($errno == E_WARNING) {
		$errTypeStr = "WARNING";
	}
	else if ($errno == E_USER_NOTICE) {
		$errTypeStr = "USER NOTICE";
	}
	else if ($errno == E_USER_WARNING) {
		$errTypeStr = "USER WARNING";
	}
	else if ($errno == E_USER_ERROR) {
		$errTypeStr = "USER FATAL ERROR";
	}
	else if ($errno == E_STRICT) {
		return;
	}
	else {
		$errTypeStr = "UNKNOWN: $errno";
	}
	$GLOBALS['xajaxErrorHandlerText'] .= "\n----\n[$errTypeStr] $errstr\nerror in line $errline of file $errfile";
}

?>