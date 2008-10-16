<?php
/**
* @version		$Id: $
* @package		Joomla
* @subpackage	Content
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Articles component
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.0
 */
class ContentViewArticles extends JView
{
	protected $filter;
	protected $pagination;
	protected $rows;
	protected $redirect;

	function display($tpl = null)
	{
		$rows		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
		$filter		= & $this->get( 'Filter' );

		$redirect			= $filter->sectionid;

		JToolBarHelper::title( JText::_( 'Article Manager' ), 'addedit.png' );
		if ($filter->state == 'A' || $filter->state == NULL) {
			JToolBarHelper::unarchiveList();
		}
		if ($filter->state != 'A') {
			JToolBarHelper::archiveList();
		}
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::customX( 'movesect', 'move.png', 'move_f2.png', 'Move' );
		JToolBarHelper::customX( 'copy', 'copy.png', 'copy_f2.png', 'Copy' );
		JToolBarHelper::trash();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences('com_content', '550');
		JToolBarHelper::help( 'screen.content' );

		JSubMenuHelper::addEntry(JText::_('Articles'), 'index.php?option=com_content', true );
		JSubMenuHelper::addEntry(JText::_('Front Page'), 'index.php?option=com_content&controller=frontpage' );

		$this->assignRef('redirect',	$redirect);
		$this->assignRef('rows',		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('filter',		$filter);

		parent::display($tpl);
	}
}
