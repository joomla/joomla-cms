<?php
/**
 * @version $Id: mod_banners.php 3222 2006-04-24 01:49:01Z webImagery $
 * @package  Joomla
 * @subpackage Banners
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.model' );

class JModelBanner extends JModel
{
	/**
	 * Gets a list of banners
	 * @param array An array of filters
	 * @return array An array of banner objects
	 */
	function getList( $filters )
	{
		$wheres = array();
		$wheres[] = 'showBanner = 1';
		$wheres[] = '(imptotal = 0 OR impmade < imptotal)';
		
		if (@$filters['cid'])
		{
			$wheres[] = 'cid = ' . (int) $filters['cid'];
		}
		if (@$filters['catid'])
		{
			$wheres[] = 'catid = ' . (int) $filters['catid'];
		}

		// TODO: Put into model
		$query = "SELECT *"
			. (@$filters['randomise'] ? ', RAND() AS ordering' : '')
			. "\n FROM #__banner"
			. "\n WHERE " . implode( " AND ", $wheres )
			. "\nORDER BY sticky, ordering "
			. "\nLIMIT " . $filters['limit'];

		$db = &$this->getDBO();
		$db->setQuery( $query );
		if(!$db->query()) {
			JError::raiseError( 500, $db->stderr());
		}
		
		$result = $db->loadObjectList();
		return $result;
	}

	/**
	 * Makes impressions on a list of banners
	 */
	function impress( $list )
	{
		$db	= &$this->getDBO();
		$n	= count( $list );

		for ($i = 0; $i < $n; $i++) {
			$item = &$list[$i];
		
			$item->impmade++;
			$expire = ($item->imptotal >= $item->impmade);

			$query = "UPDATE #__banner"
			. "\n SET impmade = impmade + 1"
			. ($expire ? ', showBanner=0' : '')
			. "\n WHERE bid = $item->bid"
			;
			$db->setQuery( $query );

			if(!$db->query()) {
				JError::raiseError( 500, $db->stderror());
			}
		}
	}

	/**
	 * Checks if a URL is an image
	 * @param string
	 * @return URL
	 */
	function isImage( $url )
	{
		$result = preg_match( '#(\.bmp|\.gif|\.jpg|\.jpeg|\.png)$#', $url );
		return $result;
	}

	/**
	 * Checks if a URL is a Flash file
	 */
	function isFlash( $url )
	{
		$result = preg_match( '#\.swf$#', $url );
		return $result;
	}

	/**
	 * Clicks the URL, incrementing the counter
	 */
	function click( $id = 0 )
	{
		$db = &$this->getDBO();

		// update click count
		$query = "UPDATE #__banner"
		. "\n SET clicks = ( clicks + 1 )"
		. "\n WHERE bid = " . (int)$id
		;
	
		$db->setQuery( $query );
		if(!$db->query()) {
			JError::raiseError( 500, $db->stderror());
		}
	
	}

	function getUrl( $id = 0 )
	{
		$db = &$this->getDBO();

		// redirect to banner url
		$query = "SELECT clickurl FROM #__banner"
		. "\n WHERE bid = $id"
		;
	
		$db->setQuery( $query );
		if(!$db->query()) {
			JError::raiseError( 500, $db->stderror());
		}
		$url = $db->loadResult();
	
		if (substr( $url, 0, 7 ) != 'http://' &&  substr( $url, 0, 8 ) != 'https://' ) {
			$url = "http://$url";
		}
		return $url;
		josRedirect( $url );
	}
}

?>