<?php
/**
* @version $Id: content.section.php 137 2005-09-12 10:21:17Z eddieajau $
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

class comContentSection {

	function show( $id, $gid, &$access, $pop, $option, $Itemid, $task )
	{
		$this->_display( $id, $gid, $access, $pop, $option, $Itemid, $task );
	}

	function _display( $id, $gid, &$access, $pop, $option, $Itemid, $task )
	{
		switch ( strtolower( $task ) )
		{
			case 'apply_sectionpop':
			case 'save_sectionpop':
			case 'apply_section':
			case 'save_section':
				mosCache::cleanCache( 'com_content' );
				$this->saveSection( $access, $task );
				break;

			case 'cancel_sectionpop':
			case 'cancel_section':
				$this->cancelSection( $access, $task );
				break;

			case 'blogsection':
				mosFS::load( 'components/com_content/content.html.php' );
				$this->showBlogSection( $id, $gid, $access, $pop );
				break;

			case 'archivesection':
				mosFS::load( 'components/com_content/content.html.php' );
				$this->showArchiveSection( $id, $gid, $access, $pop, $option );
				break;

			case 'edit_section':
			case 'edit_sectionpop':
				mosFS::load( 'components/com_content/content.edit.html.php' );
				$this->editSection( $id, $gid, $access, $task, $Itemid );
				break;

			case 'section':
			default:
				mosFS::load( 'components/com_content/content.html.php' );
				$this->showSection( $id, $gid, $access, $task );
				break;
		}
	}

	function showSection( $id, $gid, &$access, $task )
	{
		global $database, $mainframe, $Itemid, $mosConfig_zero_date;
		global $_POST;

		mosFS::load( 'components/com_content/content.utils.php' );
		$ccUtils = new comContentUtils();

		$noauth = !$mainframe->getCfg( 'shownoauth' );
		$now 	= $mainframe->getDateTime();

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

		$params->set( 'type', 				'section' );
		$params->def( 'page_title', 		1 );
		$params->def( 'pageclass_sfx', 		'' );
		$params->def( 'description', 		1 );
		$params->def( 'description_image', 	1 );
		$params->def( 'other_cat_section', 	1 );
		$params->def( 'other_cat', 			1 );
		$params->def( 'empty_cat', 			0 );
		$params->def( 'cat_items', 			1 );
		$params->def( 'cat_description', 	1 );
		$params->def( 'back_button', 		$mainframe->getCfg( 'back_button' ) );
		$params->def( 'pageclass_sfx', 		'' );
		$params->def( 'meta_key', 			'' );
		$params->def( 'meta_descrip', 		'' );
		$params->def( 'seo_title', 			$menu->name );

		// Ordering control
		$orderby = $params->get( 'orderby', '' );
		$orderby = $ccUtils->orderby_sec( $orderby );

		// Load Section information
		$section = new mosSection( $database );
		$section->load( $id );

		// group level for section
		switch ( $section->access ) {
			case 1:
				$section->groups = 'Registered';
				break;

			case 2:
				$section->groups = 'Special';
				break;

			default:
				$section->groups = 'Public';
				break;
		}

		if ( $access->canEdit ) {
			$xwhere = '';
			$xwhere2 = "\n AND b.state >= '0'";
		} else {
			$xwhere = "\n AND a.published = '1'";
			$xwhere2 = "\n AND b.state = '1'";
			$xwhere2 .= "\n AND ( b.publish_up = '$mosConfig_zero_date' OR b.publish_up <= '$now' )";
			$xwhere2 .= "\n AND ( b.publish_down = '$mosConfig_zero_date' OR b.publish_down >= '$now' )";
		}

		// show/hide empty categories
		$empty = '';
		if ( !$params->get( 'empty_cat' ) ) {
			$empty = "\n HAVING COUNT( b.id ) > 0";
		}

		// Category List
		$query = "SELECT a.*, COUNT( b.id ) AS numitems"
		. "\n FROM #__categories AS a"
		. "\n LEFT JOIN #__content AS b ON b.catid = a.id"
		. $xwhere2
		. ( $noauth ? "\n AND b.access <= '$gid'" : '' )
		. "\n WHERE a.section = '$section->id'"
		. $xwhere
		. ( $noauth ? "\n AND a.access <= '$gid'" : '' )
		. "\n GROUP BY a.id"
		. $empty
		. "\n ORDER BY $orderby";
		$database->setQuery( $query );
		$other_categories = $database->loadObjectList();

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
		$section->text = $section->description;

		contentScreens_front::list_section( $params, $section, $other_categories, $access );
	}

	function showBlogSection( $id=0, $gid, &$access, $pop )
	{
		global $database, $mainframe, $Itemid;

		mosFS::load( 'components/com_content/content.utils.php' );
		$ccUtils = new comContentUtils();

		mosFS::load( 'components/com_content/content.item.php' );
		$ccItem = new comContentItem();

		$now 	= $mainframe->getDateTime();
		$noauth = !$mainframe->getCfg( 'shownoauth' );

		// Parameters
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
			$id		= $params->def( 'sectionid', 0 );
		}

		$params->def( 'meta_key', 		'' );
		$params->def( 'meta_descrip', 	'' );
		$params->def( 'seo_title', 		$menu->name );

		$where = $ccUtils->where( 1, $access, $noauth, $gid, $id, $now );

		// Ordering control
		$orderby_sec 	= $params->def( 'orderby_sec', 'rdate' );
		$orderby_pri 	= $params->def( 'orderby_pri', '' );
		$order_sec 		= $ccUtils->orderby_sec( $orderby_sec );
		$order_pri 		= $ccUtils->orderby_pri( $orderby_pri );

		$where = ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );

		// Main data query
		$query = "SELECT a.*, v.rating_sum, v.rating_count, u.name AS author, u.usertype, cc.name AS category, g.name AS groups"
		. "\n FROM #__content AS a"
		. "\n INNER JOIN #__categories AS cc ON cc.id = a.catid"
		. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
		. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
		. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id"
		. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
		. $where
		. "\n AND s.access<=$gid"
		. "\n ORDER BY ". $order_pri . $order_sec
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();

		// SEO Meta Tags
		$mainframe->setPageMeta( $params->get( 'seo_title' ), $params->get( 'meta_key' ), $params->get( 'meta_descrip' ) );
		$ccItem->BlogOutput( $rows, $params, $gid, $access, $pop, $menu );
	}

	function showArchiveSection( $id=NULL, $gid, &$access, $pop, $option )
	{
		global $database, $mainframe;
		global $Itemid, $_LANG;

		mosFS::load( 'components/com_content/content.data.php' );
		$ccData = new comContentData();

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
			$menu = '';
			$params = new mosParameters( '' );
		}

		$params->set( 'intro_only', 1 );
		$params->set( 'year', 		$year );
		$params->set( 'month', 		$month );

		$params->def( 'meta_key', 		'' );
		$params->def( 'meta_descrip', 	'' );
		$params->def( 'seo_title', 		$menu->name );

		// Ordering control
		$orderby_sec = $params->def( 'orderby_sec', 'rdate' );
		$orderby_pri = $params->def( 'orderby_pri', '' );
		$order_sec = _orderby_sec( $orderby_sec );
		$order_pri = _orderby_pri( $orderby_pri );

		// used in query
		$where = _where( -1, $access, $noauth, $gid, $id, NULL, $year, $month );

		$where = ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );

		// checks to see if 'All Sections' options used
		if ( $id == 0 ) {
			$check = '';
		} else {
			$check = 'AND a.sectionid = '. $id ;
		}
		// query to determine if there are any archived entries for the section
		$query = 	"SELECT a.id"
		. "\n FROM #__content as a"
		. "\n WHERE a.state = '-1'"
		. $check
		;
		$database->setQuery( $query );
		$items = $database->loadObjectList();
		$archives = count( $items );

		// Main Query
		$query = "SELECT a.*, v.rating_sum, v.rating_count, u.name AS author, u.usertype, cc.name AS category, g.name AS groups"
		. "\n FROM #__content AS a"
		. "\n INNER JOIN #__categories AS cc ON cc.id = a.catid"
		. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
		. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
		. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id"
		. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
		. $where
		. "\n AND s.access <= ". $gid
		. "\n ORDER BY ". $order_pri . $order_sec
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();

		// initiate form
	 	echo '<form action="'.sefRelToAbs( 'index.php').'" method="post">';

		if ( !$archives ) {
			// if no archives for category, hides search and outputs empty message
			echo '<br /><div align="center">'. $_LANG->_( 'CATEGORY_ARCHIVE_EMPTY' ) .'</div>';
		} else {
			$ccData->BlogOutput_Archive( $rows, $params, $gid, $access, $pop, $menu, 1 );
		}

	 	echo '<input type="hidden" name="id" value="'. $id .'" />';
		echo '<input type="hidden" name="Itemid" value="'. $Itemid .'" />';
	 	echo '<input type="hidden" name="task" value="archivesection" />';
	 	echo '<input type="hidden" name="option" value="com_content" />';
	 	echo '</form>';

		// SEO Meta Tags
		$mainframe->setPageMeta( $params->get( 'seo_title' ), $params->get( 'meta_key' ), $params->get( 'meta_descrip' ) );
	}

	function editSection( $id, $gid, &$access, $task, $Itemid )
	{
		global $database, $mainframe, $my;
		global $mosConfig_live_site;
		global $_LANG;

		$row = new mosSection( $database );
		// load the row from the db table
		$row->load( $id );

		// fail if checked out not by 'me'
		if ( $row->checked_out && !( $row->checked_out == $my->id ) ) {
			$text = 'The Section [ '. $row->title .' ] is currently being edited by another person.';
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

		$images = NULL;

		$row->return = intval( mosGetParam( $_REQUEST, 'Returnid', $Itemid ) );
		mosMakeHtmlSafe( $row );

		//toolbar css file
		$css = $mosConfig_live_site .'/includes/HTML_toolbar.css';
		$mainframe->addCustomHeadTag( '<link rel="stylesheet" href="'. $css .'" type="text/css" />' );

		mosFS::load( '@toolbar_front' );

		contentEditScreens_front::editSection( $row, $task, $lists );
	}

	/**
	* Saves the Section
	*/
	function saveSection( &$access, $task )
	{
		global $database, $my;
		global $_LANG;

		$row = new mosSection( $database );
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
		$row->updateOrder( "scope='$row->scope'" );

	 	$Itemid = mosGetParam( $_POST, 'Returnid', '0' );
	 	$msg 	= $_LANG->_( 'E_ITEM_SAVED' );
		switch ( $task ) {
			case 'save_sectionpop':
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

			case 'apply_sectionpop':
				$link = sefRelToAbs( 'index2.php?option=com_content&task=edit_sectionpop&id='. $row->id .'&Itemid='. $Itemid .'&Returnid='. $Itemid );
				mosRedirect( $link, $msg );

			case 'apply_section':
				$link = sefRelToAbs( 'index.php?option=com_content&task=edit_section&id='. $row->id .'&Itemid='. $Itemid .'&Returnid='. $Itemid );
				mosRedirect( $link, $msg );

			case 'save_section':
			default:
				$referer	= mosGetParam( $_POST, 'referer', '' );
				if ( $referer ) {
					mosRedirect( $referer, $msg );
				} else {
					$link 	= sefRelToAbs( 'index.php?option=com_content&task=section&id='. $row->id .'&Itemid='. $Itemid );
					mosRedirect( $link, $msg );
				}
				break;
		}
	}

	/**
	* Cancels a Section edit operation
	* @param database A database connector object
	*/
	function cancelSection( &$access, $task )
	{
		global $database, $my;

		$row = new mosSection( $database );
		$row->bind( $_POST );

		if ( $access->canEdit || ( $access->canEditOwn && $row->created_by == $my->id ) ) {
			$row->checkin();
		}

		$Itemid = mosGetParam( $_POST, 'Returnid', '0' );

		if ( $task == 'cancel_sectionpop' ) {
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
			if ( $Itemid ) {
				$link = sefRelToAbs( 'index.php?option=com_content&task=section&id='. $row->id .'&Itemid='. $Itemid );
				mosRedirect( $link );
			} else {
				$link = sefRelToAbs( 'index.php' );
				mosRedirect( $link );
			}
		}
	}
}
?>