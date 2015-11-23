<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewRanks extends JViewLegacy
{

	protected $items;

	protected $pagination;

	protected $state;

	public function display ($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			CjForumHelper::addSubmenu('ranks');
		}
		
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
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
		$canDo = JHelperContent::getActions('com_cjforum', 'category', $this->state->get('filter.category_id'));
		$user = JFactory::getUser();
		
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		
		JToolbarHelper::title(JText::_('COM_CJFORUM_RANKS_TITLE'), 'stack rank');
		
		if ($canDo->get('core.create') || (count($user->getAuthorisedCategories('com_cjforum', 'core.create'))) > 0)
		{
			JToolbarHelper::addNew('rank.add');
		}
		
		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own')))
		{
			JToolbarHelper::editList('rank.edit');
		}
		
		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('ranks.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('ranks.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::custom('ranks.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true);
			JToolbarHelper::archiveList('ranks.archive');
			JToolbarHelper::checkin('ranks.checkin');
		}
		
		if ($this->state->get('filter.published') == - 2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'ranks.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('ranks.trash');
		}
		
		// Add a batch button
		if ($user->authorise('core.create', 'com_cjforum') && $user->authorise('core.edit', 'com_cjforum') &&
				 $user->authorise('core.edit.state', 'com_cjforum'))
		{
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');
			
			// Instantiate a new JLayoutFile instance and render the batch
			// button
			$layout = new JLayoutFile('joomla.toolbar.batch');
			
			$dhtml = $layout->render(array(
					'title' => $title
			));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::custom('ranks.sync', 'refresh.png', 'refresh.png', 'COM_CJFORUM_TOOLBAR_SYNC', false);
			JToolbarHelper::preferences('com_cjforum');
		}
		
		JToolbarHelper::help('JHELP_CJFORUM_RANK_MANAGER');
	}

	protected function getSortFields ()
	{
		return array(
				'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
				'a.state' => JText::_('JSTATUS'),
				'a.title' => JText::_('JGLOBAL_TITLE'),
				'category_title' => JText::_('JCATEGORY'),
				'access_level' => JText::_('JGRID_HEADING_ACCESS'),
				'a.created_by' => JText::_('JAUTHOR'),
				'language' => JText::_('JGRID_HEADING_LANGUAGE'),
				'a.created' => JText::_('JDATE'),
				'a.id' => JText::_('JGRID_HEADING_ID'),
				'a.featured' => JText::_('JFEATURED')
		);
	}
}
