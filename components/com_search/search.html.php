<?php
/**
* @version $Id: search.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Search
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
 * @subpackage Search
 */
class searchScreens_front {
	/**
	 * @param string The main template file to include for output
	 * @param array An array of other standard files to include
	 * @return patTemplate A template object
	 */
	function &createTemplate( $bodyHtml='', $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );

		$directory = mosComponentDirectory( $bodyHtml, dirname( __FILE__ ) );
		$tmpl->setRoot( $directory );

		$tmpl->setAttribute( 'body', 'src', $bodyHtml );

		return $tmpl;
	}

	function displaylist( &$params, &$lists, &$areas, &$searches ) {

		$showAreas = mosGetParam( $lists, 'areas', array() );
		$allAreas = array();
		foreach ( $showAreas as $area ) {
			$allAreas = array_merge( $allAreas, $area );
		}
		$i = 0;
		$hasAreas = is_array( $areas );
		foreach ( $allAreas as $val => $txt ) {
			$checked = $hasAreas && in_array( $val, $areas ) ? 'checked="true"' : '';
			$rows[$i]->val 		= $val;
			$rows[$i]->txt 		= $txt;
			$rows[$i]->checked 	= $checked;
			$i++;
		}

		$tmpl =& searchScreens_front::createTemplate( 'list.html' );

		$tmpl->addObject( 'rows', $rows, 'row_' );
		$tmpl->addObject( 'searches', $searches, 'search_' );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}
}
?>