<?php
/**
 * @version $Id: content.php 2851 2006-03-20 21:45:20Z Jinx $
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

// require the component helper 
require_once (JApplicationHelper::getPath('helper', 'com_content'));

/**
 * Content Component Category Model
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.1
 */
class JContentCategory
{

	/**
	 * Method to build data for displaying a content section
	 *
	 * @static
	 * @since 1.0
	 */
	function getCategoryData()
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize some variables
		 */
		$db						= & $mainframe->getDBO();
		$user					= & $mainframe->getUser();
		$noauth				= !$mainframe->getCfg('shownoauth');
		$now					= $mainframe->get('requestTime');
		$nullDate				= $db->getNullDate();
		$gid						= $user->get('gid');
		$id						= JRequest::getVar('id', 0, '', 'int');
		$sectionid			= JRequest::getVar('sectionid', 0, '', 'int');
		$limit					= JRequest::getVar('limit', 0, '', 'int');
		$limitstart				= JRequest::getVar('limitstart', 0, '', 'int');
		$filter_order		= JRequest::getVar('filter_order', 'a.created');
		$filter_order_Dir	= JRequest::getVar('filter_order_Dir', 'DESC');
		$category			= null;

		/*
		 * Create a user access object for the user
		 */
		$access							= new stdClass();
		$access->canEdit			= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		/*
		* Lets get the information for the current category
		*/
		$query = "SELECT c.*, s.id sectionid, s.title as sectiontitle" .
				"\n FROM #__categories AS c" .
				"\n INNER JOIN #__sections AS s ON s.id = c.section" .
				"\n WHERE c.id = '$id'". ($noauth ? "\n AND c.access <= $gid" : '') .
				"\n LIMIT 1";
		$db->setQuery($query);
		$db->loadObject($category);

		/*
		Check if category is published
		*/
		if (!$category->published)
		{
			JError::raiseError( 404, JText::_("Resource Not Found") );
		}
		/*
		* check whether category access level allows access
		*/
		if ($category->access > $gid)
		{
			JError::raiseError( 403, JText::_("Access Forbidden") );
		}

		$section = & JModel::getInstance('section', $db);
		$section->load($category->section);

		/*
		Check if section is published
		*/
		if (!$section->published)
		{
			JError::raiseError( 404, JText::_("Resource Not Found") );
		}

		/*
		* check whether section access level allows access
		*/
		if ($section->access > $gid)
		{
			JError::raiseError( 403, JText::_("Access Forbidden") );
		}

		// Paramters
		if ($Itemid)
		{
			$menu = JMenu::getInstance();
			$menu = $menu->getItem($Itemid);
			$params = new JParameter($menu->params);
			$pagetitle = $menu->name;
		}
		else
		{
			$menu = null;
			$params = new JParameter();
			$pagetitle = null;
		}

		$params->set('type', 'category');

		$params->def('page_title',				1);
		$params->def('title',							1);
		$params->def('hits',							$mainframe->getCfg('hits'));
		$params->def('author',					!$mainframe->getCfg('hideAuthor'));
		$params->def('date',						!$mainframe->getCfg('hideCreateDate'));
		$params->def('date_format',			JText::_('DATE_FORMAT_LC'));
		$params->def('navigation',				2);
		$params->def('display',					1);
		$params->def('display_num',			$mainframe->getCfg('list_limit'));
		$params->def('other_cat',				1);
		$params->def('empty_cat',			0);
		$params->def('cat_items',				1);
		$params->def('cat_description',	0);
		$params->def('back_button',			$mainframe->getCfg('back_button'));
		$params->def('pageclass_sfx',		'');
		$params->def('headings',				1);
		$params->def('filter',						1);
		$params->def('filter_type',				'title');

		if ($access->canEdit)
		{
			$xwhere = '';
			$xwhere2 = "\n AND b.state >= 0";
		}
		else
		{
			$xwhere = "\n AND c.published = 1";
			$xwhere2 = "\n AND b.state = 1" .
					"\n AND ( publish_up = '$nullDate' OR publish_up <= '$now' )" .
					"\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )";
		}

		// show/hide empty categories
		$empty = null;
		if (!$params->get('empty_cat'))
		{
			$empty = "\n HAVING COUNT( b.id ) > 0";
		}

		// get the list of other categories
		$query = "SELECT c.*, COUNT( b.id ) AS numitems" .
				"\n FROM #__categories AS c" .
				"\n LEFT JOIN #__content AS b ON b.catid = c.id " .
				$xwhere2. ($noauth ? "\n AND b.access <= $gid" : '') .
				"\n WHERE c.section = '$category->section'".
				$xwhere. ($noauth ? "\n AND c.access <= $gid" : '') .
				"\n GROUP BY c.id".
				$empty .
				"\n ORDER BY c.ordering";
		$db->setQuery($query);
		$other_categories = $db->loadObjectList();

		// get the total number of published items in the category
		// filter functionality
		$and = null;
		$filter = null;
		if ($params->get('filter'))
		{
			$filter = JRequest::getVar('filter', '', 'request');
			if ($filter)
			{
				// clean filter variable
				$filter = strtolower($filter);

				switch ($params->get('filter_type'))
				{
					case 'title' :
						$and = "\n AND LOWER( a.title ) LIKE '%$filter%'";
						break;

					case 'author' :
						$and = "\n AND ( ( LOWER( u.name ) LIKE '%$filter%' ) OR ( LOWER( a.created_by_alias ) LIKE '%$filter%' ) )";
						break;

					case 'hits' :
						$and = "\n AND a.hits LIKE '%$filter%'";
						break;
				}
			}

		}

		if ($access->canEdit)
		{
			$xwhere = "\n AND a.state >= 0";
		}
		else
		{
			$xwhere = "\n AND a.state = 1" .
					"\n AND ( publish_up = '$nullDate' OR publish_up <= '$now' )" .
					"\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )";
		}

		// Ordering control
		$orderby = "\n ORDER BY $filter_order $filter_order_Dir, a.created DESC";

		$query = "SELECT COUNT(a.id) as numitems" .
				"\n FROM #__content AS a" .
				"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
				"\n LEFT JOIN #__groups AS g ON a.access = g.id" .
				"\n WHERE a.catid = $category->id".
				$xwhere. 
				($noauth ? "\n AND a.access <= $gid" : '') .
				"\n AND $category->access <= $gid".
				$and.
				$orderby;
		$db->setQuery($query);
		$counter = $db->loadObjectList();
		$total = $counter[0]->numitems;
		$limit = $limit ? $limit : $params->get('display_num');
		if ($total <= $limit)
		{
			$limitstart = 0;
		}

		jimport('joomla.presentation.pagination');
		$page = new JPagination($total, $limitstart, $limit);

		// get the list of items for this category
		$query = "SELECT a.id, a.title, a.hits, a.created_by, a.created_by_alias, a.created AS created, a.access, u.name AS author, a.state, g.name AS groups" .
				"\n FROM #__content AS a" .
				"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
				"\n LEFT JOIN #__groups AS g ON a.access = g.id" .
				"\n WHERE a.catid = $category->id".
				$xwhere. 
				($noauth ? "\n AND a.access <= $gid" : '') .
				"\n AND $category->access <= $gid".
				$and.
				$orderby;
		$db->setQuery($query, $limitstart, $limit);
		$items = $db->loadObjectList();

		$lists['task'] = 'category';
		$lists['filter'] = $filter;

		// Dynamic Page Title
		$mainframe->SetPageTitle($pagetitle);

		/*
		 * Handle BreadCrumbs
		 */
		$breadcrumbs = & $mainframe->getPathWay();
		// Section
		$breadcrumbs->addItem($category->sectiontitle, sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$category->sectionid.'&amp;Itemid='.$menu->id));
		// Category
		$breadcrumbs->addItem($category->title, '');

		// table ordering
		if ($filter_order_Dir == 'DESC')
		{
			$lists['order_Dir'] = 'ASC';
		}
		else
		{
			$lists['order_Dir'] = 'DESC';
		}
		$lists['order'] = $filter_order;
		$selected = '';

		$cache = & JFactory::getCache('com_content');
		$cache->call('JContentViewHTML::showCategory', $category, $other_categories, $items, $access, $params, $page, $lists, $selected);
	}

	function getOtherCategories()
	{
		
	}
}
?>