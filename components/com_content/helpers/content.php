<?php
/**
 * @version		$Id$
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

// Component Helper
jimport('joomla.application.component.helper');

/**
 * Content Component Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class JContentHelper
{
	function saveContentPrep(& $row)
	{
		//Get submitted text from the request variables
		$text = JRequest::getVar('text', '', 'post', 'string', JREQUEST_ALLOWRAW);

		//Clean text for xhtml transitional compliance
		jimport('joomla.filter.output');
		$text = str_replace('<br>', '<br />', $text);
		$row->title = JOutputFilter::ampReplace($row->title);

		// Search for the {readmore} tag and split the text up accordingly.
		$tagPos = JString::strpos($text, '<hr id="system-readmore" />');

		if ($tagPos === false)	{
			$row->introtext = $text;
		} else 	{
			$row->introtext = JString::substr($text, 0, $tagPos);
			$row->fulltext = JString::substr($text, $tagPos +27);
		}

		return true;
	}

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

	/*
	* @param int 0 = Archives, 1 = Section, 2 = Category
	*/
	function buildWhere($type = 1, & $access, & $noauth, $gid, $id, $now = NULL, $year = NULL, $month = NULL)
	{
		global $mainframe;

		$db 		=& JFactory::getDBO();
		$params 	= &JComponentHelper::getParams( 'com_content' );
		$noauth 	= !$params->get('shownoauth');
		$nullDate 	= $db->getNullDate();
		$where = array ();

		// normal
		if ($type > 0) {
			$where[] = "a.state = 1";
			if (!$access->canEdit) {
				$where[] = "( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )";
				$where[] = "( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )";
			}
			if ($id > 0) {
				if ($type == 1) {
					$where[] = "a.sectionid IN ( $id ) ";
				} else
					if ($type == 2) {
						$where[] = "a.catid IN ( $id ) ";
					}
			}
		}

		// archive
		if ($type < 0)
		{
			$where[] = "a.state='-1'";
			if ($year) {
				$where[] = "YEAR( a.created ) = '$year'";
			}
			if ($month) {
				$where[] = "MONTH( a.created ) = '$month'";
			}
			if ($id > 0) {
				if ($type == -1) {
					$where[] = "a.sectionid = $id";
				} else
					if ($type == -2) {
						$where[] = "a.catid = $id";
					}
			}
		}

		if ($id == 0) {
			$where[] = "s.published = 1";
			$where[] = "cc.published = 1";
			if ($noauth) {
				$where[] = "a.access <= $gid";
				$where[] = "s.access <= $gid";
				$where[] = "cc.access <= $gid";
			}
		}

		return $where;
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

		if (empty ($links[$row->sectionid])) {
			$query = 'SELECT id, link' .
					' FROM #__menu' .
					' WHERE published = 1' .
					' AND (type = "content_section" OR type = "content_blog_section" )' .
					' AND componentid = '. $row->sectionid .
					' ORDER BY type DESC, ordering';
			$db->setQuery($query);
			//$secLinkID = $db->loadResult();
			$result = $db->loadRow();

			$secLinkID = $result[0];
			$secLinkURL = $result[1];

			/*
			 * Did we find an Itemid for the section?
			 */
			$Itemid = null;
			if ($secLinkID)	{
				$Itemid = '&Itemid='.(int) $secLinkID;

				if ($secLinkURL) {
					$link = JRoute::_($secLinkURL.$Itemid);
				} else {
					$link = JRoute::_('index.php?option=com_content&task=section&id='.$row->sectionid.$Itemid);
				}
				/*
				 * We found one.. and built the link, so lets set it
				 */
				$links[$row->sectionid] = '<a href="'.$link.'">'.$row->section.'</a>';
			} else {
				/*
				 * Didn't find an Itemid.. set the section name as the link
				 */
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


	/**
	 * @param	int	The route of the content item
	 */
	function getArticleRoute($id, $catid = 0, $sectionid = 0)
	{
		$item = JContentHelper::_getArticleMenuInfo((int)$id, (int)$catid, (int)$sectionid);

		$link = 'index.php?option=com_content&view=article';
		if(@$item->link_parts['view'] == 'article') {
			$link .=  '&Itemid='. $item->id;
		}

		if(@$item->link_parts['view'] == 'category') {
			$link .= '&catid='.$catid.'&id='. $id . '&Itemid='. $item->id;
		}

		if(@$item->link_parts['view'] == 'section') {
			$link .= '&catid='.$catid.'&id='. $id . '&Itemid='. $item->id;
		}

		return JRoute::_( $link );
	}

	/**
	 * @param	int	The menu information based on the article identifiers
	 */
	function _getArticleMenuInfo($id, $catid = 0, $sectionid = 0)
	{
		$db			=& JFactory::getDBO();
		$component	=& JComponentHelper::getInfo('com_content');

		$menus		=& JMenu::getInstance();
		$items		= $menus->getItems('componentid', $component->id);

		$n = count( $items );
		if (!$n) {
			return null;
		}

		for ($i = 0; $i < $n; $i++)
		{
			$item = &$items[$i];
			$url = str_replace('index.php?', '', $item->link);
			$url = str_replace('&amp;', '&', $url);
			$parts = null;
			parse_str($url, $parts);

			if(!isset($parts['id'])) {
				continue;
			}

			// set the link parts
			$item->link_parts = $parts;

			// Do we have a content item linked to the menu with this id?
			if (($item->published) && ($item->link_parts['id'] == $id) && ($item->link_parts['view'] = 'article')) {
				return $item;
			}

			// Check to see if it is in a published category
			if (($item->published) && ($item->link_parts['id'] == $catid) && $item->link_parts['view'] == 'category') {
				return $item;
			}

			// Check to see if it is in a published section
			if (($item->published) && ($item->link_parts['id'] == $sectionid) && $item->link_parts['view'] == 'section') {
				return $item;
			}
		}

		return null;
	}
}
?>
