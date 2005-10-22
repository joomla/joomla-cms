<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* Created by Phil Taylor me@phil-taylor.com
* Support file to display PDF Text Only using class from - http://www.ros.co.nz/pdf/readme.pdf
* HTMLDoc is available from: http://www.easysw.com/htmldoc and needs installing on the server for better HTML to PDF conversion
**/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

function dofreePDF ( $database ) {
	global $mosConfig_live_site, $mosConfig_sitename, $mosConfig_offset;
	global $mainframe;

	include( 'includes/class.ezpdf.php' );

	$id = intval( mosGetParam( $_REQUEST, 'id', 1 ) );
	$row = new mosContent( $database );
	$row->load( $id );

	$params = new mosParameters( $row->attribs );
	$params->def( 'author', 	!$mainframe->getCfg( 'hideAuthor' ) );
	$params->def( 'createdate', !$mainframe->getCfg( 'hideCreateDate' ) );
	$params->def( 'modifydate', !$mainframe->getCfg( 'hideModifyDate' ) );

	$row->fulltext 	= pdfCleaner( $row->fulltext );
	$row->introtext = pdfCleaner( $row->introtext );

	$pdf = new Cezpdf( 'a4', 'P' );  //A4 Portrait
	$pdf -> ezSetCmMargins( 2, 1.5, 1, 1);
	$pdf->selectFont( './fonts/Helvetica.afm' ); //choose font

	$all = $pdf->openObject();
	$pdf->saveState();
	$pdf->setStrokeColor( 0, 0, 0, 1 );

	// footer
	$pdf->addText( 250, 822, 6, $mosConfig_sitename );
	$pdf->line( 10, 40, 578, 40 );
	$pdf->line( 10, 818, 578, 818 );
	$pdf->addText( 30, 34, 6, $mosConfig_live_site );
	$pdf->addText( 250, 34, 6, 'Powered by Joomla!' );
	$pdf->addText( 450, 34, 6, 'Generated: '. date( 'j F, Y, H:i', time() + $mosConfig_offset * 60 * 60 ) );

	$pdf->restoreState();
	$pdf->closeObject();
	$pdf->addObject( $all, 'all' );
	$pdf->ezSetDy( 30 );

	$txt1 = $row->title;
	$pdf->ezText( $txt1, 14 );

	$txt2 = AuthorDateLine( $row, $params );

	$pdf->ezText( $txt2, 8 );

	$txt3 = $row->introtext ."\n". $row->fulltext;
	$pdf->ezText( $txt3, 10 );

	$pdf->ezStream();
}

function decodeHTML( $string ) {
	$string = strtr( $string, array_flip(get_html_translation_table( HTML_ENTITIES ) ) );
	$string = preg_replace( "/&#([0-9]+);/me", "chr('\\1')", $string );

	return $string;
}

function get_php_setting ($val ) {
	$r = ( ini_get( $val ) == '1' ? 1 : 0 );

	return $r ? 'ON' : 'OFF';
}

function pdfCleaner( $text ) {
	// Ugly but needed to get rid of all the stuff the PDF class cant handle

	$text = str_replace( '<p>', 			"\n\n", 	$text );
	$text = str_replace( '<P>', 			"\n\n", 	$text );
	$text = str_replace( '<br />', 			"\n", 		$text );
	$text = str_replace( '<br>', 			"\n", 		$text );
	$text = str_replace( '<BR />', 			"\n", 		$text );
	$text = str_replace( '<BR>', 			"\n", 		$text );
	$text = str_replace( '<li>', 			"\n - ", 	$text );
	$text = str_replace( '<LI>', 			"\n - ", 	$text );
	$text = str_replace( '{mosimage}', 		'', 		$text );
	$text = str_replace( '{mospagebreak}', 	'',			$text );

	$text = strip_tags( $text );
	$text = decodeHTML( $text );

	return $text;
}

function AuthorDateLine( &$row, &$params ) {
	global $database;

	$text = '';

	if ( $params->get( 'author' ) ) {
		// Display Author name

		//Find Author Name
		$users_rows = new mosUser( $database );
		$users_rows->load( $row->created_by );
		$row->author 	= $users_rows->name;
		$row->usertype 	= $users_rows->usertype;

		if ($row->usertype == 'administrator' || $row->usertype == 'superadministrator') {
			$text .= "\n";
			$text .=  _WRITTEN_BY .' '. ( $row->created_by_alias ? $row->created_by_alias : $row->author );
		} else {
			$text .= "\n";
			$text .=  _AUTHOR_BY .' '. ( $row->created_by_alias ? $row->created_by_alias : $row->author );
		}
	}

	if ( $params->get( 'createdate' ) && $params->get( 'author' ) ) {
		// Display Separator
		$text .= "\n";
	}

	if ( $params->get( 'createdate' ) ) {
		// Display Created Date
		if ( intval( $row->created ) ) {
			$create_date 	= mosFormatDate( $row->created );
			$text .= $create_date;
		}
	}

	if ( $params->get( 'modifydate' ) && ( $params->get( 'author' ) || $params->get( 'createdate' ) ) ) {
		// Display Separator
		$text .= "\n";
	}

	if ( $params->get( 'modifydate' ) ) {
		// Display Modified Date
		if ( intval( $row->modified ) ) {
			$mod_date 	= mosFormatDate( $row->modified );
			$text 		.= _LAST_UPDATED .' '. $mod_date;

		}
	}

	$text .= "\n\n";

	return $text;
}

dofreePDF ( $database );
?>