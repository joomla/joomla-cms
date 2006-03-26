<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('tcpdf.config.lang.eng');
jimport('tcpdf.config.tcpdf_config');
jimport('tcpdf.tcpdf');

/**
 * PDF View class for the Content component
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JViewPDF extends JView
{
	/**
	 * Name of the view.
	 * 
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'PDF';

	/**
	 * Name of the view.
	 * 
	 * @access	private
	 * @var		string
	 */
	function display()
	{
		global $l;

		// Initialize some variables
		$app		= & $this->get( 'Application' );
		$user		= & $app->getUser();
		$menu		= & $this->get( 'Menu' );
		$article		= & $this->get( 'Article' );
		$Itemid		= $menu->id;
		$params 	= & new JParameter($article->attribs);

		$params->def('author', !$mainframe->getCfg('hideAuthor'));
		$params->def('createdate', !$mainframe->getCfg('hideCreateDate'));
		$params->def('modifydate', !$mainframe->getCfg('hideModifyDate'));
		$params->def('image', 1);
		$params->def('introtext', 1);
		$params->set('intro_only', 0);

		// show/hides the intro text
		if ($params->get('introtext'))
		{
			$article->text = $article->introtext. ($params->get('intro_only') ? '' : chr(13).chr(13).$article->fulltext);
		}
		else
		{
			$article->text = $article->fulltext;
		}

		// process the new plugins
		JPluginHelper::importPlugin('content');
		$mainframe->triggerEvent('onPrepareContent', array (& $article, & $params, 0));
		//	$text = trim(implode("\n", $results));
		//				$results = $mainframe->triggerEvent('onAfterDisplayTitle', array (& $article, & $params, $page));
		//			$text .= trim(implode("\n", $results));
		//	
		//		$onBeforeDisplayContent = $mainframe->triggerEvent('onBeforeDisplayContent', array (& $article, & $params, 0));
		//		$text .= trim(implode("\n", $onBeforeDisplayContent));

		//create new PDF document (document units are set by default to millimeters)
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);

		// set document information
		$pdf->SetCreator("Joomla!");
		$pdf->SetTitle("Joomla generated PDF");
		$pdf->SetSubject($article->title);
		$pdf->SetKeywords($article->metakey);

		// prepare header lines
		$headerText = $this->_getHeaderText($article, $params);

		$pdf->SetHeaderData('', 0, $article->title, $headerText);

		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); //set image scale factor

		$pdf->setHeaderFont(Array (PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array (PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		$pdf->setLanguageArray($l); //set language items

		//initialize document
		$pdf->AliasNbPages();

		$pdf->AddPage();

		//	$pdf->WriteHTML($article->introtext ."\n". $article->fulltext, true);
		$pdf->WriteHTML($article->text, true);

		//Close and output PDF document
		$pdf->Output("joomla.pdf", "I");
	}

	function _getHeaderText(& $article, & $params)
	{
		// Initialize some variables
		$db = & $this->get('DBO');
		$text = '';

		if ($params->get('author')) {
			// Display Author name
			if ($article->usertype == 'administrator' || $article->usertype == 'superadministrator') {
				$text .= "\n";
				$text .= JText::_('Written by').' '. ($article->created_by_alias ? $article->created_by_alias : $article->author);
			} else {
				$text .= "\n";
				$text .= JText::_('Contributed by').' '. ($article->created_by_alias ? $article->created_by_alias : $article->author);
			}
		}

		if ($params->get('createdate') && $params->get('author')) {
			// Display Separator
			$text .= "\n";
		}

		if ($params->get('createdate')) {
			// Display Created Date
			if (intval($article->created)) {
				$create_date = mosFormatDate($article->created);
				$text .= $create_date;
			}
		}

		if ($params->get('modifydate') && ($params->get('author') || $params->get('createdate'))) {
			// Display Separator
			$text .= "\n";
		}

		if ($params->get('modifydate')) {
			// Display Modified Date
			if (intval($article->modified)) {
				$mod_date = mosFormatDate($article->modified);
				$text .= JText::_('Last Updated').' '.$mod_date;
			}
		}
		//	$text .= "\n\n";
		return $text;
	}
}
?>