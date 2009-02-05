<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Content
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * HTML View class for the Articles component
 *
 * @static
 * @package		Joomla.Administrator
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
		$rows		= & $this->get('Data');
		$total		= & $this->get('Total');
		$pagination = & $this->get('Pagination');
		$filter		= & $this->get('Filter');

		$redirect			= $filter->sectionid;

		JToolBarHelper::title(JText::_('Article Manager'), 'addedit.png');
		if ($filter->state == 'A' || $filter->state == NULL) {
			JToolBarHelper::unarchiveList();
		}
		if ($filter->state != 'A') {
			JToolBarHelper::archiveList();
		}
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::customX('movesect', 'move.png', 'move_f2.png', 'Move');
		JToolBarHelper::customX('copy', 'copy.png', 'copy_f2.png', 'Copy');
		JToolBarHelper::trash();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences('com_content', '550');
		JToolBarHelper::help('screen.content');

		JSubMenuHelper::addEntry(JText::_('Articles'), 'index.php?option=com_content', true);
		JSubMenuHelper::addEntry(JText::_('Front Page'), 'index.php?option=com_content&controller=frontpage');

		$this->assignRef('redirect',	$redirect);
		$this->assignRef('rows',		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('filter',		$filter);

		parent::display($tpl);
	}
}
