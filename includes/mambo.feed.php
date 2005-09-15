<?php
/**
 * @version $Id: mambo.feed.php 137 2005-09-12 10:21:17Z eddieajau $
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( 'includes/feedcreator.class.php' );

/**
 * Cleans text of all formating and scripting code
 * @param string
 * @return string
 */
function rssCleanText ( $text ) {
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

/**
 * Needed to fix a known limitation in Feedcreator which hardcodes the encoding
 * @package Joomla
 */
class MamboFeedCreator extends UniversalFeedCreator {
	/**
	 * @param string RSS output format
	 * @param string Cache file name
	 * @param boolean
	 * @param string Character encoding for the contents of the feed
	 */
	function saveFeed( $format='RSS0.91', $filename='', $displayContents=true, $encoding='iso-8859-15' ) {
		// feed format
		$this->_setFormat( $format );
		// feed encoding
		$this->_feed->encoding = $encoding;

		$this->_feed->saveFeed( $filename, $displayContents );
	}
}
?>