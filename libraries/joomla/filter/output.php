<?php
/**
 * @version $Id: functions.php 4277 2006-07-19 20:35:35Z friesengeist $
 * @package		Joomla.Framework
 * @subpackage	Filter
 * @copyright	Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * JOutputFilter
 *
 * @static
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Filter
 * @since		1.5
 */
class JOutputFilter
{
	/**
	* Makes an object safe to display in forms
	*
	* Object parameters that are non-string, array, object or start with underscore
	* will be converted
	*
	* @static
	* @param object An object to be parsed
	* @param int The optional quote style for the htmlspecialchars function
	* @param string|array An optional single field name or array of field names not
	*					 to be parsed (eg, for a textarea)
	* @since 1.5
	*/
	function objectHtmlSafe( &$mixed, $quote_style=ENT_QUOTES, $exclude_keys='' ) 
	{
		if (is_object( $mixed )) 
		{
			foreach (get_object_vars( $mixed ) as $k => $v) 
			{
				if (is_array( $v ) || is_object( $v ) || $v == NULL || substr( $k, 1, 1 ) == '_' ) {
					continue;
				}
				
				if (is_string( $exclude_keys ) && $k == $exclude_keys) {
					continue;
				} else if (is_array( $exclude_keys ) && in_array( $k, $exclude_keys )) {
					continue;
				}
				
				$mixed->$k = htmlspecialchars( $v, $quote_style );
			}
		}
	}

	/**
	* Replaces &amp; with & for xhtml compliance
	*
	* @todo There must be a better way???
	*
	* @static
	* @since 1.5
	*/
	function ampReplace( $text ) 
	{
		$text = str_replace( '&&', '*--*', $text );
		$text = str_replace( '&#', '*-*', $text );
		$text = str_replace( '&amp;', '&', $text );
		$text = preg_replace( '|&(?![\w]+;)|', '&amp;', $text );
		$text = str_replace( '*-*', '&#', $text );
		$text = str_replace( '*--*', '&&', $text );

		return $text;
	}

	/**
	 * This method processes a string and replaces all instances of & with &amp; in links only
	 *
	 * @static
	 * @param	string	$input	String to process
	 * @return	string	Processed string
	 * @since	1.5
	 */
	function linkXHTMLSafe($input)
	{
		$regex = 'href="([^"]*(&(amp;){0})[^"]*)*?"';
		return preg_replace_callback( "#$regex#i", array('JOutputFilter', '_ampReplaceCallback'), $input );
	}

	/**
	 * Callback method for replacing & with &amp; in a string
	 *
	 * @static
	 * @param	string	$m	String to process
	 * @return	string	Replaced string
	 * @since	1.5
	 */
	function _ampReplaceCallback( $m )
	{
		 $rx = '&(?!amp;)';
		 return preg_replace( '#'.$rx.'#', '&amp;', $m[0] );
	}
	
	/**
	* Cleans text of all formating and scripting code
	*/
	function cleanText ( &$text ) 
	{
		$text = preg_replace( "'<script[^>]*>.*?</script>'si", '', $text );
		$text = preg_replace( '/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2 (\1)', $text );
		$text = preg_replace( '/<!--.+?-->/', '', $text );
		$text = preg_replace( '/{.+?}/', '', $text );
		$text = preg_replace( '/&nbsp;/', ' ', $text );
		$text = preg_replace( '/&amp;/', ' ', $text );
		$text = preg_replace( '/&quot;/', ' ', $text );
		$text = strip_tags( $text );
		$text = htmlspecialchars( $text );
		return $text;
	}
}
?>