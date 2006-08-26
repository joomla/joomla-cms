<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.view');

/**
 * HTML Article View class for the Content component
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class ContentViewArticle extends JView
{
	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Article';

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function display($layout)
	{
		$document =& JFactory::getDocument();
		
		switch ($document->getType())
		{
			case 'pdf':
				$this->_displayPDF();
				break;
			default:
				$this->_displayHTML();
				break;
		}
	}

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function _displayHTML()
	{
		global $mainframe, $Itemid;
		
		$user		=& JFactory::getUser();
		$document   =& JFactory::getDocument();
		$dispatcher	=& JEventDispatcher::getInstance();
		$pathway    =& $mainframe->getPathWay();

		// Initialize variables
		$article	=& $this->get('Article');
		$params		=& $article->parameters;
	
		$linkOn   = null;
		$linkText = null;
		
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		// Handle BreadCrumbs
		
		if (!empty ($Itemid)) 
		{
			// Section
			if (!empty ($article->section)) {
				$pathway->addItem($article->section, sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$article->sectionid.'&amp;Itemid='.$Itemid));
			}
			// Category
			if (!empty ($article->category)) {
				$pathway->addItem($article->category, sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$article->sectionid.'&amp;id='.$article->catid.'&amp;Itemid='.$Itemid));
			}
		}
		// Article
		$pathway->addItem($article->title, '');

		// Handle Page Title
		$document->setTitle($article->title);

		// Handle metadata
		$document->setDescription( $article->metadesc );
		$document->setMetadata('keywords', $article->metakey);

		// If there is a pagebreak heading or title, add it to the page title
		if (isset ($article->page_title)) {
			$document->setTitle($article->title.' '.$article->page_title);
		}
		
		// Create a user access object for the current user
		$access = new stdClass();
		$access->canEdit	= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn	= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish	= $user->authorize('action', 'publish', 'content', 'all');

		// Process the content plugins
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onPrepareContent', array (& $article, & $params, $limitstart));
		
		if ($params->get('readmore') || $params->get('link_titles')) 
		{
			if ($params->get('intro_only')) 
			{
				// Check to see if the user has access to view the full article
				if ($article->access <= $user->get('gid')) 
				{
					$Itemid = JContentHelper::getItemid($article->id);
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=".$article->id."&amp;Itemid=".$Itemid);

					if (@$article->readmore) {
						// text for the readmore link
						$linkText = JText::_('Read more...');
					}
				} 
				else 
				{
					$linkOn = sefRelToAbs("index.php?option=com_registration&amp;task=register");

					if (@$article->readmore) {
						// text for the readmore link if accessible only if registered
						$linkText = JText::_('Register to read more...');
					}
				}
			}
		}
		
		$data = new stdClass();
		$data->access = $access;
	
		$article->readmore_link = $linkOn;
		$article->readmore_text = $linkText;
		
		$article->print_link = JURI::base().'index2.php?option=com_content&amp;task=view&amp;id='.$article->id.'&amp;Itemid='.$Itemid.'&amp;pop=1&amp;page='.@ $limitstart;

		$article->event = new stdClass();
		$results = $dispatcher->trigger('onAfterDisplayTitle', array ($article, &$params, $limitstart));
		$article->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onBeforeDisplayContent', array (& $article, & $params, $limitstart));
		$article->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onAfterDisplayContent', array (& $article, & $params, $limitstart));
		$article->event->afterDisplayContent = trim(implode("\n", $results));
		
		$this->set('article', $article);
		$this->set('params' , $params);
		$this->set('data'   , $data);
		$this->set('user'   , $user);
		
		$this->_loadTemplate('article');
	}

	function edit()
	{
		global $mainframe, $Itemid;

		// Initialize variables
		$document =& JFactory::getDocument();
		$user	  =& JFactory::getUser();
		
		$pathway =& $mainframe->getPathWay();

		// At some point in the future this will come from a request object
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$Returnid	= JRequest::getVar('Returnid', $Itemid, '', 'int');

		// Add the Calendar includes to the document <head> section
		$document->addStyleSheet('includes/js/calendar/calendar-mos.css');
		$document->addScript('includes/js/calendar/calendar_mini.js');
		$document->addScript('includes/js/calendar/lang/calendar-en.js');

		// Get the article from the model
		$article	=& $this->get('Article');
		$params		= $article->parameters;

		// Get the lists
		$lists = $this->_buildEditLists();

		// Load the JEditor object
		$editor =& JFactory::getEditor();

		// Ensure the row data is safe html
		mosMakeHtmlSafe($article);

		// Build the page title string
		$title = $article->id ? JText::_('Edit') : JText::_('New');

		// Set page title
		$document->setTitle($title);

		// Add pathway item
		$pathway->addItem($title, '');
		
		// Unify the introtext and fulltext fields and separated the fields by the {readmore} tag
		if (JString::strlen($article->fulltext) > 1) {
			$article->text = $article->introtext."<hr id=\"system-readmore\" />".$article->fulltext;
		} else {
			$article->text = $article->introtext;
		}
		
		$this->set('article', $article);
		$this->set('params' , $params);
		$this->set('lists'  , $lists);
		$this->set('editor' , $editor);
		$this->set('user'   , $user);
		
		$this->_loadTemplate('edit');
	}

	function _buildEditLists()
	{
		// Get the article and database connector from the model
		$article = & $this->get('Article');
		$db 	 = & JFactory::getDBO();

		// Select List: Categories
		$lists['catid'] = mosAdminMenus::ComponentCategory('catid', $article->sectionid, intval($article->catid));

		// Select List: Category Ordering
		$query = "SELECT ordering AS value, title AS text"."\n FROM #__content"."\n WHERE catid = $article->catid"."\n ORDER BY ordering";
		$lists['ordering'] = mosAdminMenus::SpecificOrdering($article, $article->id, $query, 1);

		// Radio Buttons: Should the article be published
		$lists['state'] = mosHTML::yesnoradioList('state', '', $article->state);

		// Radio Buttons: Should the article be added to the frontpage
		$query = "SELECT content_id"."\n FROM #__content_frontpage"."\n WHERE content_id = $article->id";
		$db->setQuery($query);
		$article->frontpage = $db->loadResult();

		$lists['frontpage'] = mosHTML::yesnoradioList('frontpage', '', (boolean) $article->frontpage);

		// Select List: Group Access
		$lists['access'] = mosAdminMenus::Access($article);

		return $lists;
	}

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function _displayPDF()
	{
		global $mainframe;

		jimport('tcpdf.tcpdf');
		
		$dispatcher	=& JEventDispatcher::getInstance();

		// Initialize some variables
		$article	= & $this->get( 'Article' );
		$params 	= & $article->parameters;

		$params->def('introtext', 1);
		$params->set('intro_only', 0);

		// show/hides the intro text
		if ($params->get('introtext')) {
			$article->text = $article->introtext. ($params->get('intro_only') ? '' : chr(13).chr(13).$article->fulltext);
		} else {
			$article->text = $article->fulltext;
		}

		// process the new plugins
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onPrepareContent', array (& $article, & $params, 0));

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

		//initialize document
		$pdf->AliasNbPages();

		$pdf->AddPage();

		$pdf->WriteHTML($article->text, true);

		//Close and output PDF document
		$pdf->Output("joomla.pdf", "I");
	}

	function _getHeaderText(& $article, & $params)
	{
		// Initialize some variables
		$db = & JFactory::getDBO();
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
			$text .= " - ";
		}

		if ($params->get('modifydate')) {
			// Display Modified Date
			if (intval($article->modified)) {
				$mod_date = mosFormatDate($article->modified);
				$text .= JText::_('Last Updated').' '.$mod_date;
			}
		}
		return $text;
	}
}
?>