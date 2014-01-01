<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Environment
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Create the request global object
 */
$GLOBALS['_JREQUEST'] = array();

/**
 * Set the available masks for cleaning variables
 */
define('JREQUEST_NOTRIM', 1);
define('JREQUEST_ALLOWRAW', 2);
define('JREQUEST_ALLOWHTML', 4);

JLog::add('JRequest is deprecated.', JLog::WARNING, 'deprecated');

/**
 * JRequest Class
 *
 * This class serves to provide the Joomla Platform with a common interface to access
 * request variables.  This includes $_POST, $_GET, and naturally $_REQUEST.  Variables
 * can be passed through an input filter to avoid injection or returned raw.
 *
 * @package     Joomla.Platform
 * @subpackage  Environment
 * @since       11.1
 * @deprecated  12.1  Get the JInput object from the application instead
 */
class JRequest
{
	/**
	 * Gets the full request path.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 *
	 * @deprecated   12.1
	 */
	public static function getURI()
	{
		$uri = JFactory::getURI();
		return $uri->toString(array('path', 'query'));
	}

	/**
	 * Gets the request method.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 *
	 * @deprecated   12.1
	 */
	public static function getMethod()
	{
		$method = strtoupper($_SERVER['REQUEST_METHOD']);
		return $method;
	}

	/**
	 * Fetches and returns a given variable.
	 *
	 * The default behaviour is fetching variables depending on the
	 * current request method: GET and HEAD will result in returning
	 * an entry from $_GET, POST and PUT will result in returning an
	 * entry from $_POST.
	 *
	 * You can force the source by setting the $hash parameter:
	 *
	 * post    $_POST
	 * get     $_GET
	 * files   $_FILES
	 * cookie  $_COOKIE
	 * env     $_ENV
	 * server  $_SERVER
	 * method  via current $_SERVER['REQUEST_METHOD']
	 * default $_REQUEST
	 *
	 * @param   string   $name     Variable name.
	 * @param   string   $default  Default value if the variable does not exist.
	 * @param   string   $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 * @param   string   $type     Return type for the variable, for valid values see {@link JFilterInput::clean()}.
	 * @param   integer  $mask     Filter mask for the variable.
	 *
	 * @return  mixed  Requested variable.
	 *
	 * @since   11.1
	 *
	 * @deprecated   12.1  Use JInput::Get
	 */
	public static function getVar($name, $default = null, $hash = 'default', $type = 'none', $mask = 0)
	{
		// Ensure hash and type are uppercase
		$hash = strtoupper($hash);
		if ($hash === 'METHOD')
		{
			$hash = strtoupper($_SERVER['REQUEST_METHOD']);
		}
		$type = strtoupper($type);
		$sig = $hash . $type . $mask;

		// Get the input hash
		switch ($hash)
		{
			case 'GET':
				$input = &$_GET;
				break;
			case 'POST':
				$input = &$_POST;
				break;
			case 'FILES':
				$input = &$_FILES;
				break;
			case 'COOKIE':
				$input = &$_COOKIE;
				break;
			case 'ENV':
				$input = &$_ENV;
				break;
			case 'SERVER':
				$input = &$_SERVER;
				break;
			default:
				$input = &$_REQUEST;
				$hash = 'REQUEST';
				break;
		}

		if (isset($GLOBALS['_JREQUEST'][$name]['SET.' . $hash]) && ($GLOBALS['_JREQUEST'][$name]['SET.' . $hash] === true))
		{
			// Get the variable from the input hash
			$var = (isset($input[$name]) && $input[$name] !== null) ? $input[$name] : $default;
			$var = self::_cleanVar($var, $mask, $type);
		}
		elseif (!isset($GLOBALS['_JREQUEST'][$name][$sig]))
		{
			if (isset($input[$name]) && $input[$name] !== null)
			{
				// Get the variable from the input hash and clean it
				$var = self::_cleanVar($input[$name], $mask, $type);

				// Handle magic quotes compatibility
				if (get_magic_quotes_gpc() && ($var != $default) && ($hash != 'FILES'))
				{
					$var = self::_stripSlashesRecursive($var);
				}

				$GLOBALS['_JREQUEST'][$name][$sig] = $var;
			}
			elseif ($default !== null)
			{
				// Clean the default value
				$var = self::_cleanVar($default, $mask, $type);
			}
			else
			{
				$var = $default;
			}
		}
		else
		{
			$var = $GLOBALS['_JREQUEST'][$name][$sig];
		}

		return $var;
	}

	/**
	 * Fetches and returns a given filtered variable. The integer
	 * filter will allow only digits and the - sign to be returned. This is currently
	 * only a proxy function for getVar().
	 *
	 * See getVar() for more in-depth documentation on the parameters.
	 *
	 * @param   string  $name     Variable name.
	 * @param   string  $default  Default value if the variable does not exist.
	 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 *
	 * @return  integer  Requested variable.
	 *
	 * @since   11.1
	 *
	 * @deprecated   12.1
	 */
	public static function getInt($name, $default = 0, $hash = 'default')
	{
		return self::getVar($name, $default, $hash, 'int');
	}

	/**
	 * Fetches and returns a given filtered variable. The unsigned integer
	 * filter will allow only digits to be returned. This is currently
	 * only a proxy function for getVar().
	 *
	 * See getVar() for more in-depth documentation on the parameters.
	 *
	 * @param   string  $name     Variable name.
	 * @param   string  $default  Default value if the variable does not exist.
	 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 *
	 * @return  integer  Requested variable.
	 *
	 * @deprecated  12.1
	 * @since       11.1
	 */
	public static function getUInt($name, $default = 0, $hash = 'default')
	{
		return self::getVar($name, $default, $hash, 'uint');
	}

	/**
	 * Fetches and returns a given filtered variable.  The float
	 * filter only allows digits and periods.  This is currently
	 * only a proxy function for getVar().
	 *
	 * See getVar() for more in-depth documentation on the parameters.
	 *
	 * @param   string  $name     Variable name.
	 * @param   string  $default  Default value if the variable does not exist.
	 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 *
	 * @return  float  Requested variable.
	 *
	 * @since   11.1
	 *
	 * @deprecated   12.1
	 */
	public static function getFloat($name, $default = 0.0, $hash = 'default')
	{
		return self::getVar($name, $default, $hash, 'float');
	}

	/**
	 * Fetches and returns a given filtered variable. The bool
	 * filter will only return true/false bool values. This is
	 * currently only a proxy function for getVar().
	 *
	 * See getVar() for more in-depth documentation on the parameters.
	 *
	 * @param   string  $name     Variable name.
	 * @param   string  $default  Default value if the variable does not exist.
	 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 *
	 * @return  boolean  Requested variable.
	 *
	 * @deprecated  12.1
	 * @since       11.1
	 */
	public static function getBool($name, $default = false, $hash = 'default')
	{
		return self::getVar($name, $default, $hash, 'bool');
	}

	/**
	 * Fetches and returns a given filtered variable. The word
	 * filter only allows the characters [A-Za-z_]. This is currently
	 * only a proxy function for getVar().
	 *
	 * See getVar() for more in-depth documentation on the parameters.
	 *
	 * @param   string  $name     Variable name.
	 * @param   string  $default  Default value if the variable does not exist.
	 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 *
	 * @return  string  Requested variable.
	 *
	 * @since   11.1
	 *
	 * @deprecated   12.1
	 */
	public static function getWord($name, $default = '', $hash = 'default')
	{
		return self::getVar($name, $default, $hash, 'word');
	}

	/**
	 * Cmd (Word and Integer0 filter
	 *
	 * Fetches and returns a given filtered variable. The cmd
	 * filter only allows the characters [A-Za-z0-9.-_]. This is
	 * currently only a proxy function for getVar().
	 *
	 * See getVar() for more in-depth documentation on the parameters.
	 *
	 * @param   string  $name     Variable name
	 * @param   string  $default  Default value if the variable does not exist
	 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
	 *
	 * @return  string  Requested variable
	 *
	 * @deprecated  12.1
	 * @since       11.1
	 */
	public static function getCmd($name, $default = '', $hash = 'default')
	{
		return self::getVar($name, $default, $hash, 'cmd');
	}

	/**
	 * Fetches and returns a given filtered variable. The string
	 * filter deletes 'bad' HTML code, if not overridden by the mask.
	 * This is currently only a proxy function for getVar().
	 *
	 * See getVar() for more in-depth documentation on the parameters.
	 *
	 * @param   string   $name     Variable name
	 * @param   string   $default  Default value if the variable does not exist
	 * @param   string   $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
	 * @param   integer  $mask     Filter mask for the variable
	 *
	 * @return  string   Requested variable
	 *
	 * @since   11.1
	 *
	 * @deprecated   12.1
	 */
	public static function getString($name, $default = '', $hash = 'default', $mask = 0)
	{
		// Cast to string, in case JREQUEST_ALLOWRAW was specified for mask
		return (string) self::getVar($name, $default, $hash, 'string', $mask);
	}

	/**
	 * Set a variable in one of the request variables.
	 *
	 * @param   string   $name       Name
	 * @param   string   $value      Value
	 * @param   string   $hash       Hash
	 * @param   boolean  $overwrite  Boolean
	 *
	 * @return  string   Previous value
	 *
	 * @since   11.1
	 *
	 * @deprecated   12.1
	 */
	public static function setVar($name, $value = null, $hash = 'method', $overwrite = true)
	{
		// If overwrite is true, makes sure the variable hasn't been set yet
		if (!$overwrite && array_key_exists($name, $_REQUEST))
		{
			return $_REQUEST[$name];
		}

		// Clean global request var
		$GLOBALS['_JREQUEST'][$name] = array();

		// Get the request hash value
		$hash = strtoupper($hash);
		if ($hash === 'METHOD')
		{
			$hash = strtoupper($_SERVER['REQUEST_METHOD']);
		}

		$previous = array_key_exists($name, $_REQUEST) ? $_REQUEST[$name] : null;

		switch ($hash)
		{
			case 'GET':
				$_GET[$name] = $value;
				$_REQUEST[$name] = $value;
				break;
			case 'POST':
				$_POST[$name] = $value;
				$_REQUEST[$name] = $value;
				break;
			case 'COOKIE':
				$_COOKIE[$name] = $value;
				$_REQUEST[$name] = $value;
				break;
			case 'FILES':
				$_FILES[$name] = $value;
				break;
			case 'ENV':
				$_ENV['name'] = $value;
				break;
			case 'SERVER':
				$_SERVER['name'] = $value;
				break;
		}

		// Mark this variable as 'SET'
		$GLOBALS['_JREQUEST'][$name]['SET.' . $hash] = true;
		$GLOBALS['_JREQUEST'][$name]['SET.REQUEST'] = true;

		return $previous;
	}

	/**
	 * Fetches and returns a request array.
	 *
	 * The default behaviour is fetching variables depending on the
	 * current request method: GET and HEAD will result in returning
	 * $_GET, POST and PUT will result in returning $_POST.
	 *
	 * You can force the source by setting the $hash parameter:
	 *
	 * post     $_POST
	 * get      $_GET
	 * files    $_FILES
	 * cookie   $_COOKIE
	 * env      $_ENV
	 * server   $_SERVER
	 * method   via current $_SERVER['REQUEST_METHOD']
	 * default  $_REQUEST
	 *
	 * @param   string   $hash  to get (POST, GET, FILES, METHOD).
	 * @param   integer  $mask  Filter mask for the variable.
	 *
	 * @return  mixed    Request hash.
	 *
	 * @deprecated  12.1   User JInput::get
	 * @see         JInput
	 * @since       11.1
	 */
	public static function get($hash = 'default', $mask = 0)
	{
		$hash = strtoupper($hash);

		if ($hash === 'METHOD')
		{
			$hash = strtoupper($_SERVER['REQUEST_METHOD']);
		}

		switch ($hash)
		{
			case 'GET':
				$input = $_GET;
				break;

			case 'POST':
				$input = $_POST;
				break;

			case 'FILES':
				$input = $_FILES;
				break;

			case 'COOKIE':
				$input = $_COOKIE;
				break;

			case 'ENV':
				$input = &$_ENV;
				break;

			case 'SERVER':
				$input = &$_SERVER;
				break;

			default:
				$input = $_REQUEST;
				break;
		}

		$result = self::_cleanVar($input, $mask);

		// Handle magic quotes compatibility
		if (get_magic_quotes_gpc() && ($hash != 'FILES'))
		{
			$result = self::_stripSlashesRecursive($result);
		}

		return $result;
	}

	/**
	 * Sets a request variable.
	 *
	 * @param   array    $array      An associative array of key-value pairs.
	 * @param   string   $hash       The request variable to set (POST, GET, FILES, METHOD).
	 * @param   boolean  $overwrite  If true and an existing key is found, the value is overwritten, otherwise it is ignored.
	 *
	 * @return  void
	 *
	 * @deprecated  12.1  Use JInput::Set
	 * @see         JInput::Set
	 * @since       11.1
	 */
	public static function set($array, $hash = 'default', $overwrite = true)
	{
		foreach ($array as $key => $value)
		{
			self::setVar($key, $value, $hash, $overwrite);
		}
	}

	/**
	 * Checks for a form token in the request.
	 *
	 * Use in conjunction with JHtml::_('form.token').
	 *
	 * @param   string  $method  The request method in which to look for the token key.
	 *
	 * @return  boolean  True if found and valid, false otherwise.
	 *
	 * @deprecated  12.1 Use JSession::checkToken() instead.
	 * @since       11.1
	 */
	public static function checkToken($method = 'post')
	{
		$token = JSession::getFormToken();
		if (!self::getVar($token, '', $method, 'alnum'))
		{
			$session = JFactory::getSession();
			if ($session->isNew())
			{
				// Redirect to login screen.
				$app = JFactory::getApplication();
				$return = JRoute::_('index.php');
				$app->redirect($return, JText::_('JLIB_ENVIRONMENT_SESSION_EXPIRED'));
				$app->close();
			}
			else
			{
				return false;
			}
		}
		else
		{
			return true;
		}
	}

	/**
	 * Cleans the request from script injection.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 *
	 * @deprecated   12.1
	 */
	public static function clean()
	{
		// Only run this if register globals is on.
		// Remove this code when PHP 5.4 becomes the minimum requirement.
		if (!(bool) ini_get('register_globals'))
		{
			return;
		}

		$REQUEST = $_REQUEST;
		$GET = $_GET;
		$POST = $_POST;
		$COOKIE = $_COOKIE;
		$FILES = $_FILES;
		$ENV = $_ENV;
		$SERVER = $_SERVER;

		if (isset($_SESSION))
		{
			$SESSION = $_SESSION;
		}

		foreach ($GLOBALS as $key => $value)
		{
			if ($key != 'GLOBALS')
			{
				unset($GLOBALS[$key]);
			}
		}
		$_REQUEST = $REQUEST;
		$_GET = $GET;
		$_POST = $POST;
		$_COOKIE = $COOKIE;
		$_FILES = $FILES;
		$_ENV = $ENV;
		$_SERVER = $SERVER;

		if (isset($SESSION))
		{
			$_SESSION = $SESSION;
		}

		// Make sure the request hash is clean on file inclusion
		$GLOBALS['_JREQUEST'] = array();
	}

	/**
	 * Clean up an input variable.
	 *
	 * @param   mixed    $var   The input variable.
	 * @param   integer  $mask  Filter bit mask.
	 *                           1 = no trim: If this flag is cleared and the input is a string, the string will have leading and trailing
	 *                               whitespace trimmed.
	 *                           2 = allow_raw: If set, no more filtering is performed, higher bits are ignored.
	 *                           4 = allow_html: HTML is allowed, but passed through a safe HTML filter first. If set, no more filtering
	 *                               is performed. If no bits other than the 1 bit is set, a strict filter is applied.
	 * @param   string   $type  The variable type {@see JFilterInput::clean()}.
	 *
	 * @return  mixed  Same as $var
	 *
	 * @deprecated  12.1
	 * @since       11.1
	 */
	static function _cleanVar($var, $mask = 0, $type = null)
	{
		// If the no trim flag is not set, trim the variable
		if (!($mask & 1) && is_string($var))
		{
			$var = trim($var);
		}

		// Now we handle input filtering
		if ($mask & 2)
		{
			// If the allow raw flag is set, do not modify the variable
			$var = $var;
		}
		elseif ($mask & 4)
		{
			// If the allow HTML flag is set, apply a safe HTML filter to the variable
			$safeHtmlFilter = JFilterInput::getInstance(null, null, 1, 1);
			$var = $safeHtmlFilter->clean($var, $type);
		}
		else
		{
			// Since no allow flags were set, we will apply the most strict filter to the variable
			// $tags, $attr, $tag_method, $attr_method, $xss_auto use defaults.
			$noHtmlFilter = JFilterInput::getInstance();
			$var = $noHtmlFilter->clean($var, $type);
		}
		return $var;
	}

	/**
	 * Strips slashes recursively on an array.
	 *
	 * @param   array  $value  Array or (nested arrays) of strings.
	 *
	 * @return  array  The input array with stripslashes applied to it.
	 *
	 * @deprecated  12.1
	 * @since       11.1
	 */
	protected static function _stripSlashesRecursive($value)
	{
		$value = is_array($value) ? array_map(array('JRequest', '_stripSlashesRecursive'), $value) : stripslashes($value);
		return $value;
	}
}
