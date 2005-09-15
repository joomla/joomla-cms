<?php
/**
* @version $Id: content.category.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

class comContentCategory {

	function show( $id, $gid, &$access, $pop, $option, $Itemid, $task, $sectionid, $limit, $limitstart )
	{
		$this->_display( $id, $gid, $access, $pop, $option, $Itemid, $task, $sectionid, $limit, $limitstart );
	}

	function _display($id, $gid, &$access, $pop, $option, $Itemid, $task, $sectionid, $limit, $limitstart)
	{
		switch ( strtolower( $task ) )
		{
			case 'apply_categorypop':
			case 'save_categorypop':
			case 'apply_category':
			case 'save_category':
				mosCache::cleanCache( 'com_content' );
				$this->saveCategory( $access, $task );
				break;

			case 'cancel_categorypop':
			case 'cancel_category':
				$this->cancelCategory( $access, $task );
				break;

			case 'category':
				mosFS::load( 'components/com_content/content.html.php' );
				$this->showCategory( $id, $gid, $access, $sectionid, $limit, $limitstart, $task );
				break;

			case 'archivecategory':
				mosFS::load( 'components/com_content/content.html.php' );
				$this->showArchiveCategory( @$id, $gid, $access, $pop, $option );
				break;

			case 'edit_categorypop':
			case 'edit_category':
				mosFS::load( 'components/com_content/content.edit.html.php' );
				$this->editCategory( $id, $gid, $access, $task, $Itemid );
				break;

			case 'blogcategorymulti':
			case 'blogcategory':
			default:
				mosFS::load( 'components/com_content/content.html.php' );
				$this->showBlogCategory( $id, $gid, $access, $pop );
				break;
		}
	}

	/**
	* @param int The category id
	* @param int The group id of the user
	* @param int The access level of the user
	* @param int The section id
	* @param int The number of items to dislpay
	* @param int The offset for pagination
	*/
	function showCategory( $id, $gid, &$access, $sectionid, $limit, $limitstart, $task  )
	{
		global $database, $mainframe, $Itemid, $mosConfig_list_limit, $mosConfig_zero_date;
		global $_LANG;

		$now 		= $mainframe->getDateTime();
		$noauth 	= !$mainframe->getCfg( 'shownoauth' );

		$pagetitle 	= '';
		$tOrder		= mosGetParam( $_POST, 'tOrder', 'a.created' );
		$tOrder_old	= mosGetParam( $_POST, 'tOrder_old', 'a.created' );

		// table column ordering values
		$tOrderDir = mosGetParam( $_POST, 'tOrderDir', 'DESC' );
		if ( $tOrderDir == 'ASC' ) {
			$lists['tOrderDir'] 	= 'DESC';
		} else {
			$lists['tOrderDir'] 	= 'ASC';
		}
		$lists['tOrder'] 		= $tOrder;

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

		$params->set( 'type', 'category' );

		$params->def( 'page_title', 			1 );
		$params->def( 'title', 					1 );
		$params->def( 'hits', 					$mainframe->getCfg( 'hits' ) );
		$params->def( 'author', 				!$mainframe->getCfg( 'hideAuthor' ) );
		$params->def( 'date', 					!$mainframe->getCfg( 'hideCreateDate' ) );
		$params->def( 'date_format', 			$_LANG->_( 'DATE_FORMAT_LC' ) );
		$params->def( 'navigation', 			2 );
		$params->def( 'display', 				1 );
		$params->def( 'display_num', 			$mosConfig_list_limit );
		$params->def( 'description_cat', 		1 );
		$params->def( 'description_image_cat', 	1 );
		$params->set( 'description', 			$params->get( 'description_cat' ) );
		$params->set( 'description_image', 		$params->get( 'description_image_cat' ) );
		$params->def( 'other_cat', 				1 );
		$params->def( 'empty_cat', 				0 );
		$params->def( 'cat_items', 				1 );
		$params->def( 'cat_description', 		0 );
		$params->def( 'back_button', 			$mainframe->getCfg( 'back_button' ) );
		$params->def( 'pageclass_sfx', 			'' );
		$params->def( 'headings', 				1 );
		$params->def( 'order_select',			0 );
		$params->def( 'filter', 				1 );
		$params->def( 'filter_type', 			'title' );
		$params->def( 'non_menu', 				0 );
		$params->def( 'meta_key', 				'' );
		$params->def( 'meta_descrip', 			'' );
		$params->def( 'seo_title', 				$menu->name );
		$params->set( 'catid', 					$id );

		$category = new mosCategory( $database );
		$category->load( $id );

		if ( $sectionid == 0 ) {
			$sectionid = $category->section;
		}

		if ( $access->canEdit ) {
			$xwhere = '';
			$xwhere2 = "\n AND b.state >= '0'";
		} else {
			$xwhere = "\n AND c.published='1'";
			$xwhere2 = "\n AND b.state='1'";
			$xwhere2 .= "\n AND ( publish_up = '$mosConfig_zero_date' OR publish_up <= '$now' )";
			$xwhere2 .= "\n AND ( publish_down = '$mosConfig_zero_date' OR publish_down >= '$now' )";
		}

		// show/hide empty categories
		$empty = '';
		if ( !$params->get( 'empty_cat' ) ) {
			$empty = "\n HAVING COUNT( b.id ) > 0";
		}

		// get the list of other categories
		$query = "SELECT c.*, COUNT( b.id ) AS numitems"
		. "\n FROM #__categories AS c"
		. "\n LEFT JOIN #__content AS b ON b.catid = c.id "
		. $xwhere2
		. ( $noauth ? "\n AND b.access <= '$gid'" : '' )
		. "\n WHERE c.section = '$category->section'"
		. $xwhere
		. ( $noauth ? "\n AND c.access <= '$gid'" : '' )
		. "\n GROUP BY c.id"
		. $empty
		. "\n ORDER BY c.ordering"
		;
		$database->setQuery( $query );
		$other_categories = $database->loadObjectList();

		// get the total number of published items in the category
		// filter functionality
		$filter = trim( mosGetParam( $_POST, 'filter', '' ) );
		$filter = strtolower( $filter );
		$and 	= '';
		if ( $filter ) {
			if ( $params->get( 'filter' ) ) {
				switch ( $params->get( 'filter_type' ) ) {
					case 'title':
						$and = "\n AND LOWER( a.title ) LIKE '%$filter%'";
						break;
					case 'author':
						$and = "\n AND ( ( LOWER( u.name ) LIKE '%$filter%' ) OR ( LOWER( a.created_by_alias ) LIKE '%". $filter ."%' ) )";
						break;
					case 'hits':
						$and = "\n AND a.hits LIKE '%$filter%'";
						break;
				}
			}
		}

		if ( $access->canEdit ) {
			$xwhere = "\n AND a.state >= '0'";
		} else {
			$xwhere = "\n AND a.state='1'";
			$xwhere .= "\n AND ( publish_up = '$mosConfig_zero_date' OR publish_up <= '$now' )";
			$xwhere .= "\n AND ( publish_down = '$mosConfig_zero_date' OR publish_down >= '$now' )";
			;
		}

		$join 	= '';
		$static = '';
		// special handling for `Table - Static Content`
		if ( ( $sectionid == 0 ) && ( $id == 0 ) ) {
			switch ( $params->get( 'non_menu' ) ) {
				case 1:
					// Non-Linked Static Content only
					$join 	= "\n INNER JOIN #__menu AS m";
					$static = "\n AND ( m.type = 'content_typed' AND NOT ( m.componentid = a.id ) )";
					break;

				case 2:
					// All Static Content
					break;

				default:
					// Linked Static Content only
					$join 	= "\n LEFT JOIN #__menu AS m ON m.componentid = a.id";
					$static = "\n AND ( m.componentid = a.id AND m.type = 'content_typed' )";
					break;
			}

			// page title needed as no category for static content
			$category->name = $menu->name;
		}

		// Ordering controls
		$order_drop		= mosGetParam( $_POST, 'order_drop', '' );
		$order_control	= mosGetParam( $_POST, 'order_control', '' );
		$selected 		= $order_drop;
		if ( $params->get( 'order_select' ) && $order_control ) {
		// dropdown ordering
			if ( !$order_drop ) {
				$order_drop = $params->get( 'orderby', 'rdate' );
			}
			$order_drop = _orderby_sec( $order_drop );
			$order 		= "\n ORDER BY $order_drop, a.created DESC";
		} else {
		// table column ordering
			switch ( $tOrder ) {
				default:
					$order = "\n ORDER BY $tOrder $tOrderDir, a.created DESC";
					break;
			}
		}

		// count items for this category
		$query = "SELECT COUNT(a.id) as numitems"
		. "\n FROM #__content AS a"
		. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
		. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
		. $join
		. "\n WHERE a.catid = '$category->id'"
		. $xwhere
		. ( $noauth ? "\n AND a.access <= '$gid'" : '' )
		. "\n AND '$category->access' <= '$gid'"
		. $and
		. $static
		. $order
		;
		$database->setQuery( $query );
		$counter = $database->loadObjectList();
		$total 	= $counter[0]->numitems;
		$limit 	= $limit ? $limit : $params->get( 'display_num' ) ;
		if ( $total <= $limit ) $limitstart = 0;

		// load navigation files
		mosFS::load( '@pageNavigation' );
		$pageNav = new mosPageNav( $total, $limitstart, $limit );

		// get the list of items for this category
		$query = "SELECT a.id, a.title, a.hits, a.created_by, a.created_by_alias, a.created AS created, a.access, u.name AS author, a.state, g.name AS groups"
		. "\n FROM #__content AS a"
		. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
		. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
		. $join
		. "\n WHERE a.catid = '$category->id'"
		. $xwhere
		. ( $noauth ? "\n AND a.access<='$gid'" : '' )
		. "\n AND '$category->access' <= '$gid'"
		. $and
		. $static
		. $order
		;
		$database->setQuery( $query, $limitstart, $limit );
		$items = $database->loadObjectList();

		$j 		= 1;
		$total 	= count( $items );
		for( $i=0; $i < $total; $i++ ) {
			$items[$i]->created = mosFormatDate ( $items[$i]->created, $params->get( 'date_format' ) );
			$items[$i]->hits 	= $items[$i]->hits ? $items[$i]->hits : '-';
			$items[$i]->author	= $items[$i]->created_by_alias ? $items[$i]->created_by_alias : $items[$i]->author;

			// special Itemid handling for `Table - Static Content`
			if ( ( $sectionid == 0 ) && ( $category->id == 0 ) ) {
				$Itemid = $mainframe->getItemid( $items[$i]->id, 1, 0, 0, 0, 0 );
			}
			if ( $items[$i]->access <= $gid ){
				$items[$i]->item_link = sefRelToAbs( 'index.php?option=com_content&amp;task=view&amp;id='. $items[$i]->id .'&amp;Itemid='. $Itemid );
			} else {
				$items[$i]->item_link = sefRelToAbs( 'index.php?option=com_registration&amp;task=register' );
			}

			$items[$i]->edit_icon 	= HTML_content::EditIcon( $items[$i], $params, $access, 0 );
			$items[$i]->register 	= ( $items[$i]->access <= $gid ? 1 : 0 );

			$items[$i]->num = $j;
			$j = 1 - $j;
		}

		// order dropdown
		$check 		= 0;
		if ( $params->get( 'date' ) ) {
			$order_drop_value[] = mosHTML::makeOption( 'date', $_LANG->_( 'ORDER_DROPDOWN_DA' ) );
			$order_drop_value[] = mosHTML::makeOption( 'rdate', $_LANG->_( 'ORDER_DROPDOWN_DD' ) );
			$check .= 1;
		}
		if ( $params->get( 'title' ) ) {
			$order_drop_value[] = mosHTML::makeOption( 'alpha', $_LANG->_( 'ORDER_DROPDOWN_TA' ) );
			$order_drop_value[] = mosHTML::makeOption( 'ralpha', $_LANG->_( 'ORDER_DROPDOWN_TD' ) );
			$check .= 1;
		}
		if ( $params->get( 'hits' ) ) {
			$order_drop_value[] = mosHTML::makeOption( 'hits', $_LANG->_( 'ORDER_DROPDOWN_HA' ) );
			$order_drop_value[] = mosHTML::makeOption( 'rhits', $_LANG->_( 'ORDER_DROPDOWN_HD' ) );
			$check .= 1;
		}
		if ( $params->get( 'author' ) ) {
			$order_drop_value[] = mosHTML::makeOption( 'author', $_LANG->_( 'ORDER_DROPDOWN_AUA' ) );
			$order_drop_value[] = mosHTML::makeOption( 'rauthor', $_LANG->_( 'ORDER_DROPDOWN_AUD' ) );
			$check .= 1;
		}
		$order_drop_value[] = mosHTML::makeOption( 'order', $_LANG->_( 'ORDER_DROPDOWN_O' ) );
		$lists['order_drop'] = mosHTML::selectList( $order_drop_value, 'order_drop', 'class="inputbox" size="1"  onchange="document.adminForm.submit();"', 'value', 'text', $selected );
		if ( $check < 1 ) {
			$lists['order_drop'] = '';
			$params->set( 'order_select', 0 );
		}

		// group level for category
		switch ( $category->access ) {
			case 1:
				$category->groups = 'Registered';
				break;

			case 2:
				$category->groups = 'Special';
				break;

			default:
				$category->groups = 'Public';
				break;
		}

		// group level for categories
		$total = count( $other_categories );
		for ( $i=0; $i < $total; $i++ ) {
			$link = 'index.php?option=com_content&amp;task=category&amp;sectionid='. $id .'&amp;id='. $other_categories[$i]->id .'&amp;Itemid='. $Itemid;
			$other_categories[$i]->link	= sefRelToAbs( $link );
			// needed for mosbots to work correctly
			$other_categories[$i]->text = $other_categories[$i]->description;

			switch ( $other_categories[$i]->access ) {
				case 1:
					$other_categories[$i]->groups = 'Registered';
					break;

				case 2:
					$other_categories[$i]->groups = 'Special';
					break;

				default:
					$other_categories[$i]->groups = 'Public';
					break;
			}

			$edit_icon 	= HTML_content::EditIconCategory( $other_categories[$i], $params, $access, 0, 0 );
			$other_categories[$i]->edit_icon = $edit_icon;
		}

		// SEO Meta Tags
		$mainframe->setPageMeta( $params->get( 'seo_title' ), $params->get( 'meta_key' ), $params->get( 'meta_descrip' ) );

		// needed for mosbots to work correctly
		$category->text = $category->description;

		$lists['filter'] = $filter;

		$link 	= 'index.php?option=com_content&amp;task=category&amp;sectionid='. $sectionid .'&amp;id='. $category->id .'&amp;Itemid='. $Itemid;
		$params->set( 'page_limit',		$pageNav->getLimitBox( $link ) );
		$params->set( 'page_links',		$pageNav->writePagesLinks( $link, 0 ) );
		$params->set( 'page_counter',	$pageNav->writePagesCounter() );
		$params->set( 'sectionid', 		$sectionid );
		$params->set( 'catid', 			$category->id );
		$params->set( 'task',			'category' );
		$params->set( 'sectionid', 		$sectionid );

		contentScreens_front::table_category( $params, $category, $other_categories, $access, $items, $lists );
	}


	function showBlogCategory( $id=0, $gid, &$access, $pop )
	{
		global $database, $mainframe, $Itemid;

		$now 	= $mainframe->getDateTime();
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

		$params->def( 'meta_key', 		'' );
		$params->def( 'meta_descrip', 	'' );
		$params->def( 'seo_title', 		$menu->name );

		// new blog multiple section handling
		if ( !$id ) {
			$id = $params->def( 'categoryid', 0 );
		}

		$where	= _where( 2, $access, $noauth, $gid, $id, $now );

		$where = ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );

		// Ordering control
		$orderby_sec 	= $params->def( 'orderby_sec', 'rdate' );
		$orderby_pri 	= $params->def( 'orderby_pri', '' );
		$order_sec 		= _orderby_sec( $orderby_sec );
		$order_pri 		= _orderby_pri( $orderby_pri );

		// Main data query
		$query = "SELECT a.*, v.rating_sum, v.rating_count, u.name AS author, u.usertype, s.name AS section, g.name AS groups, cc.name AS category"
		. "\n FROM #__content AS a"
		. "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid"
		. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
		. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
		. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id"
		. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
		. $where
		. "\n AND s.access <= ". $gid
		. "\n ORDER BY ". $order_pri . $order_sec;
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();

		// SEO Meta Tags
		$mainframe->setPageMeta( $params->get( 'seo_title' ), $params->get( 'meta_key' ), $params->get( 'meta_descrip' ) );

		BlogOutput( $rows, $params, $gid, $access, $pop, $menu );
	}

	function showArchiveCategory( $id=0, $gid, &$access, $pop, $option )
	{
		global $database, $mainframe;
		global $Itemid;

		$now = $mainframe->getDateTime();
		// Parameters
		$noauth = !$mainframe->getCfg( 'shownoauth' );
		$year 	= mosGetParam( $_REQUEST, 'year', date( 'Y' ) );
		$month 	= mosGetParam( $_REQUEST, 'month', date( 'm' ) );
		$module = trim( mosGetParam( $_REQUEST, 'module', '' ) );

		// used by archive module
		if ( $module ) {
			$check = '';
		} else {
			$check = 'AND a.catid = '. $id;
		}

		if ( $Itemid ) {
			$menu = new mosMenu( $database );
			$menu->load( $Itemid );
			$params = new mosParameters( $menu->params );
		} else {
			$menu = "";
			$params = new mosParameters( '' );
		}

		$params->def( 'meta_key', 		'' );
		$params->def( 'meta_descrip', 	'' );
		$params->def( 'seo_title', 		$menu->name );

		// Ordering control
		$orderby_sec = $params->def( 'orderby', 'rdate' );
		$order_sec = _orderby_sec( $orderby_sec );

		// used in query
		$where = _where( -2, $access, $noauth, $gid, $id, NULL, $year, $month );

		$where = ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );

		// query to determine if there are any archived entries for the category
		$query = 	"SELECT a.id"
		. "\n FROM #__content as a"
		. "\n WHERE a.state = '-1'"
		. "\n ". $check
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
		$archives = count( $items );

		$query = "SELECT a.*, v.rating_sum, v.rating_count, u.name AS author, u.usertype, s.name AS section, g.name AS groups"
		. "\n FROM #__content AS a"
		. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
		. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
		. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id"
		. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
		. $where
		. "\n AND s.access <= ". $gid
		. "\n ORDER BY ". $order_sec
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();

		// initiate form
	 	echo '<form action="'.sefRelToAbs( 'index.php').'" method="post">';

		if ( !$archives ) {
			// if no archives for category, hides search and outputs empty message
			echo '<br /><div align="center">'. _CATEGORY_ARCHIVE_EMPTY .'</div>';
		} else {
			BlogOutput_Archive( $rows, $params, $gid, $access, $pop, $menu, 1 );
		}

	 	echo '<input type="hidden" name="id" value="'. $id .'" />';
		echo '<input type="hidden" name="Itemid" value="'. $Itemid .'" />';
	 	echo '<input type="hidden" name="task" value="archivecategory" />';
	 	echo '<input type="hidden" name="option" value="com_content" />';
	 	echo '</form>';

		// SEO Meta Tags
		$mainframe->setPageMeta( $params->get( 'seo_title' ), $params->get( 'meta_key' ), $params->get( 'meta_descrip' ) );
	}


	function editCategory( $id, $gid, &$access, $task, $Itemid )
	{
		global $database, $mainframe, $my;
		global $mosConfig_live_site;
		global $_LANG;

		$images = NULL;
		$row = new mosCategory( $database );
		// load the row from the db table
		$row->load( $id );

		// fail if checked out not by 'me'
		if ( $row->checked_out && !( $row->checked_out == $my->id ) ) {
			$text = 'The Category [ '. $row->title .' ] is currently being edited by another person.';
			mosErrorAlert( $text );
		}

		// existing record
		if ( !( $access->canEdit ) ) {
			mosNotAuth();
			return;
		}

		$lists = array();

		// checkout item
		$row->checkout( $my->id );

		$states = array();
		// make the select list for the states
		$lists['state'] = mosHTML::yesnoRadioList( 'published', 'class="inputbox" size="1"', intval( $row->published ) );

		// build the html select list for the group access
		$lists['access'] = mosAdminMenus::Access( $row );

		// build the select list for the image positions
		$active =  ( $row->image_position ? $row->image_position : 'left' );
		$lists['image_position'] 	= mosAdminMenus::Positions( 'image_position', $active, NULL, 0 );
		// build the html select list for images
		$lists['image'] 			= mosAdminMenus::Images( 'image', $row->image );

		$row->return = intval( mosGetParam( $_REQUEST, 'Returnid', $Itemid ) );
		mosMakeHtmlSafe( $row );

		//toolbar css file
		$css = $mosConfig_live_site .'/includes/HTML_toolbar.css';
		$mainframe->addCustomHeadTag( '<link rel="stylesheet" href="'. $css .'" type="text/css" />' );

		mosFS::load( '@toolbar_front' );

		contentEditScreens_front::editCategory( $row, $task, $lists );
	}

	/**
	* Saves the Category
	*/
	function saveCategory( &$access, $task )
	{
		global $database, $my;
		global $_LANG;

		$row = new mosCategory( $database );
		if ( !$row->bind( $_POST ) ) {
			mosErrorAlert( $row->getError() );
		}

		// existing record
		if ( !( $access->canEdit ) ) {
			mosNotAuth();
			return;
		}

		if (!$row->check()) {
			mosErrorAlert( $row->getError() );
		}

		if (!$row->store()) {
			mosErrorAlert( $row->getError() );
		}

		$row->checkin();
		$row->updateOrder( "section='$row->section'" );

	 	$Itemid = mosGetParam( $_POST, 'Returnid', '0' );
	 	$msg 	= $_LANG->_( 'E_ITEM_SAVED' );
		switch ( $task ) {
			case 'save_categorypop':
				?>
				<script language="javascript" type="text/javascript">
				<!--
				onLoad = window.close( 'win1' )
				// reload main window
				opener.location.reload();
				//-->
				</script>
				<?php
				exit();
				break;

			case 'apply_categorypop':
				$link = sefRelToAbs( 'index2.php?option=com_content&task=edit_categorypop&id='. $row->id .'&Itemid='. $Itemid .'&Returnid='. $Itemid );
				mosRedirect( $link, $msg );

			case 'apply_category':
				$link = sefRelToAbs( 'index.php?option=com_content&task=edit_category&id='. $row->id .'&Itemid='. $Itemid .'&Returnid='. $Itemid );
				mosRedirect( $link, $msg );

			case 'save_category':
			default:
				$referer	= mosGetParam( $_POST, 'referer', '' );
				if ( $referer ) {
					mosRedirect( $referer, $msg );
				} else {
					$link 	= sefRelToAbs( 'index.php?option=com_content&task=category&sectionid='. $row->section .'&id='. $row->id .'&Itemid='. $Itemid );
					mosRedirect( $link, $msg );
				}
				break;
		}
	}

	/**
	* Cancels a Category edit operation
	* @param database A database connector object
	*/
	function cancelCategory( &$access, $task )
	{
		global $database, $my;

		$row = new mosCategory( $database );
		$row->bind( $_POST );

		if ( $access->canEdit || ( $access->canEditOwn && $row->created_by == $my->id ) ) {
			$row->checkin();
		}

		$Itemid = mosGetParam( $_POST, 'Returnid', '0' );

		if ( $task == 'cancel_categorypop' ) {
			?>
			<script language="javascript" type="text/javascript">
			<!--
			onLoad = window.close( 'win1' )
			// reload main window
			//opener.location.reload();
			//-->
			</script>
			<?php
			exit();
		} else {
			$referer	= mosGetParam( $_POST, 'referer', '' );
			if ( $referer && !strstr( $referer, 'task=edit' ) ) {
				mosRedirect( $referer, $msg );
			} else {
				if ( $Itemid ) {
					$link = sefRelToAbs( 'index.php?option=com_content&task=category&sectionid='. $row->section .'&id='. $row->id .'&Itemid='. $Itemid );
					mosRedirect( $link );
				} else {
					$link = sefRelToAbs( 'index.php' );
					mosRedirect( $link );
				}
			}
		}
	}
}
?>