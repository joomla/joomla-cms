<?php
/**
* version $Id$
* @package Joomla
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * HTML View class for the Newsfeeds component
 *
 * @static
 * @package Joomla
 * @subpackage Newsfeeds
 * @since 1.0
 */
class NewsfeedsViewNewsfeed
{
	function show( &$newsfeed, &$lists, &$params )
	{
		// feed elements
		$channel	= $lists['channel'];
		$image		= $lists['image'];
		$items 		= array_slice($lists['items'], 0, $newsfeed->numarticles);
		
		require(dirname(__FILE__).DS.'tmpl'.DS.'table.php');	
	}
	
	function limitText($text, $wordcount)
	{
		if(!$wordcount) {
			return $text;
		}
		
		$texts = explode( ' ', $text );
		$count = count( $texts );
	
		if ( $count > $wordcount ) 
		{
			$text = '';
			for( $i=0; $i < $wordcount; $i++ ) {
				$text .= ' '. $texts[$i];
			}
			$text .= '...';
		}
		
		return $text;
	}
}
?>