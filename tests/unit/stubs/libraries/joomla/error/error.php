<?php
// No direct access
defined('JPATH_BASE') or die;

// Error Definition: Illegal Options
defined('JERROR_ILLEGAL_OPTIONS') or define('JERROR_ILLEGAL_OPTIONS', 1);
// Error Definition: Callback does not exist
defined('JERROR_CALLBACK_NOT_CALLABLE') or define('JERROR_CALLBACK_NOT_CALLABLE', 2);
// Error Definition: Illegal Handler
defined('JERROR_ILLEGAL_MODE') or define('JERROR_ILLEGAL_MODE', 3);


abstract class JError
{
	static $exception;
	protected static $levels = array(
		E_NOTICE => 'Notice',
		E_WARNING => 'Warning',
		E_ERROR => 'Error'
	);
	protected static $handlers = array(
		E_NOTICE	=> array('mode' => 'message'),
		E_WARNING	=> array('mode' => 'message'),
		E_ERROR		=> array('mode' => 'callback', 'options' => array('JError','customErrorPage'))
	);
	protected static $stack = array();
	public static function isError(& $object)
	{
	}
	public static function & getError($unset = false)
	{
	}
	public static function & getErrors()
	{
	}
	public static function addToStack(JException &$e) {
	}
	public static function &raise($level, $code, $msg, $info = null, $backtrace = false)
	{
	}

	public static function &throwError(&$exception)
	{
	}
	public static function & raiseError($code, $msg, $info = null)
	{
		JError::$exception = array (
			'code' => $code,
			'msg' => $msg,
			'info' => $info
		);
		return JError::$exception;
	}
	public static function & raiseWarning($code, $msg, $info = null)
	{
		JError::$exception = array (
			'code' => $code,
			'msg' => $msg,
			'info' => $info
		);
		return JError::$exception;
	}
	public static function & raiseNotice($code, $msg, $info = null)
	{
	}
	public static function getErrorHandling($level)
	{
	}
	public static function setErrorHandling($level, $mode, $options = null)
	{
	}
	public static function attachHandler()
	{
	}
	public static function detachHandler()
	{
	}
	public static function registerErrorLevel($level, $name, $handler = 'ignore')
	{
	}
	public static function translateErrorLevel($level)
	{
	}
	public static function &handleIgnore(&$error, $options)
	{
	}
	public static function &handleEcho(&$error, $options)
	{
	}
	public static function &handleVerbose(& $error, $options)
	{
	}
	public static function &handleDie(& $error, $options)
	{
	}
	public static function &handleMessage(& $error, $options)
	{
	}
	public static function &handleLog(& $error, $options)
	{
	}
	public static function &handleCallback(&$error, $options)
	{
	}
	public static function customErrorPage(& $error)
	{
	}

	public static function customErrorHandler($level, $msg)
	{
	}

	public static function renderBacktrace($error)
	{
	}
}
