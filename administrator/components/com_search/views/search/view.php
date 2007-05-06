<?php
/**
* @version		$Id: view.php 6138 2007-01-02 03:44:18Z eddiea $
* @package		Joomla
* @subpackage	Search
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * @package		Joomla
 * @subpackage	Search
 * @since 1.5
 */
class searchViewSearch extends JView
{
	function display($tpl=null)
	{
		global $mainframe;

		JToolBarHelper::title( JText::_( 'Search Statistics' ), 'searchtext.png' );
		JToolBarHelper::custom( 'resetStats', 'delete.png', 'delete_f2.png', 'Reset', false );
		JToolBarHelper::preferences( 'com_search' );
		JToolBarHelper::help( 'screen.stats.searches' );

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('Search Statistics'));

		$limit 		= $mainframe->getUserStateFromRequest( 'global.list.limit',	'limit', $mainframe->getCfg('list_limit') );
		$limitstart		= $mainframe->getUserStateFromRequest( "com_search.limitstart", 'limitstart',	0 );

		$model = $this->getModel();
		$items = $model->getItems();
		$params = &JComponentHelper::getParams( 'com_search' );
		$enabled = $params->get('enabled');
		JHTML::_('behavior.tooltip');
		jimport('joomla.html.pagination');
		$pageNav = new JPagination( count($items), $limitstart, $limit );

		$showResults	= JRequest::getVar('search_results', 0);

		$search 		= $mainframe->getUserStateFromRequest( "com_search.search", 'search', '' );

		$this->assignRef('items', 	$items);
		$this->assignRef('enabled', $enabled);
		$this->assignRef('pageNav', $pageNav);
		$this->assignRef('showResults', $showResults);
		$this->assignRef('search', 	$search );
		$this->assignRef('lists',	$model->lists );

		parent::display($tpl);
	}

}
?>