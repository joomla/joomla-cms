<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Set the available masks for cleaning variables
 */
define("_J_NOTRIM", 1);
define("_J_ALLOWHTML", 2);
define("_J_ALLOWRAW", 4);

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
 * @author		Louis Landry <louis@webimagery.net>
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.1
 */
class JRequest
{

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
	 *   method     via current $_SERVER['REQUEST_METHOD']
	 *   default    $_REQUEST
	 *
	 * @static
	 * @param string $name Variable name
	 * @param string $default Default value if the variable does not exist
	 * @param string $hash Where the var should come from (POST, GET, FILES, METHOD)
	 * @param string $type Return type for the variable (INT, FLOAT, STRING, BOOLEAN, ARRAY)
	 * @param int $mask Filter mask for the variable
	 * @return mixed Requested variable
	 * @since 1.1
	 */

	function getVar($name, $default = null, $hash = 'default', $type = 'none', $mask = 0)
	{

		static $vars;
		
		/*
		 * Initialize variables
		 */
		$hash		= strtoupper($hash);
		$type		= strtoupper($type);
		$signature	= $name.$default.$hash.$type.$mask;

		if (!isset($vars[$signature]))
		{
			$result		= null;
			$matches	= array ();
	
			if ($hash === 'METHOD')
			{
				$hash = strtoupper($_SERVER['REQUEST_METHOD']);
			} else
			{
				switch ($hash)
				{
					case 'GET' :
						if (isset ($_GET[$name]))
							$result = $_GET[$name];
						break;
					case 'POST' :
						if (isset ($_POST[$name]))
							$result = $_POST[$name];
						break;
					case 'FILES' :
						if (isset ($_FILES[$name]))
							$result = $_FILES[$name];
						break;
					default:
						if (isset ($_REQUEST[$name]))
							$result = $_REQUEST[$name];
						break;
				}
			}
	
			/*
			 * Handle default case
			 */
			if ((empty($result)) && (!is_null($default)))
			{
				$result = $default;
			}
	
			if ($result != null)
			{
				/*
				 * Handle the type constraint
				 */
				switch ($type)
				{
					case 'INT' :
					case 'INTEGER' :
						// Only use the first integer value
						@preg_match('/[0-9]+/', $result, $matches);
						$result = (int) $matches[0];
						break;
					case 'FLOAT' :
					case 'DOUBLE' :
						// Only use the first floating point value
						@preg_match('/[0-9]+(\.[0-9]+)?/', $result, $matches);
						$result = (float) $matches[0];
						break;
					case 'BOOL' :
					case 'BOOLEAN' :
						$result = (bool) $result;
						break;
					case 'ARRAY' :
	
						/*
						 * Clean the variable given using the given filter mask
						 */
						$result = JRequest :: cleanVar($result, $mask);
	
						if (!is_array($result))
						{
							$result = null;
						}
						break;
					case 'STRING' :
	
						/*
						 * Clean the variable given using the given filter mask
						 */
						$result = JRequest :: cleanVar($result, $mask);
	
						$result = (string) $result;
						break;
					case 'NONE' :
					default :
	
						/*
						 * Clean the variable given using the given filter mask
						 */
						$result = JRequest :: cleanVar($result, $mask);
						break;
				}
			}
			$vars[$signature] = $result;
		}
		return $vars[$signature];
	}

	/**
	 * Utility method to clean a string variable using input filters
	 * 
	 * Available Options masks:
	 * 		_J_NOTRIM 		: Prevents the trimming of the variable
	 * 		_J_ALLOWHTML	: Allows safe HTML in the variable
	 * 		_J_ALLOWRAW		: Allows raw input
	 * 
	 * @static
	 * @param mixed $var The variable to clean
	 * @param int $mask An options mask
	 * @return mixed The cleaned variable
	 * @since 1.1
	 */
	function cleanVar(& $var, $mask = 0)
	{
		/*
		 * Static input filters for specific settings
		 */
		static $noHtmlFilter = null;
		static $safeHtmlFilter = null;

		// Initialize variables	
		$return = null;

		// Ensure the variable to clean is a string
		if (is_string($var))
		{
			/*
			 * If the no trim flag is not set, trim the variable
			 */
			if (!($mask & 1))
			{
				$var = trim($var);
			}

			/*
			 * Now we handle input filtering
			 */
			if ($mask & 2)
			{
				/*
				 * If the allow raw flag is set, do not modify the variable
				 */
				$return = $var;
			} elseif ($mask & 4)
			{
				/*
				 * If the allow html flag is set, apply a safe html filter to the variable
				 */
				if (is_null($safeHtmlFilter))
				{
					jimport( 'phpinputfilter.inputfilter' );
					$safeHtmlFilter = new InputFilter(null, null, 1, 1);
				}
				$return = $safeHtmlFilter->process($var);
			} else
			{
				/*
				 * Since no allow flags were set, we will apply the most strict filter to the variable
				 */
				if (is_null($noHtmlFilter))
				{
					jimport( 'phpinputfilter.inputfilter' );
					$noHtmlFilter = new InputFilter(/* $tags, $attr, $tag_method, $attr_method, $xss_auto */
					);
				}
				$return = $noHtmlFilter->process($var);
			}

			/*
			 * Handle magic quotes compatability
			 */
			if (get_magic_quotes_gpc()) {
				$return = stripslashes($return);
			}
		}
		elseif (is_array($var))
		{
			/*
			 * If the variable to clean is an array, recursively iterate through it
			 */
			foreach ($var as $offset)
			{
				$offset = JRequest :: cleanVar($offset, $mask);
			}
			$return = $var;
		} else
		{
			/*
			 * If the variable is neither an array or string just return the raw value
			 */
			$return = $var;
		}
		return $return;
	}
}
?>