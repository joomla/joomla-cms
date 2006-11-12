<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
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
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JContentHelper
{
	function saveContentPrep(& $row)
	{
		/*
		 * Get submitted text from the request variables
		 */
		$text = JRequest::getVar('text', '', 'post', 'string', _J_ALLOWRAW);

		/*
		 * Clean text for xhtml transitional compliance
		 */
		$text = str_replace('<br>', '<br />', $text);
		$row->title = ampReplace($row->title);

		/*
		 * Now we need to search for the {readmore} tag and split the text up
		 * accordingly.
		 */
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
			$select = "\n , ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count";
			$join = "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id";
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
			$query = "SELECT id, link" .
					"\n FROM #__menu" .
					"\n WHERE published = 1" .
					"\n AND (type = 'content_section' OR type = 'content_blog_section' )" .
					"\n AND componentid = $row->sectionid" .
					"\n ORDER BY type DESC, ordering";
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
				$Itemid = '&amp;Itemid='.(int) $secLinkID;

				if ($secLinkURL) {
					$link = sefRelToAbs($secLinkURL.$Itemid);
				} else {
					$link = sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$row->sectionid.$Itemid);
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

			$query = "SELECT id, link" .
					"\n FROM #__menu" .
					"\n WHERE published = 1" .
					"\n AND (type = 'content_category' OR type = 'content_blog_category' )" .
					"\n AND componentid = " . (int) $row->catid .
					"\n ORDER BY type DESC, ordering";
			$db->setQuery($query);
			$result = $db->loadRow();

			$catLinkID = $result[0];
			$catLinkURL = $result[1];

			/*
			 * Did we find an Itemid for the category?
			 */
			$Itemid = null;
			if ($catLinkID) 	{
				$Itemid = '&amp;Itemid='.(int) $catLinkID;
			} else {
				/*
				 * Nope, lets try to find it by section...
				 */
				$query = "SELECT id, link" .
						"\n FROM #__menu" .
						"\n WHERE published = 1" .
						"\n AND (type = 'content_section' OR type = 'content_blog_section' )" .
						"\n AND componentid = $row->sectionid" .
						"\n ORDER BY type DESC, ordering";
				$db->setQuery($query);
				$secLinkID = $db->loadResult();

				/*
				 * Find it by section?
				 */
				if ($secLinkID)	{
					$Itemid = '&amp;Itemid='.$secLinkID;
				}
			}

			if ($Itemid !== null) {
				if ($catLinkURL) {
					$link = sefRelToAbs($catLinkURL.$Itemid);
				} else {
					$link = sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$row->sectionid.'&amp;id='.$row->catid.$Itemid);
				}
				/*
				 * We found an Itemid... build the link
				 */
				$links[$row->catid] = '<a href="'.$link.'">'.$row->category.'</a>';
			} else {
				/*
				 * Didn't find an Itemid.. set the section name as the link
				 */
				$links[$row->catid] = $row->category;
			}
		}

		return $links[$row->catid];
	}

	/**
	 * @param	int	The id of the content item
	 */
	function getItemid($id)
	{
		$db    = & JFactory::getDBO();
		$menus =& JMenu::getInstance();
		$items = $menus->getMenu();
		$Itemid = null;
		$component = JComponentHelper::getInfo('com_content');

		$n = count( $items );
		if ($n)
		{
			// Do we have a content item linked to the menu with this id?
			for ($i = 0; $i < $n; $i++) {
				$item = &$items[$i];
				$params =& $menus->getParams($item->id);

				if (($item->componentid == $component->id) && ($item->published) && ($params->get('article_id') == $id)) {
					return $item->id;
				}
			}

			/*
			 * Not a linked as an article, so perhaps is it in a category or section that is linked
			 * to the menu?
			 */

			// First we must load the article data to know what section/category it is in.
			$article = JTable::getInstance('content');
			$article->load($id);

			// Check to see if it is in a published category
			for ($i = 0; $i < $n; $i++) {
				$item = &$items[$i];
				$params =& $menus->getParams($item->id);

				if (($item->componentid == $component->id) && ($item->published) && ($params->get('category_id') == $article->catid)) {
					return $item->id;
				}
			}

			// Check to see if it is in a published section
			for ($i = 0; $i < $n; $i++) {
				$item = &$items[$i];
				$params =& $menus->getParams($item->id);

				if (($item->componentid == $component->id) && ($item->published) && ($params->get('section_id') == $article->sectionid)) {
					return $item->id;
				}
			}

			/*
			 * Once we have exhausted all our options for finding the Itemid in
			 * the content structure, lets see if maybe we have a global
			 * category or section in the menu we can put it under.
			 */

			// Category
			for ($i = 0; $i < $n; $i++) {
				$item = &$items[$i];
				$params =& $menus->getParams($item->id);

				if (($item->componentid == $component->id) && ($item->published) && ($params->get('category_id') == 0)) {
					return $item->id;
				}
			}
			// Section
			for ($i = 0; $i < $n; $i++) {
				$item = &$items[$i];
				$params =& $menus->getParams($item->id);

				if (($item->componentid == $component->id) && ($item->published) && ($params->get('section_id') == 0)) {
					return $item->id;
				}
			}
		}


		return $Itemid;
	}
}
?>