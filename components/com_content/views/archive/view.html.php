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

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Content component
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class ContentViewArchive extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option, $Itemid;

		if (empty( $layout ))
		{
			// degrade to default
			$layout = 'list';
		}

		// Initialize some variables
		$user	  =& JFactory::getUser();
		$document =& JFactory::getDocument();
		$pathway  = & $mainframe->getPathWay();

		// Get the menu object of the active menu item
		$menu    =& JSiteHelper::getCurrentMenuItem();
		$params  =& JSiteHelper::getMenuParams();

		// Request variables
		$task 	    = JRequest::getVar('task');
		$limit		= JRequest::getVar('limit', $params->get('display_num', 20), '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$month		= JRequest::getVar( 'month' );
		$year		= JRequest::getVar( 'year' );
		$filter		= JRequest::getVar( 'filter' );

		// Get some data from the model
		$items = & $this->get( 'data'  );
		$total = & $this->get( 'total' );
	
		// Add item to pathway
		$pathway->addItem(JText::_('Archive'), '');

		$mainframe->setPageTitle($menu->name);

		$intro		= $params->def('intro', 	4);
		$leading	= $params->def('leading', 	1);
		$links		= $params->def('link', 		4);

		$params->def('title',			1);
		$params->def('hits',			$mainframe->getCfg('hits'));
		$params->def('author',			!$mainframe->getCfg('hideAuthor'));
		$params->def('date',			!$mainframe->getCfg('hideCreateDate'));
		$params->def('date_format',		JText::_('DATE_FORMAT_LC'));
		$params->def('navigation',		2);
		$params->def('display',			1);
		$params->def('display_num',		$mainframe->getCfg('list_limit'));
		$params->def('empty_cat',		0);
		$params->def('cat_items',		1);
		$params->def('cat_description',0);
		$params->def('pageclass_sfx',	'');
		$params->def('headings',		1);
		$params->def('filter',			1);
		$params->def('filter_type',		'title');
		$params->set('intro_only', 		1);

		if ($params->def('page_title', 1)) {
			$params->def('header', $menu->name);
		}

		$limit	= $intro + $leading + $links;
		$i		= $limitstart;

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$form = new stdClass();
		// Month Field
		$months = array(
			JHTML::makeOption( null, JText::_( 'Month' ) ),
			JHTML::makeOption( '01', JText::_( 'JAN' ) ),
			JHTML::makeOption( '02', JText::_( 'FEB' ) ),
			JHTML::makeOption( '03', JText::_( 'MAR' ) ),
			JHTML::makeOption( '04', JText::_( 'APR' ) ),
			JHTML::makeOption( '05', JText::_( 'MAY' ) ),
			JHTML::makeOption( '06', JText::_( 'JUN' ) ),
			JHTML::makeOption( '07', JText::_( 'JUL' ) ),
			JHTML::makeOption( '08', JText::_( 'AUG' ) ),
			JHTML::makeOption( '09', JText::_( 'SEP' ) ),
			JHTML::makeOption( '10', JText::_( 'OCT' ) ),
			JHTML::makeOption( '11', JText::_( 'NOV' ) ),
			JHTML::makeOption( '12', JText::_( 'DEC' ) )
		);
		$form->monthField	= JHTML::selectList( $months, 'month', 'size="1" class="inputbox"', 'value', 'text', $month );
		
		// Year Field
		$years = array();
		$years[] = JHTML::makeOption( null, JText::_( 'Year' ) );
		for ($i=2000; $i <= 2010; $i++) {
			$years[] = JHTML::makeOption( $i, $i );
		}
		$form->yearField	= JHTML::selectList( $years, 'year', 'size="1" class="inputbox"', 'value', 'text', $year );
		$form->limitField	= $pagination->getLimitBox('index.php?option=com_content&amp;view=archive&amp;month='.$month.'&amp;year='.$year.'&amp;limitstart='.$limitstart.'&amp;Itemid='.$Itemid);

		$this->assign('filter' 		, $filter);
		$this->assign('year'  		, $year);
		$this->assign('month' 		, $month);

		$this->assignRef('form'      , $form);
		$this->assignRef('items'     , $items);
		$this->assignRef('params'    , $params);
		$this->assignRef('user'      , $user);
		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
	}
}
?>