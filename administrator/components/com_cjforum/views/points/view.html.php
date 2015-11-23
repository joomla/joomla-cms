<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewPoints extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	public function display ($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			CjForumHelper::addSubmenu('points');
		}
		
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->authors = $this->get('Authors');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			
			return false;
		}
		
		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}
		
		parent::display($tpl);
	}

	protected function addToolbar ()
	{
		$canDo = JHelperContent::getActions('com_cjforum');
		$user = JFactory::getUser();
		
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		
		JToolbarHelper::title(JText::_('COM_CJFORUM_POINTS_TITLE'), 'stack point');
		
		if ($canDo->get('core.create') || (count($user->getAuthorisedCategories('com_cjforum', 'core.create'))) > 0)
		{
			JToolbarHelper::addNew('point.add');
		}
		
		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own')))
		{
			JToolbarHelper::editList('point.edit');
		}
		
		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('points.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('points.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList('points.archive');
			JToolbarHelper::checkin('points.checkin');
		}
		
		if ($this->state->get('filter.published') == - 2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'points.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('points.trash');
		}
		
		// Add a batch button
		if ($user->authorise('core.create', 'com_cjforum') && $user->authorise('core.edit', 'com_cjforum') && $user->authorise('core.edit.state', 'com_cjforum'))
		{
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');
			
			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');
			
			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}
		
		if ($user->authorise('core.admin', 'com_cjforum'))
		{
			JToolbarHelper::preferences('com_cjforum');
		}
	}

	protected function getSortFields ()
	{
		return array(
				'a.state' => JText::_('JSTATUS'),
				'a.title' => JText::_('JGLOBAL_TITLE'),
				'access_level' => JText::_('JGRID_HEADING_ACCESS'),
				'a.created_by' => JText::_('JAUTHOR'),
				'a.user_id' => JText::_('COM_CJFORUM_USER'),
				'language' => JText::_('JGRID_HEADING_LANGUAGE'),
				'a.created' => JText::_('JDATE'),
				'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
