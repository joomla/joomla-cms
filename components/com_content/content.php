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

// require the html view class
require_once (JApplicationHelper :: getPath('front_html', 'com_content'));

/*
 * Get some variables
 */
global $Itemid;
$now 		= date('Y-m-d H:i', time() + $mainframe->getCfg('offset') * 60 * 60);
$limit		= JRequest::getVar('limit', 1, '', 'int');
$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

// Editor access object
$access = new stdClass();
$access->canEdit 	= $acl->acl_check('action', 'edit', 'users', $my->usertype, 'content', 'all');
$access->canEditOwn = $acl->acl_check('action', 'edit', 'users', $my->usertype, 'content', 'own');
$access->canPublish = $acl->acl_check('action', 'publish', 'users', $my->usertype, 'content', 'all');

// cache activation
$cache = & JFactory :: getCache('com_content');

// loads function for frontpage component
if ($option == 'com_frontpage') {
	//JContentController :: frontpage($access, $now);
	$cache->call('JContentController::frontpage', $access, $now, $limit, $limitstart);
	return;
}

switch (strtolower($task)) 
{
	case 'findkey' :
		JContentController::_findKeyItem($access, $now);
		break;

	case 'view' :
		JContentController::showItem($access, $now);
		break;
		
	case 'viewpdf' :
		JContentController::showItemAsPDF($access, $now);
		break;

	case 'section' :
		$cache->call('JContentController::showSection', $access, $now);
		break;

	case 'category' :
		$cache->call('JContentController::showCategory', $access, $now, $limit, $limitstart);
		break;

	case 'blogsection' :
		$cache->call('JContentController::showBlogSection', $access, $now, $limit, $limitstart);
		break;

	case 'blogcategorymulti' :
	case 'blogcategory' :
		$cache->call('JContentController::showBlogCategory', $access, $now, $limit, $limitstart);
		break;

	case 'archivesection' :
		JContentController::showArchiveSection($access);
		break;

	case 'archivecategory' :
		JContentController::showArchiveCategory($access, $now);
		break;

	case 'edit' :
	case 'new' :
		JContentController::editItem($access, $Itemid);
		break;

	case 'save' :
	case 'apply' :
	case 'apply_new' :
		$cache = JFactory::getCache();
		$cache->cleanCache('com_content');
		JContentController::saveContent($access);
		break;

	case 'cancel' :
		JContentController::cancelContent($access);
		break;

	case 'emailform' :
		JContentController::emailContentForm();
		break;

	case 'emailsend' :
		JContentController::emailContentSend();
		break;

	case 'vote' :
		JContentController::recordVote();
		break;

	default :
		//$cache->call('showBlogSection', $access, $now );
		header("HTTP/1.0 404 Not Found");
		echo JText :: _('NOT_EXIST');
		break;
}

/**
 * Content Component Controller
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.1
 */
class JContentController 
{
	function frontpage(& $access, $now) 
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize some variables
		 */
		$db			= & $mainframe->getDBO();
		$my			= & $mainframe->getUser();
		$nullDate 	= $db->getNullDate();
		$noauth 	= !$mainframe->getCfg('shownoauth');
		$offset		= $mainframe->getCfg('offset');
		$pop 		= JRequest :: getVar('pop', 0, '', 'int');

		// Parameters
		$menu 			= & JModel :: getInstance( 'menu', $db);
		$menu->load($Itemid);
		$params 		= new JParameter($menu->params);		
		
		$orderby_sec 	= $params->def( 'orderby_sec', 	'' );
		$orderby_pri 	= $params->def( 'orderby_pri', 	'' );

		// Ordering control
		$order_sec 		= JContentController :: _orderby_sec($orderby_sec);
		$order_pri 		= JContentController :: _orderby_pri($orderby_pri);

		// query records
		$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by,"
				. "\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access,"
				. "\n CHAR_LENGTH( a.fulltext ) AS readmore, s.published AS sec_pub, cc.published AS cat_pub, s.access AS sec_access, cc.access AS cat_access," 
				. "\n ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups" 
				. "\n FROM #__content AS a" 
				. "\n INNER JOIN #__content_frontpage AS f ON f.content_id = a.id" 
				. "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid" 
				. "\n LEFT JOIN #__sections AS s ON s.id = a.sectionid" 
				. "\n LEFT JOIN #__users AS u ON u.id = a.created_by" 
				. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id" 
				. "\n LEFT JOIN #__groups AS g ON a.access = g.id" 
				. "\n WHERE a.state = 1"
				. ($noauth ? "\n AND a.access <= $my->gid" : '') 
				. "\n AND ( publish_up = '$nullDate' OR publish_up <= '$now'  )" 
				. "\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )" 
				."\n ORDER BY $order_pri $order_sec";
		$db->setQuery($query);
		$Arows = $db->loadObjectList();

		// special handling required as static content does not have a section / category id linkage
		$i = 0;
		foreach( $Arows as $row ) {
			if ( ($row->sec_pub == 1 && $row->cat_pub == 1) || ($row->sec_pub == '' && $row->cat_pub == '') ) {
			// check to determine if section or category is published
				if ( ($row->sec_access <= $my->gid && $row->cat_access <= $my->gid) || ($row->sec_access == '' && $row->cat_access == '') ) {
					// check to determine if section or category has proper access rights
					$rows[$i] = $row;
					$i++;
				}
			}
		}
		
		// Dynamic Page Title
		$mainframe->SetPageTitle($menu->name);

		JContentView :: showBlog($rows, $params, $my->gid, $access, $pop, $menu);
	}

	/**
	 * Method to build data for displaying a content section
	 *
	 * @static
	 * @param int $id Section id number to display
	 * @param object $access An access object
	 * @param string $now Timestamp
	 * @since 1.0
	 */
	function showSection(& $access, $now) 
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize some variables
		 */
		$db			= & $mainframe->getDBO();
		$my			= & $mainframe->getUser();
		$id 		= JRequest :: getVar('id', 0, '', 'int');
		$nullDate 	= $db->getNullDate();
		$noauth 	= !$mainframe->getCfg('shownoauth');

		// Load the section data object
		$section = & JModel :: getInstance( 'section', $db );
		$section->load($id);

		/*
		Check if section is published
		*/
		if(!$section->published) {
			mosNotAuth();
			return;
		}
		/*
		* check whether section access level allows access
		*/
		if( $section->access > $my->gid ) {
			mosNotAuth();
			return;
		}	

		/*
		 * Build menu parameters
		 */
		if ($Itemid) {
			$menu 	= & JModel :: getInstance( 'menu', $db );
			$menu->load($Itemid);
			$params = new JParameter($menu->params);
		} else {
			$menu = null;
			$params = new JParameter();
		}

		// Set the display type parameter
		$params->set('type', 'section');

		// Set some parameter defaults
		$params->def('page_title', 			1);
		$params->def('pageclass_sfx', 		'');
		$params->def('other_cat_section', 	1);
		$params->def('empty_cat_section', 	0);
		$params->def('other_cat', 			1);
		$params->def('empty_cat', 			0);
		$params->def('cat_items', 			1);
		$params->def('cat_description', 	1);
		$params->def('back_button', 		$mainframe->getCfg('back_button'));
		$params->def('pageclass_sfx', 		'');

		// Ordering control
		$orderby = $params->get('orderby', '');
		$orderby = JContentController :: _orderby_sec($orderby);

		// Handle the access permissions part of the main database query
		if ($access->canEdit) {
			$xwhere = '';
			$xwhere2 = "\n AND b.state >= 0";
		} else {
			$xwhere = "\n AND a.published = 1";
			$xwhere2 = "\n AND b.state = 1" .
					"\n AND ( b.publish_up = '$nullDate' OR b.publish_up <= '$now' )" .
					"\n AND ( b.publish_down = '$nullDate' OR b.publish_down >= '$now' )";
		}

		// Determine whether to show/hide the empty categories and sections
		$empty = null;
		$empty_sec = null;
		if ($params->get('type') == 'category') {
			// show/hide empty categories
			if (!$params->get('empty_cat')) {
				$empty = "\n HAVING numitems > 0";
			}
		}
		if ($params->get('type') == 'section') {
			// show/hide empty categories in section
			if (!$params->get('empty_cat_section')) {
				$empty_sec = "\n HAVING numitems > 0";
			}
		}

		// Handle the access permissions
		$access = null;
		if ($noauth) {
			$access = "\n AND a.access <= $my->gid";
		}

		// Query of categories within section
		$query = "SELECT a.*, COUNT( b.id ) AS numitems" 
				. "\n FROM #__categories AS a" 
				. "\n LEFT JOIN #__content AS b ON b.catid = a.id"
				. $xwhere2 
				. "\n WHERE a.section = '$section->id'"
				. $xwhere
				. $access 
				. "\n GROUP BY a.id".$empty.$empty_sec 
				. "\n ORDER BY $orderby"
				;
		$db->setQuery($query);
		$categories = $db->loadObjectList();

		/*
		 * Lets set the page title
		 */
		$document = & $mainframe->getDocument();
		if (!empty ($menu->name)) {
			$document->setTitle($menu->name);
		}

		/*
		 * Handle BreadCrumbs
		 */
		$breadcrumbs = & $mainframe->getPathWay();
		$breadcrumbs->addItem($section->title, '');

		JContentView :: showSection($section, $categories, $params, $access, $my->gid);
	}

	/**
	* @param int The category id
	* @param int The group id of the user
	* @param int The access level of the user
	* @param int The section id
	* @param int The number of items to dislpay
	* @param int The offset for pagination
	*/
	/**
	 * Method to build data for displaying a content section
	 *
	 * @static
	 * @param int $id Category id number to display
	 * @param object $access An access object
	 * @param int $sectionid The section id
	 * @param string $now Timestamp
	 * @since 1.0
	 */
	function showCategory(& $access, $now) 
	{
		global $mainframe, $Itemid, $my;

		/*
		 * Initialize some variables
		 */
		$db					= & $mainframe->getDBO();
		$my					= & $mainframe->getUser();
		$id 				= JRequest :: getVar('id', 					0, '', 'int');
		$sectionid 			= JRequest :: getVar('sectionid', 			0, '', 'int');
		$limit 				= JRequest :: getVar('limit', 				0, '', 'int');
		$limitstart 		= JRequest :: getVar('limitstart', 			0, '', 'int');
		$nullDate 			= $db->getNullDate();
		$noauth 			= !$mainframe->getCfg('shownoauth');
		$category			= null;
		$filter_order		= JRequest :: getVar('filter_order', 		'a.created');
		$filter_order_Dir	= JRequest :: getVar('filter_order_Dir', 	'DESC');
		
		/*
		* Lets get the information for the current category
		*/
		$query = "SELECT c.*, s.id sectionid, s.title as sectiontitle" 
		. "\n FROM #__categories AS c" 
		. "\n INNER JOIN #__sections AS s ON s.id = c.section" 
		. "\n WHERE c.id = '$id'" 
		. ($noauth ? "\n AND c.access <= $my->gid" : '') 
		. "\n LIMIT 1"
		;
		$db->setQuery($query);
		$db->loadObject($category);

		/*
		Check if category is published
		*/
		if(!$category->published) {
			mosNotAuth();
			return;
		}
		/*
		* check whether category access level allows access
		*/
		if( $category->access > $my->gid ) {
			mosNotAuth();
			return;
		}	

		$section = & JModel :: getInstance( 'section', $db );
		$section->load( $category->section );

		/*
		Check if section is published
		*/
		if(!$section->published) {
			mosNotAuth();
			return;
		}
		/*
		* check whether section access level allows access
		*/
		if( $section->access > $my->gid ) {
			mosNotAuth();
			return;
		}	

		// Paramters
		if ($Itemid) {
			$menu 		= & JModel :: getInstance( 'menu', $db);
			$menu->load($Itemid);
			$params 	= new JParameter($menu->params);
			$pagetitle 	= $menu->name;
		} else {
			$menu 		= null;
			$params		= new JParameter();
			$pagetitle 	= null;
		}
		
		$params->set('type', 'category');

		$params->def('page_title', 		1);
		$params->def('title', 			1);
		$params->def('hits', 			$mainframe->getCfg('hits'));
		$params->def('author', 			!$mainframe->getCfg('hideAuthor'));
		$params->def('date', 			!$mainframe->getCfg('hideCreateDate'));
		$params->def('date_format', 	JText :: _('DATE_FORMAT_LC'));
		$params->def('navigation', 		2);
		$params->def('display', 		1);
		$params->def('display_num', 	$mainframe->getCfg('list_limit'));
		$params->def('other_cat', 		1);
		$params->def('empty_cat', 		0);
		$params->def('cat_items', 		1);
		$params->def('cat_description', 0);
		$params->def('back_button',		$mainframe->getCfg('back_button'));
		$params->def('pageclass_sfx', 	'');
		$params->def('headings', 		1);
		$params->def('filter', 			1);
		$params->def('filter_type', 	'title');

		if ($access->canEdit) {
			$xwhere = '';
			$xwhere2 = "\n AND b.state >= 0";
		} else 	{
			$xwhere = "\n AND c.published = 1";
			$xwhere2 = "\n AND b.state = 1" .
					"\n AND ( publish_up = '$nullDate' OR publish_up <= '$now' )" .
					"\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )";
		}


		// show/hide empty categories
		$empty = null;
		if (!$params->get('empty_cat'))	{
			$empty = "\n HAVING COUNT( b.id ) > 0";
		}

		// get the list of other categories
		$query = "SELECT c.*, COUNT( b.id ) AS numitems" .
				"\n FROM #__categories AS c" .
				"\n LEFT JOIN #__content AS b ON b.catid = c.id ".$xwhere2. ($noauth ? "\n AND b.access <= $my->gid" : '') .
				"\n WHERE c.section = '$category->section'".$xwhere. ($noauth ? "\n AND c.access <= $my->gid" : '') .
				"\n GROUP BY c.id".$empty .
				"\n ORDER BY c.ordering";
		$db->setQuery($query);
		$other_categories = $db->loadObjectList();

		// get the total number of published items in the category
		// filter functionality
		$filter = JRequest::getVar( 'filter', '', 'post' );
		$filter = strtolower($filter);
		$and = null;
		if ($filter) {
			if ($params->get('filter'))	{
				switch ($params->get('filter_type')) {
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

		if ($access->canEdit) {
			$xwhere = "\n AND a.state >= 0";
		} else {
			$xwhere = "\n AND a.state = 1" .
					"\n AND ( publish_up = '$nullDate' OR publish_up <= '$now' )" .
					"\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )";
		}

		// Ordering control
		$orderby = "\n ORDER BY $filter_order $filter_order_Dir, a.created DESC";
	
		$query = "SELECT COUNT(a.id) as numitems"
				. "\n FROM #__content AS a"
				. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
				. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
				. "\n WHERE a.catid = $category->id"
				. $xwhere
				. ($noauth ? "\n AND a.access <= $my->gid" : '')
				. "\n AND $category->access <= $my->gid"
				. $and
				. $orderby
				;
		$db->setQuery($query);
		$counter = $db->loadObjectList();
		$total = $counter[0]->numitems;
		$limit = $limit ? $limit : $params->get('display_num');
		if ($total <= $limit) {
			$limitstart = 0;
		}

		jimport('joomla.utilities.presentation.pagination');
		$page = new JPagination($total, $limitstart, $limit);

		// get the list of items for this category
		$query = "SELECT a.id, a.title, a.hits, a.created_by, a.created_by_alias, a.created AS created, a.access, u.name AS author, a.state, g.name AS groups"
				. "\n FROM #__content AS a"
				. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
				. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
				. "\n WHERE a.catid = $category->id"
				. $xwhere
				. ($noauth ? "\n AND a.access <= $my->gid" : '')
				. "\n AND $category->access <= $my->gid"
				. $and
				. $orderby
				;
		$db->setQuery($query, $limitstart, $limit);
		$items = $db->loadObjectList();
		
		$lists['task'] 		= 'category';
		$lists['filter'] 	= $filter;

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
		if ( $filter_order_Dir == 'DESC' ) {
			$lists['order_Dir'] = 'ASC';
		} else {
			$lists['order_Dir'] = 'DESC';
		}
		$lists['order'] = $filter_order;
		$selected = '';
		
		JContentView :: showCategory($category, $other_categories, $items, $access, $my->gid, $params, $page, $lists, $selected);
	}

	function showBlogSection(& $access, $now = NULL) 
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize some variables
		 */
		$db			= & $mainframe->getDBO();
		$my			= & $mainframe->getUser();
		$noauth 	= !$mainframe->getCfg('shownoauth');
		$id 		= JRequest :: getVar('id', 0, '', 'int');
		$pop 		= JRequest :: getVar('pop', 0, '', 'int');

		// needed for check whether section is published
		$check = ( $id ? $id : 0 );
		
		// Parameters
		if ($Itemid) {
			$menu = & JModel :: getInstance( 'menu', $db );
			$menu->load($Itemid);
			$params = new JParameter($menu->params);
		} else {
			$menu = null;
			$params = new JParameter(null);
		}

		// new blog multiple section handling
		if (!$id) {
			$id = $params->def('sectionid', 0);
		}

		$where = JContentController :: _where(1, $access, $noauth, $my->gid, $id, $now);

		// Ordering control
		$orderby_sec 	= $params->def('orderby_sec', 'rdate');
		$orderby_pri 	= $params->def('orderby_pri', '');
		$order_sec 		= JContentController :: _orderby_sec($orderby_sec);
		$order_pri 		= JContentController :: _orderby_pri($orderby_pri);

		// Main data query
		$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by," 
				. "\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access," 
				. "\n CHAR_LENGTH( a.fulltext ) AS readmore," 
				. "\n ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups" 
				. "\n FROM #__content AS a" 
				. "\n INNER JOIN #__categories AS cc ON cc.id = a.catid" 
				. "\n LEFT JOIN #__users AS u ON u.id = a.created_by" 
				. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id" 
				. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id" 
				. "\n LEFT JOIN #__groups AS g ON a.access = g.id". (count($where) ? "\n WHERE ".implode("\n AND ", $where) : '')
				. "\n AND s.access <= $my->gid" 
				. "\n AND cc.access <= $my->gid" 
				. "\n AND s.published = 1" 
				. "\n AND cc.published = 1" 
				. "\n ORDER BY $order_pri $order_sec"
				;
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Dynamic Page Title and BreadCrumbs
		$breadcrumbs 	= & $mainframe->getPathWay();
		$document 		= & $mainframe->getDocument();
		if ($menu->name) {
			$breadcrumbs->addItem($menu->name, '');
			$document->setTitle($menu->name);
		} else {
			$breadcrumbs->addItem($rows[0]->section, '');
		}

		// check whether section is published
		if (!count($rows)) {
			$secCheck = new JModelSection( $db );
			$secCheck->load( $check );
			
			/*
			* check whether section is published
			*/
			if (!$secCheck->published) {
				mosNotAuth();
				return;
			}
			/*
			* check whether section access level allows access
			*/
			if( $secCheck->access > $my->gid ) {
				mosNotAuth();
				return;
			}			
		}
		
		JContentView :: showBlog($rows, $params, $my->gid, $access, $pop, $menu);
	}

	function showBlogCategory(& $access, $now) 
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize variables
		 */
		$db			= & $mainframe->getDBO();
		$my			= & $mainframe->getUser();
		$noauth 	= !$mainframe->getCfg('shownoauth');
		$id 		= JRequest :: getVar('id', 0, '', 'int');
		$pop 		= JRequest :: getVar('pop', 0, '', 'int');

		// needed for check whether section & category is published
		$check = ( $id ? $id : 0 );
		
		// Paramters
		if ($Itemid) {
			$menu 	= & JModel :: getInstance( 'menu', $db );
			$menu->load($Itemid);
			$params = new JParameter($menu->params);
		} else {
			$menu 	= null;
			$params = new JParameter();
		}

		// new blog multiple section handling
		if (!$id) {
			$id = $params->def('categoryid', 0);
		}

		$where = JContentController :: _where(2, $access, $noauth, $my->gid, $id, $now);

		// Ordering control
		$orderby_sec 	= $params->def('orderby_sec', 'rdate');
		$orderby_pri 	= $params->def('orderby_pri', '');
		$order_sec 		= JContentController :: _orderby_sec($orderby_sec);
		$order_pri 		= JContentController :: _orderby_pri($orderby_pri);

		// Main data query
		$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by," 
				. "\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access," 
				. "\n CHAR_LENGTH( a.fulltext ) AS readmore," 
				. "\n ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups" 
				. "\n FROM #__content AS a" 
				. "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid" 
				. "\n LEFT JOIN #__users AS u ON u.id = a.created_by" 
				. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id" 
				. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id" 
				. "\n LEFT JOIN #__groups AS g ON a.access = g.id". (count($where) ? "\n WHERE ".implode("\n AND ", $where) : '')
				. "\n AND s.access <= $my->gid"
				. "\n AND cc.access <= $my->gid"
				. "\n AND s.published = 1"
				. "\n AND cc.published = 1"
				. "\n ORDER BY $order_pri $order_sec"
				;
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Dynamic Page Title and BreadCrumbs
		$breadcrumbs 	= & $mainframe->getPathWay();
		$document		= & $mainframe->getDocument();
		if ($menu->name) {
			$document->setTitle($menu->name);
			$breadcrumbs->addItem($menu->name, '');
		} else {
			$breadcrumbs->addItem($rows[0]->section, '');
		}
		
		// check whether section & category is published
		if (!count($rows)) {
			$catCheck = new JModelCategory( $b );
			$catCheck->load( $check );
			
			/*
			* check whether category is published
			*/
			if (!$catCheck->published) {
				mosNotAuth();
				return;
			}
			/*
			* check whether category access level allows access
			*/
			if( $catCheck->access > $my->gid ) {
				mosNotAuth();
				return;
			}			
			
			$secCheck = new JModelSection( $db );
			$secCheck->load( $catCheck->section );
			
			/*
			* check whether section is published
			*/
			if (!$secCheck->published) {
				mosNotAuth();
				return;
			}
			/*
			* check whether section access level allows access
			*/
			if( $secCheck->access > $my->gid ) {
				mosNotAuth();
				return;
			}			
		}
		
		JContentView :: showBlog($rows, $params, $my->gid, $access, $pop, $menu);
	}

	function showArchiveSection(& $access) 
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize some variables
		 */
		$db			= & $mainframe->getDBO();
		$my			= & $mainframe->getUser();
		$noauth 	= !$mainframe->getCfg('shownoauth');
		$option		= JRequest :: getVar('option');
		$id 		= JRequest :: getVar('id', 0, '', 'int');
		$pop 		= JRequest :: getVar('pop', 0, '', 'int');
		$year 	= JRequest::getVar( 'year', date('Y') );
		$month 	= JRequest::getVar( 'month', date('m') );

		// needed for check whether section is published
		$check = ( $id ? $id : 0 );
		
		if ($Itemid) {
			$menu 	= & JModel :: getInstance( 'menu', $db );
			$menu->load($Itemid);
			$params = new JParameter($menu->params);
		} else {
			$menu = null;
			$params = new JParameter();
		}

		$params->set('intro_only', 1);
		$params->set('year', $year);
		$params->set('month', $month);

		// Ordering control
		$orderby_sec 	= $params->def('orderby_sec', 'rdate');
		$orderby_pri 	= $params->def('orderby_pri', '');
		$order_sec 		= JContentController :: _orderby_sec($orderby_sec);
		$order_pri 		= JContentController :: _orderby_pri($orderby_pri);

		// Build the WHERE clause for the database query
		$where = JContentController :: _where(-1, $access, $noauth, $my->gid, $id, NULL, $year, $month);

		// checks to see if 'All Sections' options used
		if ($id == 0) {
			$check = null;
		} else {
			$check = "\n AND a.sectionid = $id";
		}

		// query to determine if there are any archived entries for the section
		$query = "SELECT a.id" .
				"\n FROM #__content as a" .
				"\n WHERE a.state = -1".$check;
		$db->setQuery($query);
		$items = $db->loadObjectList();
		$archives = count($items);

		// Main Query
		$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by," 
				. "\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access," 
				. "\n CHAR_LENGTH( a.fulltext ) AS readmore," 
				. "\n ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups" 
				. "\n FROM #__content AS a" 
				. "\n INNER JOIN #__categories AS cc ON cc.id = a.catid" 
				. "\n LEFT JOIN #__users AS u ON u.id = a.created_by" 
				. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id" 
				. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id" 
				. "\n LEFT JOIN #__groups AS g ON a.access = g.id". (count($where) ? "\n WHERE ".implode("\n AND ", $where) : '')
				. "\n AND s.access <= $my->gid" 
				. "\n AND cc.access <= $my->gid" 
				. "\n AND s.published = 1" 
				. "\n AND cc.published = 1" 
				. "\n ORDER BY $order_pri $order_sec"
				;
		$db->setQuery($query);
		$rows = $db->loadObjectList();


		// Dynamic Page Title
		$mainframe->SetPageTitle($menu->name);

		// Append Archives to BreadCrumbs
		$breadcrumbs = & $mainframe->getPathWay();
		$breadcrumbs->addItem('Archives', '');

		// check whether section is published
		if (!count($rows)) {
			$secCheck = new JModelSection( $db );
			$secCheck->load( $check );			
			
			/*
			* check whether section is published
			*/
			if (!$secCheck->published) {
				mosNotAuth();
				return;
			}
			/*
			* check whether section access level allows access
			*/
			if( $secCheck->access > $my->gid ) {
				mosNotAuth();
				return;
			}			
		}
		
		if (!$archives) {
			JContentView :: emptyContainer(JText :: _('CATEGORY_ARCHIVE_EMPTY'));
		} else {
			JContentView :: showArchive($rows, $params, $menu, $access, $id, $my->gid, $pop);
		}
	}

	function showArchiveCategory(& $access, $now) 
	{
		global $mainframe, $Itemid;

		// Parameters
		$db			= & $mainframe->getDBO();
		$my			= & $mainframe->getUser();
		$noauth 	= !$mainframe->getCfg('shownoauth');
		$option		= JRequest :: getVar('option');
		$id 		= JRequest :: getVar('id', 0, '', 'int');
		$pop 		= JRequest :: getVar('pop', 0, '', 'int');
		$year		= mosGetParam($_REQUEST, 'year', date('Y'));
		$month 		= mosGetParam($_REQUEST, 'month', date('m'));
		$module 	= mosGetParam($_REQUEST, 'module', '');

		// needed for check whether section & category is published
		$check = ( $id ? $id : 0 );
		
		// used by archive module
		if ($module) {
			$check = '';
		} else {
			$check = "\n AND a.catid = $id";
		}

		if ($Itemid) {
			$menu 	= & JModel :: getInstance( 'menu', $db );
			$menu->load($Itemid);
			$params = new JParameter($menu->params);
		} else {
			$menu 	= null;
			$params = new JParameter();
		}

		$params->set('year', $year);
		$params->set('month', $month);

		// Ordering control
		$orderby_sec = $params->def('orderby', 'rdate');
		$order_sec = JContentController :: _orderby_sec($orderby_sec);

		// used in query
		$where = JContentController :: _where(-2, $access, $noauth, $my->gid, $id, NULL, $year, $month);

		// query to determine if there are any archived entries for the category
		$query = "SELECT a.id" .
				"\n FROM #__content as a" .
				"\n WHERE a.state = -1".$check;
		$db->setQuery($query);
		$items = $db->loadObjectList();
		$archives = count($items);

		$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by," 
				. "\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access," 
				. "\n CHAR_LENGTH( a.fulltext ) AS readmore," 
				. "\n ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups" 
				. "\n FROM #__content AS a" 
				. "\n INNER JOIN #__categories AS cc ON cc.id = a.catid" 
				. "\n LEFT JOIN #__users AS u ON u.id = a.created_by" 
				. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id" 
				. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id" 
				. "\n LEFT JOIN #__groups AS g ON a.access = g.id". (count($where) ? "\n WHERE ".implode("\n AND ", $where) : '')
				. "\n AND s.access <= $my->gid"
				. "\n AND cc.access <= $my->gid"
				. "\n AND s.published = 1"
				. "\n AND cc.published = 1"
				. "\n ORDER BY $order_sec"
				;
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Page Title
		$mainframe->SetPageTitle($menu->name);

		// Append Archives to BreadCrumbs
		$breadcrumbs = & $mainframe->getPathWay();
		$breadcrumbs->addItem('Archives', '');

		// check whether section & category is published
		if (!count($rows)) {
			$catCheck = new JModelCategory( $b );
			$catCheck->load( $check );
			
			/*
			* check whether category is published
			*/
			if (!$catCheck->published) {
				mosNotAuth();
				return;
			}
			/*
			* check whether category access level allows access
			*/
			if( $catCheck->access > $my->gid ) {
				mosNotAuth();
				return;
			}			
			
			$secCheck = new JModelSection( $db );
			$secCheck->load( $catCheck->section );
			
			/*
			* check whether section is published
			*/
			if (!$secCheck->published) {
				mosNotAuth();
				return;
			}
			/*
			* check whether category access level allows access
			*/
			if( $secCheck->access > $my->gid ) {
				mosNotAuth();
				return;
			}			
		}
		
		if (!$archives) {
			JContentView :: emptyContainer(JText :: _('CATEGORY_ARCHIVE_EMPTY'));
		} else {
			JContentView :: showArchive($rows, $params, $menu, $access, $id, $my->gid, $pop);
		}
	}

	/**
	 * Method to show a content item as the main page display
	 *
	 * @static
	 * @param object $access 	User access control object
	 * @param string $now		Current timestamp
	 * @return void
	 * @since 1.0
	 */
	function showItem( & $access, $now ) 
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize variables
		 */
		$db 		= & $mainframe->getDBO();
		$my			= & $mainframe->getUser();
		$MetaTitle 	= $mainframe->getCfg('MetaTitle');
		$MetaAuthor = $mainframe->getCfg('MetaAuthor');
		$nullDate	= $db->getNullDate();
		$option		= JRequest::getVar('option');
		$uid 		= JRequest::getVar('id', 0, '', 'int');
		$pop 		= JRequest::getVar('pop', 0, '', 'int');
		$row 		= null;

		if ($access->canEdit) {
			$xwhere = '';
		} else {
			$xwhere = " AND ( a.state = 1 OR a.state = -1 )" .
					"\n AND ( publish_up = '$nullDate' OR publish_up <= '$now' )" .
					"\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )";
		}

		// Main content item query
		$query = "SELECT a.*, ROUND(v.rating_sum/v.rating_count) AS rating, v.rating_count, u.name AS author, u.usertype, cc.title AS category, s.title AS section,"
				. "\n g.name AS groups, s.published AS sec_pub, cc.published AS cat_pub, s.access AS sec_access, cc.access AS cat_access"
				. "\n FROM #__content AS a"
				. "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid"
				. "\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope = 'content'"
				. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
				. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
				. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
				. "\n WHERE a.id = $uid"
				. $xwhere
				. "\n AND a.access <= $my->gid"
				;
		$db->setQuery($query);

		if ($db->loadObject($row)) {
			if (!$row->cat_pub && $row->catid) {
				// check whether category is published
				mosNotAuth();
				return;
			}
			if (!$row->sec_pub && $row->sectionid) {
				// check whether section is published
				mosNotAuth();
				return;
			}
			if ( ($row->cat_access > $my->gid) && $row->catid ) {
				// check whether category access level allows access
				mosNotAuth();  
				return;
			}
			if ( ($row->sec_access > $my->gid) && $row->sectionid ) {
				// check whether section access level allows access
				mosNotAuth();  
				return;
			}			

			$params = new JParameter($row->attribs);
			$params->set('intro_only', 0);
			$params->def('back_button', $mainframe->getCfg('back_button'));
			if ($row->sectionid == 0) {
				$params->set('item_navigation', 0);
			} else {
				$params->set('item_navigation', $mainframe->getCfg('item_navigation'));
			}
			if ($MetaTitle == '1') {
				$mainframe->addMetaTag('title', $row->title);
			}
			if ($MetaAuthor == '1') {
				$mainframe->addMetaTag('author', $row->author);
			}

			/*
			 * Handle BreadCrumbs and Page Title
			 */
			$breadcrumbs 	= & $mainframe->getPathWay();
			$document		= & $mainframe->getDocument();
			if (!empty ($Itemid)) {
				// Section
				if (!empty ($row->section)) {
					$breadcrumbs->addItem($row->section, sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$row->sectionid.'&amp;Itemid='.$Itemid));
				}
				// Category
				if (!empty ($row->section)) {
					$breadcrumbs->addItem($row->category, sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$row->sectionid.'&amp;id='.$row->catid.'&amp;Itemid='.$Itemid));
				}
			}
			// Item
			$breadcrumbs->addItem($row->title, '');
			$document->setTitle($row->title);


			JContentController::show($row, $params, $my->gid, $access, $pop, $option);
		} else {
			mosNotAuth();
			return;
		}
	}
	
	function showItemAsPDF($access, $now) {
		require_once (dirname(__FILE__).DS.'content.pdf.php');
		doUtfPDF ( );
	}

	function show($row, $params, $gid, & $access, $pop, $option, $ItemidCount = NULL) 
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$db 	= & $mainframe->getDBO();
		$noauth = !$mainframe->getCfg('shownoauth');

		if ($access->canEdit) {
			if ($row->id === null || $row->access > $gid) {
				mosNotAuth();
				return;
			}
		} else {
			if ($row->id === null || $row->state == 0) {
				mosNotAuth();
				return;
			}
			if ($row->access > $gid) {
				if ($noauth) {
					mosNotAuth();
					return;
				} else {
					if (!($params->get('intro_only'))) {
						mosNotAuth();
						return;
					}
				}
			}
		}
		
		// GC Parameters
		$params->def('link_titles', 	$mainframe->getCfg('link_titles'));
		$params->def('author', 			!$mainframe->getCfg('hideAuthor'));
		$params->def('createdate', 		!$mainframe->getCfg('hideCreateDate'));
		$params->def('modifydate', 		!$mainframe->getCfg('hideModifyDate'));
		$params->def('print', 			!$mainframe->getCfg('hidePrint'));
		$params->def('pdf', 			!$mainframe->getCfg('hidePdf'));
		$params->def('email', 			!$mainframe->getCfg('hideEmail'));
		$params->def('rating', 			$mainframe->getCfg('vote'));
		$params->def('icons', 			$mainframe->getCfg('icons'));
		$params->def('readmore', 		$mainframe->getCfg('readmore'));
		// Other Params
		$params->def('image', 			1);
		$params->def('section', 		0);
		$params->def('section_link', 	0);
		$params->def('category', 		0);
		$params->def('category_link', 	0);
		$params->def('introtext', 		1);
		$params->def('pageclass_sfx', 	'');
		$params->def('item_title', 		1);
		$params->def('url', 			1);


		/*
		 * Get itemid values for section and component links
		 */
		if (($params->get('section_link') && $row->sectionid) || ($params->get('category_link') && $row->catid)) {
			$query = "SELECT id as value, componentid as key" 
					. "\n FROM #__menu" 
					. "\n WHERE componentid = $row->sectionid" 
					//. "\n OR componentid = $row->catid"
					;
			$db->setQuery($query);
			$arr = $db->loadObjectList();

			if (count($arr)) {
				foreach($arr as $item) {
					$m[$item['key']] = $item['value'];
				}
			}
		}

		// loads the link for Section name
		if ($params->get('section_link') && $row->sectionid) {
			$_Itemid = JApplicationHelper :: getItemid($row->sectionid, 0, 0);
			if ($_Itemid) {
				$_Itemid = '&amp;Itemid='. $_Itemid;
			} else {
				$_Itemid = '';
			}

			$link = sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$row->sectionid.$_Itemid);
			$row->section = '<a href="'.$link.'">'.$row->section.'</a>';
		}


		// loads the link for Category name
		if ($params->get('category_link') && $row->catid) {
			$_Itemid = JApplicationHelper :: getItemid($row->catid, 0, 0);
			if ($_Itemid) {
				$_Itemid = '&amp;Itemid='. $_Itemid;
			} else {
				$_Itemid = '';
			}
			
			$link = sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$row->sectionid.'&amp;id='.$row->catid.$_Itemid);
			$row->category = '<a href="'.$link.'">'.$row->category.'</a>';
		}

		// loads current template for the pop-up window
		$template = null;
		if ($pop) {
			$params->set('popup', 1);
			$query = "SELECT template" .
					"\n FROM #__templates_menu" .
					"\n WHERE client_id = 0" .
					"\n AND menuid = 0";
			$db->setQuery($query);
			$template = $db->loadResult();
		}

		// show/hides the intro text
		if ($params->get('introtext')) {
			$row->text = $row->introtext. ($params->get('intro_only') ? '' : chr(13).chr(13).$row->fulltext);
		} else {
			$row->text = $row->fulltext;
		}

		// deal with the {mospagebreak} plugins
		// only permitted in the full text area
		$page = JRequest::getVar( 'limitstart', 0, '', 'int' );

		// record the hit
		if (!$params->get('intro_only') && ($page == 0)) {
			$obj = & JModel :: getInstance( 'content', $db );
			$obj->hit($row->id);
		}

		JContentView :: show($row, $params, $access, $page, $option, $ItemidCount);
	}

	function editItem(& $access, $Itemid) {
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$db 			= & $mainframe->getDBO();
		$my				= & $mainframe->getUser();
		$breadcrumbs 	= & $mainframe->getPathWay();
		$nullDate		= $db->getNullDate();
		$uid 			= JRequest :: getVar('id', 			0, '', 'int');
		$sectionid 		= JRequest :: getVar('sectionid', 	0, '', 'int');
		$task 			= JRequest :: getVar('task');

		/*
		 * Get the content data object
		 */
		$row = & JModel :: getInstance( 'content', $db );
		$row->load($uid);

		// fail if checked out not by 'me'
		if ($row->isCheckedOut($my->id)) {
			JContentView :: userInputError(JText :: _('The module')." [ ".$row->title." ] ".JText :: _('DESCBEINGEDITTEDBY'));
		}

		if ($uid) {
			// existing record
			if (!($access->canEdit || ($access->canEditOwn && $row->created_by == $my->id))) {
				mosNotAuth();
				return;
			}
		} else {
			// new record
			if (!($access->canEdit || $access->canEditOwn)) {
				mosNotAuth();
				return;
			}
		}

		if ($uid) {
			$sectionid = $row->sectionid;
		}

		$lists = array ();

		// get the type name - which is a special category
		$query = "SELECT name FROM #__sections" 
				. "\n WHERE id = $sectionid"
				;
		$db->setQuery($query);
		$section = $db->loadResult();

		if ($uid == 0) {
			$row->catid = 0;
		}

		if ($uid) {
			$row->checkout($my->id);
			if (trim($row->publish_down) == '0000-00-00 00:00:00') {
				$row->publish_down = 'Never';
			}
			if (trim($row->images)) {
				$row->images = explode("\n", $row->images);
			} else {
				$row->images = array ();
			}
			$query = "SELECT name"
					. "\n FROM #__users" 
					. "\n WHERE id = $row->created_by"
					;
			$db->setQuery($query);
			$row->creator = $db->loadResult();

			$query = "SELECT name" 
					. "\n FROM #__users" 
					. "\n WHERE id = $row->modified_by"
					;
			$db->setQuery($query);
			$row->modifier = $db->loadResult();

			$query = "SELECT content_id"
					. "\n FROM #__content_frontpage" 
					. "\n WHERE content_id = $row->id"
					;
			$db->setQuery($query);
			$row->frontpage = $db->loadResult();
			
			$title = JText::_( 'Edit' );
		} else {
			$row->sectionid 	= $sectionid;
			$row->version 		= 0;
			$row->state 		= 0;
			$row->ordering 		= 0;
			$row->images 		= array ();
			$row->publish_up 	= date('Y-m-d', time());
			$row->publish_down 	= 'Never';
			$row->creator 		= 0;
			$row->modifier 		= 0;
			$row->frontpage 	= 0;
			
			$title 				= JText::_( 'New' );
		}

		// calls function to read image from directory
		$pathA 		= 'images/stories';
		$pathL 		= 'images/stories';
		$images 	= array ();
		$folders 	= array ();
		$folders[] 	= mosHTML :: makeOption('/');
		mosAdminMenus :: ReadImages($pathA, '/', $folders, $images);
		// list of folders in images/stories/
		$lists['folders'] = mosAdminMenus :: GetImageFolders($folders, $pathL);
		// list of images in specfic folder in images/stories/
		$lists['imagefiles'] = mosAdminMenus :: GetImages($images, $pathL);
		// list of saved images
		$lists['imagelist'] = mosAdminMenus :: GetSavedImages($row, $pathL);

		// make the select list for the states
		$states[] = mosHTML :: makeOption(0, _CMN_UNPUBLISHED);
		$states[] = mosHTML :: makeOption(1, _CMN_PUBLISHED);
		$lists['state'] = mosHTML :: selectList($states, 'state', 'class="inputbox" size="1"', 'value', 'text', intval($row->state));

		// build the html select list for ordering
		$query = "SELECT ordering AS value, title AS text" 
				. "\n FROM #__content" 
				. "\n WHERE catid = $row->catid" 
				. "\n ORDER BY ordering"
				;
		$lists['ordering'] = mosAdminMenus :: SpecificOrdering($row, $uid, $query, 1);

		// build list of categories
		$lists['catid'] = mosAdminMenus :: ComponentCategory('catid', $sectionid, intval($row->catid));
		// build the select list for the image positions
		$lists['_align'] = mosAdminMenus :: Positions('_align');
		// build the html select list for the group access
		$lists['access'] = mosAdminMenus :: Access($row);

		// build the select list for the image caption alignment
		$lists['_caption_align'] = mosAdminMenus :: Positions('_caption_align');
		// build the html select list for the group access
		// build the select list for the image caption position
		$pos[] = mosHTML :: makeOption('bottom', JText :: _('Bottom'));
		$pos[] = mosHTML :: makeOption('top', JText :: _('Top'));
		$lists['_caption_position'] = mosHTML :: selectList($pos, '_caption_position', 'class="inputbox" size="1"', 'value', 'text');

		$title = $title .' '. JText::_('Content');
		
		// Set page title
		$mainframe->setPageTitle( $title );
		
		// Add pathway item
		$breadcrumbs->addItem( $title, '');
		
		JContentView :: editContent($row, $section, $lists, $images, $access, $my->id, $sectionid, $task, $Itemid);
	}

	/**
	* Saves the content item an edit form submit
	*/
	function saveContent(& $access)
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize variables
		 */
		$db 		= & $mainframe->getDBO();
		$my 		= & $mainframe->getUser();
		$nullDate 	= $db->getNullDate();
		$task 		= JRequest::getVar( 'task' );

		$row = & JModel :: getInstance( 'content', $db );
		if (!$row->bind($_POST)) {
			JContentView :: userInputError($row->getError());
			exit ();
		}

		$isNew = ($row->id < 1);
		if ($isNew) {
			// new record
			if (!($access->canEdit || $access->canEditOwn)) {
				mosNotAuth();
				return;
			}
			$row->created = date('Y-m-d H:i:s');
			$row->created_by = $my->id;
		} else {
			// existing record
			if (!($access->canEdit || ($access->canEditOwn && $row->created_by == $my->id))) {
				mosNotAuth();
				return;
			}
			$row->modified = date('Y-m-d H:i:s');
			$row->modified_by = $my->id;
		}
		if (trim($row->publish_down) == 'Never') {
			$row->publish_down = $nullDate;
		}

		// code cleaner for xhtml transitional compliance
		$row->introtext = str_replace('<br>', '<br />', $row->introtext);
		$row->fulltext = str_replace('<br>', '<br />', $row->fulltext);

		// remove <br /> take being automatically added to empty fulltext
		$length = strlen($row->fulltext) < 9;
		$search = strstr($row->fulltext, '<br />');
		if ($length && $search) {
			$row->fulltext = NULL;
		}

		$row->title = ampReplace($row->title);

		// Publishing state hardening for Authors
		if ( !$access->canPublish ) {
			if ( $isNew ) {
				// For new items - author is not allowed to publish - prevent them from doing so
				$row->state = 0;
			} else {
				// For existing items keep existing state - author is not allowed to change status
				$query = "SELECT state"
						. "\n FROM #__content"
						. "\n WHERE id = $row->id"
						;
				$db->setQuery($query);
				$state = $db->loadResult();

				if ( $state ) {
					$row->state = 1;
				} else {
					$row->state = 0;
				}
			}
		}

		if (!$row->check()) {
			JContentView :: userInputError($row->getError());
			exit ();
		}
		$row->version++;
		if (!$row->store()) {
			JContentView :: userInputError($row->getError());
			exit ();
		}

		// manage frontpage items
		require_once (JApplicationHelper :: getPath('class', 'com_frontpage'));
		$fp = new JFrontPageModel($db);

		if (JRequest :: getVar( 'frontpage', false, '', 'boolean' )) {

			// toggles go to first place
			if (!$fp->load($row->id)) {
				// new entry
				$query = "INSERT INTO #__content_frontpage" .
						"\n VALUES ( $row->id, 1 )";
				$db->setQuery($query);
				if (!$db->query()) {
					JContentView :: userInputError($db->stderror());
					exit ();
				}
				$fp->ordering = 1;
			}
		} else {
			// no frontpage mask
			if (!$fp->delete($row->id))
			{
				$msg .= $fp->stderr();
			}
			$fp->ordering = 0;
		}
		$fp->updateOrder();

		$row->checkin();
		$row->updateOrder("catid = $row->catid");

		// gets section name of item
		$query = "SELECT s.title" .
				"\n FROM #__sections AS s" .
				"\n WHERE s.scope = 'content'" .
				"\n AND s.id = $row->sectionid";
		$db->setQuery($query);
		// gets category name of item
		$section = $db->loadResult();

		$query = "SELECT c.title" .
				"\n FROM #__categories AS c" .
				"\n WHERE c.id = $row->catid";
		$db->setQuery($query);
		$category = $db->loadResult();

		if ($isNew) {
			// messaging for new items
			require_once (JApplicationHelper :: getPath('class', 'com_messages'));
			$query = "SELECT id" .
					"\n FROM #__users" .
					"\n WHERE sendEmail = 1";
			$db->setQuery($query);
			$users = $db->loadResultArray();
			foreach ($users as $user_id) {
				$msg = new mosMessage($db);
				$msg->send($my->id, $user_id, "New Item", sprintf(JText :: _('ON_NEW_CONTENT'), $my->username, $row->title, $section, $category));
			}
		}

		$msg = $isNew ? JText :: _('THANK_SUB') : JText :: _('Item succesfully saved.');
		switch ($task) {
			case 'apply' :
				$link = $_SERVER['HTTP_REFERER'];
				break;

			case 'apply_new' :
				$Itemid = JRequest::getVar( 'Returnid', $Itemid, 'post' );
				$link = 'index.php?option=com_content&task=edit&id='.$row->id.'&Itemid='.$Itemid;
				break;

			case 'save' :
			default :
				$Itemid = JRequest::getVar( 'Returnid', '', 'post' );
				if ($Itemid) {
					$link = 'index.php?option=com_content&task=view&id='.$row->id.'&Itemid='.$Itemid;
				} else {
					$link = JRequest::getVar( 'referer', '', 'post' );
				}
				break;
		}
		josRedirect($link, $msg);
	}

	/**
	* Cancels an edit content item operation
	*
	* @static
	* @param database A database connector object
	* @since 1.0
	*/
	function cancelContent(& $access)
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$db 		= & $mainframe->getDBO();
		$my 		= & $mainframe->getUser();
		$task 		= JRequest::getVar( 'task' );
		$Itemid 	= JRequest::getVar( 'Returnid', '0', 'post' );
		$referer 	= JRequest::getVar( 'referer', '', 'post' );
		$query 		= null;

		$row = & JModel :: getInstance( 'content', $db );
		$row->bind($_POST);

		if ($access->canEdit || ($access->canEditOwn && $row->created_by == $my->id))
		{
			$row->checkin();
		}

		/*
		 * If the task was edit or cancel, we go back to the content item
		 */
		if ($task == 'edit' || $task == 'cancel')
		{
			$referer = 'index.php?option=com_content&task=view&id='.$row->id.'&Itemid='.$Itemid;
		}
		
		echo $task;

		/*
		 * If the task was not new, we go back to the referrer
		 */
		if ($referer && !isset($row->id))
		{
			josRedirect($referer);
		} else
		{
			josRedirect('index.php');
		}
	}

	/**
	 * Shows the send email form for a content item
	 *
	 * @static
	 * @param int $uid The content item id to show form for
	 * @since 1.0
	 */
	function emailContentForm()
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$db = & $mainframe->getDBO();
		$my = & $mainframe->getUser();
		$uid 		= JRequest :: getVar('id', 0, '', 'int');

		$row = & JModel :: getInstance( 'content', $db );
		$row->load($uid);

		if ($row->id === null || $row->access > $my->gid)
		{
			mosNotAuth();
			return;
		} else
		{
			$query = "SELECT template" .
					"\n FROM #__templates_menu" .
					"\n WHERE client_id = 0" .
					"\n AND menuid = 0";
			$db->setQuery($query);
			$template = $db->loadResult();
			JContentView :: emailForm($row->id, $row->title, $template);
		}

	}

	/**
	 * Builds and sends an email to a content item
	 *
	 * @static
	 * @param int $uid The content item id to send
	 * @since 1.0
	 */
	function emailContentSend()
	{
		global $mainframe;

		$db			= & $mainframe->getDBO();
		$SiteName 	= $mainframe->getCfg('sitename');
		$MailFrom 	= $mainframe->getCfg('mailfrom');
		$FromName 	= $mainframe->getCfg('fromname');
		$uid 		= JRequest :: getVar('id', 0, '', 'int');
		$validate 	= JRequest::getVar( mosHash('validate'), 0, 'post' );

		if (!$validate)
		{
			header("HTTP/1.0 403 Forbidden");
			die(_NOT_AUTH);
			exit;
		}

		/*
		 * This obviously won't catch all attempts, but it does not hurt to make
		 * sure the request came from a client with a user agent string.
		 */
		if (!isset ($_SERVER['HTTP_USER_AGENT']))
		{
			header("HTTP/1.0 403 Forbidden");
			die(_NOT_AUTH);
			exit;
		}

		/*
		 * This obviously won't catch all attempts either, but we ought to check
		 * to make sure that the request was posted as well.
		 */
		if (!$_SERVER['REQUEST_METHOD'] == 'POST')
		{
			header("HTTP/1.0 403 Forbidden");
			die(_NOT_AUTH);
			exit;
		}

		// An array of e-mail headers we do not want to allow as input
		$headers = array ('Content-Type:',
						  'MIME-Version:',
						  'Content-Transfer-Encoding:',
						  'bcc:',
						  'cc:');

		// An array of the input fields to scan for injected headers
		$fields = array ('email',
						 'yourname',
						 'youremail',
						 'subject',
						 );

		/*
		 * Here is the meat and potatoes of the header injection test.  We
		 * iterate over the array of form input and check for header strings.
		 * If we fine one, send an unauthorized header and die.
		 */
		foreach ($fields as $field)
		{
			foreach ($headers as $header)
			{
				if (strpos($_POST[$field], $header) !== false)
				{
					header("HTTP/1.0 403 Forbidden");
					die(_NOT_AUTH);
					exit;
				}
			}
		}

		/*
		 * Free up memory
		 */
		unset ($headers, $fields);

		$_Itemid 			= JApplicationHelper :: getItemid($uid, 0, 0);
		$email 				= JRequest::getVar( 'email', '', 'post' );
		$yourname 			= JRequest::getVar( 'yourname', '', 'post' );
		$youremail 			= JRequest::getVar( 'youremail', '', 'post' );
		$subject_default 	= sprintf(JText :: _('Item sent by'), $yourname);
		$subject 			= JRequest::getVar( 'subject', $subject_default, 'post' );

		if ($uid < 1 || !$email || !$youremail || (JMailHelper::isEmailAddress($email) == false) || (JMailHelper::isEmailAdress($youremail) == false))
		{
			JContentView :: userInputError(JText :: _('EMAIL_ERR_NOINFO'));
		}

		$query = "SELECT template" .
				"\n FROM #__templates_menu" .
				"\n WHERE client_id = 0" .
				"\n AND menuid = 0";
		$db->setQuery($query);
		$template = $db->loadResult();

		/*
		 * Build the link to send in the email
		 */
		$link = sefRelToAbs('index.php?option=com_content&task=view&id='.$uid.'&Itemid='.$_Itemid);

		/*
		 * Build the message to send
		 */
		$msg = sprintf(JText :: _('EMAIL_MSG'), $SiteName, $yourname, $youremail, $link);

		/*
		 * Send the email
		 */
		josMail($youremail, $yourname, $email, $subject, $msg);

		JContentView :: emailSent($email, $template);
	}

	function recordVote()
	{
		global $mainframe;

		$db				= & $mainframe->getDBO();
		$url 			= JRequest::getVar( 'url', '' );
		$user_rating 	= JRequest::getVar( 'user_rating', 0, '', 'int' );
		$cid 			= JRequest::getVar( 'cid', 0, '', 'int' );

		if (($user_rating >= 1) and ($user_rating <= 5))
		{
			$currip = getenv('REMOTE_ADDR');

			$query = "SELECT *" .
					"\n FROM #__content_rating" .
					"\n WHERE content_id = $cid";
			$db->setQuery($query);
			$votesdb = NULL;
			if (!($db->loadObject($votesdb)))
			{
				$query = "INSERT INTO #__content_rating ( content_id, lastip, rating_sum, rating_count )" .
						"\n VALUES ( $cid, '$currip', $user_rating, 1 )";
				$db->setQuery($query);
				$db->query() or die($db->stderr());
			} else
			{
				if ($currip != ($votesdb->lastip))
				{
					$query = "UPDATE #__content_rating" .
							"\n SET rating_count = rating_count + 1, rating_sum = rating_sum + $user_rating, lastip = '$currip'" .
							"\n WHERE content_id = $cid";
					$db->setQuery($query);
					$db->query() or die($db->stderr());
				} else
				{
					josRedirect($url, JText :: _('You already voted for this poll today!'));
				}
			}
			josRedirect($url, JText :: _('Thanks for your vote!'));
		}
	}

	function _orderby_pri($orderby)
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

	function _orderby_sec($orderby)
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
	function _where($type = 1, & $access, & $noauth, $gid, $id, $now = NULL, $year = NULL, $month = NULL)
	{
		global $database;

		$nullDate = $database->getNullDate();
		$where = array ();

		// normal
		if ($type > 0)
		{
			$where[] = "a.state = '1'";
			if (!$access->canEdit)
			{
				$where[] = "( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )";
				$where[] = "( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )";
			}
			if ($noauth)
			{
				$where[] = "a.access <= $gid";
			}
			if ($id > 0)
			{
				if ($type == 1)
				{
					$where[] = "a.sectionid IN ( $id ) ";
				} else
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
			if ($noauth)
			{
				$where[] = "a.access <= $gid";
			}
			if ($id > 0)
			{
				if ($type == -1)
				{
					$where[] = "a.sectionid = $id";
				} else
					if ($type == -2)
					{
						$where[] = "a.catid = $id";
					}
			}
		}

		return $where;
	}

	/**
	 * Searches for an item by a key parameter
	 *
	 * @static
	 * @param object Actions this user can perform
	 * @param string A timestamp
	 * @return void
	 * @since 1.0
	 */
	function _findKeyItem($access, $now)
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$db 	= & $mainframe->getDBO();
		$my 	= & $mainframe->getUser();
		$pop 	= JRequest::getVar( 'pop', 0, '', 'int' );
		$option = JRequest::getVar( 'option' );
		$keyref = $db->getEscaped(JRequest::getVar( 'keyref' ));

		$query = "SELECT id" .
				"\n FROM #__content" .
				"\n WHERE attribs LIKE '%keyref=$keyref%'";
		$db->setQuery($query);
		$id = $db->loadResult();
		if ($id > 0)
		{
			showItem($id, $my->gid, $access, $pop, $option, $now);
		} else
		{
			echo JText :: _('Key not found');
		}
	}
}
?>