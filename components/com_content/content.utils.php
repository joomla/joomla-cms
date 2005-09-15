<?php
/**
* @version $Id: content.utils.php 137 2005-09-12 10:21:17Z eddieajau $
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

class comContentUtils {

	/**
	* build the select list to choose an image
	*/
	function Images( $name, &$active, $javascript=NULL, $directory=NULL )
	{
		global $mosConfig_absolute_path, $_LANG;

		$javascript = "onchange=\"javascript:if (document.forms.image.options[selectedIndex].value!='') {document.imagelib.src='$mosConfig_absolute_path/images/stories/' + document.forms.image.options[selectedIndex].value} else {document.imagelib.src='$mosConfig_absolute_path/images/blank.png'}\"";
		$directory 	= '/images/stories';

		$imageFiles = mosReadDirectory( $mosConfig_absolute_path . $directory );
		$images = array(  mosHTML::makeOption( '', '- '. $_LANG->_( 'Select Image' ) .' -' ) );
		foreach ( $imageFiles as $file ) {
			if ( eregi( "\.(bmp|gif|jpg|png)$", $file ) ) {
				$images[] = mosHTML::makeOption( $file );
			}
		}
		$images = mosHTML::selectList( $images, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

		return $images;
	}

	function is_email( $email )
	{
		$rBool = false;

		if ( preg_match( "/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $email ) ) {
			$rBool = true;
		}
		return $rBool;
	}

	function orderby_pri( $orderby )
	{
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


	function orderby_sec( $orderby )
	{
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
				$orderby = 'a.hits DESC';
				break;

			case 'rhits':
				$orderby = 'a.hits ASC';
				break;

			case 'order':
				$orderby = 'a.ordering';
				break;

			case 'author':
				$orderby = 'a.created_by, u.name';
				break;

			case 'rauthor':
				$orderby = 'a.created_by DESC, u.name DESC';
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


	// @param int 0 = Archives, 1 = Section, 2 = Category
	function where( $type=1, &$access, &$noauth, $gid, $id, $now=NULL, $year=NULL, $month=NULL )
	{
		global $mosConfig_zero_date;
		$where = array();

		// normal
		if ( $type > 0) {
			$where[] = "a.state = '1'";
			if ( !$access->canEdit ) {
				$where[] = "( a.publish_up = '$mosConfig_zero_date' OR a.publish_up <= '". $now ."' )";
				$where[] = "( a.publish_down = '$mosConfig_zero_date' OR a.publish_down >= '". $now ."' )";
			}
			if ( $noauth ) {
				$where[] = "a.access <= '". $gid ."'";
			}
			if ( $id > 0 ) {
				if ( $type == 1 ) {
					$where[] = "a.sectionid IN ( ". $id ." ) ";
				} else if ( $type == 2 ) {
					$where[] = "a.catid IN ( ". $id ." ) ";
				}
			}
		}

		// archive
		if ( $type < 0 ) {
			$where[] = "a.state='-1'";
			if ( $year ) {
				$where[] = "YEAR( a.created ) = '". $year ."'";
			}
			if ( $month ) {
				$where[] = "MONTH( a.created ) = '". $month ."'";
			}
			if ( $noauth ) {
				$where[] = "a.access <= '". $gid ."'";
			}
			if ( $id > 0 ) {
				if ( $type == -1 ) {
					$where[] = "a.sectionid = '". $id ."'";
				} else if ( $type == -2) {
					$where[] = "a.catid = '". $id ."'";
				}
			}
		}

		return $where;
	}

	function frontpage( $gid, &$access, $pop )
	{
		global $database, $mainframe, $my, $Itemid;
		global $mosConfig_zero_date;

		mosFS::load( 'components/com_content/content.item.php' );
		$ccItem = new comContentItem();

		$now 	= $mainframe->getDateTime();
		$noauth = !$mainframe->getCfg( 'shownoauth' );

		// Parameters
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );
		$orderby_sec 	= $params->def( 'orderby_sec', 	'' );
		$orderby_pri 	= $params->def( 'orderby_pri', 	'' );

		$params->def( 'meta_key', 		'' );
		$params->def( 'meta_descrip', 	'' );
		$params->def( 'seo_title', 		$menu->name );

		// Ordering control
		$order_sec = $this->orderby_sec( $orderby_sec );
		$order_pri = $this->orderby_pri( $orderby_pri );

		// query records
		$query = "SELECT a.*, v.rating_sum, v.rating_count, u.name AS author, u.usertype, s.name AS section, cc.name AS category, g.name AS groups"
		. "\n FROM #__content AS a"
		. "\n INNER JOIN #__content_frontpage AS f ON f.content_id = a.id"
		. "\n LEFT JOIN #__categories AS cc ON cc.id = a.catid"
		. "\n LEFT JOIN #__sections AS s ON s.id = a.sectionid"
		. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
		. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
		. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
		. "\n WHERE a.state = '1'"
		. ( $noauth ? "\n AND a.access <= '$my->gid'" : '' )
		. "\n AND ( publish_up = '$mosConfig_zero_date' OR publish_up <= '$now'  )"
		. "\n AND ( publish_down = '$mosConfig_zero_date' OR publish_down >= '$now' )"
		. "\n ORDER BY ". $order_pri . $order_sec
		;

		$database->setQuery( $query );
		$rows = $database->loadObjectList();

		// SEO Meta Tags
		$mainframe->setPageMeta( $params->get( 'seo_title' ), $params->get( 'meta_key' ), $params->get( 'meta_descrip' ) );
		$ccItem->BlogOutput( $rows, $params, $gid, $access, $pop, $menu );
	}
}
?>
