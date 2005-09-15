<?php
/**
 * Support functions for Language Manager
 * @version $Id: mambots.class.php 137 2005-09-12 10:21:17Z eddieajau $
 * @package Joomla
 * @subpackage Mambots
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Template factory class
 * @package Joomla
 * @subpackage Templates
 */
class mosMambotFactory {
	/**
	 * @return object A template installer object
	 */
	function &createInstaller() {
		mosFS::load( '/administrator/components/com_installer/installer.class.php' );
		mosFS::load( '/administrator/components/com_mambots/mambots.installer.php' );

		return new mosMambotInstaller();
	}
}

/**
 * @package Joomla
 * @subpackage Mambots
 */
class mosMambotViews extends mosMambot {
	/**
	 * Retrieves a data view.
	 * @param string The view name
	 * @param array
	 * @param boolean
	 * @return mixed array of results or count
	 */
	function getView( $view, $options=array(), $countOnly=false ) {
		global $my;

		$wheres = array();

		if ( $folder = mosGetParam( $options, 'folder' ) ) {
			$wheres[] = "m.folder = '$folder'";
		}

		if ( $search = mosGetParam( $options, 'search' ) ) {
			$wheres[] = "LOWER( m.name ) LIKE '%$search%'";
		}

		if ( $filter_state = mosGetParam( $options, 'state' ) ) {
			$wheres[] = "m.published = '$filter_state'";
		}

		if ( $filter_access = mosGetParam( $options, 'access' ) ) {
			$wheres[] = "m.access = '$filter_access'";
		}

		switch ( $view ) {
			case 'items':
				$where = (count( $wheres ) > 0 ? "\n WHERE " . implode( ' AND ', $wheres ) : '');

				if ( $countOnly ) {
					$query = "SELECT COUNT( id )"
					. "\n FROM #__mambots AS m "
					. $where
					;
					$this->_db->setQuery( $query );

					return $this->_db->loadResult();
				}

				$orderby = mosGetParam( $options, 'orderby' );

				if ( empty( $orderby ) ) {
					$orderby = 'm.ordering';
				} else {
					$orderby .= ', m.ordering';
				}

				$query = "SELECT m.*, u.name AS editor, g.name AS groupname"
				. "\n FROM #__mambots AS m"
				. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
				. "\n LEFT JOIN #__groups AS g ON g.id = m.access"
				. $where
				. "\n GROUP BY m.id"
				. ( $orderby ? "\n ORDER BY " . $orderby : '' )
				;
				break;

			case 'folders':
				$query = "SELECT folder"
				. "\n FROM #__mambots"
				//. "\n WHERE client_id = '$client_id'"
				. "\n GROUP BY folder"
				. "\n ORDER BY folder"
				;
				break;

			default:
				break;
		}

		$limitstart = mosGetParam( $options, 'limitstart', 0 );
		$limit 		= mosGetParam( $options, 'limit', 0 );

		$this->_db->setQuery( $query, $limitstart, $limit );

		return $this->_db->loadObjectList();
	}
}
?>