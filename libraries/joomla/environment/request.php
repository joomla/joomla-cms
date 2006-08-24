<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

jimport('joomla.utilities.array');
jimport('joomla.utilities.functions');

/**
 * JRequest Class
 *
 * This class serves to provide the Joomla Framework with a common interface to access
 * request variables.  This includes $_POST, $_GET, and naturally $_REQUEST.  Variables
 * can be passed through an input filter to avoid injection or returned raw.
 *
 * The concept and implementation of this class is inspired by the binary cloud
 * environment package.  <http://www.binarycloud.com/>
 *
 * @static
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Environment
 * @since		1.5
 */
class JRequest
{
	/**
	 * Gets the query part of the URL
	 */
	function getQuery()
	{
		return $_SERVER['QUERY_STRING'];
	}

	/**
	 * Gets the full request path
	 *
	 * @return string
	 */
	function getUrl()
	{
		$uri = &JURI::getInstance();
		return $uri->toString();
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
	 *   post       $_POST
	 *   get        $_GET
	 *   files      $_FILES
	 *   cookie     $_COOKIE
	 *   method     via current $_SERVER['REQUEST_METHOD']
	 *   default    $_REQUEST
	 *
	 * @static
	 * @param	string	$name		Variable name
	 * @param	string	$default	Default value if the variable does not exist
	 * @param	string	$hash		Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
	 * @param	string	$type		Return type for the variable (INT, FLOAT, STRING, BOOLEAN, ARRAY)
	 * @param	int		$mask		Filter mask for the variable
	 * @return	mixed	Requested variable
	 * @since	1.5
	 */
	function getVar($name, $default = null, $hash = 'default', $type = 'none', $mask = 0)
	{
		// TODO: Louis, had to add $default otherwise it doesn't always work
		// Must be a way to cache the actual request value, and the processes default value?
		$signature	= $name.$default.$hash.$type.$mask;

		if (!isset($GLOBALS['JRequest'][$signature])) {
			$result		= null;
			$matches	= array();

			$hash = strtoupper( $hash );
			if ($hash === 'METHOD') {
				$hash = strtoupper( $_SERVER['REQUEST_METHOD'] );
			}

			switch ($hash)
			{
				case 'GET' :
					$input  = &$_GET;
					break;

				case 'POST' :
					$input  = &$_POST;
					break;

				case 'FILES' :
					$input  = &$_FILES;
					break;

				case 'COOKIE' :
					$input  = &$_COOKIE;
					break;

				default:
					$input  = &$_REQUEST;
					break;
			}

			// Get the casted value
			$result = JArrayHelper::getValue( $input, $name, $default, $type );

			// Run through input filter if necessary
			switch (strtoupper($type))
			{
				case 'INT' :
				case 'INTEGER' :
				case 'FLOAT' :
				case 'DOUBLE' :
				case 'BOOL' :
				case 'BOOLEAN' :
					break;

				default :
					// Clean the variable given using the given filter mask
					$result = josFilterValue($result, $mask);
					break;
			}

			// Handle magic quotes compatability
			if (get_magic_quotes_gpc() && ($result != $default))
			{
				if (!is_array($result) && is_string($result)) {
					$result = stripslashes($result);
				}
			}
			$GLOBALS['JRequest'][$signature] = $result;
		}
		return $GLOBALS['JRequest'][$signature];
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
	 *   post       $_POST
	 *   get        $_GET
	 *   files      $_FILES
	 *   cookie     $_COOKIE
	 *   method     via current $_SERVER['REQUEST_METHOD']
	 *   default    $_REQUEST
	 *
	 * @static
	 * @param	string	$hash	to get (POST, GET, FILES, METHOD)
	 * @param	int		$mask	Filter mask for the variable
	 * @return	mixed	Request hash
	 * @since	1.5
	 */
	function get($hash = 'default', $mask = 0)
	{
		static $hashes;

		if (!isset($hashes)) {
			$hashes = array();
		}

		$signature	= $hash.$mask;
		if (!isset($hashes[$signature])) {
			$result		= null;
			$matches	= array();

			$hash = strtoupper( $hash );
			if ($hash === 'METHOD') {
				$hash = strtoupper( $_SERVER['REQUEST_METHOD'] );
			}

			switch ($hash)
			{
				case 'GET' :
					$input  = $_GET;
					break;

				case 'POST' :
					$input  = $_POST;
					break;

				case 'FILES' :
					$input  = $_FILES;
					break;

				case 'COOKIE' :
					$input  = $_COOKIE;
					break;

				default:
					$input  = $_REQUEST;
					break;
			}

			$result = josFilterValue($input, $mask);

			// Handle magic quotes compatability
			if (get_magic_quotes_gpc()) {
				$result = JRequest::_stripSlashesRecursive( $result );
			}
			$hashes[$signature] = &$result;
		}
		return $hashes[$signature];
	}

	function setVar($name, $value = null, $hash = 'default', $type = 'none', $mask = 0)
	{
		// Initialize variables
		$hash		= strtoupper($hash);
		$type		= strtoupper($type);
		$signature	= $name.$hash.$type.$mask;

		// Set global request var
		$GLOBALS['JRequest'][$signature] = $value;

		if ($hash === 'METHOD') {
			$hash = strtoupper($_SERVER['REQUEST_METHOD']);
		}
		switch ($hash)
		{
			case 'GET' :
					$_GET[$name] = $value;
					$_REQUEST[$name] = $value;
				break;
			case 'POST' :
					$_POST[$name] = $value;
					$_REQUEST[$name] = $value;
				break;
			case 'FILES' :
					$_FILES[$name] = $value;
					$_REQUEST[$name] = $value;
				break;
			case 'COOKIE' :
					$_COOKIE[$name] = $value;
					$_REQUEST[$name] = $value;
				break;
			default:
					$_GET[$name] = $value;
					$_POST[$name] = $value;
					$_REQUEST[$name] = $value;
				break;
		}

		return $GLOBALS['JRequest'][$signature];
	}

	/**
	 * Strips slashes recursively on an array
	 *
	 * @access	protected
	 * @param	array	$array		Array of (nested arrays of) strings
	 * @return	array	The input array with stripshlashes applied to it
	 */
	function _stripSlashesRecursive( $value )
	{
		$value = is_array( $value ) ? array_map( array( 'JRequest', '_stripSlashesRecursive' ), $value ) : stripslashes( $value );
		return $value;
	}
}
?>