<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( $mainframe->getPath( 'front_html', 'com_content' ) );

$id			= intval( mosGetParam( $_REQUEST, 'id', 0 ) );
$sectionid 	= intval( mosGetParam( $_REQUEST, 'sectionid', 0 ) );
$pop 		= intval( mosGetParam( $_REQUEST, 'pop', 0 ) );
$limit 		= intval( mosGetParam( $_REQUEST, 'limit', '' ) );
$order 		= mosGetParam( $_REQUEST, 'order', '' );
$limitstart = intval( mosGetParam( $_REQUEST, 'limitstart', 0 ) );

$now = date( 'Y-m-d H:i', time() + $mosConfig_offset * 60 * 60 );

// Editor usertype check
$access = new stdClass();
$access->canEdit 	= $acl->acl_check( 'action', 'edit', 'users', $my->usertype, 'content', 'all' );
$access->canEditOwn = $acl->acl_check( 'action', 'edit', 'users', $my->usertype, 'content', 'own' );
$access->canPublish = $acl->acl_check( 'action', 'publish', 'users', $my->usertype, 'content', 'all' );

// cache activation
$cache =& JFactory::getCache( 'com_content' );

// loads function for frontpage component
if ( $option == 'com_frontpage' ) {
	frontpage( $gid, $access, $pop, $now );
	//$cache->call( 'frontpage', $gid, $access, $pop, $now );
	return;
}

switch ( strtolower( $task ) ) {
	case 'findkey':
		findKeyItem( $gid, $access, $pop, $option, $now );
		break;

	case 'view':
		showItem( $id, $gid, $access, $pop, $option, $now );
		break;

	case 'section':
		$cache->call( 'showSection', $id, $gid, $access, $now );
		break;

	case 'category':
		$cache->call( 'showCategory', $id, $gid, $access, $sectionid, $limit, $order, $limitstart, $now );
		break;

	case 'blogsection':
		$cache->call('showBlogSection', $id, $gid, $access, $pop, $now );
		break;

	case 'blogcategorymulti':
	case 'blogcategory':
		$cache->call( 'showBlogCategory', $id, $gid, $access, $pop, $now );
		break;

	case 'archivesection':
		showArchiveSection( $id, $gid, $access, $pop, $option );
		break;

	case 'archivecategory':
		showArchiveCategory( $id, $gid, $access, $pop, $option, $now );
		break;

	case 'edit':
		editItem( $id, $gid, $access, 0, $task, $Itemid );
		break;

	case 'new':
		editItem( 0, $gid, $access, $sectionid, $task, $Itemid );
		break;

	case 'save':
	case 'apply':
	case 'apply_new':
		$cache = JFactory::getCache();
		$cache->cleanCache( 'com_content' );
		saveContent( $access, $task );
		break;

	case 'cancel':
		cancelContent( $access );
		break;

	case 'emailform':
		emailContentForm( $id );
		break;

	case 'emailsend':
		emailContentSend( $id );
		break;

	case 'vote':
		recordVote ();
		break;

	default:
		//$cache->call('showBlogSection', 0, $gid, $access, $pop, $now );
		header("HTTP/1.0 404 Not Found");
		echo JText::_( 'NOT_EXIST' );
		break;
}

/**
 * Searches for an item by a key parameter
 * @param int The user access level
 * @param object Actions this user can perform
 * @param int
 * @param string The url option
 * @param string A timestamp
 */
function findKeyItem( $gid, $access, $pop, $option, $now ) {
	global $database;

	$keyref = mosGetParam( $_REQUEST, 'keyref', '' );
	$keyref = $database->getEscaped( $keyref );

	$query = "SELECT id"
	. "\n FROM #__content"
	. "\n WHERE attribs LIKE '%keyref=$keyref%'"
	;
	$database->setQuery( $query );
	$id = $database->loadResult();
	if ($id > 0) {
		showItem( $id, $gid, $access, $pop, $option, $now );
	} else {
		echo JText::_( 'Key not found' );
	}
}

function frontpage( $gid, &$access, $pop, $now ) {
	global $database, $mainframe, $my, $Itemid;
	global $mosConfig_offset;

	$nullDate = $database->getNullDate();
	$noauth = !$mainframe->getCfg( 'shownoauth' );

	// Parameters
	$menu = new mosMenu( $database );
	$menu->load( $Itemid );
	$params = new mosParameters( $menu->params );
	$orderby_sec = $params->def( 'orderby_sec', '' );
	$orderby_pri = $params->def( 'orderby_pri', '' );

	// Ordering control
	$order_sec = _orderby_sec( $orderby_sec );
	$order_pri = _orderby_pri( $orderby_pri );

	// query records
	//$query = "SELECT a.*, ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups"
	$query = "SELECT a.id, a.title, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by,"
	. "\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access,"
	. "\n CHAR_LENGTH( a.fulltext ) AS readmore,"
	. "\n ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups"
	. "\n FROM #__content AS a"
	. "\n INNER JOIN #__content_frontpage AS f ON f.content_id = a.id"
	. "\n INNER JOIN #__categories AS cc ON cc.id = a.catid"
	. "\n INNER JOIN #__sections AS s ON s.id = a.sectionid"
	. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
	. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
	. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
	. "\n WHERE a.state = 1"
	. ( $noauth ? "\n AND a.access <= $my->gid" : '' )
	. "\n AND ( publish_up = '$nullDate' OR publish_up <= '$now'  )"
	. "\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )"
	. "\n AND s.published = 1"
	. "\n AND cc.published = 1"
	. "\n ORDER BY $order_pri $order_sec"
	;
	$database->setQuery( $query );
	$rows = $database->loadObjectList();

	// Dynamic Page Title
	$mainframe->SetPageTitle( $menu->name );

	BlogOutput( $rows, $params, $gid, $access, $pop, $menu );
}


function showSection( $id, $gid, &$access, $now ) {
	global $database, $mainframe, $Itemid;

	$nullDate = $database->getNullDate();
	$noauth = !$mainframe->getCfg( 'shownoauth' );

	// Paramters
	$params = new stdClass();
	if ( $Itemid ) {
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );
	} else {
		$menu 	= '';
		$params = new mosEmpty();

	}
	$orderby = $params->get( 'orderby', '' );

	$params->set( 'type', 				'section' );

	$params->def( 'page_title', 		1 );
	$params->def( 'pageclass_sfx', 		'' );
	$params->def( 'other_cat_section', 	1 );
	$params->def( 'empty_cat_section', 	0 );
	$params->def( 'other_cat', 			1 );
	$params->def( 'empty_cat', 			0 );
	$params->def( 'cat_items', 			1 );
	$params->def( 'cat_description', 	1 );
	$params->def( 'back_button', 		$mainframe->getCfg( 'back_button' ) );
	$params->def( 'pageclass_sfx', 		'' );

	// Ordering control
	$orderby = _orderby_sec( $orderby );

	$section = new mosSection( $database );
	$section->load( $id );

	if ( $access->canEdit ) {
		$xwhere = '';
		$xwhere2 = "\n AND b.state >= 0";
	} else {
		$xwhere = "\n AND a.published = 1";
		$xwhere2 = "\n AND b.state = 1"
		. "\n AND ( b.publish_up = '$nullDate' OR b.publish_up <= '$now' )"
		. "\n AND ( b.publish_down = '$nullDate' OR b.publish_down >= '$now' )"
		;
	}

	$empty 		= '';
	$empty_sec 	= '';
	if ( $params->get( 'type' ) == 'category' ) {
		// show/hide empty categories
		if ( !$params->get( 'empty_cat' ) ) {
			$empty = "\n HAVING numitems > 0";
		}
	}
	if ( $params->get( 'type' ) == 'section' ) {
		// show/hide empty categories in section
		if ( !$params->get( 'empty_cat_section' ) ) {
			$empty_sec = "\n HAVING numitems > 0";
		}
	}

	$access = '';
	if ($noauth) {
		$access = "\n AND a.access <= $gid";
	}

	// Main Query
	$query = "SELECT a.*, COUNT( b.id ) AS numitems"
	. "\n FROM #__categories AS a"
	. "\n LEFT JOIN #__content AS b ON b.catid = a.id"
	. $xwhere2
	. "\n WHERE a.section = '$section->id'"
	. $xwhere
	. $access
	. "\n GROUP BY a.id"
	. $empty
	. $empty_sec
	. "\n ORDER BY $orderby"
	;
	$database->setQuery( $query );
	$categories = $database->loadObjectList();

	// Dynamic Page Title
	$mainframe->SetPageTitle( $menu->name );

	/*
	 * Handle BreadCrumbs
	 */
	// Section
	$breadcrumbs =& $mainframe->getBreadCrumbs();
	$breadcrumbs->addItem( $section->title, '');

	$null = null;
	HTML_content::showContentList( $section, $null, $access, $id, $null,  $gid, $params, $null, $categories, $null, $null );
}


/**
* @param int The category id
* @param int The group id of the user
* @param int The access level of the user
* @param int The section id
* @param int The number of items to dislpay
* @param int The offset for pagination
*/
function showCategory( $id, $gid, &$access, $sectionid, $limit, $selected, $limitstart, $now  ) {
	global $database, $mainframe, $Itemid, $mosConfig_list_limit;

	$nullDate = $database->getNullDate();
	$noauth = !$mainframe->getCfg( 'shownoauth' );

	// Paramters
	$params = new stdClass();
	if ( $Itemid ) {
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );
	} else {
		$menu = '';
		$params = new mosParameters( '' );
	}

	if ( $selected ) {
		$orderby = $selected;
	} else {
		$orderby = $params->get( 'orderby', 'rdate' );
		$selected = $orderby;
	}

	$params->set( 'type', 				'category' );

	$params->def( 'page_title',      1 );
	$params->def( 'title',           1 );
	$params->def( 'hits',            $mainframe->getCfg( 'hits' ) );
	$params->def( 'author',          !$mainframe->getCfg( 'hideAuthor' ) );
	$params->def( 'date',            !$mainframe->getCfg( 'hideCreateDate' ) );
	$params->def( 'date_format',     JText::_( 'DATE_FORMAT_LC' ) );
	$params->def( 'navigation',      2 );
	$params->def( 'display',         1 );
	$params->def( 'display_num',     $mosConfig_list_limit );
	$params->def( 'other_cat',       1 );
	$params->def( 'empty_cat',       0 );
	$params->def( 'cat_items',       1 );
	$params->def( 'cat_description', 0 );
	$params->def( 'back_button',     $mainframe->getCfg( 'back_button' ) );
	$params->def( 'pageclass_sfx',   '' );
	$params->def( 'headings',        1 );
	$params->def( 'order_select',    1 );
	$params->def( 'filter',          1 );
	$params->def( 'filter_type',     'title' );

	// Ordering control
	$orderby = _orderby_sec( $orderby );

	$category = new mosCategory( $database );
	$category->load( $id );

	if ( $sectionid == 0 ) {
		$sectionid = $category->section;
	}

	if ( $access->canEdit ) {
		$xwhere = '';
		$xwhere2 = "\n AND b.state >= 0";
	} else {
		$xwhere = "\n AND c.published = 1";
		$xwhere2 = "\n AND b.state = 1"
		. "\n AND ( publish_up = '$nullDate' OR publish_up <= '$now' )"
		. "\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )"
		;
	}

	$pagetitle = '';
	if ( $Itemid ) {
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$pagetitle = $menu->name;
	} // if

	// show/hide empty categories
	$empty = '';
	if ( !$params->get( 'empty_cat' ) )
		$empty = "\n HAVING COUNT( b.id ) > 0";

	// get the list of other categories
	$query = "SELECT c.*, COUNT( b.id ) AS numitems"
	. "\n FROM #__categories AS c"
	. "\n LEFT JOIN #__content AS b ON b.catid = c.id "
	. $xwhere2
	. ( $noauth ? "\n AND b.access <= $gid" : '' )
	. "\n WHERE c.section = '$category->section'"
	. $xwhere
	. ( $noauth ? "\n AND c.access <= $gid" : '' )
	. "\n GROUP BY c.id"
	. $empty
	. "\n ORDER BY c.ordering"
	;
	$database->setQuery( $query );
	$other_categories = $database->loadObjectList();

	// get the total number of published items in the category
	// filter functionality
	$filter = mosGetParam( $_POST, 'filter', '' );
	$filter = strtolower( $filter );
	$and = '';
	if ( $filter ) {
		if ( $params->get( 'filter' ) ) {
			switch ( $params->get( 'filter_type' ) ) {
				case 'title':
					$and = "\n AND LOWER( a.title ) LIKE '%$filter%'";
					break;

				case 'author':
					$and = "\n AND ( ( LOWER( u.name ) LIKE '%$filter%' ) OR ( LOWER( a.created_by_alias ) LIKE '%$filter%' ) )";
					break;

				case 'hits':
					$and = "\n AND a.hits LIKE '%$filter%'";
					break;
			}
		}

	}

	if ( $access->canEdit ) {
		$xwhere = "\n AND a.state >= 0";
	} else {
		$xwhere = "\n AND a.state = 1"
		. "\n AND ( publish_up = '$nullDate' OR publish_up <= '$now' )"
		. "\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )"
		;
	}

	$query = "SELECT COUNT(a.id) as numitems"
	. "\n FROM #__content AS a"
	. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
	. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
	. "\n WHERE a.catid = $category->id"
	. $xwhere
	. ( $noauth ? "\n AND a.access <= $gid" : '' )
	. "\n AND $category->access <= $gid"
	. $and
	. "\n ORDER BY $orderby"
	;
	$database->setQuery( $query );
	$counter = $database->loadObjectList();
	$total = $counter[0]->numitems;
	$limit = $limit ? $limit : $params->get( 'display_num' ) ;
	if ( $total <= $limit ) $limitstart = 0;

	require_once( JPATH_SITE . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit );

	// get the list of items for this category
	$query = "SELECT a.id, a.title, a.hits, a.created_by, a.created_by_alias, a.created AS created, a.access, u.name AS author, a.state, g.name AS groups"
	. "\n FROM #__content AS a"
	. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
	. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
	. "\n WHERE a.catid = $category->id"
	. $xwhere
	. ( $noauth ? "\n AND a.access <= $gid" : '' )
	. "\n AND $category->access <= $gid"
	. $and
	. "\n ORDER BY $orderby"
	. "\n LIMIT $limitstart, $limit"
	;
	$database->setQuery( $query );
	$items = $database->loadObjectList();

	$check = 0;
	if ( $params->get( 'date' ) ) {
		$order[] = mosHTML::makeOption( 'date', JText::_( 'Date asc' ) );
		$order[] = mosHTML::makeOption( 'rdate', JText::_( 'Date desc' ) );
		$check .= 1;
	}
	if ( $params->get( 'title' ) ) {
		$order[] = mosHTML::makeOption( 'alpha', JText::_( 'Title asc' ) );
		$order[] = mosHTML::makeOption( 'ralpha', JText::_( 'Title desc' )  );
		$check .= 1;
	}
	if ( $params->get( 'hits' ) ) {
		$order[] = mosHTML::makeOption( 'hits', JText::_( 'Hits asc' ) );
		$order[] = mosHTML::makeOption( 'rhits', JText::_( 'Hits desc' ) );
		$check .= 1;
	}
	if ( $params->get( 'author' ) ) {
		$order[] = mosHTML::makeOption( 'author', JText::_( 'Author asc' ) );
		$order[] = mosHTML::makeOption( 'rauthor', JText::_( 'Author desc' ) );
		$check .= 1;
	}
	$order[] = mosHTML::makeOption( 'order', JText::_( 'Ordering' ) );
	$lists['order'] = mosHTML::selectList( $order, 'order', 'class="inputbox" size="1"  onchange="document.adminForm.submit();"', 'value', 'text', $selected );
	if ( $check < 1 ) {
		$lists['order'] = '';
		$params->set( 'order_select', 0 );
	}

	$lists['task'] = 'category';
	$lists['filter'] = $filter;

	// Dynamic Page Title
	$mainframe->SetPageTitle( $pagetitle );

	/*
	 * Handle BreadCrumbs
	 */
	// Section
	$section = new mosSection($database);
	$section->load($category->section);

	$breadcrumbs =& $mainframe->getBreadCrumbs();
	$breadcrumbs->addItem( $section->title, sefRelToAbs( 'index.php?option=com_content&amp;task=section&amp;id='. $category->section .'&amp;Itemid='.$menu->id ));
	// Category
	$breadcrumbs->addItem( $category->title, '');

	HTML_content::showContentList( $category, $items, $access, $id, $sectionid, $gid, $params, $pageNav, $other_categories, $lists, $selected );
} // showCategory


function showBlogSection( $id=0, $gid, &$access, $pop, $now=NULL ) {
	global $database, $mainframe, $Itemid;

	$noauth = !$mainframe->getCfg( 'shownoauth' );

	// Parameters
	$params = new stdClass();
	if ( $Itemid ) {
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );
	} else {
		$menu = "";
		$params = new mosParameters( '' );
	}

	// new blog multiple section handling
	if ( !$id ) {
		$id		= $params->def( 'sectionid', 0 );
	}

	$where 		= _where( 1, $access, $noauth, $gid, $id, $now );

	// Ordering control
	$orderby_sec 	= $params->def( 'orderby_sec', 'rdate' );
	$orderby_pri 	= $params->def( 'orderby_pri', '' );
	$order_sec 		= _orderby_sec( $orderby_sec );
	$order_pri 		= _orderby_pri( $orderby_pri );

	// Main data query
	//$query = "SELECT a.*, ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, cc.name AS category, g.name AS groups"
	$query = "SELECT a.id, a.title, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by,"
	. "\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access,"
	. "\n CHAR_LENGTH( a.fulltext ) AS readmore,"
	. "\n ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups"
	. "\n FROM #__content AS a"
	. "\n INNER JOIN #__categories AS cc ON cc.id = a.catid"
	. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
	. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
	. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id"
	. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
	. ( count( $where ) ? "\n WHERE ".implode( "\n AND ", $where ) : '' )
	. "\n AND s.access <= $gid"
	. "\n AND s.published = 1"
	. "\n AND cc.published = 1"
	. "\n ORDER BY $order_pri $order_sec"
	;
	$database->setQuery( $query );
	$rows = $database->loadObjectList();

	// Dynamic Page Title
	if ($menu) {
		$mainframe->setPageTitle( $menu->name );
	}

	// Append Blog to BreadCrumbs
	$breadcrumbs =& $mainframe->getBreadCrumbs();

	if ($id == 0) {
		$breadcrumbs->addItem( 'Blog' , '');
	} else {
		$breadcrumbs->addItem( $rows[0]->section, '');
	}

	BlogOutput( $rows, $params, $gid, $access, $pop, $menu );
}

function showBlogCategory( $id=0, $gid, &$access, $pop, $now ) {
	global $database, $mainframe, $Itemid;

	$noauth = !$mainframe->getCfg( 'shownoauth' );

	// Paramters
	$params = new stdClass();
	if ( $Itemid ) {
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );
	} else {
		$menu = '';
		$params = new mosParameters( '' );
	}

	// new blog multiple section handling
	if ( !$id ) {
		$id 		= $params->def( 'categoryid', 0 );
	}

	$where		= _where( 2, $access, $noauth, $gid, $id, $now );

	// Ordering control
	$orderby_sec 	= $params->def( 'orderby_sec', 'rdate' );
	$orderby_pri 	= $params->def( 'orderby_pri', '' );
	$order_sec 		= _orderby_sec( $orderby_sec );
	$order_pri 		= _orderby_pri( $orderby_pri );

	// Main data query
	//$query = "SELECT a.*, ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, g.name AS groups, cc.name AS category"
	$query = "SELECT a.id, a.title, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by,"
	. "\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access,"
	. "\n CHAR_LENGTH( a.fulltext ) AS readmore,"
	. "\n ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups"
	. "\n FROM #__content AS a"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid"
	. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
	. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
	. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id"
	. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
	. ( count( $where ) ? "\n WHERE ".implode( "\n AND ", $where ) : '' )
	. "\n AND s.access <= $gid"
	. "\n AND s.published = 1"
	. "\n AND cc.published = 1"
	. "\n ORDER BY $order_pri $order_sec"
	;
	$database->setQuery( $query );
	$rows = $database->loadObjectList();

	// Dynamic Page Title
	$mainframe->SetPageTitle( $menu->name );

	// Append Blog to BreadCrumbs
	$breadcrumbs =& $mainframe->getBreadCrumbs();

	if ($id == 0) {
		$breadcrumbs->addItem( 'Blog' , '');
	} else {
		$breadcrumbs->addItem( $rows[0]->section, '');
	}

	BlogOutput( $rows, $params, $gid, $access, $pop, $menu );
}

function showArchiveSection( $id=NULL, $gid, &$access, $pop, $option ) {
	global $database, $mainframe;
	global $Itemid;

	$noauth = !$mainframe->getCfg( 'shownoauth' );

	// Paramters
	$year 	= mosGetParam( $_REQUEST, 'year', date( 'Y' ) );
	$month 	= mosGetParam( $_REQUEST, 'month', date( 'm' ) );

	$params = new stdClass();
	if ( $Itemid ) {
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );
	} else {
		$menu = "";
		$params = new mosParameters( '' );
	}

	$params->set( 'intro_only', 1 );
	$params->set( 'year', $year );
	$params->set( 'month', $month );

	// Ordering control
	$orderby_sec = $params->def( 'orderby_sec', 'rdate' );
	$orderby_pri = $params->def( 'orderby_pri', '' );
	$order_sec = _orderby_sec( $orderby_sec );
	$order_pri = _orderby_pri( $orderby_pri );

	// used in query
	$where = _where( -1, $access, $noauth, $gid, $id, NULL, $year, $month );

	// checks to see if 'All Sections' options used
	if ( $id == 0 ) {
		$check = '';
	} else {
		$check = "\n AND a.sectionid = $id";
	}
	// query to determine if there are any archived entries for the section
	$query = 	"SELECT a.id"
	. "\n FROM #__content as a"
	. "\n WHERE a.state = -1"
	. $check
	;
	$database->setQuery( $query );
	$items = $database->loadObjectList();
	$archives = count( $items );

	// Main Query
	//$query = "SELECT a.*, ROUND(v.rating_sum/v.rating_count) AS rating, v.rating_count, u.name AS author, u.usertype, cc.name AS category, g.name AS groups"
	$query = "SELECT a.id, a.title, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by,"
	. "\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access,"
	. "\n CHAR_LENGTH( a.fulltext ) AS readmore,"
	. "\n ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups"
	. "\n FROM #__content AS a"
	. "\n INNER JOIN #__categories AS cc ON cc.id = a.catid"
	. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
	. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
	. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id"
	. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
	. ( count( $where ) ? "\n WHERE ". implode( "\n AND ", $where ) : '')
	. "\n AND s.access <= $gid"
	. "\n AND s.published = 1"
	. "\n AND cc.published = 1"
	. "\n ORDER BY $order_pri $order_sec"
	;
	$database->setQuery( $query );
	$rows = $database->loadObjectList();

	// initiate form
	$link = 'index.php?option=com_content&task=archivesection&id='. $id .'&Itemid='. $Itemid;
 	echo '<form action="'.sefRelToAbs( $link ).'" method="post">';

	// Dynamic Page Title
	$mainframe->SetPageTitle( $menu->name );

	// Append Archives to BreadCrumbs
	$breadcrumbs =& $mainframe->getBreadCrumbs();
	$breadcrumbs->addItem( 'Archives', '');

	if ( !$archives ) {
		// if no archives for category, hides search and outputs empty message
		echo '<br /><div align="center">'. JText::_( 'CATEGORY_ARCHIVE_EMPTY' ) .'</div>';
	} else {
		BlogOutput( $rows, $params, $gid, $access, $pop, $menu, 1 );
	}

 	echo '<input type="hidden" name="id" value="'. $id .'" />';
	echo '<input type="hidden" name="Itemid" value="'. $Itemid .'" />';
 	echo '<input type="hidden" name="task" value="archivesection" />';
 	echo '<input type="hidden" name="option" value="com_content" />';
 	echo '</form>';
}


function showArchiveCategory( $id=0, $gid, &$access, $pop, $option, $now ) {
	global $database, $mainframe;
	global $Itemid;

	// Parameters
	$noauth = !$mainframe->getCfg( 'shownoauth' );
	$year 	= mosGetParam( $_REQUEST, 'year', 	date( 'Y' ) );
	$month 	= mosGetParam( $_REQUEST, 'month', 	date( 'm' ) );
	$module = mosGetParam( $_REQUEST, 'module', '' );

	// used by archive module
	if ( $module ) {
		$check = '';
	} else {
		$check = "\n AND a.catid = $id";
	}

	if ( $Itemid ) {
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );
	} else {
		$menu = '';
		$params = new mosParameters( '' );
	}

	$params->set( 'year', $year );
	$params->set( 'month', $month );

	// Ordering control
	$orderby_sec = $params->def( 'orderby', 'rdate' );
	$order_sec = _orderby_sec( $orderby_sec );

	// used in query
	$where = _where( -2, $access, $noauth, $gid, $id, NULL, $year, $month );

	// query to determine if there are any archived entries for the category
	$query = 	"SELECT a.id"
	. "\n FROM #__content as a"
	. "\n WHERE a.state = -1"
	. $check
	;
	$database->setQuery( $query );
	$items = $database->loadObjectList();
	$archives = count( $items );

	//$query = "SELECT a.*, ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, g.name AS groups"
	$query = "SELECT a.id, a.title, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by,"
	. "\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access,"
	. "\n CHAR_LENGTH( a.fulltext ) AS readmore,"
	. "\n ROUND( v.rating_sum / v.rating_count ) AS rating, v.rating_count, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups"
	. "\n FROM #__content AS a"
	. "\n INNER JOIN #__categories AS cc ON cc.id = a.catid"
	. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
	. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
	. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id"
	. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
	. ( count( $where ) ? "\n WHERE ". implode( "\n AND ", $where ) : '' )
	. "\n AND s.access <= $gid"
	. "\n AND s.published = 1"
	. "\n AND cc.published = 1"
	. "\n ORDER BY $order_sec"
	;
	$database->setQuery( $query );
	$rows = $database->loadObjectList();

	// initiate form
	$link = 'index.php?option=com_content&task=archivecategory&id='. $id .'&Itemid='. $Itemid;
 	echo '<form action="'.sefRelToAbs( $link ).'" method="post">';

	// Page Title
	$mainframe->SetPageTitle( $menu->name );

	// Append Archives to BreadCrumbs
	$breadcrumbs =& $mainframe->getBreadCrumbs();
	$breadcrumbs->addItem( 'Archives', '');

	if ( !$archives ) {
		// if no archives for category, hides search and outputs empty message
		echo '<br /><div align="center">'. JText::_( 'CATEGORY_ARCHIVE_EMPTY' ) .'</div>';
	} else {
		// if coming from the Archive Module, the Archive Dropdown selector is not shown
		if ( $id ) {
			BlogOutput( $rows, $params, $gid, $access, $pop, $menu, 1 );
		} else {
			BlogOutput( $rows, $params, $gid, $access, $pop, $menu, 0 );
		}
	}

 	echo '<input type="hidden" name="id" value="'. $id .'" />';
	echo '<input type="hidden" name="Itemid" value="'. $Itemid .'" />';
 	echo '<input type="hidden" name="task" value="archivecategory" />';
 	echo '<input type="hidden" name="option" value="com_content" />';
 	echo '</form>';
}


function BlogOutput ( &$rows, &$params, $gid, &$access, $pop, &$menu, $archive=NULL ) {
	global $mainframe, $Itemid, $task, $id, $option, $database;

	// parameters
	if ( $params->get( 'page_title', 1 ) && $menu) {
		$header = $params->def( 'header', $menu->name );
	} else {
		$header = '';
	}
	$columns = $params->def( 'columns', 2 );
	if ( $columns == 0 ) {
		$columns = 1;
	}
	$intro				= $params->def( 'intro', 				4 );
	$leading 			= $params->def( 'leading', 				1 );
	$links				= $params->def( 'link', 				4 );
	$pagination 		= $params->def( 'pagination', 			2 );
	$pagination_results = $params->def( 'pagination_results', 	1 );
	$pagination_results = $params->def( 'pagination_results', 	1 );
	$descrip		 	= $params->def( 'description', 			1 );
	$descrip_image	 	= $params->def( 'description_image', 	1 );
	// needed for back button for page
	$back 				= $params->get( 'back_button', $mainframe->getCfg( 'back_button' ) );
	// needed to disable back button for item
	$params->set( 'back_button', 0 );
	$params->def( 'pageclass_sfx', '' );
	$params->set( 'intro_only', 1 );

	$total = count( $rows );

	// pagination support
	$limitstart = intval( mosGetParam( $_REQUEST, 'limitstart', 0 ) );
	$limit = $intro + $leading + $links;
	if ( $total <= $limit ) {
		$limitstart = 0;
	}
	$i = $limitstart;

	// needed to reduce queries used by getItemid
	$ItemidCount['bs'] 		= JApplicationHelper::getBlogSectionCount();
	$ItemidCount['bc'] 		= JApplicationHelper::getBlogCategoryCount();
	$ItemidCount['gbs'] 	= JApplicationHelper::getGlobalBlogSectionCount();

	// used to display section/catagory description text and images
	// currently not supported in Archives
	if ( $menu && $menu->componentid && ( $descrip || $descrip_image ) ) {
		switch ( $menu->type ) {
			case 'content_blog_section':
				$description = new mosSection( $database );
				$description->load( $menu->componentid );
				break;

			case 'content_blog_category':
				$description = new mosCategory( $database );
				$description->load( $menu->componentid );
				break;

			default:
				$menu->componentid = 0;
				break;
		}
	}

	// Page Output
	// page header
	if ( $header ) {
		echo '<div class="componentheading'. $params->get( 'pageclass_sfx' ) .'">'. $header .'</div>';
	}

	if ( $archive ) {
		echo '<br />';
		echo mosHTML::monthSelectList( 'month', 'size="1" class="inputbox"', $params->get( 'month' ) );
		echo mosHTML::integerSelectList( 2000, 2010, 1, 'year', 'size="1" class="inputbox"', $params->get( 'year' ), "%04d" );
		echo '<input type="submit" class="button" />';
	}

	// checks to see if there are there any items to display
	if ( $total ) {
		$col_width = 100 / $columns;			// width of each column
		$width = 'width="'. intval($col_width) .'%"';

		if ( $archive ) {
			// Search Success message
			$msg = sprintf( JText::_( 'ARCHIVE_SEARCH_SUCCESS' ), $params->get( 'month' ), $params->get( 'year' ) );
			echo "<br /><br /><div align='center'>". $msg ."</div><br /><br />";
		}
		echo '<table class="blog' . $params->get( 'pageclass_sfx' ) . '" cellpadding="0" cellspacing="0">';

		// Secrion/Category Description & Image
		if ( $menu && $menu->componentid && ( $descrip || $descrip_image ) ) {
			$link = JURL_SITE .'/images/stories/'. $description->image;
			echo '<tr>';
			echo '<td valign="top">';
			if ( $descrip_image && $description->image ) {
				echo '<img src="'. $link .'" align="'. $description->image_position .'" hspace="6" alt="" />';
			}
			if ( $descrip && $description->description ) {
				echo $description->description;
			}
			echo '<br/><br/>';
			echo '</td>';
			echo '</tr>';
		}

		// Leading story output
		if ( $leading ) {
			echo '<tr>';
			echo '<td valign="top">';
			for ( $z = 0; $z < $leading; $z++ ) {
				if ( $i >= $total ) {
					// stops loop if total number of items is less than the number set to display as leading
					break;
				}
				echo '<div>';
				show( $rows[$i], $params, $gid, $access, $pop, $option, $ItemidCount );
				echo '</div>';
				$i++;
			}
			echo '</td>';
			echo '</tr>';
		}


// use newspaper style vertical layout rather than horizontal table
		if ( $intro && ( $i < $total ) ) {
			echo '<tr>';
			echo '<td valign="top">';
			echo '<table width="100%"  cellpadding="0" cellspacing="0">';

			$indexcount = 0;
			for ( $z = 0; $z < $columns; $z++ ) {
				if ($z > 0) $divider = " column_seperator";
				echo "<td valign=\"top\"" . $width . "class=\"article_column" . $divider . "\">\n";
				for ($y = 0; $y < $intro/$columns; $y++) {
					if ($indexcount < $intro)
						//echo $rows[$indexcount++] . "\n";
						show( $rows[$indexcount++], $params, $gid, $access, $pop, $option, $ItemidCount );
				}
				echo "</td>\n";

			}
			echo '</table>';
			echo '</td>';
			echo '</tr>';

// TODO: remove this below

//			echo '<tr>';
//			echo '<td valign="top">';
//			echo '<table width="100%"  cellpadding="0" cellspacing="0">';
//			// intro story output
//			for ( $z = 0; $z < $intro; $z++ ) {
//				if ( $i >= $total ) {
//					// stops loop if total number of items is less than the number set to display as intro + leading
//					break;
//				}
//
//				if ( !( $z % $columns ) || $columns == 1 ) {
//					echo '<tr>';
//				}
//
//				echo '<td valign="top" '. $width .' class="column_seperator">';
//
//				// outputs either intro or only a link
//				if ( $z < $intro ) {
//					show( $rows[$i], $params, $gid, $access, $pop, $option, $ItemidCount );
//				} else {
//					echo '</td>';
//					echo '</tr>';
//					break;
//				}
//
//				echo '</td>';
//
//				if ( !( ( $z + 1 ) % $columns ) || $columns == 1 ) {
//					echo '</tr>';
//				}
//
//				$i++;
//			}
//
//			// this is required to output a final closing </tr> tag when the number of items does not fully
//			// fill the last row of output - a blank column is left
//			if ( $intro % $columns ) {
//				echo '</tr>';
//			}
//
//			echo '</table>';
//			echo '</td>';
//			echo '</tr>';

// NOTE: End remove
		}

		// Links output
		if ( $links && ( $i < $total )  ) {
			echo '<tr>';
			echo '<td valign="top">';
			echo '<div class="blog_more'. $params->get( 'pageclass_sfx' ) .'">';
			HTML_content::showLinks( $rows, $links, $total, $i, 1, $ItemidCount );
			echo '</div>';
			echo '</td>';
			echo '</tr>';
		}

		// Pagination output
		if ( $pagination ) {
			if ( ( $pagination == 2 ) && ( $total <= $limit ) ) {
				// not visible when they is no 'other' pages to display
			} else {
				// get the total number of records
				$limitstart = $limitstart ? $limitstart : 0;
				require_once( JPATH_SITE . '/includes/pageNavigation.php' );
				$pageNav = new mosPageNav( $total, $limitstart, $limit );
				if ( $option == 'com_frontpage' ) {
					$link = 'index.php?option=com_frontpage&amp;Itemid='. $Itemid;
				} else if ( $archive ) {
					$year = $params->get( 'year' );
					$month = $params->get( 'month' );
					$link = 'index.php?option=com_content&amp;task='. $task .'&amp;id='. $id .'&amp;Itemid='. $Itemid.'&amp;year='. $year .'&amp;month='. $month;
				} else {
					$link = 'index.php?option=com_content&amp;task='. $task .'&amp;id='. $id .'&amp;Itemid='. $Itemid;
				}
				echo '<tr>';
				echo '<td valign="top" align="center">';
				echo $pageNav->writePagesLinks( $link );
				echo '<br /><br />';
				echo '</td>';
				echo '</tr>';
				if ( $pagination_results ) {
					echo '<tr>';
					echo '<td valign="top" align="center">';
					echo $pageNav->writePagesCounter();
					echo '</td>';
					echo '</tr>';
				}
			}
		}

		echo '</table>';

	} else if ( $archive && !$total ) {
		// Search Failure message for Archives
		$msg = sprintf( JText::_( 'ARCHIVE_SEARCH_FAILURE' ), $params->get( 'month' ), $params->get( 'year' ) );
		echo '<br /><br /><div align="center">'. $msg .'</div><br />';
	} else {
		// Generic blog empty display
		echo _EMPTY_BLOG;
	}

	// Back Button
	$params->set( 'back_button', $back );
	mosHTML::BackButton ( $params );
}


function showItem( $uid, $gid, &$access, $pop, $option, $now ) {
	global $database, $mainframe;
	global $mosConfig_MetaTitle, $mosConfig_MetaAuthor;

	$nullDate = $database->getNullDate();
	if ( $access->canEdit ) {
		$xwhere = '';
	} else {
		$xwhere = " AND ( a.state = 1 OR a.state = -1 )"
		. "\n AND ( publish_up = '$nullDate' OR publish_up <= '$now' )"
		. "\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )"
		;
	}

	$query = "SELECT a.*, ROUND(v.rating_sum/v.rating_count) AS rating, v.rating_count, u.name AS author, u.usertype, cc.name AS category, s.name AS section, g.name AS groups, s.published AS sec_pub, cc.published AS cat_pub"
	. "\n FROM #__content AS a"
	. "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid"
	. "\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope = 'content'"
	. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
	. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
	. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
	. "\n WHERE a.id = $uid"
	. $xwhere
	. "\n AND a.access <= $gid"
	;
	$database->setQuery( $query );
	$row = NULL;

	if ( $database->loadObject( $row ) ) {
		if ( !$row->cat_pub && $row->catid ) {
		// check whether category is published
			mosNotAuth();
			return;
		}
		if ( !$row->sec_pub && $row->sectionid ) {
		// check whether section is published
			mosNotAuth();
			return;
		}

		$params = new mosParameters( $row->attribs );
		$params->set( 'intro_only', 0 );
		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
		if ( $row->sectionid == 0) {
			$params->set( 'item_navigation', 0 );
		} else {
			$params->set( 'item_navigation', $mainframe->getCfg( 'item_navigation' ) );
		}
		// loads the links for Next & Previous Button
		if ( $params->get( 'item_navigation' ) ) {
			$query = "SELECT a.id"
			. "\n FROM #__content AS a"
			. "\n WHERE a.catid = $row->catid"
			. "\n AND a.state = $row->state"
			. "\n AND ordering < $row->ordering"
			. ($access->canEdit ? '' : "\n AND a.access <= $gid" )
			. $xwhere
			. "\n ORDER BY a.ordering DESC"
			. "\n LIMIT 1"
			;
			$database->setQuery( $query );
			$row->prev = $database->loadResult();

			$query = "SELECT a.id"
			. "\n FROM #__content AS a"
			. "\n WHERE a.catid = $row->catid"
			. "\n AND a.state = $row->state"
			. "\n AND ordering > $row->ordering"
			. ($access->canEdit ? '' : "\n AND a.access <= $gid" )
			. $xwhere
			. "\n ORDER BY a.ordering"
			. "\n LIMIT 1"
			;
			$database->setQuery( $query );
			$row->next = $database->loadResult();
		}
		// page title
		$mainframe->setPageTitle( $row->title );
		if ($mosConfig_MetaTitle=='1') {
			$mainframe->addMetaTag( 'title' , $row->title );
		}
		if ($mosConfig_MetaAuthor=='1') {
			$mainframe->addMetaTag( 'author' , $row->author );
		}

		/*
		 * Handle BreadCrumbs
		 */
		 $breadcrumbs =& $mainframe->getBreadCrumbs();

		// We need the Itemid because we haven't eliminated it
		$query = 	"SELECT a.id"
		. "\n FROM #__menu AS a"
		. "\n WHERE a.componentid = ". $row->sectionid;
		$database->setQuery( $query );
		$_Itemid = $database->loadResult();

		if (!empty($_Itemid)) {
			// Section
			if (!empty($row->section)) {
				$breadcrumbs->addItem( $row->section, sefRelToAbs( 'index.php?option=com_content&amp;task=section&amp;id='. $row->sectionid .'&amp;Itemid='.$_Itemid ));
			}
			// Category
			if (!empty($row->section)) {
				$breadcrumbs->addItem( $row->category, sefRelToAbs( 'index.php?option=com_content&amp;task=category&amp;sectionid='. $row->sectionid .'&amp;id='. $row->catid .'&amp;Itemid='.$_Itemid ));
			}
		}
		// Item
		$breadcrumbs->addItem( $row->title, '');



		show( $row, $params, $gid, $access, $pop, $option );
	} else {
		mosNotAuth();
		return;
	}
}


function show( $row, $params, $gid, &$access, $pop, $option, $ItemidCount=NULL ) {
	global $database, $mainframe;

	$noauth = !$mainframe->getCfg( 'shownoauth' );

	if ( $access->canEdit ) {
		if ( $row->id === null || $row->access > $gid ) {
			mosNotAuth();
			return;
		}
	} else {
		if ( $row->id === null || $row->state == 0 ) {
			mosNotAuth();
			return;
		}
		if ( $row->access > $gid ) {
			if ( $noauth ) {
				mosNotAuth();
				return;
			} else {
				if ( !( $params->get( 'intro_only' ) ) ) {
					mosNotAuth();
					return;
				}
			}
		}
	}

	// GC Parameters
	$params->def( 'link_titles', 	$mainframe->getCfg( 'link_titles' ) );
	$params->def( 'author', 		!$mainframe->getCfg( 'hideAuthor' ) );
	$params->def( 'createdate', 	!$mainframe->getCfg( 'hideCreateDate' ) );
	$params->def( 'modifydate', 	!$mainframe->getCfg( 'hideModifyDate' ) );
	$params->def( 'print', 			!$mainframe->getCfg( 'hidePrint' ) );
	$params->def( 'pdf', 			!$mainframe->getCfg( 'hidePdf' ) );
	$params->def( 'email', 			!$mainframe->getCfg( 'hideEmail' ) );
	$params->def( 'rating', 		$mainframe->getCfg( 'vote' ) );
	$params->def( 'icons', 			$mainframe->getCfg( 'icons' ) );
	$params->def( 'readmore', 		$mainframe->getCfg( 'readmore' ) );
	// Other Params
	$params->def( 'image', 			1 );
	$params->def( 'section', 		0 );
	$params->def( 'section_link', 	0 );
	$params->def( 'category', 		0 );
	$params->def( 'category_link', 	0 );
	$params->def( 'introtext', 		1 );
	$params->def( 'pageclass_sfx', 	'' );
	$params->def( 'item_title', 	1 );
	$params->def( 'url', 			1 );

	// loads the link for Section name
	if ( $params->get( 'section_link' ) ) {
		$query = 	"SELECT a.id"
		. "\n FROM #__menu AS a"
		. "\n WHERE a.componentid = ". $row->sectionid.""
		;
		$database->setQuery( $query );
		$_Itemid = $database->loadResult();

		if ( $_Itemid ) {
			$_Itemid = '&amp;Itemid='. $_Itemid;
		}

		$link 			= sefRelToAbs( 'index.php?option=com_content&amp;task=section&amp;id='. $row->sectionid . $_Itemid );
		$row->section 	= '<a href="'. $link .'">'. $row->section .'</a>';
	}

	// loads the link for Category name
	if ( $params->get( 'category_link' ) ) {
		$query = 	"SELECT a.id"
		. "\n FROM #__menu AS a"
		. "\n WHERE a.componentid = $row->catid"
		;
		$database->setQuery( $query );
		$_Itemid = $database->loadResult();

		if ( $_Itemid ) {
			$_Itemid = '&amp;Itemid='. $_Itemid;
		}

		$link 			= sefRelToAbs( 'index.php?option=com_content&amp;task=category&amp;sectionid='. $row->sectionid .'&amp;id='. $row->catid . $_Itemid );
		$row->category 	= '<a href="'. $link .'">'. $row->category .'</a>';
	}

	// loads current template for the pop-up window
	$template = '';
	if ( $pop ) {
		$params->set( 'popup', 1 );
		$query = "SELECT template"
		. "\n FROM #__templates_menu"
		. "\n WHERE client_id = 0"
		. "\n AND menuid = 0"
		;
		$database->setQuery( $query );
		$template = $database->loadResult();
	}

	// show/hides the intro text
	if ( $params->get( 'introtext'  ) ) {
		$row->text = $row->introtext. ( $params->get( 'intro_only' ) ? '' : chr(13) . chr(13) . $row->fulltext);
	} else {
		$row->text = $row->fulltext;
	}

	// deal with the {mospagebreak} mambots
	// only permitted in the full text area
	$page = intval( mosGetParam( $_REQUEST, 'limitstart', 0 ) );

	// record the hit
	if ( !$params->get( 'intro_only' ) && ($page == 0)) {
		$obj = new mosContent( $database );
		$obj->hit( $row->id );
	}

	HTML_content::show($row, $params, $access, $page, $option, $ItemidCount );
}


function editItem( $uid, $gid, &$access, $sectionid=0, $task, $Itemid ){
	global $database, $my;

	$nullDate = $database->getNullDate();
	$row = new mosContent( $database );
	// load the row from the db table
	$row->load( $uid );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut( $my->id )) {
		mosErrorAlert( JText::_( 'The module' ) ." [ ".$row->title." ] ". JText::_( 'DESCBEINGEDITTEDBY' ) );
	}

	if ( $uid ) {
		// existing record
		if ( !( $access->canEdit || ( $access->canEditOwn && $row->created_by == $my->id ) ) ) {
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

	if ( $uid ) {
		$sectionid = $row->sectionid;
	}

	$lists = array();

	// get the type name - which is a special category
	$query = "SELECT name FROM #__sections"
	. "\n WHERE id = $sectionid"
	;
	$database->setQuery( $query );
	$section = $database->loadResult();

	if ( $uid == 0 ) {
		$row->catid = 0;
	}

	if ( $uid ) {
		$row->checkout( $my->id );
		if (trim( $row->publish_down ) == '0000-00-00 00:00:00') {
			$row->publish_down = 'Never';
		}
		if (trim( $row->images )) {
			$row->images = explode( "\n", $row->images );
		} else {
			$row->images = array();
		}
		$query = "SELECT name from #__users"
		. "\n WHERE id = $row->created_by"
		;
		$database->setQuery( $query	);
		$row->creator = $database->loadResult();

		$query = "SELECT name from #__users"
		. "\n WHERE id = $row->modified_by"
		;
		$database->setQuery( $query );
		$row->modifier = $database->loadResult();

		$query = "SELECT content_id from #__content_frontpage"
		."\n WHERE content_id = $row->id"
		;
		$database->setQuery( $query );
		$row->frontpage = $database->loadResult();
	} else {
		$row->sectionid 	= $sectionid;
		$row->version 		= 0;
		$row->state 		= 0;
		$row->ordering 		= 0;
		$row->images 		= array();
		$row->publish_up 	= date( 'Y-m-d', time() );
		$row->publish_down 	= 'Never';
		$row->creator 		= 0;
		$row->modifier 		= 0;
		$row->frontpage 	= 0;
	}


	// calls function to read image from directory
	$pathA 		= JPATH_SITE .'/images/stories';
	$pathL 		= JURL_SITE .'/images/stories';
	$images 	= array();
	$folders 	= array();
	$folders[] 	= mosHTML::makeOption( '/' );
	mosAdminMenus::ReadImages( $pathA, '/', $folders, $images );
	// list of folders in images/stories/
	$lists['folders'] 		= mosAdminMenus::GetImageFolders( $folders, $pathL );
	// list of images in specfic folder in images/stories/
	$lists['imagefiles']	= mosAdminMenus::GetImages( $images, $pathL );
	// list of saved images
	$lists['imagelist'] 	= mosAdminMenus::GetSavedImages( $row, $pathL );

	// make the select list for the states
	$states[] = mosHTML::makeOption( 0, _CMN_UNPUBLISHED );
	$states[] = mosHTML::makeOption( 1, _CMN_PUBLISHED );
	$lists['state'] 		= mosHTML::selectList( $states, 'state', 'class="inputbox" size="1"', 'value', 'text', intval( $row->state ) );

	// build the html select list for ordering
	$query = "SELECT ordering AS value, title AS text"
	. "\n FROM #__content"
	. "\n WHERE catid = $row->catid"
	. "\n ORDER BY ordering"
	;
	$lists['ordering'] 		= mosAdminMenus::SpecificOrdering( $row, $uid, $query, 1 );

	// build list of categories
	$lists['catid'] 		= mosAdminMenus::ComponentCategory( 'catid', $sectionid, intval( $row->catid ) );
	// build the select list for the image positions
	$lists['_align'] 		= mosAdminMenus::Positions( '_align' );
	// build the html select list for the group access
	$lists['access'] 		= mosAdminMenus::Access( $row );

	// build the select list for the image caption alignment
	$lists['_caption_align'] 	= mosAdminMenus::Positions( '_caption_align' );
	// build the html select list for the group access
	// build the select list for the image caption position
	$pos[] = mosHTML::makeOption( 'bottom', JText::_( 'Bottom' ) );
	$pos[] = mosHTML::makeOption( 'top', JText::_( 'Top' ) );
	$lists['_caption_position'] = mosHTML::selectList( $pos, '_caption_position', 'class="inputbox" size="1"', 'value', 'text' );

	HTML_content::editContent( $row, $section, $lists, $images, $access, $my->id, $sectionid, $task, $Itemid );
}


/**
* Saves the content item an edit form submit
*/
function saveContent( &$access, $task ) {
	global $database, $mainframe, $my;
	global $Itemid;

	$nullDate = $database->getNullDate();
	$row = new mosContent( $database );
	if ( !$row->bind( $_POST ) ) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$isNew = $row->id < 1;
	if ( $isNew ) {
		// new record
		if ( !( $access->canEdit || $access->canEditOwn ) ) {
			mosNotAuth();
			return;
		}
		$row->created = date( 'Y-m-d H:i:s' );
		$row->created_by = $my->id;
	} else {
		// existing record
		if ( !( $access->canEdit || ( $access->canEditOwn && $row->created_by == $my->id ) ) ) {
			mosNotAuth();
			return;
		}
		$row->modified 		= date( 'Y-m-d H:i:s' );
		$row->modified_by 	= $my->id;
	}
	if ( trim( $row->publish_down ) == 'Never' ) {
		$row->publish_down = $nullDate;
	}

	// code cleaner for xhtml transitional compliance
	$row->introtext = str_replace( '<br>', '<br />', $row->introtext );
	$row->fulltext 	= str_replace( '<br>', '<br />', $row->fulltext );

 	// remove <br /> take being automatically added to empty fulltext
 	$length	= strlen( $row->fulltext ) < 9;
 	$search = strstr( $row->fulltext, '<br />');
 	if ( $length && $search ) {
 		$row->fulltext = NULL;
 	}

	$row->title = ampReplace( $row->title );

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->version++;
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	// manage frontpage items
	require_once( $mainframe->getPath( 'class', 'com_frontpage' ) );
	$fp = new mosFrontPage( $database );

	if ( mosGetParam( $_REQUEST, 'frontpage', 0 ) ) {

		// toggles go to first place
		if (!$fp->load( $row->id )) {
			// new entry
			$query = "INSERT INTO #__content_frontpage"
			. "\n VALUES ( $row->id, 1 )"
			;
			$database->setQuery( $query );
			if (!$database->query()) {
				echo "<script> alert('".$database->stderr()."');</script>\n";
				exit();
			}
			$fp->ordering = 1;
		}
	} else {
		// no frontpage mask
		if ( !$fp->delete( $row->id ) ) {
			$msg .= $fp->stderr();
		}
		$fp->ordering = 0;
	}
	$fp->updateOrder();

	$row->checkin();
	$row->updateOrder( "catid = $row->catid" );

	// gets section name of item
	$query = "SELECT s.title"
	. "\n FROM #__sections AS s"
	. "\n WHERE s.scope = 'content'"
	. "\n AND s.id = $row->sectionid"
	;
	$database->setQuery( $query );
	// gets category name of item
	$section = $database->loadResult();

	$query = "SELECT c.title"
	. "\n FROM #__categories AS c"
	. "\n WHERE c.id = $row->catid"
	;
	$database->setQuery( $query	);
	$category = $database->loadResult();

	if ( $isNew ) {
		// messaging for new items
		require_once( JPATH_SITE .'/components/com_messages/messages.class.php' );

		$query = "SELECT id"
		. "\n FROM #__users"
		. "\n WHERE sendEmail = 1"
		;
		$database->setQuery( $query );
		$users = $database->loadResultArray();
		foreach ($users as $user_id) {
			$msg = new mosMessage( $database );
			$msg->send( $my->id, $user_id, "New Item", sprintf( JText::_( 'ON_NEW_CONTENT' ), $my->username, $row->title, $section, $category ) );
		}
	}

	$msg = $isNew ? JText::_( 'THANK_SUB' ) : JText::_( 'Item succesfully saved.' );
	switch ( $task ) {
		case 'apply':
			$link = $_SERVER['HTTP_REFERER'];
			break;

		case 'apply_new':
			$Itemid = mosGetParam( $_POST, 'Returnid', $Itemid );
			$link = 'index.php?option=com_content&task=edit&id='. $row->id.'&Itemid='. $Itemid;
			break;


		case 'save':
		default:
			$Itemid = mosGetParam( $_POST, 'Returnid', '' );
			if ( $Itemid ) {
				$link = 'index.php?option=com_content&task=view&id='. $row->id.'&Itemid='. $Itemid;
			} else {
				$link = mosGetParam( $_POST, 'referer', '' );
			}
			break;
	}
	mosRedirect( $link, $msg );
}


/**
* Cancels an edit operation
* @param database A database connector object
*/
function cancelContent( &$access ) {
	global $database, $my, $task;

	$row = new mosContent( $database );
	$row->bind( $_POST );

	if ( $access->canEdit || ( $access->canEditOwn && $row->created_by == $my->id ) ) {
		$row->checkin();
	}

	$Itemid = mosGetParam( $_POST, 'Returnid', '0' );

	$referer 	= mosGetParam( $_POST, 'referer', '' );
	$parts 		= parse_url( $referer );
	parse_str( $parts['query'], $query );

	if ( $task == 'edit' || $task == 'cancel' ) {
		$Itemid  = mosGetParam( $_POST, 'Returnid', '' );
		$referer = 'index.php?option=com_content&task=view&id='. $row->id.'&Itemid='. $Itemid;
	}

	if ( $referer && !( $task == 'new' ) ) {
		mosRedirect( $referer );
	} else {
		mosRedirect( 'index.php' );
	}
}

/**
 * Shows the email form for a given content item.
 * @param int The content item id
 */
function emailContentForm( $uid ) {
	global $database, $my;

	$row = new mosContent( $database );
	$row->load( $uid );

	if ( $row->id === null || $row->access > $my->gid ) {
		mosNotAuth();
		return;
	} else {
		$query = "SELECT template"
		. "\n FROM #__templates_menu"
		. "\n WHERE client_id = 0"
		. "\n AND menuid = 0"
		;
		$database->setQuery( $query );
		$template = $database->loadResult();
		HTML_content::emailForm( $row->id, $row->title, $template );
	}

}

/**
 * Shows the email form for a given content item.
 * @param int The content item id
 */
function emailContentSend( $uid ) {
	global $database, $mainframe;
	global $mosConfig_sitename;
	global $mosConfig_mailfrom, $mosConfig_fromname;

	$validate = mosGetParam( $_POST, mosHash( 'validate' ), 0 );
	if (!$validate) {
		// probably a spoofing attack
		echo JText::_('ALERTNOTAUTH');
		return;
	}

	$_Itemid 			= JApplicationHelper::getItemid( $uid, 0, 0  );
	$email 				= mosGetParam( $_POST, 'email', '' );
	$yourname 			= mosGetParam( $_POST, 'yourname', '' );
	$youremail 			= mosGetParam( $_POST, 'youremail', '' );
	$subject_default 	= sprintf( JText::_( 'Item sent by' ), $yourname );
	$subject = mosGetParam( $_POST, 'subject', $subject_default );

	if ($uid < 1 || !$email || !$youremail || ( is_email( $email ) == false ) || (is_email( $youremail ) == false)) {
		mosErrorAlert( JText::_( 'EMAIL_ERR_NOINFO' ) );
	}

	$query = "SELECT template"
	. "\n FROM #__templates_menu"
	. "\n WHERE client_id = 0"
	. "\n AND menuid = 0"
	;
	$database->setQuery( $query );
	$template = $database->loadResult();

	// link sent in email
	$link = sefRelToAbs( JURL_SITE .'/index.php?option=com_content&task=view&id='. $uid .'&Itemid='. $_Itemid );

	// message text
	$msg = sprintf( JText::_( 'EMAIL_MSG' ), $mosConfig_sitename, $yourname, $youremail, $link );

	// mail function
	mosMail( $youremail, $yourname, $email, $subject, $msg );

	HTML_content::emailSent( $email, $template );
}

function is_email( $email ){
	$rBool = false;

	if (preg_match( "/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $email )) {
		$rBool = true;
	}
	return $rBool;
}

function recordVote() {
	global $database;

	$user_rating = mosGetParam( $_REQUEST, 'user_rating', 0 );
	$url = mosGetParam( $_REQUEST, 'url', '' );
	$cid = mosGetParam( $_REQUEST, 'cid', 0 );
	$cid = intval( $cid );
	$user_rating = intval( $user_rating );

	if (($user_rating >= 1) and ($user_rating <= 5)) {
		$currip = getenv( 'REMOTE_ADDR' );

		$query = "SELECT *"
		. "\n FROM #__content_rating"
		. "\n WHERE content_id = $cid"
		;
		$database->setQuery( $query );
		$votesdb = NULL;
		if ( !( $database->loadObject( $votesdb ) ) ) {
			$query = "INSERT INTO #__content_rating ( content_id, lastip, rating_sum, rating_count )"
			. "\n VALUES ( $cid, '$currip', $user_rating, 1 )";
			$database->setQuery( $query );
			$database->query() or die( $database->stderr() );;
		} else {
			if ($currip <> ($votesdb->lastip)) {
				$query = "UPDATE #__content_rating"
				. "\n SET rating_count = rating_count + 1, rating_sum = rating_sum + $user_rating, lastip = '$currip'"
				. "\n WHERE content_id = $cid"
				;
				$database->setQuery( $query );
				$database->query() or die( $database->stderr() );
			} else {
				mosRedirect ( $url, JText::_( 'You already voted for this poll today!' ) );
			}
		}
		mosRedirect ( $url, JText::_( 'Thanks for your vote!' ) );
	}
}


function _orderby_pri( $orderby ) {
	switch ( $orderby ) {
		case 'alpha':
			$orderby = 'cc.title, ';
			break;

		case 'ralpha':
			$orderby = 'cc.title DESC, ';
			break;

		case 'order':
			$orderby = 'cc.ordering, ';
			break;

		default:
			$orderby = '';
			break;
	}

	return $orderby;
}


function _orderby_sec( $orderby ) {
	switch ( $orderby ) {
		case 'date':
			$orderby = 'a.created';
			break;

		case 'rdate':
			$orderby = 'a.created DESC';
			break;

		case 'alpha':
			$orderby = 'a.title';
			break;

		case 'ralpha':
			$orderby = 'a.title DESC';
			break;

		case 'hits':
			$orderby = 'a.hits';
			break;

		case 'rhits':
			$orderby = 'a.hits DESC';
			break;

		case 'order':
			$orderby = 'a.ordering';
			break;

		case 'author':
			$orderby = 'a.created_by_alias, u.name';
			break;

		case 'rauthor':
			$orderby = 'a.created_by_alias DESC, u.name DESC';
			break;

		case 'front':
			$orderby = 'f.ordering';
			break;

		default:
			$orderby = 'a.ordering';
			break;
	}

	return $orderby;
}

/*
* @param int 0 = Archives, 1 = Section, 2 = Category
*/
function _where( $type=1, &$access, &$noauth, $gid, $id, $now=NULL, $year=NULL, $month=NULL ) {
	global $database;

	$nullDate = $database->getNullDate();
	$where = array();

	// normal
	if ( $type > 0) {
		$where[] = "a.state = '1'";
		if ( !$access->canEdit ) {
			$where[] = "( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )";
			$where[] = "( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )";
		}
		if ( $noauth ) {
			$where[] = "a.access <= $gid";
		}
		if ( $id > 0 ) {
			if ( $type == 1 ) {
				$where[] = "a.sectionid IN ( $id ) ";
			} else if ( $type == 2 ) {
				$where[] = "a.catid IN ( $id ) ";
			}
		}
	}

	// archive
	if ( $type < 0 ) {
		$where[] = "a.state='-1'";
		if ( $year ) {
			$where[] = "YEAR( a.created ) = '$year'";
		}
		if ( $month ) {
			$where[] = "MONTH( a.created ) = '$month'";
		}
		if ( $noauth ) {
			$where[] = "a.access <= $gid";
		}
		if ( $id > 0 ) {
			if ( $type == -1 ) {
				$where[] = "a.sectionid = $id";
			} else if ( $type == -2) {
				$where[] = "a.catid = $id";
			}
		}
	}

	return $where;
}
?>
