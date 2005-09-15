<?php
/**
* @version $Id: poll.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Polls
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
 * @subpackage Polls
 */
class pollScreens_front {
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

	function displaylist( &$params, &$poll, &$rows ) {
		global $_LANG;

		$tmpl =& pollScreens_front::createTemplate( 'list.html' );

		if ( $params->get( 'show_poll' ) ) {
			// individual poll variables
			$tmpl->addObject( 'rows', $rows, 'row_' );
		}

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}

	function vote( $text, $link='', $show=0 ) {
		global $mainframe;

		$params = new mosParameters( '' );
		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );

		$tmpl =& pollScreens_front::createTemplate( 'vote.html' );

		$tmpl->addVar( 'body', 'text', 			$text );
		$tmpl->addVar( 'body', 'show', 			$show );
		$tmpl->addVar( 'body', 'link', 			$link );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}
}
?>