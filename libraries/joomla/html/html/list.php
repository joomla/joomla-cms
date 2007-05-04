<?php
/**
* @version		$Id: helper.php 7286 2007-05-03 01:44:29Z jinx $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Utility class for creating different select lists
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JHTMLList
{
   /**
	* Build the select list for access level
	*/
	function accesslevel( &$row )
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT id AS value, name AS text'
		. ' FROM #__groups'
		. ' ORDER BY id'
		;
		$db->setQuery( $query );
		$groups = $db->loadObjectList();
		$access = JHTML::_('select.genericlist',   $groups, 'access', 'class="inputbox" size="3"', 'value', 'text', intval( $row->access ), '', 1 );

		return $access;
	}

   /**
	* Build the select list to choose an image
	*/
	function images( $name, &$active, $javascript=NULL, $directory=NULL )
	{
		if ( !$directory ) {
			$directory = '/images/stories/';
		}

		if ( !$javascript ) {
			$javascript = "onchange=\"javascript:if (document.forms[0]." . $name . ".options[selectedIndex].value!='') {document.imagelib.src='..$directory' + document.forms[0]." . $name . ".options[selectedIndex].value} else {document.imagelib.src='../images/blank.png'}\"";
		}

		jimport( 'joomla.filesystem.folder' );
		$imageFiles = JFolder::files( JPATH_SITE.DS.$directory );
		$images 	= array(  JHTML::_('select.option',  '', '- '. JText::_( 'Select Image' ) .' -' ) );
		foreach ( $imageFiles as $file ) {
			if ( eregi( "bmp|gif|jpg|png", $file ) ) {
				$images[] = JHTML::_('select.option',  $file );
			}
		}
		$images = JHTML::_('select.genericlist',   $images, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

		return $images;
	}

	/**
	 * Description
	 *
 	 * @param string SQL with ordering As value and 'name field' AS text
 	 * @param integer The length of the truncated headline
 	 * @since 1.5
 	 */
	function genericordering( $sql, $chop='30' )
	{
		$db =& JFactory::getDBO();
		$order = array();
		$db->setQuery( $sql );
		if (!($orders = $db->loadObjectList())) {
			if ($db->getErrorNum()) {
				echo $db->stderr();
				return false;
			} else {
				$order[] = JHTML::_('select.option',  1, JText::_( 'first' ) );
				return $order;
			}
		}
		$order[] = JHTML::_('select.option',  0, '0 '. JText::_( 'first' ) );
		for ($i=0, $n=count( $orders ); $i < $n; $i++) {

			if (JString::strlen($orders[$i]->text) > $chop) {
				$text = JString::substr($orders[$i]->text,0,$chop)."...";
			} else {
				$text = $orders[$i]->text;
			}

			$order[] = JHTML::_('select.option',  $orders[$i]->value, $orders[$i]->value.' ('.$text.')' );
		}
		$order[] = JHTML::_('select.option',  $orders[$i-1]->value+1, ($orders[$i-1]->value+1).' '. JText::_( 'last' ) );

		return $order;
	}

   /**
	* Build the select list for Ordering of a specified Table
	*/
	function specificordering( &$row, $id, $query, $neworder=0 )
	{
		$db =& JFactory::getDBO();

		if ( $id ) {
			$order = JHTML::_('list.genericordering',  $query );
			$ordering = JHTML::_('select.genericlist',   $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ) );
		} else {
			if ( $neworder ) {
				$text = JText::_( 'descNewItemsFirst' );
			} else {
				$text = JText::_( 'descNewItemsLast' );
			}
			$ordering = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. $text;
		}
		return $ordering;
	}

   /**
	* Select list of active users
	*/
	function users( $name, $active, $nouser=0, $javascript=NULL, $order='name', $reg=1 )
	{
		$db =& JFactory::getDBO();

		$and = '';
		if ( $reg ) {
		// does not include registered users in the list
			$and = ' AND gid > 18';
		}

		$query = 'SELECT id AS value, name AS text'
		. ' FROM #__users'
		. ' WHERE block = 0'
		. $and
		. ' ORDER BY '. $order
		;
		$db->setQuery( $query );
		if ( $nouser ) {
			$users[] = JHTML::_('select.option',  '0', '- '. JText::_( 'No User' ) .' -' );
			$users = array_merge( $users, $db->loadObjectList() );
		} else {
			$users = $db->loadObjectList();
		}

		$users = JHTML::_('select.genericlist',   $users, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

		return $users;
	}

   /**
	* Select list of positions - generally used for location of images
	*/
	function positions( $name, $active=NULL, $javascript=NULL, $none=1, $center=1, $left=1, $right=1, $id=false )
	{
		if ( $none ) {
			$pos[] = JHTML::_('select.option',  '', JText::_( 'None' ) );
		}
		if ( $center ) {
			$pos[] = JHTML::_('select.option',  'center', JText::_( 'Center' ) );
		}
		if ( $left ) {
			$pos[] = JHTML::_('select.option',  'left', JText::_( 'Left' ) );
		}
		if ( $right ) {
			$pos[] = JHTML::_('select.option',  'right', JText::_( 'Right' ) );
		}

		$positions = JHTML::_('select.genericlist',   $pos, $name, 'class="inputbox" size="1"'. $javascript, 'value', 'text', $active, $id );

		return $positions;
	}

   /**
	* Select list of active categories for components
	*/
	function category( $name, $section, $active=NULL, $javascript=NULL, $order='ordering', $size=1, $sel_cat=1 )
	{
		global $mainframe;

		$db =& JFactory::getDBO();

		$query = 'SELECT id AS value, title AS text'
		. ' FROM #__categories'
		. ' WHERE section = "'. $section . '"'
		. ' AND published = 1'
		. ' ORDER BY '. $order
		;
		$db->setQuery( $query );
		if ( $sel_cat ) {
			$categories[] = JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Category' ) .' -' );
			$categories = array_merge( $categories, $db->loadObjectList() );
		} else {
			$categories = $db->loadObjectList();
		}

		if ( count( $categories ) < 1 ) {
			$mainframe->redirect( 'index.php?option=com_categories&section='. $section, JText::_( 'You must create a category first.' ) );
		}

		$category = JHTML::_('select.genericlist',   $categories, $name, 'class="inputbox" size="'. $size .'" '. $javascript, 'value', 'text', $active );

		return $category;
	}

   /**
	* Select list of active sections
	*/
	function section( $name, $active=NULL, $javascript=NULL, $order='ordering' )
	{
		$db =& JFactory::getDBO();

		$categories[] = JHTML::_('select.option',  '-1', '- '. JText::_( 'Select Section' ) .' -' );
		$categories[] = JHTML::_('select.option',  '0', JText::_( 'Uncategorized' ) );
		$query = 'SELECT id AS value, title AS text'
		. ' FROM #__sections'
		. ' WHERE published = 1'
		. ' ORDER BY ' . $order
		;
		$db->setQuery( $query );
		$sections = array_merge( $categories, $db->loadObjectList() );

		$category = JHTML::_('select.genericlist',   $sections, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

		return $category;
	}
}

?>