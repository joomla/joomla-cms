<?php
/**
 * @version		$Id$
 * @package  Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights
 * reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );
jimport( 'joomla.application.component.helper' );

/**
 * @package		Joomla
 * @subpackage	Banners
 */
class BannersModelBanner extends JModel
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
			. ' FROM #__banner'
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' ORDER BY sticky DESC, ordering ';

		$db->setQuery( $query, 0, $filters['limit'] );

		$result = $db->loadObjectList();

//		if($db->getErrorNum()) {
//			JError::raiseError( 500, $db->stderr());
//		}
		return $result;
	}

	/**
	 * Makes impressions on a list of banners
	 */
	function impress( $list )
	{
		$config =& JComponentHelper::getParams( 'com_banners' );
		$db		= &$this->getDBO();
		$n		= count( $list );

		$trackImpressions = $config->get( 'track_impressions' );
		// TODO: Should this be JDate?
		$trackDate = date( 'Y-m-d' );

		// TODO: Change loop single sql with where bid = x OR bid = y format
		for ($i = 0; $i < $n; $i++) {
			$item = &$list[$i];

			$item->impmade++;
			$expire = ($item->impmade >= $item->imptotal) && ($item->imptotal != 0);

			$query = 'UPDATE #__banner'
			. ' SET impmade = impmade + 1'
			. ($expire ? ', showBanner=0' : '')
			. ' WHERE bid = '.(int) $item->bid
			;
			$db->setQuery( $query );

			if(!$db->query()) {
				JError::raiseError( 500, $db->stderror());
			}

			if ($trackImpressions)
			{
				// TODO: Add impression tracking
				/*
				$query = 'UPDATE #__bannertrack SET' .
					' track_type = 1,' .
					' banner_id = ' . $item->bid;
				*/
				$query = 'INSERT INTO #__bannertrack ( track_type, banner_id, track_date )' .
					' VALUES ( 1, '.(int) $item->bid.', '.$db->Quote($trackDate).' )'
					;
				$db->setQuery( $query );

				if(!$db->query()) {
					JError::raiseError( 500, $db->stderror() );
				}
			}
		}
	}

	/**
	 * Clicks the URL, incrementing the counter
	 */
	function click( $id = 0 )
	{
		$config =& JComponentHelper::getParams( 'com_banners' );
		$db		= &$this->getDBO();

		$trackClicks = $config->get( 'track_clicks' );
		// TODO: Should this be JDate?
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
			$query = 'INSERT INTO #__bannertrack ( track_type, banner_id, track_date )' .
				' VALUES ( 2, '.(int)$id.', '.$db->Quote($trackDate).' )'
				;
			$db->setQuery( $query );

			if(!$db->query()) {
				JError::raiseError( 500, $db->stderror() );
			}
		}

	}

	/**
	 * Get the URL for a
	 */
	function getUrl( $id = 0 )
	{
		global $mainframe;

		$db = &$this->getDBO();

		// redirect to banner url
		$query = 'SELECT clickurl FROM #__banner' .
			' WHERE bid = ' . (int) $id;

		$db->setQuery( $query );
		if(!$db->query())
		{
			JError::raiseError( 500, $db->stderr());
		}
		$url = $db->loadResult();

		// check for links
		if (!preg_match( '#http[s]?://|index[2]?\.php#', $url ))
		{
			$url = "http://$url";
		}
		return $url;
	}
}