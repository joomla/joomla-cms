<?php
/**
 * @version $Id$
 * @package JoomlaFramework
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
define("_J_NOTRIM", 0x0001);
define("_J_ALLOWHTML", 0x0002);
define("_J_ALLOWRAW", 0x0004);

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
 * @author Louis Landry <louis@webimagery.net>
 * @package JoomlaFramework
 * @subpackage Environment
 * @since 1.1
 */
class JRequest {

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
	 *   default    via default order (GET, POST, FILE)
	 *
	 * @static
	 * @param string $name Variable name
	 * @param string $hash Where the var should come from (POST, GET, FILES, METHOD)
	 * @param string $type Return type for the variable (INT, FLOAT, STRING, BOOLEAN, ARRAY)
	 * @param int $mask Filter mask for the variable
	 * @return mixed Requested variable
	 * @since 1.1
	 */

	function getVar($name, $hash = 'default', $type = 'string', $mask = 0) {
		$hash = strtoupper($hash);
		$type = strtoupper($type);
		$result = null;

		if ($hash === 'METHOD')
			$hash = strtoupper($_SERVER['REQUEST_METHOD']);
		else
			if ($hash == 'DEFAULT') {
				if (isset ($_GET[$name]))
					$result = $_GET[$name];
				else
					if (isset ($_POST[$name]))
						$result = $_POST[$name];
					else
						if (isset ($_FILES[$name]))
							$result = $_FILES[$name];
			} else {
				switch ($hash) {
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
				}
			}

		/*
		 * Clean the variable given using the given filter mask
		 */
		$result = JRequest :: cleanVar($result, $mask);

		if ($result != null) {
			/*
			 * Handle the type constraint
			 */
			switch ($type) {
				case 'INT' :
				case 'INTEGER' :
					$result = (int) $result;
					break;
				case 'FLOAT' :
				case 'DOUBLE' :
					$result = (float) $result;
					break;
				case 'BOOL' :
				case 'BOOLEAN' :
					$result = (bool) $result;
					break;
				case 'ARRAY' :
					if (!is_array($result)) {
						$result = null;
					}
					break;
				default :
					$result = (string) $result;
					break;
			}
		}
		return $result;
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
	function cleanVar(& $var, $mask = 0) {
		/*
		 * Static input filters for specific settings
		 */
		static $noHtmlFilter = null;
		static $safeHtmlFilter = null;

		// Initialize variables	
		$return = null;

		// Ensure the variable to clean is a string
		if (is_string($var)) {
			/*
			 * If the no trim flag is not set, trim the variable
			 */
			if (!($mask & _J_NOTRIM)) {
				$var = trim($var);
			}
			/*
			 * If the allow raw flag is set, do not modify the variable
			 */
			if ($mask & _J_ALLOWRAW) {
				// do nothing
				$return = $var;
				/*
				 * If the allow html flag is set, apply a safe html filter to the variable
				 */
			} else
				if ($mask & _J_ALLOWHTML) {
					if (is_null($safeHtmlFilter)) {
						$safeHtmlFilter = new InputFilter(null, null, 1, 1);
					}
					$return = $safeHtmlFilter->process($var);
					/*
					 * Since no allow flags were set, we will apply the most strict filter to the variable
					 */
				} else {
					if (is_null($noHtmlFilter)) {
						$noHtmlFilter = new InputFilter(/* $tags, $attr, $tag_method, $attr_method, $xss_auto */
						);
					}
					$return = $noHtmlFilter->process($var);
				}
			/*
			 * Handle magic quotes compatability
			 */
			if (!get_magic_quotes_gpc()) {
				$return = addslashes($return);
			}
			/*
			 * If the variable to clean is an array, recursively iterate through it
			 */
		}
		elseif (is_array($var)) {
			for ($i = 0; $i < count($var); $i ++) {
				$var[$i] = JRequest :: cleanVar($var[$i], $mask);
			}
			$return = $var;
			/*
			 * If the variable is neither an array or string just return the raw value
			 */
		} else {
			$return = $var;
		}
		return $return;
	}
}
?>