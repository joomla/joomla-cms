<?php
/**
 * @version $Id: mod_banners.php 3222 2006-04-24 01:49:01Z webImagery $
 * @package  Joomla
 * @subpackage Banners
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.model' );
jimport( 'joomla.application.extension.component' );

/**
 * @package Joomla
 * @subpackage Banners
 */
class JModelBanner extends JModel
{
	/**
	 * Gets a list of banners
	 * @param array An array of filters
	 * @return array An array of banner objects
	 */
	function getList( $filters )
	{
		$db			= &$this->getDBO();
		$ordering	= @$filters['ordering'];
		$tagSearch	= @$filters['tag_search'];
		$randomise	= ($ordering == 'random');

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
		if (is_array( $tagSearch ))
		{
			$temp = array();
			$n = count( $tagSearch );
			if ($n == 0)
			{
				// if tagsearch is an array, and empty, fail the query
				$result = array();
				return $result;
			}
			for ($i = 0; $i < $n; $i++)
			{
				$temp[] = "tags REGEXP '[[:<:]]".$db->getEscaped( $tagSearch[$i] ) . "[[:>:]]'";
			}
			if ($n)
			{
				$wheres[] = '(' . implode( ' OR ', $temp). ')';
			}
		}

		$query = "SELECT *"
			. ($randomise ? ', RAND() AS ordering' : '')
			. "\n FROM #__banner"
			. "\n WHERE " . implode( " AND ", $wheres )
			. "\nORDER BY sticky, ordering "
			. "\nLIMIT " . $filters['limit'];

		$db->setQuery( $query );
		//echo $db->getQuery();die;
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
		$config = JComponentHelper::getParams( 'com_banners' );
		$db		= &$this->getDBO();
		$n		= count( $list );

		$trackImpressions = $config->get( 'track_impressions' );
		$trackDate = date( 'Y-m-d' );

		// TODO: Change loop single sql with where bid = x OR bid = y format
		for ($i = 0; $i < $n; $i++) {
			$item = &$list[$i];
		
			$item->impmade++;
			$expire = ($item->imptotal >= $item->impmade);

			$query = 'UPDATE #__banner'
			. "\n SET impmade = impmade + 1"
			. ($expire ? ', showBanner=0' : '')
			. "\n WHERE bid = $item->bid"
			;
			$db->setQuery( $query );

			if(!$db->query()) {
				JError::raiseError( 500, $db->stderror());
			}
			
			if ($trackImpressions)
			{
				// TODO: Add impression tracking
				$query = 'UPDATE #__bannertrack SET' .
					' track_type = 1,' .
					' banner_id = ' . $item->bid; 
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
		$config = JComponentHelper::getParams( 'com_banners' );
		$db		= &$this->getDBO();

		$trackClicks = $config->get( 'track_clicks' );
		$trackDate = date( 'Y-m-d' );

		// update click count
		$query = 'UPDATE #__banner' .
			' SET clicks = ( clicks + 1 )' .
			' WHERE bid = ' . (int)$id;
	
		$db->setQuery( $query );
		if(!$db->query()) {
			JError::raiseError( 500, $db->stderror());
		}

		if ($trackClicks)
		{
			// TODO: Add click tracking
		}

	}

	/**
	 * Get the URL for a 
	 */
	function getUrl( $id = 0 )
	{
		$db = &$this->getDBO();

		// redirect to banner url
		$query = 'SELECT clickurl FROM #__banner' .
			' WHERE bid = ' . (int) $id;
	
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

/**
 * @package Joomla
 * @subpackage Banners
 */
class JBannerHelper
{
	/**
	 * Returns a list of valid keywords based on the prefix in banner
	 * configuration
	 * @param mixed An array of keywords, or comma delimited string
	 * @return array
	 * @static
	 */
	function &getKeywords( $keywords )
	{
		static $instance;

		if (!$instance)
		{
			$config = JComponentHelper::getParams( 'com_banners' );
			$prefix = $config->get( 'tag_prefix' );
	
			$instance = array();

			if (!is_array( $keywords ))
			{
				$keywords = explode( ',', $keywords );
			}
				
			foreach ($keywords as $keyword)
			{
				$keyword = trim( $keyword );
				$regex = '#^' . $prefix . '#';
				if (preg_match( $regex, $keyword ))
				{
					$instance[] = $keyword;
				}
			}
		}
		return $instance;
	}
}
?>