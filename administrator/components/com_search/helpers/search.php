<?php
/**
 * @version $Id$
 * @package  Joomla
 * @subpackage Search
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * @package Joomla
 * @subpackage Search
 */
class SearchHelper
{
	/**
	 * Prepares results from search for display
	 *
	 * @param string The source string
	 * @param int Number of chars to trim
	 * @param string The searchword to select around
	 * @return string	
	 */
	function prepareSearchContent( $text, $length = 200, $searchword ) 
	{
		// strips tags won't remove the actual jscript
		$text = preg_replace( "'<script[^>]*>.*?</script>'si", "", $text );
		$text = preg_replace( '/{.+?}/', '', $text);
		//$text = preg_replace( '/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2', $text );
		// replace line breaking tags with whitespace
		$text = preg_replace( "'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text );

		return SearchHelper::_smartSubstr( strip_tags( $text ), $length, $searchword );
	}

	/**
	 * returns substring of characters around a searchword
	 *
	 * @param string The source string
	 * @param int Number of chars to return
	 * @param string The searchword to select around
	 * @return string
	 */
	function _smartSubstr($text, $length = 200, $searchword) 
	{
  		$wordpos = JString::strpos(JString::strtolower($text), JString::strtolower($searchword));
  		$halfside = intval($wordpos - $length/2 - JString::strlen($searchword));
  		if ($wordpos && $halfside > 0) {
			return '...' . JString::substr($text, $halfside, $length) . '...';
  		} else {
			return JString::substr( $text, 0, $length);
  		}
	}
}
?>