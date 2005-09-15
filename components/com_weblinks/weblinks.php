<?php
/**
* @version $Id: weblinks.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Weblinks
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Weblinks
 * @subpackage Weblinks
 */
class weblinksTasks_front extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function weblinksTasks_front() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'display' );

		// set task level access control
		//$this->setAccessControl( 'com_templates', 'manage' );

		// additional mappings
		$this->registerTask( 'new', 'edit' );
	}

	/**
	 * Displays the list of weblinks and a category table
	 */
	function display() {
		global $mainframe, $database, $_LANG;
		global $mosConfig_live_site;
		global $Itemid;

		mosFS::load( '@class' );
		mosFS::load( '@front_html' );

		$catid 		= intval( mosGetParam( $_REQUEST, 'catid', 0 ) );
		$orderCol	= mosGetParam( $_REQUEST, 'orderCol' );
		$orderDirn	= mosGetParam( $_REQUEST, 'orderDirn', 0 );
		if (empty( $orderCol )) {
			$orderCol = 'ordering';
		}

		$vars = array(
			'orderCol' 	=> $orderCol,
			'orderDirn' => $orderDirn,
			'catid' 	=> $catid,
		);

		$rows 			= array();
		$currentcat 	= NULL;
		$vars['type'] 	= 'section';
		$vars['catid'] 	= $catid;

		$weblinks = new mosWeblink( $database );

		// Query to retrieve all categories that belong under the web links
		// section and that are published.
		$categories = $weblinks->getView( 'categories', array( 'published' => 1 ) );
		$count = count( $categories );
		for ( $i = 0; $i < $count; $i++ ) {
			$link 					= 'index.php?option=com_weblinks&amp;catid='. $categories[$i]->id .'&amp;Itemid='. $Itemid;
			$categories[$i]->link	= sefRelToAbs( $link );
		}

		// Parameters
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );

		$params->def( 'page_title', 			1 );
		$params->def( 'pageclass_sfx', 			'' );
		$params->def( 'item_description',		1 );
		$params->def( 'description', 			1 );
		$params->def( 'image', 					-1 );
		$params->def( 'image_align', 			'right' );
		$params->def( 'other_cat_section', 		1 );
		// Category List Display control
		$params->def( 'other_cat',				1 );
		$params->def( 'cat_description', 		1 );
		$params->def( 'cat_items', 				1 );
		// Table Display control
		$params->def( 'headings', 				1 );
		$params->def( 'weblink_icons', 			'' );
		$params->def( 'image_align', 			'right' );
		$params->def( 'meta_key', 				'' );
		$params->def( 'meta_descrip', 			'' );

		$params->def( 'header', 				$menu->name );
		$params->def( 'hits', 					$mainframe->getCfg( 'hits' ) );
		$params->def( 'description_text', 		'' );
		$params->def( 'back_button', 			$mainframe->getCfg( 'back_button' ) );
		$params->def( 'seo_title', 				$menu->name );

		if ( $catid ) {
			// if url contains a `catid` value, than the page is Table - Weblink Category

			$orderCol 	= $database->getEscaped( $orderCol );
			$orderDirn 	= $orderDirn ? 'DESC' : 'ASC';

			$options = array(
				'catid' => $catid,
				'orderby' => $orderCol . ' ' . $orderDirn
			);
			$rows = $weblinks->getView( 'items', $options );

			// info for current category
			$total = count( $categories );
			for ($i = 0; $i < $total; $i++) {
				if ($categories[$i]->id == $catid) {
					$currentcat = &$categories[$i];

					// use the image from the category
					if ($currentcat->image <> '') {
						$params->set( 'image', $currentcat->image );
						$params->set( 'image_position', $currentcat->image_position );
					}
					if ($currentcat->description <> '') {
						$params->set( 'description_text', $currentcat->description );
					}
					if ($currentcat->name <> '') {
						$params->set( 'header', $currentcat->name );
					}
					break;
				}
			}

			// table data
			if ( $params->get( 'weblink_icons' ) <> -1 ) {
				$row->img = mosAdminMenus::ImageCheck( 'weblink.png', '/images/M_images/', $params->get( 'weblink_icons' ) );
			} else {
				$row->img = NULL;
			}

			// used to show table rows in alternating colours
			$count = count( $rows );
			$n = 0;
			for ( $i = 0; $i < $count; $i++ ) {
				$row = &$rows[$i];
				$iparams = new mosParameters( $row->params );

				$link = sefRelToAbs( 'index.php?option=com_weblinks&amp;task=view&amp;catid='. $catid .'&amp;id='. $rows[$i]->id );
				switch ($iparams->get( 'target' )) {
					case 1:
						// open in a new window
						$row->target = '_blank';
						$row->href = $link;
						break;

					case 2:
						// open in a popup window
						$row->target = '_top';
						$row->href = '#';
						$row->onclick = 'window.open(\''. $link .'\', \'\', \'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550\'); return false';
						break;

					default:	// formerly case 2
						// open in parent window
						$row->target = '_top';
						$row->href = $link;
						break;
				}
				$link 		= 'index.php?option=com_newsfeeds&amp;task=view&amp;feedid='. $rows[$i]->id .'&amp;Itemid='. $Itemid;
				$row->url	= sefRelToAbs( $link );
			}
		}

/*
		$params->set( 'cat', 0 );
		if ( ( $vars['type'] == 'category' ) && $params->get( 'other_cat' ) ) {
			$params->set( 'cat', 1 );
		} else if ( ( $vars['type'] == 'section' ) && $params->get( 'other_cat_section' ) ) {
			$params->set( 'cat', 1 );
		}
*/
/*
		// page description
		$currentcat->descrip = '';
		if( ( @$currentcat->description ) <> '' ) {
			$currentcat->descrip = $currentcat->description;
		} else if ( !$catid ) {
			// show description
			if ( $params->get( 'description' ) ) {
				$currentcat->descrip = $params->get( 'description_text' );
			}
		}
	*/
		// page image
/*
		$currentcat->img = '';
		if ( ( @$currentcat->image ) <> '' ) {
			$vars['categoryImg'] 	= $currentcat->image;
			$vars['categoryAlign'] 	= $currentcat->image_position;
		} else if ( !$catid ) {
			if ( $params->get( 'image' ) <> -1 ) {
				$vars['categoryImg'] = $params->get( 'image' );
				$vars['categoryAlign'] = $params->get( 'image_align' );
			}
		}
*/
/*
		// page header
		$currentcat->header = '';
		if ( @$currentcat->name <> '' ) {
			$currentcat->header = $currentcat->name;
		} else {
			$currentcat->header = $params->get( 'header' );
		}
*/
		weblinksScreens::displayList( $params, $currentcat, $rows, $categories, $vars );
	}

	function view() {
		global $database;

		$id 	= intval( mosGetParam( $_REQUEST, 'id', 0 ) );

		//Record the hit
		$query = "UPDATE #__weblinks"
		. "\n SET hits = hits + 1"
		. "\n WHERE id = '$id'"
		;
		$database->setQuery( $query );
		$database->query();

		$query = "SELECT url"
		. "\n FROM #__weblinks"
		. "\n WHERE id = '$id'"
		;
		$database->setQuery( $query );
		$url = $database->loadResult();

		mosRedirect ( $url );
	}

	function edit() {
		global $database, $my, $option, $mainframe;
		global $mosConfig_live_site;
		global $_LANG;

		$id = intval( mosGetParam( $_REQUEST, 'id', 0 ) );

		if ( $my->gid < 1 ) {
			mosNotAuth();
			return;
		}

		mosFS::load( '@front_html' );
		mosFS::load( '@class', 'com_components' );
		mosFS::load( '@class' );
		mosFS::load( '@toolbar_front' );

		$row = new mosWeblink( $database );
		// load the row from the db table
		$row->load( $id );

		// fail if checked out not by 'me'
		if ( $row->isCheckedOut() ) {
			$msg = $row->title . $_LANG->_( 'descBeingEditted' );
			mosRedirect( 'index2.php?option='. $option, $msg );
		}

		if ( $id ) {
			$row->checkout( $my->id );
		} else {
			// initialise new record
			$row->published 	= 0;
			$row->approved 		= 0;
			$row->ordering 		= 0;
		}

		$row->title 		= htmlspecialchars( $row->title, ENT_QUOTES );
		$row->description 	= htmlspecialchars( $row->description, ENT_QUOTES );

		$file = $mainframe->getPath( 'com_xml', 'com_weblinks' );
		$params = new mosParameters( $row->params, $file, 'component' );

		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );

		$lists['return'] 	= intval( mosGetParam( $_REQUEST, 'Returnid', 0 ) );
		// build list of categories
		$lists['catid'] 	= mosComponentFactory::buildCategoryList( 'catid', $option, intval( $row->catid ) );

		//toolbar css file
		$css = $mosConfig_live_site .'/includes/HTML_toolbar.css';
		$mainframe->addCustomHeadTag( '<link rel="stylesheet" href="'. $css .'" type="text/css" />' );

		weblinksScreens::edit( $row, $lists, $params );
	}

	/**
	* Saves the record on an edit form submit
	* @param database A database connector object
	*/
	function save() {
		global $database, $my, $option;
		global $_LANG;

		if ($my->gid < 1) {
			mosNotAuth();
			return;
		}

		mosFS::load( '@class' );

		$row = new mosWeblink( $database );
		if (!$row->bind( $_POST, 'approved published' )) {
			mosErrorAlert( $row->getError() );
		}
		$isNew = $row->id < 1;

		$row->published = 0;
		$row->approved = 0;

		$row->date = date( 'Y-m-d H:i:s' );
		if (!$row->check()) {
			mosErrorAlert( $row->getError() );
		}
		if (!$row->store()) {
			mosErrorAlert( $row->getError() );
		}
		$row->checkin();

		/** Notify admin's */
		$query = "SELECT email, name"
		. "\n FROM #__users"
		. "\n WHERE gid = 25"
		. "\n AND sendemail = '1'"
		;
		$database->setQuery( $query );
		if(!$database->query()) {
			mosErrorAlert( $database->stderr() );
		}

		$adminRows = $database->loadObjectList();
		foreach( $adminRows as $adminRow) {
			mosSendAdminMail($adminRow->name, $adminRow->email, '', 'Weblink', $row->title, $my->username );
		}

		$msg 	= $isNew ? $_LANG->_( 'THANK_SUB' ) : '';
		$Itemid = mosGetParam( $_POST, 'Returnid', '' );
		mosRedirect( 'index.php?Itemid='. $Itemid, $msg );
	}

	function cancel() {
		global $database, $my;

		if ( $my->gid < 1 ) {
			mosNotAuth();
			return;
		}

		mosFS::load( '@class' );

		$row = new mosWeblink( $database );
		$row->id = intval( mosGetParam( $_POST, 'id', 0 ) );
		$row->checkin();
		$Itemid = mosGetParam( $_POST, 'Returnid', '' );

		mosRedirect( 'index.php?Itemid='. $Itemid );
	}
}
$tasker = new weblinksTasks_front();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();
?>