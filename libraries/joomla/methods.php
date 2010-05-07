<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('JPATH_BASE') or die;

/**
 * Route handling class
 *
 * @static
 * @package		Joomla.Framework
 * @since		1.5
 */
class JRoute
{
	/**
	 * Translates an internal Joomla URL to a humanly readible URL.
	 *
	 * @param	string	Absolute or Relative URI to Joomla resource.
	 * @param	boolean	Replace & by &amp; for xml compilance.
	 * @param	int		Secure state for the resolved URI.
	 *		1: Make URI secure using global secure site URI.
	 *		0: Leave URI in the same secure state as it was passed to the function.
	 *		-1: Make URI unsecure using the global unsecure site URI.
	 * @return	The translated humanly readible URL.
	 */
	public static function _($url, $xhtml = true, $ssl = null)
	{
		// Get the router.
		$app	= JFactory::getApplication();
		$router	= $app->getRouter();

		// Make sure that we have our router
		if (!$router) {
			return null;
		}

		if ((strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0)) {
			return $url;
		}

		// Build route.
		$uri = $router->build($url);
		$url = $uri->toString(array('path', 'query', 'fragment'));

		// Replace spaces.
		$url = preg_replace('/\s/u', '%20', $url);

		/*
		 * Get the secure/unsecure URLs.
		 *
		 * If the first 5 characters of the BASE are 'https', then we are on an ssl connection over
		 * https and need to set our secure URL to the current request URL, if not, and the scheme is
		 * 'http', then we need to do a quick string manipulation to switch schemes.
		 */
		if ((int) $ssl) {
			$uri = &JURI::getInstance();

			// Get additional parts.
			static $prefix;
			if (!$prefix) {
				$prefix = $uri->toString(array('host', 'port'));
			}

			// Determine which scheme we want.
			$scheme	= ($ssl === 1) ? 'https' : 'http';

			// Make sure our URL path begins with a slash.
			if (!preg_match('#^/#', $url)) {
				$url = '/'.$url;
			}

			// Build the URL.
			$url = $scheme.'://'.$prefix.$url;
		}

		if ($xhtml) {
			$url = str_replace('&', '&amp;', $url);
		}

		return $url;
	}
}

/**
 * Text  handling class.
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	Language
 * @since		1.5
 */
class JText
{
	/**
	 * javascript strings
	 */
	protected static $strings=array();

	/**
	 * Translates a string into the current language.
	 *
	 * @param	string			The string to translate.
	 * @param	boolean|array	boolean: Make the result javascript safe. array an array of option as described in the JText::sprintf function
	 * @param	boolean			To interprete backslashes (\\=\, \n=carriage return, \t=tabulation)
	 * @param	boolean			To indicate that the string will be push in the javascript array strings
	 * @return	string			The translated string or the key is $script is true
	 * @example	<script>alert(Joomla.JText._('<?php echo JText::_("JDEFAULT", array("script"=>true));?>'));</script> will generate an alert message containing 'Default'
	 * @example	<?php echo JText::_("JDEFAULT");?> it will generate a 'Default' string	 
	 * @since	1.5
	 *
	 */
	public static function _($string, $jsSafe = false, $interpreteBackSlashes = true, $script = false)
	{
		$lang = JFactory::getLanguage();
		if (is_array($jsSafe)) {
			if (array_key_exists('interpreteBackSlashes', $jsSafe)) {
				$interpreteBackSlashes = (boolean) $jsSafe['interpreteBackSlashes'];
			}
			if (array_key_exists('script', $jsSafe)) {
				$script = (boolean) $jsSafe['script'];
			}
			if (array_key_exists('jsSafe', $jsSafe)) {
				$jsSafe = (boolean) $jsSafe['jsSafe'];
			}
			else {
				$jsSafe = false;
			}
		}
		if ($script) {
			self::$strings[$string] = $lang->_($string, $jsSafe, $interpreteBackSlashes);
			return $string;
		}
		else {
			return $lang->_($string, $jsSafe, $interpreteBackSlashes);
		}
	}

	/**
	 * Like JText::sprintf but tries to pluralise the string.
	 *
	 * @param	string	The format string.
	 * @param	int		The number of items
	 * @param	mixed	Mixed number of arguments for the sprintf function. The first should be an integer.
	 * @param	array	optional Array of option array('jsSafe'=>boolean, 'interpreteBackSlashes'=>boolean, 'script'=>boolean) where
	 *					-jsSafe is a boolean to generate a javascript safe string
	 *					-interpreteBackSlashes is a boolean to interprete backslashes \\->\, \n->new line, \t->tabulation
	 *					-script is a boolean to indicate that the string will be push in the javascript array strings
	 * @return	string	The translated strings or the key if 'script' is true in the array of options
	 * @example	<script>alert(Joomla.JText._('<?php echo JText::plural("COM_PLUGINS_N_ITEMS_UNPUBLISHED", 1, array("script"=>true));?>'));</script> will generate an alert message containing '1 plugin successfully disabled'
	 * @example	<?php echo JText::plural("COM_PLUGINS_N_ITEMS_UNPUBLISHED", 1);?> it will generate a '1 plugin successfully disabled' string	 
	 * @since	1.6
	 */
	 
	public static function plural($string, $n)
	{
		$lang = JFactory::getLanguage();
		$args = func_get_args();
		$count = count($args);

		if ($count > 1) {
			// Try the key from the language plural potential suffixes
			$found = false;
			$suffixes = $lang->getPluralSuffices((int)$n);
			foreach ($suffixes as $suffix) {
				$key = $string.'_'.$suffix;
				if ($lang->hasKey($key)) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				// Not found so revert to the original.
				$key = $string;
			}
			if (is_array($args[$count-1])) {
				$args[0] = $lang->_($key, array_key_exists('jsSafe', $args[$count-1]) ? $args[$count-1]['jsSafe'] : false, array_key_exists('interpreteBackSlashes', $args[$count-1]) ? $args[$count-1]['interpreteBackSlashes'] : true);
				if (array_key_exists('script',$args[$count-1]) && $args[$count-1]['script']) {
					self::$strings[$key] = call_user_func_array('sprintf', $args);
					return $key;
				}
			}
			else {
				$args[0] = $lang->_($key);
			}
			return call_user_func_array('sprintf', $args);
		}
		elseif ($count > 0) {

			// Default to the normal sprintf handling.
			$args[0] = $lang->_($string);
			return call_user_func_array('sprintf', $args);
		}

		return '';
	}

	/**
	 * Passes a string thru an sprintf.
	 *
	 * @param	string	The format string.
	 * @param	mixed	Mixed number of arguments for the sprintf function.
	 * @param	array	optional Array of option array('jsSafe'=>boolean, 'interpreteBackSlashes'=>boolean, 'script'=>boolean) where
	 *					-jsSafe is a boolean to generate a javascript safe strings
	 *					-interpreteBackSlashes is a boolean to interprete backslashes \\->\, \n->new line, \t->tabulation
	 *					-script is a boolean to indicate that the string will be push in the javascript array strings
	 * @return	string	The translated strings or the key if 'script' is true in the array of options
	 * @since	1.5
	 */
	public static function sprintf($string)
	{
		$lang = JFactory::getLanguage();
		$args = func_get_args();
		$count = count($args);
		if ($count > 0) {
			if (is_array($args[$count-1])) {
				$args[0] = $lang->_($string, array_key_exists('jsSafe', $args[$count-1]) ? $args[$count-1]['jsSafe'] : false, array_key_exists('interpreteBackSlashes', $args[$count-1]) ? $args[$count-1]['interpreteBackSlashes'] : true);
				if (array_key_exists('script', $args[$count-1]) && $args[$count-1]['script']) {
					self::$strings[$string] = call_user_func_array('sprintf', $args);
					return $string;
				}
			}
			else {
				$args[0] = $lang->_($string);
			}
			return call_user_func_array('sprintf', $args);
		}
		return '';
	}

	/**
	 * Passes a string thru an printf.
	 *
	 * @param	format The format string.
	 * @param	mixed Mixed number of arguments for the sprintf function.
	 * @since	1.5
	 */
	public static function printf($string)
	{
		$lang	= JFactory::getLanguage();
		$args	= func_get_args();
		$count	= count($args);
		if ($count > 0) {
			if (is_array($args[$count-1])) {
				$args[0] = $lang->_($string, array_key_exists('jsSafe', $args[$count-1]) ? $args[$count-1]['jsSafe'] : false, array_key_exists('interpreteBackSlashes', $args[$count-1]) ? $args[$count-1]['interpreteBackSlashes'] : true);
			}
			else {
				$args[0] = $lang->_($string);
			}
			return call_user_func_array('printf', $args);
		}
		return '';
	}

	/**
	 * Translate a string into the current language and stores it in the JavaScript language store.
	 *
	 * @param	string	The JText key.
	 * @since	1.6
	 */
	public static function script($string = null, $jsSafe = false, $interpreteBackSlashes = true)
	{
		if (is_array($jsSafe)) {
			if (array_key_exists('interpreteBackSlashes', $jsSafe)) {
				$interpreteBackSlashes = (boolean) $jsSafe['interpreteBackSlashes'];
			}
			if (array_key_exists('jsSafe', $jsSafe)) {
				$jsSafe = (boolean) $jsSafe['jsSafe'];
			}
			else {
				$jsSafe = false;
			}
		}

		// Add the string to the array if not null.
		if ($string !== null) {
			// Normalize the key and translate the string.
			self::$strings[strtoupper($string)] = JFactory::getLanguage()->_($string, $jsSafe, $interpreteBackSlashes);
		}

		return self::$strings;
	}
}
