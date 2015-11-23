<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewActivities extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	public function display ($tpl = null)
	{
		CjForumHelper::addSubmenu('activities');
		
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
		
		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		
		parent::display($tpl);
	}

	protected function addToolbar ()
	{
		$canDo = JHelperContent::getActions('com_cjforum');
		$user = JFactory::getUser();
		
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		
		JToolbarHelper::title(JText::_('COM_CJFORUM_ACTIVITIES_TITLE'), 'stack activities');
		
		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own')))
		{
			JToolbarHelper::editList('activity.edit');
		}
		
		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('activities.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('activities.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::custom('activities.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true);
			JToolbarHelper::archiveList('activities.archive');
			JToolbarHelper::checkin('activities.checkin');
		}
		
		if ($this->state->get('filter.published') == - 2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'activities.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('activities.trash');
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
			
			$dhtml = $layout->render(array('title' => $title));
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
				'a.activity_type' => JText::_('COM_CJFORUM_ACTIVITY_TYPE'),
				'access_level' => JText::_('JGRID_HEADING_ACCESS'),
				'a.created_by' => JText::_('JAUTHOR'),
				'language' => JText::_('JGRID_HEADING_LANGUAGE'),
				'a.created' => JText::_('JDATE'),
				'a.id' => JText::_('JGRID_HEADING_ID'),
				'a.featured' => JText::_('JFEATURED')
		);
	}
}
