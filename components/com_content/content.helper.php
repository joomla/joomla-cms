<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


/**
 * Content Component Helper
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.1
 */
class JContentHelper
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
				$orderby = 'a.hits';
				break;

			case 'rhits' :
				$orderby = 'a.hits DESC';
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
		global $database, $mainframe;

		$noauth = !$mainframe->getCfg('shownoauth');
		$nullDate = $database->getNullDate();
		$where = array ();

		// normal
		if ($type > 0)
		{
			$where[] = "a.state = 1";
			if (!$access->canEdit)
			{
				$where[] = "( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )";
				$where[] = "( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )";
			}
			if ($id > 0)
			{
				if ($type == 1)
				{
					$where[] = "a.sectionid IN ( $id ) ";
				}
				else
					if ($type == 2)
					{
						$where[] = "a.catid IN ( $id ) ";
					}
			}
		}

		// archive
		if ($type < 0)
		{
			$where[] = "a.state='-1'";
			if ($year)
			{
				$where[] = "YEAR( a.created ) = '$year'";
			}
			if ($month)
			{
				$where[] = "MONTH( a.created ) = '$month'";
			}
			if ($id > 0)
			{
				if ($type == -1)
				{
					$where[] = "a.sectionid = $id";
				}
				else
					if ($type == -2)
					{
						$where[] = "a.catid = $id";
					}
			}
		}

		if ($id == 0)
		{
			$where[] = "s.published = 1";
			$where[] = "cc.published = 1";
			if ($noauth)
			{
				$where[] = "a.access <= $gid";
				$where[] = "s.access <= $gid";
				$where[] = "cc.access <= $gid";
			}
		}

		return $where;
	}

	function buildVotingQuery()
	{
		global $mainframe;

		$voting = $mainframe->getCfg('vote');

		if ($voting)
		{
			// calculate voting count
			$select = "\n , ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count";
			$join = "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id";
		}
		else
		{
			$select = '';
			$join = '';
		}

		$results = array ('select' => $select, 'join' => $join);

		return $results;
	}

	function getSectionLink(&$row)
	{
		static $links;

		if (!isset ($links)) {
			$links = array ();
		}

		if (empty ($links[$row->sectionid]))
		{
			$query = "SELECT id, link" .
					"\n FROM #__menu" .
					"\n WHERE published = 1" .
					"\n AND (type = 'content_section' OR type = 'content_blog_section' )" .
					"\n AND componentid = $row->sectionid" .
					"\n ORDER BY type DESC, ordering";
			$database->setQuery($query);
			//$secLinkID = $database->loadResult();
			$result = $database->loadRow();

			$secLinkID = $result[0];
			$secLinkURL = $result[1];

			/*
			 * Did we find an Itemid for the section?
			 */
			$Itemid = null;
			if ($secLinkID)
			{
				$Itemid = '&amp;Itemid='.(int)$secLinkID;

				if ($secLinkURL)
				{
					$link = sefRelToAbs($secLinkURL.$Itemid);
				}
				else
				{
					$link = sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$row->sectionid.$Itemid);
				}
				/*
				 * We found one.. and built the link, so lets set it
				 */
				$links[$row->sectionid] = '<a href="'.$link.'">'.$row->section.'</a>';
			}
			else
			{
				/*
				 * Didn't find an Itemid.. set the section name as the link
				 */
				$links[$row->sectionid] = $row->section;
			}
		}

		return $links[$row->sectionid];
	}

	function getCategoryLink(&$row)
	{
		static $links;

		if (!isset ($links)) {
			$links = array ();
		}

		if (empty ($links[$row->catid])) {
	
			$query = "SELECT id, link" .
					"\n FROM #__menu" .
					"\n WHERE published = 1" .
					"\n AND (type = 'content_category' OR type = 'content_blog_category' )" .
					"\n AND componentid = $row->catid" .
					"\n ORDER BY type DESC, ordering";
			$database->setQuery($query);
			$result = $database->loadRow();

			$catLinkID = $result[0];
			$catLinkURL = $result[1];

			/*
			 * Did we find an Itemid for the category?
			 */
			$Itemid = null;
			if ($catLinkID)
			{
				$Itemid = '&amp;Itemid='.(int)$catLinkID;
			}	else
			{
				/*
				 * Nope, lets try to find it by section...
				 */
				$query = "SELECT id, link" .
						"\n FROM #__menu" .
						"\n WHERE published = 1" .
						"\n AND (type = 'content_section' OR type = 'content_blog_section' )" .
						"\n AND componentid = $row->sectionid" .
						"\n ORDER BY type DESC, ordering";
				$database->setQuery($query);
				$secLinkID = $database->loadResult();
				
				/*
				 * Find it by section?
				 */
				if ($secLinkID)
				{
					$Itemid = '&amp;Itemid='.$secLinkID;
				}
			}

			if ($Itemid !== null)
			{
				if ($catLinkURL)
				{
					$link = sefRelToAbs($catLinkURL.$Itemid);
				}
				else
				{
					$link = sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$row->sectionid.'&amp;id='.$row->catid.$Itemid);
				}
				/*
				 * We found an Itemid... build the link
				 */
				$links[$row->catid] = '<a href="'.$link.'">'.$row->category.'</a>';
			}
			else
			{
				/*
				 * Didn't find an Itemid.. set the section name as the link
				 */
				$links[$row->catid] = $row->category;
			}
		}

		return $links[$row->catid];
	}

	function getItemid($id)
	{
		global $mainframe;

		$db			= & $mainframe->getDBO();
		$menu		= JMenu::getInstance();
		$items		= $menu->getMenu();
		$Itemid	= null;

		if (count($items))
		{

			/*
			 * Do we have a content item linked to the menu with this id?
			 */
			foreach ($items as $item)
			{
				if ($item->link == "index.php?option=com_content&task=view&id=$id")
				{
					return $item->id;
				}
			}

			/*
			 * Not a content item, so perhaps is it in a section that is linked
			 * to the menu?
			 */
			$query = "SELECT m.id " .
					"\n FROM #__content AS i" .
					"\n LEFT JOIN #__sections AS s ON i.sectionid = s.id" .
					"\n LEFT JOIN #__menu AS m ON m.componentid = s.id " .
					"\n WHERE (m.type = 'content_section' OR m.type = 'content_blog_section')" .
					"\n AND m.published = 1" .
					"\n AND i.id = $id";
			$db->setQuery($query);
			$Itemid = $db->loadResult();
			if ($Itemid != '')
			{
				return $Itemid;
			}

			/*
			 * Not a section either... is it in a category that is linked to the
			 * menu?
			 */
			$query = "SELECT m.id " .
					"\n FROM #__content AS i" .
					"\n LEFT JOIN #__categories AS c ON i.catid = c.id" .
					"\n LEFT JOIN #__menu AS m ON m.componentid = c.id " .
					"\n WHERE (m.type = 'content_blog_category' OR m.type = 'content_category')" .
					"\n AND m.published = 1" .
					"\n AND i.id = $id";
			$db->setQuery($query);
			$Itemid = $db->loadResult();
			if ($Itemid != '')
			{
				return $Itemid;
			}

			/*
			 * Once we have exhausted all our options for finding the Itemid in
			 * the content structure, lets see if maybe we have a global blog
			 * section in the menu we can put it under.
			 */
			foreach ($items as $item)
			{
				if ($item->type == "content_blog_section" && $item->componentid == "0")
				{
					return $item->id;
				}
			}
		}

		if ($Itemid != '')
		{
			return $Itemid;
		}
		else
		{
			return JRequest::getVar('Itemid', 9999, '', 'int');
		}
	}
}
?>