<?php
/**
 * @version		$Id: content.php 7054 2007-03-28 23:54:44Z louis $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Content Component Helper Model
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentHelperModel
{
	function orderbyPrimary($orderby)
	{
		switch ($orderby)
		{
			case 'alpha' :
				$orderby = 'cc.title, ';
				break;

			case 'ralpha' :
				$orderby = 'cc.title DESC, ';
				break;

			case 'order' :
				$orderby = 'cc.ordering, ';
				break;

			default :
				$orderby = '';
				break;
		}

		return $orderby;
	}

	function orderbySecondary($orderby)
	{
		switch ($orderby)
		{
			case 'date' :
				$orderby = 'a.created';
				break;

			case 'rdate' :
				$orderby = 'a.created DESC';
				break;

			case 'alpha' :
				$orderby = 'a.title';
				break;

			case 'ralpha' :
				$orderby = 'a.title DESC';
				break;

			case 'hits' :
				$orderby = 'a.hits DESC';
				break;

			case 'rhits' :
				$orderby = 'a.hits';
				break;

			case 'order' :
				$orderby = 'a.ordering';
				break;

			case 'author' :
				$orderby = 'a.created_by_alias, u.name';
				break;

			case 'rauthor' :
				$orderby = 'a.created_by_alias DESC, u.name DESC';
				break;

			case 'front' :
				$orderby = 'f.ordering';
				break;

			default :
				$orderby = 'a.ordering';
				break;
		}

		return $orderby;
	}

	function buildVotingQuery()
	{
		$params = &JComponentHelper::getParams( 'com_content' );
		$voting = $params->get('vote');

		if ($voting) {
			// calculate voting count
			$select = ' , ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count';
			$join = ' LEFT JOIN #__content_rating AS v ON a.id = v.content_id';
		} else {
			$select = '';
			$join = '';
		}

		$results = array ('select' => $select, 'join' => $join);

		return $results;
	}

	function getSectionLink(& $row)
	{
		$db =& JFactory::getDBO();
		static $links;

		if (!isset ($links)) {
			$links = array ();
		}

		if (empty ($links[$row->sectionid]))
		{
			$query = 'SELECT id, link' .
					' FROM #__menu' .
					' WHERE published = 1' .
					' AND (type = "content_section" OR type = "content_blog_section" )' .
					' AND componentid = '. $row->sectionid .
					' ORDER BY type DESC, ordering';
			$db->setQuery($query);
			$result = $db->loadRow();

			$secLinkID = $result[0];
			$secLinkURL = $result[1];

			$Itemid = null;
			if ($secLinkID)
			{
				$Itemid = '&Itemid='.(int) $secLinkID;

				if ($secLinkURL) {
					$link = JRoute::_($secLinkURL.$Itemid);
				} else {
					$link = JRoute::_('index.php?option=com_content&task=section&id='.$row->sectionid.$Itemid);
				}

				$links[$row->sectionid] = '<a href="'.$link.'">'.$row->section.'</a>';
			}
			else
			{
				$links[$row->sectionid] = $row->section;
			}
		}

		return $links[$row->sectionid];
	}

	function getCategoryLink(& $row)
	{
		$db =& JFactory::getDBO();
		static $links;

		if (!isset ($links)) {
			$links = array ();
		}

		if (empty ($links[$row->catid])) {

			$query = 'SELECT id, link' .
					' FROM #__menu' .
					' WHERE published = 1' .
					' AND (type = "content_category" OR type = "content_blog_category" )' .
					' AND componentid = ' . (int) $row->catid .
					' ORDER BY type DESC, ordering';
			$db->setQuery($query);
			$result = $db->loadRow();

			$catLinkID = $result[0];
			$catLinkURL = $result[1];

			// Did we find an Itemid for the category?
			$Itemid = null;
			if ($catLinkID)
			{
				$Itemid = '&amp;Itemid='.(int) $catLinkID;
			}
			else
			{
				// Nope, lets try to find it by section...
				$query = 'SELECT id, link' .
						' FROM #__menu' .
						' WHERE published = 1' .
						' AND (type = "content_section" OR type = "content_blog_section" )' .
						' AND componentid = '. $row->sectionid .
						' ORDER BY type DESC, ordering';
				$db->setQuery($query);
				$secLinkID = $db->loadResult();

				// Find it by section?
				if ($secLinkID)	{
					$Itemid = '&amp;Itemid='.$secLinkID;
				}
			}

			if ($Itemid !== null)
			{
				if ($catLinkURL) {
					$link = JRoute::_($catLinkURL.$Itemid);
				} else {
					$link = JRoute::_('index.php?option=com_content&task=category&sectionid='.$row->sectionid.'&id='.$row->catid.$Itemid);
				}

				// We found an Itemid... build the link
				$links[$row->catid] = '<a href="'.$link.'">'.$row->category.'</a>';
			}
			else
			{
				// Didn't find an Itemid.. set the section name as the link
				$links[$row->catid] = $row->category;
			}
		}

		return $links[$row->catid];
	}
}
?>
