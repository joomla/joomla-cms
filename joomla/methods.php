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
 * @package 	Joomla.Framework
 * @since		1.5
 */
class JRoute
{
	/**
	 * Translates an internal Joomla URL to a humanly readible URL.
	 *
	 * @param 	string 	 $url 	Absolute or Relative URI to Joomla resource.
	 * @param 	boolean  $xhtml Replace & by &amp; for xml compilance.
	 * @param	int		 $ssl	Secure state for the resolved URI.
	 * 		 1: Make URI secure using global secure site URI.
	 * 		 0: Leave URI in the same secure state as it was passed to the function.
	 * 		-1: Make URI unsecure using the global unsecure site URI.
	 * @return	The translated humanly readible URL.
	 */
	public static function _($url, $xhtml = true, $ssl = null)
	{
		// Get the router.
		$app = &JFactory::getApplication();
		$router = &$app->getRouter();

		// Make sure that we have our router
		if (!$router) {
			return null;
		}

		if ((strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0)) {
            return $url;
 		}

		// Build route.
		$uri = &$router->build($url);
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
		if ((int) $ssl)
		{
			$uri = &JURI::getInstance();

			// Get additional parts.
			static $prefix;
			if (!$prefix)
			{
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
 * @package 	Joomla.Framework
 * @subpackage	Language
 * @since		1.5
 */
class JText
{
	/**
	 * Translates a string into the current language.
	 *
	 * @param	string $string The string to translate.
	 * @param	boolean	$jsSafe		Make the result javascript safe.
	 * @since	1.5
	 *
	 */
	public static function _($string, $jsSafe = false)
	{
		$lang = &JFactory::getLanguage();
		return $lang->_($string, $jsSafe);
	}

	/**
	 * Passes a string thru an sprintf.
	 *
	 * @param	format The format string.
	 * @param	mixed Mixed number of arguments for the sprintf function.
	 * @since	1.5
	 */
	public static function sprintf($string)
	{
		$lang = &JFactory::getLanguage();
		$args = func_get_args();
		if (count($args) > 0)
		{
			$args[0] = $lang->_($args[0]);
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
		$lang = &JFactory::getLanguage();
		$args = func_get_args();
		if (count($args) > 0)
		{
			$args[0] = $lang->_($args[0]);
			return call_user_func_array('printf', $args);
		}
		return '';
	}

	/**
	 * Translate a string into the current language and stores it in the JavaScript language store.
	 *
	 * @param	string		$string		The JText key.
	 * @return	void
	 * @since	1.6
	 */
	public static function script($string = null)
	{
		static $strings;

		// Instante the array if necessary.
		if (!is_array($strings)) {
			$strings = array();
		}

		// Add the string to the array if not null.
		if ($string !== null) {
			// Normalize the key and translate the string.
			$strings[strtoupper($string)] = JFactory::getLanguage()->_($string);
		}

		return $strings;
	}
}