<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Content component
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewArchive extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		if (empty( $layout ))
		{
			// degrade to default
			$layout = 'list';
		}

		// Initialize some variables
		$user		=& JFactory::getUser();
		$document	=& JFactory::getDocument();
		$pathway	= & $mainframe->getPathWay();

		// Get the page/component configuration
		$state  = &$this->get('State');
		$params = &$state->get('parameters.menu');
		if (!is_object($params)) {
			$params = &JComponentHelper::getParams('com_content');
		}

		// Request variables
		$task 		= JRequest::getVar('task');
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

		$mainframe->setPageTitle($item->name);

		$params->def('filter',			1);
		$params->def('filter_type',		'title');

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		$form = new stdClass();
		// Month Field
		$months = array(
			JHTMLSelect::option( null, JText::_( 'Month' ) ),
			JHTMLSelect::option( '01', JText::_( 'JAN' ) ),
			JHTMLSelect::option( '02', JText::_( 'FEB' ) ),
			JHTMLSelect::option( '03', JText::_( 'MAR' ) ),
			JHTMLSelect::option( '04', JText::_( 'APR' ) ),
			JHTMLSelect::option( '05', JText::_( 'MAY' ) ),
			JHTMLSelect::option( '06', JText::_( 'JUN' ) ),
			JHTMLSelect::option( '07', JText::_( 'JUL' ) ),
			JHTMLSelect::option( '08', JText::_( 'AUG' ) ),
			JHTMLSelect::option( '09', JText::_( 'SEP' ) ),
			JHTMLSelect::option( '10', JText::_( 'OCT' ) ),
			JHTMLSelect::option( '11', JText::_( 'NOV' ) ),
			JHTMLSelect::option( '12', JText::_( 'DEC' ) )
		);
		$form->monthField	= JHTMLSelect::genericList( $months, 'month', 'size="1" class="inputbox"', 'value', 'text', $month );

		// Year Field
		$years = array();
		$years[] = JHTMLSelect::option( null, JText::_( 'Year' ) );
		for ($i=2000; $i <= 2010; $i++) {
			$years[] = JHTMLSelect::option( $i, $i );
		}
		$form->yearField	= JHTMLSelect::genericList( $years, 'year', 'size="1" class="inputbox"', 'value', 'text', $year );
		$form->limitField	= $pagination->getLimitBox();

		$this->assign('filter' 		, $filter);
		$this->assign('year'  		, $year);
		$this->assign('month' 		, $month);

		$this->assignRef('form',		$form);
		$this->assignRef('items',		$items);
		$this->assignRef('params',		$params);
		$this->assignRef('user',		$user);
		$this->assignRef('pagination',	$pagination);
		
		parent::display($tpl);
	}
}
?>
