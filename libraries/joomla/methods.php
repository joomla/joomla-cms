<?php
/**
* @version		$Id: router.php 8340 2007-08-07 08:18:10Z jinx $
* @package		Joomla.Framework
* @subpackage	Application
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Route handling class
 *
 * @static
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JRoute
{
	/**
	 * Translates an internal Joomla URL to a humanly readible URL.
	 *
	 * @access public
	 * @param 	string 	 $url 	Absolute or Relative URI to Joomla resource
	 * @param 	boolean  $xhtml Replace & by &amp; for xml compilance
	 * @param	int		 $ssl	Secure state for the resolved URI
	 * 		 1: Make URI secure using global secure site URI
	 * 		 0: Leave URI in the same secure state as it was passed to the function
	 * 		-1: Make URI unsecure using the global unsecure site URI
	 * @return The translated humanly readible URL
	 */
	function _($url, $xhtml = true, $ssl = 0)
	{
		// Get the router
		$app	= &JFactory::getApplication();
		$router = &$app->getRouter();
	
		// Build route
		if ($router) 
		{	
			$uri = &$router->build($url);
			$url = $uri->toString(array('path', 'query', 'fragment'));
		}
		
		/*
		 * Get the secure/unsecure URLs.

		 * If the first 5 characters of the BASE are 'https', then we are on an ssl connection over
		 * https and need to set our secure URL to the current request URL, if not, and the scheme is
		 * 'http', then we need to do a quick string manipulation to switch schemes.
		 */

		$base = JURI::base(); //get base URL

		if ( substr( $base, 0, 5 ) == 'https' )
		{
			$secure 	= $base;
			$unsecure	= 'http'.substr( $base, 5 );
		}
		elseif ( substr( $base, 0, 4 ) == 'http' )
		{
			$secure		= 'https'.substr( $base, 4 );
			$unsecure	= $base;
		}

		// Ensure that proper secure URL is used if ssl flag set secure
		if ($ssl == 1) {
			$url = $secure.$url;
		}

		// Ensure that unsecure URL is used if ssl flag is set to unsecure
		if ($ssl == -1) {
			$url = $unsecure.$url;
		}

		if($xhtml) {
			$url = str_replace( '&', '&amp;', $url );
		}

		return $url;
	}
}

/**
 * Text  handling class
 *
 * @static
 * @package 		Joomla.Framework
 * @subpackage	I18N
 * @since		1.5
 */
class JText
{
	/**
	 * Translates a string into the current language
	 *
	 * @access	public
	 * @param	string $string The string to translate
	 * @param	boolean	$jsSafe		Make the result javascript safe
	 * @since	1.5
	 *
	 */
	function _($string, $jsSafe = false)
	{
		$lang =& JFactory::getLanguage();
		return $lang->_($string, $jsSafe);
	}

	/**
	 * Passes a string thru an sprintf
	 *
	 * @access	public
	 * @param	format The format string
	 * @param	mixed Mixed number of arguments for the sprintf function
	 * @since	1.5
	 */
	function sprintf($string)
	{
		$lang =& JFactory::getLanguage();
		$args = func_get_args();
		if (count($args) > 0) {
			$args[0] = $lang->_($args[0]);
			return call_user_func_array('sprintf', $args);
		}
		return '';
	}

	/**
	 * Passes a string thru an printf
	 *
	 * @access	public
	 * @param	format The format string
	 * @param	mixed Mixed number of arguments for the sprintf function
	 * @since	1.5
	 */
	function printf($string)
	{
		$lang =& JFactory::getLanguage();
		$args = func_get_args();
		if (count($args) > 0) {
			$args[0] = $lang->_($args[0]);
			return call_user_func_array('printf', $args);
		}
		return '';
	}

}