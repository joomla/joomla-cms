<?php
/**
* @version $Id$
* @package Joomla.Framework
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
**/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('tcpdf.config.lang.eng');
jimport('tcpdf.tcpdf');


/**
 * PDF Creator
 * 
 * Support file to display content as PDF using class from - TODO
 * 
 * @author David Gal <david@joomla.co.il>
 * @package Joomla.Framework
 * @since 1.1
 */

function doUtfPDF () {
	global $mainframe;

	$db = $mainframe->getDBO();
	$id = intval( mosGetParam( $_REQUEST, 'id', 1 ) );
	$row =& JModel::getInstance('content', $db );
	// $row = new mosContent( $database );
	$row->load( $id );

	$params = new JParameters( $row->attribs );
	$params->def( 'author', 	!$mainframe->getCfg( 'hideAuthor' ) );
	$params->def( 'createdate', !$mainframe->getCfg( 'hideCreateDate' ) );
	$params->def( 'modifydate', !$mainframe->getCfg( 'hideModifyDate' ) );
	$params->def( 'image', 1 );
	$params->def('introtext', 1);
	$params->set('intro_only', 0);
	
	// show/hides the intro text
	if ($params->get('introtext')) {
		$row->text = $row->introtext. ($params->get('intro_only') ? '' : chr(13).chr(13).$row->fulltext);
	} else {
		$row->text = $row->fulltext;
	}
	
	// process the new plugins
	JPluginHelper :: importGroup('content');
	$mainframe->triggerEvent('onPrepareContent', array (& $row, & $params, 0));
//	$text = trim(implode("\n", $results));
//				$results = $mainframe->triggerEvent('onAfterDisplayTitle', array (& $row, & $params, $page));
//			$text .= trim(implode("\n", $results));
//	
//		$onBeforeDisplayContent = $mainframe->triggerEvent('onBeforeDisplayContent', array (& $row, & $params, 0));
//		$text .= trim(implode("\n", $onBeforeDisplayContent));
		
	//create new PDF document (document units are set by default to millimeters)
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true); 
	
	// set document information
	$pdf->SetCreator("Joomla!");
	$pdf->SetTitle("Joomla generated PDF");
	$pdf->SetSubject($row->title);
	$pdf->SetKeywords($row->metakey);
	
	// prepare header lines
	$headerText = getHeaderText($row, $params);
	
	$pdf->SetHeaderData('', 0, $row->title, $headerText);
	
	//set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); //set image scale factor
	
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	
	$pdf->setLanguageArray($l); //set language items
	
	//initialize document
	$pdf->AliasNbPages();
	
	$pdf->AddPage();
	
//	$pdf->WriteHTML($row->introtext ."\n". $row->fulltext, true);
	$pdf->WriteHTML($row->text, true);
	
	
	//Close and output PDF document
	$pdf->Output("joomla.pdf", "I");

}


function getHeaderText( &$row, &$params ) {
	global $mainframe;

	$db = $mainframe->getDBO();
	$text = '';

	if ( $params->get( 'author' ) ) {
		// Display Author name

		//Find Author Name
		$users_rows =& JModel::getInstance('user', $db );
		$users_rows->load( $row->created_by );
		$row->author 	= $users_rows->name;
		$row->usertype 	= $users_rows->usertype;

		if ($row->usertype == 'administrator' || $row->usertype == 'superadministrator') {
			$text .= "\n";
			$text .=  JText::_( 'Written by' ) .' '. ( $row->created_by_alias ? $row->created_by_alias : $row->author );
		} else {
			$text .= "\n";
			$text .=  JText::_( 'Contributed by' ) .' '. ( $row->created_by_alias ? $row->created_by_alias : $row->author );
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
			$text 		.= JText::_( 'Last Updated' ) .' '. $mod_date;

		}
	}

//	$text .= "\n\n";

	return $text;
}

doUtfPDF ( );
?>
