<?php
/**
* @version $Id: components.class.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Components
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Components
 */
class mosComponentFactory {
	/**
	 * @return object A template installer object
	 */
	function &createInstaller() {
		mosFS::load( '/administrator/components/com_installer/installer.class.php' );
		mosFS::load( '/administrator/components/com_components/components.installer.php' );
		return new mosComponentInstaller();
	}

	/**
	 * build the select list to choose a component
	 * @return string
	 */
	function buildList( &$menu, $id ) {
		global $database;

		$query = "SELECT c.id AS value, c.name AS text, c.link"
		. "\n FROM #__components AS c"
		. "\n WHERE c.link <> ''"
		. "\n ORDER BY c.name"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList( );

		if ( $id ) {
			// existing component, just show name
			foreach ( $rows as $row ) {
				if ( $row->value == $menu->componentid ) {
					$component = $row->text;
				}
			}
			$component .= '<input type="hidden" name="componentid" value="'. $menu->componentid .'" />';
		} else {
			$component = mosHTML::selectList( $rows, 'componentid', 'class="inputbox" size="10"', 'value', 'text' );
		}
		return $component;
	}

	/**
	 * build the select list to choose a component
	 * @return string
	 */
	function getComponentName( &$menu, $id ) {
		global $database;

		$query = "SELECT c.id AS value, c.name AS text, c.link"
		. "\n FROM #__components AS c"
		. "\n WHERE c.link <> ''"
		. "\n ORDER BY c.name"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList( );

		$component = 'Component';
		foreach ( $rows as $row ) {
			if ( $row->value == $menu->componentid ) {
				$component = $row->text;
			}
		}

		return $component;
	}

	/**
	 * Select list of active categories for components
	 * @return string
	 */
	function buildCategoryList( $name, $section, $active=NULL, $javascript=NULL, $order='ordering', $size=1, $sel_cat=1 ) {
		global $database, $_LANG;

		$query = "SELECT id , name"
		. "\n FROM #__categories"
		. "\n WHERE section = '". $section ."'"
		. "\n AND published = '1'"
		. "\n ORDER BY ". $order
		;
		$database->setQuery( $query );
		if ( $sel_cat ) {
			$categories[] = mosHTML::makeOption( '0', '- ' . $_LANG->_( 'Category' ) . ' -', 'id', 'name' );
			$categories = array_merge( $categories, $database->loadObjectList() );
		} else {
			$categories = $database->loadObjectList();
		}

		if ( count( $categories ) < 1 ) {
			mosRedirect( 'index2.php?option=com_categories&section='. $section, 'You must create a category first.' );
		}

		$category = mosHTML::selectList( $categories, $name, 'class="inputbox" size="'. $size .'" '. $javascript, 'id', 'name', $active );

		return $category;
	}
}
?>